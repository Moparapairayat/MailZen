import { spawnSync } from 'node:child_process';
import fs from 'node:fs/promises';
import os from 'node:os';
import path from 'node:path';

const projectRoot = process.cwd();

function argValue(flag) {
    const idx = process.argv.indexOf(flag);
    if (idx === -1) return undefined;
    return process.argv[idx + 1];
}

function hasFlag(flag) {
    return process.argv.includes(flag);
}

const zipEnabled = hasFlag('--zip');
const zipClean = hasFlag('--clean');
const outDir = path.resolve(projectRoot, argValue('--out') ?? 'client-build');

function run(command, args, options = {}) {
    const result = spawnSync(command, args, {
        stdio: 'inherit',
        shell: false,
        ...options,
    });

    if (result.error) throw result.error;
    if (result.status !== 0) {
        throw new Error(`${command} ${args.join(' ')} failed with exit code ${result.status}`);
    }
}

function runCapture(command, args, options = {}) {
    const result = spawnSync(command, args, {
        stdio: ['ignore', 'pipe', 'inherit'],
        shell: false,
        encoding: 'utf8',
        ...options,
    });

    if (result.error) throw result.error;
    if (result.status !== 0) {
        throw new Error(`${command} ${args.join(' ')} failed with exit code ${result.status}`);
    }

    return (result.stdout ?? '').trim();
}

async function pathExists(p) {
    try {
        await fs.access(p);
        return true;
    } catch {
        return false;
    }
}

async function ensureDir(p) {
    await fs.mkdir(p, { recursive: true });
}

async function safeReadFile(p) {
    try {
        return await fs.readFile(p, 'utf8');
    } catch {
        return null;
    }
}

function extractVersionFromMailZenConfig(configContents) {
    if (!configContents) return null;

    const directMatch = configContents.match(/['"]version['"]\s*=>\s*['"]([^'"]+)['"]/);
    if (directMatch && directMatch[1]) return directMatch[1].trim();

    const envFallbackMatch = configContents.match(
        /['"]version['"]\s*=>\s*env\([^,]+,\s*['"]([^'"]+)['"]\s*\)/,
    );
    if (envFallbackMatch && envFallbackMatch[1]) return envFallbackMatch[1].trim();

    return null;
}

async function patchFile(p, transform) {
    const current = await safeReadFile(p);
    if (current === null) return;
    const next = transform(current);
    if (next !== current) {
        await fs.writeFile(p, next, 'utf8');
    }
}

async function copyPath(src, dest, shouldCopy) {
    let stat;
    try {
        stat = await fs.lstat(src);
    } catch (err) {
        if (err && typeof err === 'object' && err.code === 'ENOENT') return;
        throw err;
    }

    if (!shouldCopy(src, stat)) return;

    if (stat.isSymbolicLink()) {
        let link;
        try {
            link = await fs.readlink(src);
        } catch (err) {
            if (err && typeof err === 'object' && err.code === 'ENOENT') return;
            throw err;
        }
        await ensureDir(path.dirname(dest));
        await fs.symlink(link, dest);
        return;
    }

    if (stat.isDirectory()) {
        await ensureDir(dest);
        let entries;
        try {
            entries = await fs.readdir(src);
        } catch (err) {
            if (err && typeof err === 'object' && err.code === 'ENOENT') return;
            throw err;
        }
        await Promise.all(
            entries.map((name) => copyPath(path.join(src, name), path.join(dest, name), shouldCopy)),
        );
        return;
    }

    await ensureDir(path.dirname(dest));
    try {
        await fs.copyFile(src, dest);
    } catch (err) {
        if (err && typeof err === 'object' && err.code === 'ENOENT') return;
        throw err;
    }
}

function shouldCopyFactory() {
    const ignored = new Set(['.git', '.cursor', 'node_modules', '.vite', 'client-build']);

    return (src, stat) => {
        const rel = path.relative(projectRoot, src);
        if (rel === '') return true;

        const first = rel.split(path.sep)[0];
        if (ignored.has(first)) return false;

        if (first === 'public') return false;

        if (stat.isFile() && path.extname(src).toLowerCase() === '.zip') return false;
        if (stat.isFile() && /^mailzen_build_.*\.zip$/i.test(path.basename(src))) return false;

        if (stat.isFile()) {
            const base = path.basename(src);
            if (base === '.env') return false;
            if (base.startsWith('.env.') && base !== '.env.example') return false;
        }

        return true;
    };
}

async function main() {
    await fs.rm(outDir, { recursive: true, force: true });
    await ensureDir(outDir);

    const vendorAutoload = path.join(projectRoot, 'vendor', 'autoload.php');
    if (!(await pathExists(vendorAutoload))) {
        run('composer', ['install', '--no-dev', '--optimize-autoloader']);
    }

    run('npm', ['run', 'build']);

    const manifestSrc = path.join(projectRoot, 'public', 'build', 'manifest.json');
    const assetsSrc = path.join(projectRoot, 'public', 'build', 'assets');

    if (!(await pathExists(manifestSrc))) {
        throw new Error('Vite manifest not found at public/build/manifest.json after build');
    }

    if (!(await pathExists(assetsSrc))) {
        throw new Error('Vite assets folder not found at public/build/assets after build');
    }

    const shouldCopy = shouldCopyFactory();

    const entries = await fs.readdir(projectRoot);
    await Promise.all(
        entries.map((name) =>
            copyPath(path.join(projectRoot, name), path.join(outDir, name), shouldCopy),
        ),
    );

    const publicBuildDir = path.join(outDir, 'public', 'build');
    await ensureDir(publicBuildDir);

    await fs.copyFile(manifestSrc, path.join(publicBuildDir, 'manifest.json'));

    const rootBuildDir = path.join(outDir, 'build');
    await ensureDir(rootBuildDir);
    await fs.copyFile(manifestSrc, path.join(rootBuildDir, 'manifest.json'));

    const buildAssetsDest = path.join(publicBuildDir, 'assets');
    await ensureDir(buildAssetsDest);
    await fs.cp(assetsSrc, buildAssetsDest, { recursive: true });

    const rootBuildAssetsDest = path.join(rootBuildDir, 'assets');
    await ensureDir(rootBuildAssetsDest);
    await fs.cp(assetsSrc, rootBuildAssetsDest, { recursive: true });

    const publicIndexSrc = path.join(projectRoot, 'public', 'index.php');
    const indexDest = path.join(outDir, 'index.php');

    const indexContents = await fs.readFile(publicIndexSrc, 'utf8');
    const updatedIndex = indexContents
        .replace("require __DIR__.'/../vendor/autoload.php';", "require __DIR__.'/vendor/autoload.php';")
        .replace("(require_once __DIR__.'/../bootstrap/app.php')", "(require_once __DIR__.'/bootstrap/app.php')")
        .replace("__DIR__.'/../storage/framework/maintenance.php'", "__DIR__.'/storage/framework/maintenance.php'");

    await fs.writeFile(indexDest, updatedIndex, 'utf8');

    const htaccessDest = path.join(outDir, '.htaccess');
    await fs.copyFile(path.join(projectRoot, '.htaccess'), htaccessDest);

    run('php', [
        'artisan',
        'translations:export',
        '--locale',
        'en',
        '--dir',
        path.join(outDir, 'language'),
        '--format',
        'json',
        '--force',
    ], { cwd: projectRoot });

    if (zipEnabled) {
        const zipFlat = hasFlag('--zip-flat');
        const zipFolderName = zipFlat ? null : (argValue('--zip-folder') ?? 'mailzen');

        let version = null;

        const mailzenConfig = await safeReadFile(path.join(projectRoot, 'config', 'mailzen.php'));
        version = extractVersionFromMailZenConfig(mailzenConfig);

        if (!version) {
            const pkgJson = await safeReadFile(path.join(projectRoot, 'package.json'));
            if (pkgJson) {
                try {
                    const parsed = JSON.parse(pkgJson);
                    if (typeof parsed?.version === 'string' && parsed.version.trim() !== '') {
                        version = parsed.version.trim();
                    }
                } catch {
                    // ignore
                }
            }
        }

        if (!version) version = '0.0.0';

        const envSha =
            process.env.GIT_SHA ??
            process.env.COMMIT_SHA ??
            process.env.GITHUB_SHA ??
            process.env.VERCEL_GIT_COMMIT_SHA ??
            process.env.CF_PAGES_COMMIT_SHA ??
            process.env.RAILWAY_GIT_COMMIT_SHA;

        let hash = typeof envSha === 'string' && envSha.trim() !== '' ? envSha.trim() : '';
        if (!hash) {
            try {
                hash = runCapture('git', ['rev-parse', '--short', 'HEAD'], { cwd: projectRoot });
            } catch {
                hash = 'nogit';
            }
        }
        hash = hash.replace(/[^a-zA-Z0-9]/g, '').slice(0, 12) || 'nogit';

        const zipFileName =
            argValue('--zip-file') ?? `mailzen_build_${version.replace(/[^a-zA-Z0-9._-]/g, '')}_${hash}.zip`;
        const zipPath = path.resolve(projectRoot, zipFileName);

        const tmpDir = await fs.mkdtemp(path.join(os.tmpdir(), 'mailzen-client-zip-'));
        const stagedDir = zipFlat ? tmpDir : path.join(tmpDir, zipFolderName);

        await fs.rm(zipPath, { force: true });
        await ensureDir(stagedDir);
        await fs.cp(outDir, stagedDir, { recursive: true });

        const stagedHtaccessPath = path.join(stagedDir, '.htaccess');
        if (!(await pathExists(stagedHtaccessPath))) {
            throw new Error('Missing .htaccess in staged client zip directory');
        }

        const stagedHtaccessContents = await fs.readFile(stagedHtaccessPath, 'utf8');
        if (!stagedHtaccessContents.includes('FilesMatch "^\\\\."')) {
            throw new Error('Staged .htaccess does not contain dotfile deny rule (FilesMatch "^\\.")');
        }

        await fs.rm(path.join(stagedDir, 'storage', 'logs'), { recursive: true, force: true });
        await fs.rm(path.join(stagedDir, 'storage', 'framework', 'sessions'), { recursive: true, force: true });
        await fs.rm(path.join(stagedDir, 'storage', 'framework', 'views'), { recursive: true, force: true });
        await fs.rm(path.join(stagedDir, 'storage', 'framework', 'cache'), { recursive: true, force: true });
        await fs.rm(path.join(stagedDir, 'storage', 'app', 'private'), { recursive: true, force: true });

        await ensureDir(path.join(stagedDir, 'storage', 'logs'));
        await ensureDir(path.join(stagedDir, 'storage', 'framework', 'sessions'));
        await ensureDir(path.join(stagedDir, 'storage', 'framework', 'views'));
        await ensureDir(path.join(stagedDir, 'storage', 'framework', 'cache'));
        await ensureDir(path.join(stagedDir, 'storage', 'app', 'private'));

        run('zip', ['-r', zipPath, zipFlat ? '.' : zipFolderName], { cwd: tmpDir });

        console.log(`Created ${zipFileName}`);

        const masterZipFileName = argValue('--master-zip-file') ?? 'MailZen-master.zip';
        const masterZipPath = path.resolve(projectRoot, masterZipFileName);

        await fs.rm(masterZipPath, { force: true });

        const masterTmpDir = await fs.mkdtemp(path.join(os.tmpdir(), 'mailzen-master-zip-'));

        await fs.copyFile(zipPath, path.join(masterTmpDir, path.basename(zipPath)));
        await fs.writeFile(path.join(masterTmpDir, 'docs.txt'), 'https://getmailpurse.com/docs\n', 'utf8');

        const wpBaseDir = path.join(projectRoot, 'wordpress-plugin');
        const wpPluginDir = path.join(wpBaseDir, 'mailzen-integration');

        if (!(await pathExists(wpPluginDir))) {
            throw new Error('Missing wordpress-plugin/mailzen-integration directory');
        }

        const wpPluginZipPath = path.join(masterTmpDir, 'wp-mailzen-integration.zip');
        run('zip', ['-r', wpPluginZipPath, 'mailzen-integration'], { cwd: wpBaseDir });

        run('zip', ['-r', masterZipPath, '.'], { cwd: masterTmpDir });

        console.log(`Created ${masterZipFileName}`);

        await fs.rm(masterTmpDir, { recursive: true, force: true });

        await fs.rm(tmpDir, { recursive: true, force: true });

        if (zipClean) {
            await fs.rm(outDir, { recursive: true, force: true });
        }
    }
}

main().catch((err) => {
    console.error(err);
    process.exit(1);
});

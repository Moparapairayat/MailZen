<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class InstallController extends Controller
{
    public function welcome()
    {
        return view('install.welcome');
    }

    public function requirements()
    {
        $phpVersion = PHP_VERSION;
        $phpOk = version_compare($phpVersion, '8.1.0', '>=');

        $extensions = [
            'ctype',
            'curl',
            'dom',
            'fileinfo',
            'filter',
            'hash',
            'mbstring',
            'openssl',
            'pcre',
            'pdo',
            'session',
            'tokenizer',
            'xml',
            'zip',
        ];

        $extResults = [];
        foreach ($extensions as $ext) {
            $extResults[$ext] = extension_loaded($ext);
        }

        $paths = [
            base_path(),
            base_path('bootstrap/cache'),
            storage_path(),
        ];

        $writable = [];
        foreach ($paths as $p) {
            $writable[$p] = is_dir($p) ? is_writable($p) : is_writable(dirname($p));
        }

        $allOk = $phpOk
            && !in_array(false, $extResults, true)
            && !in_array(false, $writable, true);

        return view('install.requirements', compact('phpVersion', 'phpOk', 'extResults', 'writable', 'allOk'));
    }

    public function setup(Request $request)
    {
        $host = (string) $request->getHost();
        $host = preg_replace('/^www\./i', '', $host) ?? $host;
        $domainLabel = explode('.', $host)[0] ?? '';

        $guessedAppName = $domainLabel !== ''
            ? Str::studly(str_replace(['-', '_'], ' ', $domainLabel))
            : 'MailZen';

        $guessedAppUrl = rtrim($request->getSchemeAndHttpHost() . $request->getBaseUrl(), '/');

        return view('install.setup', [
            'guessedAppName' => $guessedAppName,
            'guessedAppUrl' => $guessedAppUrl,
        ]);
    }

    public function storeSetup(Request $request)
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:100'],
            'app_url' => ['required', 'url'],
            'db_connection' => ['required', 'in:mysql,pgsql'],
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'db_database' => ['required', 'string', 'max:255'],
            'db_username' => ['required', 'string', 'max:255'],
            'db_password' => ['nullable', 'string'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $appUrl = rtrim($validated['app_url'], '/');

        $testError = $this->testDatabaseConnection(
            $validated['db_connection'],
            $validated['db_host'],
            (int) $validated['db_port'],
            $validated['db_database'],
            $validated['db_username'],
            (string) ($validated['db_password'] ?? '')
        );

        if ($testError !== null) {
            return back()->withInput()->withErrors(['db_database' => $testError]);
        }

        $envUpdates = [
            'APP_NAME' => $validated['app_name'],
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'APP_URL' => $appUrl,
            'DB_CONNECTION' => $validated['db_connection'],
            'DB_HOST' => $validated['db_host'],
            'DB_PORT' => (string) $validated['db_port'],
            'DB_DATABASE' => $validated['db_database'],
            'DB_USERNAME' => $validated['db_username'],
            'DB_PASSWORD' => (string) ($validated['db_password'] ?? ''),
            'CACHE_STORE' => 'file',
            'SESSION_DRIVER' => 'file',
            'QUEUE_CONNECTION' => 'database',
        ];

        $this->ensureEnvExists();
        $this->ensureAppKey();
        $this->updateEnvFile($envUpdates);

        config([
            'app.name' => $validated['app_name'],
            'app.url' => $appUrl,
            'database.default' => $validated['db_connection'],
        ]);

        config([
            'cache.default' => 'file',
        ]);

        config([
            'database.connections.' . $validated['db_connection'] . '.host' => $validated['db_host'],
            'database.connections.' . $validated['db_connection'] . '.port' => (int) $validated['db_port'],
            'database.connections.' . $validated['db_connection'] . '.database' => $validated['db_database'],
            'database.connections.' . $validated['db_connection'] . '.username' => $validated['db_username'],
            'database.connections.' . $validated['db_connection'] . '.password' => (string) ($validated['db_password'] ?? ''),
        ]);

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['db_database' => 'Could not connect to the database with the provided settings.']);
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'Unsupported operand types: string + int')) {
                try {
                    $this->repairMigrationsTableIfNeeded();
                    Artisan::call('migrate', ['--force' => true]);
                } catch (\Throwable $e2) {
                    $errorDetails = $e2->getMessage() . "\n\n" . $e2->getTraceAsString();
                    @file_put_contents(storage_path('migration_error.txt'), $errorDetails);
                    return back()->withInput()->withErrors(['db_database' => 'Migrations failed: ' . $e2->getMessage()]);
                }
            } else {
                $errorDetails = $e->getMessage() . "\n\n" . $e->getTraceAsString();
                @file_put_contents(storage_path('migration_error.txt'), $errorDetails);
                return back()->withInput()->withErrors(['db_database' => 'Migrations failed: ' . $e->getMessage()]);
            }
        }

        $superadminGroup = UserGroup::firstOrCreate(
            ['name' => 'Superadmin'],
            [
                'description' => 'Full access to all admin actions.',
                'permissions' => ['*'],
                'is_system' => true,
            ]
        );

        UserGroup::firstOrCreate(
            ['name' => 'Admin'],
            [
                'description' => 'Default admin access (configurable via Accessibility Control).',
                'permissions' => ['admin.*'],
                'is_system' => true,
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => $validated['admin_email']],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make($validated['admin_password']),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $admin->userGroups()->syncWithoutDetaching([$superadminGroup->id]);

        try {
            Artisan::call('storage:link');
        } catch (\Throwable $e) {
        }

        try {
            Artisan::call('optimize:clear');
        } catch (\Throwable $e) {
        }

        $this->markInstalled(['admin_email' => $validated['admin_email']]);

        return redirect()->route('install.done');
    }

    public function done()
    {
        return view('install.done');
    }

    private function testDatabaseConnection(string $driver, string $host, int $port, string $database, string $username, string $password): ?string
    {
        try {
            if ($driver === 'mysql') {
                $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            } elseif ($driver === 'pgsql') {
                $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
            } else {
                return 'Unsupported database connection.';
            }

            $pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_TIMEOUT => 5,
            ]);
            $pdo->query('SELECT 1');
            return null;
        } catch (\Throwable $e) {
            return 'Database connection failed. Please check host, port, database name, username and password.';
        }
    }

    private function ensureEnvExists(): void
    {
        $envPath = base_path('.env');
        if (is_file($envPath)) {
            return;
        }

        $examplePath = base_path('.env.example');
        if (!is_file($examplePath)) {
            return;
        }

        @copy($examplePath, $envPath);
    }

    private function ensureAppKey(): void
    {
        $envPath = base_path('.env');
        if (!is_file($envPath)) {
            return;
        }

        $contents = (string) @file_get_contents($envPath);
        if ($contents === '') {
            return;
        }

        if (preg_match('/^APP_KEY=(.+)$/m', $contents, $m)) {
            $current = trim((string) ($m[1] ?? ''));
            if ($current !== '') {
                return;
            }
        }

        $key = 'base64:' . base64_encode(random_bytes(32));
        if (preg_match('/^APP_KEY=.*$/m', $contents)) {
            $contents = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $contents);
        } else {
            $contents = rtrim($contents, "\r\n") . "\nAPP_KEY={$key}\n";
        }

        @file_put_contents($envPath, $contents);
    }

    private function updateEnvFile(array $updates): void
    {
        $envPath = base_path('.env');
        $contents = (string) @file_get_contents($envPath);
        $hasExistingContents = $contents !== '';

        foreach ($updates as $key => $value) {
            $line = $key . '=' . $this->formatEnvValue($value);
            if ($hasExistingContents && preg_match('/^' . preg_quote($key, '/') . '=.*/m', $contents)) {
                $contents = preg_replace('/^' . preg_quote($key, '/') . '=.*/m', $line, $contents);
                continue;
            }

            $contents = rtrim($contents, "\r\n") . "\n{$line}\n";
        }

        @file_put_contents($envPath, $contents);
    }

    private function formatEnvValue(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (preg_match('/\s|#|=|"|\\\\/', $value)) {
            $escaped = str_replace('\\', '\\\\', $value);
            $escaped = str_replace('"', '\\"', $escaped);
            return '"' . $escaped . '"';
        }

        return $value;
    }

    private function markInstalled(array $meta = []): void
    {
        $path = storage_path('app/private/installed.json');
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $payload = array_merge([
            'installed_at' => now()->toIso8601String(),
        ], $meta);

        @file_put_contents($path, json_encode($payload));
    }

    private function repairMigrationsTableIfNeeded(): void
    {
        if (!Schema::hasTable('migrations')) {
            return;
        }

        $needsRepair = false;

        try {
            if (!Schema::hasColumn('migrations', 'batch')) {
                $needsRepair = true;
            }
        } catch (\Throwable $e) {
            $needsRepair = true;
        }

        if (!$needsRepair) {
            try {
                $maxBatch = DB::table('migrations')->max('batch');
                if ($maxBatch !== null && !is_numeric($maxBatch)) {
                    $needsRepair = true;
                }
            } catch (\Throwable $e) {
                $needsRepair = true;
            }
        }

        if (!$needsRepair) {
            return;
        }

        $backupName = 'migrations_backup_' . date('Ymd_His');

        try {
            Schema::rename('migrations', $backupName);
        } catch (\Throwable $e) {
            try {
                Schema::drop('migrations');
            } catch (\Throwable $e2) {
            }
        }
    }
}

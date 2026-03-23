<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Ensure .env exists (from .env.example) and APP_KEY is set before Laravel boots.
// This allows the installer to render on first-time deployments.
$envPath = __DIR__ . '/../.env';
$envExamplePath = __DIR__ . '/../.env.example';
$appKeyStoragePath = __DIR__ . '/../storage/app/private/app_key.txt';

if (!file_exists($envPath) && file_exists($envExamplePath)) {
    @copy($envExamplePath, $envPath);
}

if (!file_exists($envPath)) {
    @file_put_contents(
        $envPath,
        "APP_NAME=MailZen\n" .
        "APP_ENV=production\n" .
        "APP_DEBUG=false\n" .
        "CACHE_STORE=file\n" .
        "SESSION_DRIVER=file\n" .
        "QUEUE_CONNECTION=sync\n" .
        "DB_CONNECTION=mysql\n"
    );
}

$envContents = file_exists($envPath) ? (string) @file_get_contents($envPath) : '';
$key = null;

if (preg_match('/^APP_KEY=(.*)$/m', $envContents, $m)) {
    $key = trim((string) ($m[1] ?? ''));
}

if ($key === null || $key === '') {
    $storedKey = '';
    if (file_exists($appKeyStoragePath)) {
        $storedKey = trim((string) @file_get_contents($appKeyStoragePath));
    }

    $key = $storedKey !== ''
        ? $storedKey
        : ('base64:' . base64_encode(random_bytes(32)));

    @mkdir(dirname($appKeyStoragePath), 0755, true);
    if (!file_exists($appKeyStoragePath) || $storedKey === '') {
        @file_put_contents($appKeyStoragePath, $key);
    }

    $envDirWritable = @is_writable(dirname($envPath));
    $envFileWritable = file_exists($envPath) ? @is_writable($envPath) : $envDirWritable;

    if ($envFileWritable) {
        if (preg_match('/^APP_KEY=.*$/m', $envContents)) {
            $envContents = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $envContents);
        } else {
            $envContents = rtrim($envContents, "\r\n") . "\nAPP_KEY={$key}\n";
        }

        @file_put_contents($envPath, $envContents);
    }
}

if (is_string($key) && $key !== '') {
    @putenv('APP_KEY=' . $key);
    $_ENV['APP_KEY'] = $key;
    $_SERVER['APP_KEY'] = $key;
}

// Ensure required runtime directories exist before Laravel bootstraps.
$requiredDirs = [
    __DIR__ . '/../bootstrap/cache',
    __DIR__ . '/../storage/app',
    __DIR__ . '/../storage/app/private',
    __DIR__ . '/../storage/app/public',
    __DIR__ . '/../storage/framework',
    __DIR__ . '/../storage/framework/cache',
    __DIR__ . '/../storage/framework/sessions',
    __DIR__ . '/../storage/framework/views',
    __DIR__ . '/../storage/logs',
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/bootstrap/app.php')
    ->handleRequest(Request::capture());


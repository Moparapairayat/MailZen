@extends('layouts.public')

@section('title', 'Documentation')

@section('content')
<div
    x-data="{
        copied: null,
        copy(text) {
            if (!text) return;
            navigator.clipboard.writeText(text);
            this.copied = text;
            setTimeout(() => this.copied = null, 1200);
        }
    }"
    class="bg-white dark:bg-gray-900"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-12">
        <div class="lg:grid lg:grid-cols-12 lg:gap-8">
            <aside class="hidden lg:block lg:col-span-3">
                <div class="sticky top-24 space-y-4">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Documentation</div>
                    <nav class="space-y-6">
                        <div class="space-y-2">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Getting started</div>
                        <div class="space-y-1">
                                <a href="#project-credit" class="block rounded-md px-2 py-1 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Project credit</a>
                                <a href="#base-url" class="block rounded-md px-2 py-1 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Base URL</a>
                                <a href="#requirements" class="block rounded-md px-2 py-1 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Requirements</a>
                                <a href="#installation" class="block rounded-md px-2 py-1 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Installation</a>
                                <a href="#installation-installer" class="block rounded-md px-2 py-1 pl-6 text-xs text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Installer wizard</a>
                                <a href="#installation-manual" class="block rounded-md px-2 py-1 pl-6 text-xs text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Manual installation</a>
                                <a href="#installation-mysql" class="block rounded-md px-2 py-1 pl-6 text-xs text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Local MySQL setup</a>
                                <a href="#installation-cpanel" class="block rounded-md px-2 py-1 pl-6 text-xs text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">cPanel</a>
                                <a href="#installation-cloud" class="block rounded-md px-2 py-1 pl-6 text-xs text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Cloud hosting (VPS)</a>
                                <a href="#installation-docker" class="block rounded-md px-2 py-1 pl-6 text-xs text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Docker</a>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Operations</div>
                            <div class="space-y-1">
                                <a href="#queues" class="block rounded-md px-2 py-1 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Background jobs</a>
                                <a href="#scheduler" class="block rounded-md px-2 py-1 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Scheduler / Cron</a>
                                <a href="#webhooks" class="block rounded-md px-2 py-1 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Webhooks</a>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Configuration</div>
                            <div class="space-y-1">
                                <a href="#email-providers" class="block rounded-md px-2 py-1 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Email providers</a>
                                <a href="#google-integrations" class="block rounded-md px-2 py-1 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Google Sheets & Drive</a>
                                <a href="#wordpress-integrations" class="block rounded-md px-2 py-1 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">WordPress & WooCommerce</a>
                                <a href="#billing" class="block rounded-md px-2 py-1 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Billing</a>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Monetization</div>
                            <div class="space-y-1">
                                <a href="#monetization" class="block rounded-md px-2 py-1 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Overview</a>
                                <a href="#monetization-invoices" class="block rounded-md px-2 py-1 pl-6 text-xs text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Invoices</a>
                                <a href="#monetization-coupons" class="block rounded-md px-2 py-1 pl-6 text-xs text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Coupons</a>
                                <a href="#monetization-plans" class="block rounded-md px-2 py-1 pl-6 text-xs text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Plans</a>
                                <a href="#monetization-payment-methods" class="block rounded-md px-2 py-1 pl-6 text-xs text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Payment method</a>
                                <a href="#monetization-vat-tax" class="block rounded-md px-2 py-1 pl-6 text-xs text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">VAT/Tax</a>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Servers</div>
                            <div class="space-y-1">
                                <a href="#servers" class="block rounded-md px-2 py-1 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Overview</a>
                                <a href="#servers-delivery" class="block rounded-md px-2 py-1 pl-6 text-xs text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Delivery servers</a>
                                <a href="#servers-bounce" class="block rounded-md px-2 py-1 pl-6 text-xs text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Bounce servers</a>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Marketing</div>
                            <div class="space-y-1">
                                <a href="#marketing" class="block rounded-md px-2 py-1 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Overview</a>
                                <a href="#marketing-email-lists" class="block rounded-md px-2 py-1 pl-6 text-xs text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Email lists</a>
                                <a href="#marketing-campaigns" class="block rounded-md px-2 py-1 pl-6 text-xs text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Campaigns</a>
                                <a href="#marketing-validation" class="block rounded-md px-2 py-1 pl-6 text-xs text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Validation</a>
                                <a href="#marketing-autoresponders" class="block rounded-md px-2 py-1 pl-6 text-xs text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Autoresponders</a>
                                <a href="#marketing-spintax-spam-scoring" class="block rounded-md px-2 py-1 pl-6 text-xs text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Spintax & Spam Scoring</a>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Usage</div>
                            <div class="space-y-1">
                                <a href="#usage" class="block rounded-md px-2 py-1 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Using the app</a>
                                <a href="#troubleshooting" class="block rounded-md px-2 py-1 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Troubleshooting</a>
                            </div>
                        </div>
                    </nav>
                </div>
            </aside>

            <main class="lg:col-span-7">
                <div class="max-w-3xl">
                    <h1 id="top" class="text-4xl font-extrabold text-gray-900 dark:text-white">Documentation</h1>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">
                        This guide covers installation, configuration, and day-to-day usage of {{ config('app.name', 'MailZen') }}.
                    </p>
                </div>

                <div class="mt-12 space-y-10">
                    <section id="project-credit" class="space-y-4 scroll-mt-32">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Project Credit</h2>
                        <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-white to-gray-50 p-6 shadow-sm dark:border-gray-700 dark:from-gray-900 dark:to-gray-800">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-primary-600 dark:text-primary-400">Maintainer</p>
                            <p class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white">MOPARA PAIR AYAT</p>
                            <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-300">
                                This MailZen build is branded, maintained, and prepared for deployment by MOPARA PAIR AYAT.
                            </p>
                        </div>
                    </section>

                    <section id="base-url" class="space-y-4 scroll-mt-32">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Base URL</h2>
                        <div class="space-y-3 text-gray-700 dark:text-gray-300">
                            <p>
                                All URLs in this documentation assume your application base URL. Ensure <span class="font-mono">APP_URL</span> is set correctly.
                            </p>
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <span x-ref="baseUrl" class="font-mono text-sm text-gray-900 dark:text-gray-100 break-all">{{ config('app.url') }}</span>
                                    </div>
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800"
                                        x-on:click="copy($refs.baseUrl.innerText)"
                                    >
                                        Copy
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>
            <section id="requirements" class="space-y-4 scroll-mt-32">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Requirements</h2>
                <div class="space-y-2 text-gray-700 dark:text-gray-300">
                    <p>Backend:</p>
                    <ul class="list-disc pl-6 space-y-1">
                        <li>PHP 8.2+</li>
                        <li>Composer</li>
                        <li>MySQL (or compatible) database</li>
                        <li>Redis (recommended for queues)</li>
                    </ul>
                    <p>Frontend:</p>
                    <ul class="list-disc pl-6 space-y-1">
                        <li>Node.js + npm</li>
                    </ul>
                    <p>Optional (Bounce processing):</p>
                    <ul class="list-disc pl-6 space-y-1">
                        <li>PHP IMAP extension (required for processing IMAP/POP3 bounce mailboxes)</li>
                    </ul>
                </div>
            </section>

            <section id="installation" class="space-y-4 scroll-mt-32">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Installation</h2>
                <div class="space-y-3 text-gray-700 dark:text-gray-300">
                    <p>
                        There are two ways to install {{ config('app.name', 'MailZen') }}:
                    </p>
                    <ol class="list-decimal pl-6 space-y-1">
                        <li><span class="font-semibold text-gray-900 dark:text-white">Installer wizard</span> (recommended for most people, especially shared hosting)</li>
                        <li><span class="font-semibold text-gray-900 dark:text-white">Manual installation</span> (advanced / for VPS + SSH)</li>
                    </ol>

                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">Video walkthrough</p>
                        <div class="mt-3 w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-black relative" style="padding-top: 56.25%;">
                            <iframe
                                class="absolute inset-0 h-full w-full"
                                src="https://www.youtube.com/embed/iX6pDQXXabk"
                                title="Installation guide"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                            ></iframe>
                        </div>
                        <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                            If the video does not load, open it here:
                            <a href="https://www.youtube.com/watch?v=iX6pDQXXabk" target="_blank" rel="noopener noreferrer" class="text-primary-600 dark:text-primary-400 underline">https://www.youtube.com/watch?v=iX6pDQXXabk</a>
                        </div>
                    </div>

                    <div id="installation-installer" class="space-y-3 scroll-mt-32">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Installer wizard (recommended)</h3>
                        <div class="rounded-lg border border-blue-200 dark:border-blue-900/50 bg-blue-50 dark:bg-blue-900/20 p-4">
                            <p class="font-semibold text-blue-900 dark:text-blue-200">Perfect for non-coders</p>
                            <div class="mt-2 space-y-2 text-sm text-blue-900/90 dark:text-blue-200/90">
                                <p>
                                    You upload the files, open your site, and the installer will guide you through:
                                    server checks, app name + URL, database setup, migrations, and creating your first admin account.
                                </p>
                            </div>
                        </div>
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                            <p class="font-semibold text-gray-900 dark:text-white">Step-by-step</p>
                            <ol class="mt-2 list-decimal pl-6 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                <li>
                                    In your hosting file manager (cPanel), upload the provided <span class="font-mono">zip</span> file to your domain folder (example: <span class="font-mono">public_html</span>).
                                </li>
                                <li>
                                    Extract the zip.
                                </li>
                                <li>
                                    Make sure these folders are writable:
                                    <span class="font-mono">storage/</span> and <span class="font-mono">bootstrap/cache/</span>.
                                </li>
                                <li>
                                    Visit your domain in the browser (example: <span class="font-mono">https://yourdomain.com</span>). You will be redirected to <span class="font-mono">/install</span>.
                                </li>
                                <li>
                                    Follow the wizard and enter:
                                    <ul class="mt-1 list-disc pl-6 space-y-1">
                                        <li>App name + App URL</li>
                                        <li>Database host, name, username, password (from your hosting panel)</li>
                                        <li>Admin email + password (this becomes your first admin login)</li>
                                    </ul>
                                </li>
                                <li>
                                    Click <span class="font-semibold">Install</span> and wait. The installer will run database migrations and finalize setup.
                                </li>
                                <li>
                                    When finished, click <span class="font-semibold">Login</span> and sign in using the admin email/password you created.
                                </li>
                            </ol>
                        </div>
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                            <p class="font-semibold text-gray-900 dark:text-white">Important note</p>
                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                There are <span class="font-semibold">no default admin credentials</span>. Your admin account is created during the installer.
                            </p>
                        </div>
                    </div>

                    <div id="installation-manual" class="space-y-3 scroll-mt-32 pt-2">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Manual installation (advanced)</h3>
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                            <p class="font-semibold text-gray-900 dark:text-white">When should I use manual install?</p>
                            <div class="mt-2 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                <p>
                                    Use this if you have a VPS/server with SSH access and you’re comfortable running commands.
                                    If you’re on shared hosting without SSH, use the <span class="font-semibold">Installer wizard</span>.
                                </p>
                            </div>
                        </div>
                        <div class="rounded-lg border border-blue-200 dark:border-blue-900/50 bg-blue-50 dark:bg-blue-900/20 p-4">
                            <p class="font-semibold text-blue-900 dark:text-blue-200">Where do I put the files?</p>
                            <div class="mt-2 space-y-2 text-sm text-blue-900/90 dark:text-blue-200/90">
                                <p>
                                    Put the full project folder on your server (example: <span class="font-mono">/var/www/mailzen</span>).
                                </p>
                                <p>
                                    Your web server should point to the <span class="font-mono">public</span> directory inside the project
                                    (example: <span class="font-mono">/var/www/mailzen/public</span>).
                                </p>
                            </div>
                        </div>
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                            <p class="font-semibold text-gray-900 dark:text-white">Typical command sequence (SSH)</p>
                            <div class="mt-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                                <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-900 dark:text-gray-100">composer install --no-dev --optimize-autoloader
npm install
npm run build

cp .env.example .env
php artisan key:generate

php artisan migrate --force
php artisan storage:link
php artisan optimize:clear</pre>
                            </div>
                            <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                                After this, visit your site and create your first admin account via the app.
                            </p>
                            <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                                Windows PowerShell equivalent: <span class="font-mono">Copy-Item .env.example .env</span>
                            </p>
                        </div>
                    </div>

                    <div class="pt-4 space-y-8">
                        <div id="installation-mysql" class="space-y-3 scroll-mt-32">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Local MySQL setup</h3>
                            <div class="space-y-3">
                                <p>
                                    MailZen supports any Laravel-compatible MySQL database. For local development, MySQL 8+ is recommended.
                                </p>
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                    <p class="font-semibold text-gray-900 dark:text-white">1) Ensure MySQL is running (choose one)</p>
                                    <div class="mt-2 space-y-4 text-sm">
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-white">Docker (example)</p>
                                            <div class="mt-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                                                <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-900 dark:text-gray-100">docker run --name mailzen-mysql \
  -e MYSQL_ROOT_PASSWORD=root \
  -e MYSQL_DATABASE=mailzen \
  -p 3306:3306 \
  -d mysql:8.0</pre>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-white">Native MySQL (Homebrew on macOS)</p>
                                            <div class="mt-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                                                <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-900 dark:text-gray-100">brew install mysql
brew services start mysql</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                    <p class="font-semibold text-gray-900 dark:text-white">2) Create the database (if you did not create it already)</p>
                                    <div class="mt-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                                        <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-900 dark:text-gray-100">CREATE DATABASE mailzen;</pre>
                                    </div>
                                </div>
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                    <p class="font-semibold text-gray-900 dark:text-white">3) Configure <span class="font-mono">.env</span> for MySQL</p>
                                    <div class="mt-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                                        <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-900 dark:text-gray-100">DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mailzen
DB_USERNAME=root
DB_PASSWORD=</pre>
                                    </div>
                                    <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                                        <li>If you are using Docker with the command above, <span class="font-mono">DB_PASSWORD</span> for <span class="font-mono">root</span> is <span class="font-mono">root</span>.</li>
                                        <li>Prefer <span class="font-mono">127.0.0.1</span> over <span class="font-mono">localhost</span> to avoid socket/TCP differences on some systems.</li>
                                        <li>Ensure your PHP has the MySQL driver enabled (<span class="font-mono">pdo_mysql</span>).</li>
                                    </ul>
                                </div>
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                    <p class="font-semibold text-gray-900 dark:text-white">4) Clear cached config and run migrations</p>
                                    <div class="mt-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                                        <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-900 dark:text-gray-100">php artisan optimize:clear
php artisan migrate</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="installation-cpanel" class="space-y-3 scroll-mt-32">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Install on cPanel</h3>
                            <div class="space-y-3">
                                <p>
                                    This documentation provides a step-by-step guide on how to deploy a Laravel application to cPanel shared hosting.
                                </p>

                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                    <p class="font-semibold text-gray-900 dark:text-white">1. Upload and Extract Files</p>
                                    <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                                        <li>Upload: Log in to cPanel, open File Manager, and navigate to the <span class="font-mono">public_html</span> directory. Upload your project's <span class="font-mono">.zip</span> file here.</li>
                                        <li>Unzip: Right-click the uploaded zip file and select Extract.</li>
                                        <li>
                                            Move to Root: If your files extracted into a folder (e.g., <span class="font-mono">/public_html/my-project</span>), enter that folder:
                                            <div class="mt-2">
                                                <ul class="list-disc pl-6 space-y-1">
                                                    <li>Click Select All.</li>
                                                    <li>Click Move from the top toolbar.</li>
                                                    <li>Change the destination path to <span class="font-mono">/public_html/</span> and click Move Files.</li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                    <p class="font-semibold text-gray-900 dark:text-white">2. Configure Public Assets</p>
                                    <p class="mt-2 text-sm">
                                        To make the site accessible without typing <span class="font-mono">/public</span> in the URL, move the core entry files:
                                    </p>
                                    <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                                        <li>Go inside the <span class="font-mono">public</span> folder.</li>
                                        <li>Select the <span class="font-mono">index.php</span> file and the <span class="font-mono">build</span> folder (if using Vite/React/Vue).</li>
                                        <li>Move them directly into the <span class="font-mono">/public_html/</span> directory.</li>
                                    </ul>
                                </div>

                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                    <p class="font-semibold text-gray-900 dark:text-white">3. Update index.php Paths</p>
                                    <p class="mt-2 text-sm">
                                        Since <span class="font-mono">index.php</span> is now in the same directory as the <span class="font-mono">vendor</span> and <span class="font-mono">bootstrap</span> folders, you must update the internal links.
                                        Open <span class="font-mono">public_html/index.php</span> in the cPanel File Editor.
                                        Find the following lines (usually near the top) and remove the <span class="font-mono">../</span> from the paths:
                                    </p>
                                    <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">Change this:</p>
                                    <div class="mt-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                                        <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-900 dark:text-gray-100">require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';</pre>
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">To this:</p>
                                    <div class="mt-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                                        <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-900 dark:text-gray-100">require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';</pre>
                                    </div>
                                </div>

                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                    <p class="font-semibold text-gray-900 dark:text-white">4. Environment Configuration (<span class="font-mono">.env</span>)</p>
                                    <p class="mt-2 text-sm">
                                        Locate the <span class="font-mono">.env</span> file in <span class="font-mono">public_html</span>. If it’s hidden, click Settings in the top right of File Manager and check Show Hidden Files (dotfiles).
                                        Edit the file with these settings:
                                    </p>
                                    <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                                        <li>Database: Enter your cPanel MySQL Database name, username, and password.</li>
                                        <li>App Settings:</li>
                                    </ul>
                                    <div class="mt-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                                        <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-900 dark:text-gray-100">APP_NAME=YourProjectName
APP_URL=https://yourdomain.com
APP_ENV=production
APP_DEBUG=false</pre>
                                    </div>
                                </div>

                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                    <p class="font-semibold text-gray-900 dark:text-white">5. Routing Fix (<span class="font-mono">.htaccess</span>)</p>
                                    <p class="mt-2 text-sm">
                                        Because you moved <span class="font-mono">index.php</span> to the root, you must ensure the server handles URLs correctly.
                                        Create a file named <span class="font-mono">.htaccess</span> in <span class="font-mono">public_html</span> and paste the following:
                                    </p>
                                    <div class="mt-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                                        <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-900 dark:text-gray-100">&lt;IfModule mod_rewrite.c&gt;
    &lt;IfModule mod_negotiation.c&gt;
        Options -MultiViews -Indexes
    &lt;/IfModule&gt;

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)/$ /$1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
&lt;/IfModule&gt;</pre>
                                    </div>
                                </div>

                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                    <p class="font-semibold text-gray-900 dark:text-white">6. Finalize via Terminal</p>
                                    <p class="mt-2 text-sm">
                                        Open the Terminal in cPanel. Navigate to your project folder (usually <span class="font-mono">cd public_html</span>) and run these commands in order to set up the database and optimize performance:
                                    </p>
                                    <div class="mt-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                                        <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-900 dark:text-gray-100">php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache</pre>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="installation-cloud" class="space-y-3 scroll-mt-32">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Install on cloud hosting (VPS)</h3>
                            <div class="space-y-3">
                                <p>
                                    Recommended for production. Use a VPS (Ubuntu/Debian/CentOS) with Nginx or Apache, PHP-FPM, and a managed database.
                                </p>
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                    <p class="font-semibold text-gray-900 dark:text-white">Where you run the commands on a VPS</p>
                                    <div class="mt-2 space-y-2 text-sm">
                                        <p>
                                            You normally connect to your server using SSH (for example via a tool like Terminal on Mac, or PuTTY on Windows).
                                            Once connected, you run the commands inside the project folder on the server (example: <span class="font-mono">/var/www/mailzen</span>).
                                        </p>
                                        <p>
                                            If you prefer, you can also build the frontend assets on your computer and upload only <span class="font-mono">public/build</span>, but Composer dependencies
                                            (<span class="font-mono">vendor</span>) are usually installed directly on the server.
                                        </p>
                                    </div>
                                </div>
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                    <p class="font-semibold text-gray-900 dark:text-white">Beginner SSH example (VPS)</p>
                                    <div class="mt-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                                        <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-900 dark:text-gray-100">ssh your-user@your-server-ip
cd /var/www/mailzen
composer install --no-dev --optimize-autoloader
npm install
npm run build</pre>
                                    </div>
                                </div>
                                <ol class="list-decimal pl-6 space-y-2">
                                    <li>Install system requirements: PHP 8.2+, extensions, Composer, Node (optional if you build assets locally), and a database.</li>
                                    <li>
                                        Deploy the project (Git clone or upload) to a directory like <span class="font-mono">/var/www/mailzen</span>.
                                    </li>
                                    <li>
                                        Configure your web server document root to <span class="font-mono">/var/www/mailzen/public</span>.
                                    </li>
                                    <li>
                                        Create <span class="font-mono">.env</span> and set <span class="font-mono">APP_ENV=production</span>, <span class="font-mono">APP_DEBUG=false</span>, <span class="font-mono">APP_URL</span>, database and mail settings.
                                    </li>
                                    <li>
                                        Install dependencies and build assets:
                                        <div class="mt-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3">
                                            <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-900 dark:text-gray-100">composer install --no-dev --optimize-autoloader
npm install
npm run build</pre>
                                        </div>
                                    </li>
                                    <li>
                                        Run the application setup:
                                        <div class="mt-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3">
                                            <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-900 dark:text-gray-100">php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache</pre>
                                        </div>
                                    </li>
                                    <li>
                                        Run a queue worker with a process manager (Supervisor/systemd). Example Supervisor command:
                                        <div class="mt-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3">
                                            <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-900 dark:text-gray-100">php /var/www/mailzen/artisan queue:work --sleep=3 --tries=3 --timeout=120</pre>
                                        </div>
                                    </li>
                                    <li>
                                        Configure cron to run Laravel scheduler every minute (see <a href="#scheduler" class="underline">Scheduler / Cron</a> section).
                                    </li>
                                    <li>Enable HTTPS (Let’s Encrypt) and confirm <span class="font-mono">APP_URL</span> uses <span class="font-mono">https</span>.</li>
                                </ol>
                            </div>
                        </div>

                        <div id="installation-docker" class="space-y-3 scroll-mt-32">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Install using Docker</h3>
                            <div class="space-y-3">
                                <p>
                                    This repository includes a <span class="font-mono">Dockerfile</span>, but does not include a full compose setup by default.
                                    You can run the container behind a reverse proxy and connect it to a database (recommended: MySQL) and Redis.
                                </p>
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                    <p class="font-semibold text-gray-900 dark:text-white">Where you run Docker commands</p>
                                    <div class="mt-2 space-y-2 text-sm">
                                        <p>
                                            You run <span class="font-mono">docker</span> commands on the machine that has Docker installed (your computer, or a Docker-enabled server).
                                            The <span class="font-mono">docker build</span> command is run in the project folder (the folder that contains the <span class="font-mono">Dockerfile</span>).
                                        </p>
                                        <p>
                                            Commands like <span class="font-mono">php artisan migrate</span> are run inside the container using <span class="font-mono">docker exec</span>.
                                        </p>
                                    </div>
                                </div>
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                    <p class="font-semibold text-gray-900 dark:text-white">Common beginner mistake</p>
                                    <p class="mt-2 text-sm">
                                        If you run <span class="font-mono">docker build</span> and get “Dockerfile not found”, you are in the wrong folder. Make sure you are in the project folder first.
                                    </p>
                                </div>
                                <ol class="list-decimal pl-6 space-y-2">
                                    <li>
                                        Build the image:
                                        <div class="mt-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3">
                                            <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-900 dark:text-gray-100">docker build -t mailzen:latest .</pre>
                                        </div>
                                    </li>
                                    <li>
                                        Start the container with environment variables (example):
                                        <div class="mt-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3">
                                            <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-900 dark:text-gray-100">docker run -d --name mailzen \
  -p 8000:8000 \
  -e APP_ENV=production \
  -e APP_DEBUG=false \
  -e APP_URL=https://your-domain.com \
  -e PORT=8000 \
  -e DB_CONNECTION=mysql \
  -e DB_HOST=your-db-host \
  -e DB_DATABASE=mailzen \
  -e DB_USERNAME=mailzen \
  -e DB_PASSWORD=secret \
  mailzen:latest</pre>
                                        </div>
                                    </li>
                                    <li>
                                        Run migrations inside the container:
                                        <div class="mt-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3">
                                            <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-900 dark:text-gray-100">docker exec -it mailpurse php artisan migrate --force</pre>
                                        </div>
                                    </li>
                                    <li>
                                        Run queue workers and scheduler (recommended as separate processes/containers):
                                        <div class="mt-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3">
                                            <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-900 dark:text-gray-100"># Queue worker
docker exec -d mailpurse php artisan queue:work --sleep=3 --tries=3 --timeout=120

# Scheduler (every minute) - typically via host cron
docker exec mailpurse php artisan schedule:run</pre>
                                        </div>
                                    </li>
                                    <li>
                                        Put the container behind a reverse proxy (Nginx/Traefik) and configure TLS.
                                    </li>
                                </ol>
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                    <p class="font-semibold text-gray-900 dark:text-white">Tip</p>
                                    <p class="mt-1 text-sm">
                                        Persist uploads by mounting <span class="font-mono">storage</span> as a volume, and consider using S3-compatible storage for production.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="google-integrations" class="space-y-4 scroll-mt-32">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Google Sheets & Drive integrations</h2>
                <div class="space-y-4 text-gray-700 dark:text-gray-300">
                    <p>
                        MailZen supports connecting Google accounts to enable Google Sheets and Google Drive features (for example: importing subscribers from spreadsheets, exporting results, and using Drive-backed assets).
                    </p>

                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">Important concepts</p>
                        <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                            <li>Connections are stored per customer account (each customer can connect their own Google account).</li>
                            <li>Customer access can be controlled per customer group: Admin → Customer Groups → Integrations → “Access to Google Integrations”.</li>
                            <li>Google login/register (the <span class="font-mono">google_enabled</span> setting) is separate from Sheets/Drive integrations.</li>
                        </ul>
                    </div>

                    <div class="space-y-2">
                        <p class="font-semibold text-gray-900 dark:text-white">Step 1: Create a Google Cloud project</p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li>Go to Google Cloud Console and create/select a project.</li>
                            <li>Under “APIs & Services”, enable the required APIs.</li>
                        </ul>
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                            <p class="font-semibold text-gray-900 dark:text-white">Enable these APIs</p>
                            <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                                <li>Google Sheets API</li>
                                <li>Google Drive API</li>
                            </ul>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <p class="font-semibold text-gray-900 dark:text-white">Step 2: Configure OAuth consent screen</p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li>Go to “APIs & Services” → “OAuth consent screen”.</li>
                            <li>Choose External (recommended for SaaS) or Internal (Google Workspace only).</li>
                            <li>Set App name, support email, and add your domain (for production).</li>
                        </ul>
                    </div>

                    <div class="space-y-2">
                        <p class="font-semibold text-gray-900 dark:text-white">Step 3: Create OAuth Client ID</p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li>Go to “APIs & Services” → “Credentials” → “Create Credentials” → “OAuth client ID”.</li>
                            <li>Application type: <span class="font-mono">Web application</span>.</li>
                        </ul>
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                            <p class="font-semibold text-gray-900 dark:text-white">Authorized redirect URIs</p>
                            <p class="mt-2 text-sm">Add both Sheets and Drive callbacks (adjust the base URL to your domain):</p>
                            <div class="mt-3 space-y-2 text-sm">
                                <div class="rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                                    <div class="font-mono break-all">{{ url('/customer/integrations/google/sheets/callback') }}</div>
                                </div>
                                <div class="rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                                    <div class="font-mono break-all">{{ url('/customer/integrations/google/drive/callback') }}</div>
                                </div>
                            </div>
                            <div class="mt-3 rounded-lg border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/20 p-4">
                                <p class="font-semibold text-amber-900 dark:text-amber-200">Local development</p>
                                <p class="mt-1 text-sm text-amber-900/90 dark:text-amber-200/90">
                                    Google requires an exact match for <span class="font-mono">redirect_uri</span> (scheme, host, port, and trailing slash).
                                    If you develop on <span class="font-mono">http://127.0.0.1:8000</span> or <span class="font-mono">http://localhost:8000</span>, add redirect URIs for the exact host you use in the browser.
                                </p>
                                <div class="mt-3 space-y-2 text-sm">
                                    <div class="rounded-md border border-amber-200 dark:border-amber-900/50 bg-white/70 dark:bg-gray-900/40 p-3">
                                        <div class="font-mono break-all">http://127.0.0.1:8000/customer/integrations/google/sheets/callback</div>
                                        <div class="font-mono break-all">http://127.0.0.1:8000/customer/integrations/google/drive/callback</div>
                                    </div>
                                    <div class="rounded-md border border-amber-200 dark:border-amber-900/50 bg-white/70 dark:bg-gray-900/40 p-3">
                                        <div class="font-mono break-all">http://localhost:8000/customer/integrations/google/sheets/callback</div>
                                        <div class="font-mono break-all">http://localhost:8000/customer/integrations/google/drive/callback</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <p class="font-semibold text-gray-900 dark:text-white">Step 4: Configure Google credentials in MailZen</p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li>Go to Admin → Settings → Auth.</li>
                            <li>Set <span class="font-mono">google_client_id</span> and <span class="font-mono">google_client_secret</span> from the Google Cloud credentials screen.</li>
                        </ul>
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                            <p class="font-semibold text-gray-900 dark:text-white">APP_URL and caching</p>
                            <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                                <li>Ensure <span class="font-mono">APP_URL</span> matches your current host (for example: <span class="font-mono">http://127.0.0.1:8000</span> vs <span class="font-mono">http://localhost:8000</span>).</li>
                                <li>If you changed settings, clear caches: <span class="font-mono">php artisan optimize:clear</span>.</li>
                            </ul>
                        </div>
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                            <p class="font-semibold text-gray-900 dark:text-white">Environment variables (optional)</p>
                            <p class="mt-2 text-sm">You can also set them in your <span class="font-mono">.env</span>:</p>
                            <div class="mt-3 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                                <div class="font-mono text-sm break-all">GOOGLE_CLIENT_ID=...<br>GOOGLE_CLIENT_SECRET=...</div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <p class="font-semibold text-gray-900 dark:text-white">Step 5: Enable access for customer groups</p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li>Go to Admin → Customer Groups → Edit.</li>
                            <li>Open the Integrations tab.</li>
                            <li>Enable “Access to Google Integrations”.</li>
                        </ul>
                    </div>

                    <div class="space-y-2">
                        <p class="font-semibold text-gray-900 dark:text-white">Step 6: Connect as a customer</p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li>Login as a customer.</li>
                            <li>Go to Customer → Integrations → Google.</li>
                            <li>Use “Connect” on either Google Sheets or Google Drive (each can be connected independently).</li>
                        </ul>
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                            <p class="font-semibold text-gray-900 dark:text-white">Scopes used</p>
                            <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                                <li>Sheets: <span class="font-mono">https://www.googleapis.com/auth/spreadsheets</span></li>
                                <li>Drive: <span class="font-mono">https://www.googleapis.com/auth/drive.file</span></li>
                            </ul>
                        </div>
                    </div>

                    <div class="rounded-lg border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/20 p-4">
                        <p class="font-semibold text-amber-900 dark:text-amber-200">Troubleshooting</p>
                        <ul class="mt-2 list-disc pl-6 space-y-1 text-sm text-amber-900/90 dark:text-amber-200/90">
                            <li><span class="font-mono">redirect_uri_mismatch</span>: the redirect URI in Google Cloud must exactly match the one shown above (including http/https and trailing slash).</li>
                            <li><span class="font-mono">invalid_client</span>: verify Client ID/Secret and that you created a Web OAuth client.</li>
                            <li>“App not verified” warning: ensure consent screen is configured; for External apps you may need to add test users while in Testing mode.</li>
                            <li>If you changed settings, clear caches: <span class="font-mono">php artisan optimize:clear</span>.</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="wordpress-integrations" class="space-y-4 scroll-mt-32">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">WordPress & WooCommerce integration</h2>
                <div class="space-y-4 text-gray-700 dark:text-gray-300">
                    <p>
                        MailZen can receive WordPress and WooCommerce events and use them as Automation Builder triggers.
                        Examples include: <span class="font-mono">wp_user_registered</span>, <span class="font-mono">wp_user_updated</span>, <span class="font-mono">woo_order_completed</span>.
                    </p>

                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">Create an API key (MailZen)</p>
                        <ol class="mt-2 list-decimal pl-6 space-y-1 text-sm">
                            <li>Login to the Customer area.</li>
                            <li>Open <span class="font-semibold">Dashboard → API</span>.</li>
                            <li>Click <span class="font-semibold">Create API Key</span>.</li>
                            <li>Copy the key and store it securely (it may be shown only once).</li>
                        </ol>
                    </div>

                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">Configure the WordPress plugin</p>
                        <ol class="mt-2 list-decimal pl-6 space-y-1 text-sm">
                            <li>Install and activate the plugin ZIP from <span class="font-mono">wp-mailzen-integration.zip</span>.</li>
                            <li>Go to <span class="font-semibold">WordPress Admin → Settings → MailZen Integration</span>.</li>
                            <li>Set <span class="font-semibold">Base URL</span> (your MailZen URL) and <span class="font-semibold">API Key</span> (from Dashboard → API).</li>
                            <li>Click <span class="font-semibold">Save Settings</span>.</li>
                            <li>Click <span class="font-semibold">Test Connection</span> to fetch the signing secret used to sign event requests.</li>
                            <li>Enable the events you want to send.</li>
                        </ol>
                    </div>

                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">List routing (default, per-event, per-site)</p>
                        <p class="mt-2 text-sm">
                            You can route different events to different lists. Routing precedence is:
                        </p>
                        <ol class="mt-2 list-decimal pl-6 space-y-1 text-sm">
                            <li>Per-site → per-event</li>
                            <li>Per-event</li>
                            <li>Default list</li>
                        </ol>
                        <p class="mt-3 text-sm">
                            If you select <span class="font-semibold">No list (system)</span>, the plugin omits <span class="font-mono">list_id</span> and MailZen uses a hidden per-customer system list.
                        </p>
                    </div>

                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">Build an Automation Builder flow (WordPress/WooCommerce)</p>
                        <ol class="mt-2 list-decimal pl-6 space-y-2 text-sm">
                            <li>Go to <span class="font-semibold">Customer → Automations → Create</span>.</li>
                            <li>Select a trigger under <span class="font-semibold">WordPress</span> or <span class="font-semibold">WooCommerce</span>.</li>
                            <li>
                                (Optional) Select a list:
                                <ul class="mt-1 list-disc pl-6 space-y-1">
                                    <li>If a list is selected, the automation triggers only for events routed to that list.</li>
                                    <li>If no list is selected, the automation can trigger for WordPress/WooCommerce events regardless of list routing.</li>
                                </ul>
                            </li>
                            <li>Add nodes like <span class="font-semibold">Delay</span>, <span class="font-semibold">Email</span>, and <span class="font-semibold">Condition</span> to build your flow.</li>
                        </ol>
                    </div>

                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">Example: Order completed → Day 1/2/5 emails</p>
                        <ol class="mt-2 list-decimal pl-6 space-y-1 text-sm">
                            <li>Trigger: <span class="font-mono">woo_order_completed</span></li>
                            <li>Email: “Thanks for your order”</li>
                            <li>Delay: 1 day</li>
                            <li>Email: “How was your delivery?”</li>
                            <li>Delay: 3 days</li>
                            <li>Email: “Review request / Upsell”</li>
                        </ol>
                    </div>

                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">Using event payload in Conditions</p>
                        <p class="mt-2 text-sm">
                            Condition nodes can evaluate fields from the trigger payload using <span class="font-mono">payload.*</span>.
                            Example fields:
                        </p>
                        <div class="mt-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                            <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-900 dark:text-gray-100">payload.user_id
payload.order_id
payload.site.url</pre>
                        </div>
                        <p class="mt-2 text-sm">
                            Payload keys depend on the event. For the exact payload structure, review the WordPress integration hook definitions in the plugin source.
                        </p>
                    </div>
                </div>
            </section>

            <section id="queues" class="space-y-4 scroll-mt-32">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Running background jobs (required)</h2>
                <div class="space-y-3 text-gray-700 dark:text-gray-300">
                    <p>
                        Campaign sending and CSV imports run via queues. You must run a queue worker in production.
                    </p>
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">What is a “queue worker”?</p>
                        <div class="mt-2 space-y-2 text-sm">
                            <p>
                                A queue worker is a command that runs in the background and processes tasks that should not happen inside your browser request.
                                For example: sending campaign emails, importing subscribers, and processing validations.
                            </p>
                            <p>
                                If the worker is not running, the app may look fine, but emails/imports will stay “stuck” and nothing will process.
                            </p>
                        </div>
                    </div>
                    <ul class="list-disc pl-6 space-y-1">
                        <li><span class="font-mono">php artisan queue:work</span> (simple worker)</li>
                        <li><span class="font-mono">php artisan horizon</span> (if Horizon is configured in your environment)</li>
                    </ul>
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">Beginner setup options</p>
                        <ul class="mt-2 list-disc pl-6 space-y-2 text-sm">
                            <li>
                                Local/dev: open a terminal in the project folder and run <span class="font-mono">php artisan queue:work</span>. Keep that terminal window open.
                            </li>
                            <li>
                                VPS/Cloud (recommended): run the worker using a process manager so it stays running after you log out (Supervisor or systemd).
                            </li>
                            <li>
                                cPanel/shared hosting: persistent workers are often not possible. If you cannot keep a worker running, use cPanel Cron Jobs to run
                                <span class="font-mono">php artisan queue:work --stop-when-empty</span> every minute (less reliable than a VPS).
                            </li>
                            <li>
                                Docker: run the worker as a separate container or a separate process. Running it once with <span class="font-mono">docker exec -d</span> can stop on container restart.
                            </li>
                        </ul>
                    </div>
                    <div class="rounded-lg border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/20 p-4">
                        <p class="font-semibold text-amber-900 dark:text-amber-200">How to quickly check if it’s working</p>
                        <ul class="mt-2 list-disc pl-6 space-y-1 text-sm text-amber-900/90 dark:text-amber-200/90">
                            <li>Trigger an import or send a small test campaign and confirm the status changes within a minute.</li>
                            <li>On a server, check logs in <span class="font-mono">storage/logs</span> if jobs fail.</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="scheduler" class="space-y-4 scroll-mt-32">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Scheduler / Cron (recommended)</h2>
                <div class="space-y-3 text-gray-700 dark:text-gray-300">
                    <p>
                        The app schedules:
                    </p>
                    <ul class="list-disc pl-6 space-y-1">
                        <li>Starting scheduled campaigns every minute (<span class="font-mono">campaigns:start-scheduled</span>)</li>
                        <li>Processing bounces every 5 minutes (<span class="font-mono">email:process-bounces --all</span>)</li>
                    </ul>
                    <p>
                        Ensure your server runs Laravel scheduler:
                    </p>

                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">What is “cron”?</p>
                        <div class="mt-2 space-y-2 text-sm">
                            <p>
                                Cron is a built-in server feature that runs a command on a schedule. We use it to run Laravel’s scheduler every minute.
                                Laravel then decides which tasks should run (campaign checks, bounce processing, etc.).
                            </p>
                            <p>
                                Without cron, scheduled items may not run automatically.
                            </p>
                        </div>
                    </div>


                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3">
                        <div class="flex items-center justify-between gap-3">
                            <p x-ref="cronLine" class="font-mono text-sm text-gray-900 dark:text-gray-100 break-all">* * * * * php /path/to/artisan schedule:run &gt;&gt; /dev/null 2&gt;&amp;1</p>
                            <button
                                type="button"
                                class="inline-flex items-center rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800"
                                x-on:click="copy($refs.cronLine.innerText)"
                            >
                                Copy
                            </button>
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3">
                        <div class="flex items-center justify-between gap-3">
                            <p x-ref="cronLine" class="font-mono text-sm text-gray-900 dark:text-gray-100 break-all">* * * * * php /path/to/artisan queue:work --queue=email-validation,campaigns,default --sleep=1 --tries=3 --stop-when-empty >> /dev/null 2>&1</p>
                            <button
                                type="button"
                                class="inline-flex items-center rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800"
                                x-on:click="copy($refs.cronLine.innerText)"
                            >
                                Copy
                            </button>
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">Where to add this cron line</p>
                        <ul class="mt-2 list-disc pl-6 space-y-2 text-sm">
                            <li>
                                VPS/Cloud: connect via SSH and add it to your server crontab (often using <span class="font-mono">crontab -e</span>). Make sure the path points to your real
                                <span class="font-mono">artisan</span> file (example: <span class="font-mono">/var/www/yourapp/artisan</span>).
                            </li>
                            <li>
                                cPanel: open “Cron Jobs” and paste the command there. Use the full path (example: <span class="font-mono">/home/&lt;cpanel_user&gt;/yourapp/artisan</span>).
                            </li>
                            <li>
                                Docker: cron usually runs on the host machine, and you call the scheduler inside the container using <span class="font-mono">docker exec</span>.
                            </li>
                        </ul>
                    </div>
                    <div class="rounded-lg border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/20 p-4">
                        <p class="font-semibold text-amber-900 dark:text-amber-200">Common setup mistakes</p>
                        <ul class="mt-2 list-disc pl-6 space-y-1 text-sm text-amber-900/90 dark:text-amber-200/90">
                            <li>Using <span class="font-mono">/path/to/artisan</span> literally — replace it with your real path.</li>
                            <li>Cron runs, but the app can’t write to <span class="font-mono">storage</span> / <span class="font-mono">bootstrap/cache</span> due to permissions.</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="email-providers" class="space-y-4 scroll-mt-32">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Email providers & environment variables</h2>
                <div class="space-y-4 text-gray-700 dark:text-gray-300">
                    <p>
                        The application supports multiple delivery server types (SMTP and provider APIs). Provider credentials are read from environment variables.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                            <p class="font-semibold text-gray-900 dark:text-white">Google OAuth (optional)</p>
                            <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                                <li><span class="font-mono">GOOGLE_CLIENT_ID</span></li>
                                <li><span class="font-mono">GOOGLE_CLIENT_SECRET</span></li>
                                <li><span class="font-mono">GOOGLE_REDIRECT_URI</span></li>
                            </ul>
                        </div>

                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                            <p class="font-semibold text-gray-900 dark:text-white">Mailgun (optional)</p>
                            <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                                <li><span class="font-mono">MAILGUN_DOMAIN</span></li>
                                <li><span class="font-mono">MAILGUN_SECRET</span></li>
                                <li><span class="font-mono">MAILGUN_ENDPOINT</span> (default: <span class="font-mono">api.mailgun.net</span>)</li>
                            </ul>
                        </div>

                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                            <p class="font-semibold text-gray-900 dark:text-white">Amazon SES (optional)</p>
                            <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                                <li><span class="font-mono">AWS_ACCESS_KEY_ID</span></li>
                                <li><span class="font-mono">AWS_SECRET_ACCESS_KEY</span></li>
                                <li><span class="font-mono">AWS_DEFAULT_REGION</span> (default: <span class="font-mono">us-east-1</span>)</li>
                            </ul>
                        </div>

                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                            <p class="font-semibold text-gray-900 dark:text-white">SendGrid (optional)</p>
                            <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                                <li><span class="font-mono">SENDGRID_API_KEY</span></li>
                            </ul>
                        </div>

                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                            <p class="font-semibold text-gray-900 dark:text-white">Postmark (optional)</p>
                            <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                                <li><span class="font-mono">POSTMARK_TOKEN</span></li>
                            </ul>
                        </div>

                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                            <p class="font-semibold text-gray-900 dark:text-white">SparkPost (optional)</p>
                            <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                                <li><span class="font-mono">SPARKPOST_SECRET</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <section id="billing" class="space-y-4 scroll-mt-32">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Billing configuration (optional)</h2>
                <div class="space-y-3 text-gray-700 dark:text-gray-300">
                    <p>
                        For non-coders, the easiest way to set up billing is from the Admin panel:
                        <span class="font-semibold">Admin</span> &rarr; <span class="font-semibold">Payment Methods</span>.
                        You can enable a provider, choose <span class="font-semibold">Live</span> or <span class="font-semibold">Sandbox</span>, paste the keys, and choose the default provider.
                    </p>
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">Quick setup (no-code)</p>
                        <ol class="mt-2 list-decimal pl-6 space-y-2 text-sm">
                            <li>Go to <span class="font-semibold">Admin</span> &rarr; <span class="font-semibold">Payment Methods</span>.</li>
                            <li>Enable the provider you want to use (Stripe / Razorpay / Flutterwave).</li>
                            <li>Select <span class="font-semibold">Sandbox</span> for testing, then switch to <span class="font-semibold">Live</span> later.</li>
                            <li>Paste the keys/secrets into the matching fields and click <span class="font-semibold">Save</span>.</li>
                            <li>Set the <span class="font-mono">Default Provider</span> dropdown to the provider you enabled.</li>
                            <li>Make sure your <span class="font-mono">APP_URL</span> is correct and your site is reachable over HTTPS (required for webhooks in real deployments).</li>
                        </ol>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                            <p class="font-semibold text-gray-900 dark:text-white">Stripe (recommended for subscriptions)</p>
                            <div class="mt-2 space-y-2 text-sm">
                                <p class="font-semibold text-gray-900 dark:text-white">What you need</p>
                                <ul class="list-disc pl-6 space-y-1">
                                    <li>Stripe account</li>
                                    <li>API keys: <span class="font-mono">STRIPE_SECRET</span>, <span class="font-mono">STRIPE_PUBLIC_KEY</span></li>
                                    <li>Webhook secret: <span class="font-mono">STRIPE_WEBHOOK_SECRET</span></li>
                                </ul>
                                <p class="font-semibold text-gray-900 dark:text-white">Webhook</p>
                                <p>
                                    Create a webhook endpoint in Stripe pointing to:
                                    <span class="font-mono">POST /webhooks/stripe</span>
                                </p>
                                <p class="font-semibold text-gray-900 dark:text-white">Minimum events</p>
                                <ul class="list-disc pl-6 space-y-1">
                                    <li><span class="font-mono">customer.subscription.created</span></li>
                                    <li><span class="font-mono">customer.subscription.updated</span></li>
                                    <li><span class="font-mono">customer.subscription.deleted</span></li>
                                    <li><span class="font-mono">invoice.payment_succeeded</span></li>
                                    <li><span class="font-mono">invoice.payment_failed</span></li>
                                </ul>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                            <p class="font-semibold text-gray-900 dark:text-white">Razorpay (Payment Links)</p>
                            <div class="mt-2 space-y-2 text-sm">
                                <p class="font-semibold text-gray-900 dark:text-white">What you need</p>
                                <ul class="list-disc pl-6 space-y-1">
                                    <li>Razorpay account</li>
                                    <li>API keys: <span class="font-mono">RAZORPAY_KEY_ID</span>, <span class="font-mono">RAZORPAY_KEY_SECRET</span></li>
                                    <li>Webhook secret: <span class="font-mono">RAZORPAY_WEBHOOK_SECRET</span></li>
                                </ul>
                                <p class="font-semibold text-gray-900 dark:text-white">Webhook</p>
                                <p>
                                    Create a Razorpay webhook pointing to:
                                    <span class="font-mono">POST /webhooks/razorpay</span>
                                    (event: <span class="font-mono">payment_link.paid</span>).
                                </p>
                                <p class="font-semibold text-gray-900 dark:text-white">Customer return URL</p>
                                <p>
                                    After payment, Razorpay can send customers back to:
                                    <span class="font-mono">GET /billing/razorpay/callback</span>.
                                </p>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                            <p class="font-semibold text-gray-900 dark:text-white">Flutterwave</p>
                            <div class="mt-2 space-y-2 text-sm">
                                <p class="font-semibold text-gray-900 dark:text-white">What you need</p>
                                <ul class="list-disc pl-6 space-y-1">
                                    <li>Flutterwave account</li>
                                    <li>API keys: <span class="font-mono">FLUTTERWAVE_PUBLIC_KEY</span>, <span class="font-mono">FLUTTERWAVE_SECRET</span></li>
                                    <li>(Optional) <span class="font-mono">FLUTTERWAVE_ENCRYPTION_KEY</span></li>
                                    <li>Webhook secret: <span class="font-mono">FLUTTERWAVE_WEBHOOK_SECRET</span></li>
                                </ul>
                                <p class="font-semibold text-gray-900 dark:text-white">Webhook</p>
                                <p>
                                    Create a Flutterwave webhook pointing to:
                                    <span class="font-mono">POST /webhooks/flutterwave</span>
                                </p>
                                <p class="font-semibold text-gray-900 dark:text-white">Customer return URL</p>
                                <p>
                                    After payment, Flutterwave can redirect back to:
                                    <span class="font-mono">GET /billing/flutterwave/callback</span>
                                </p>
                            </div>
                        </div>

                        <div class="rounded-lg border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/20 p-4">
                            <p class="font-semibold text-amber-900 dark:text-amber-200">PayPal / Paystack</p>
                            <div class="mt-2 space-y-2 text-sm text-amber-900/90 dark:text-amber-200/90">
                                <p>
                                    You can store PayPal/Paystack credentials in Payment Methods, but checkout/portal/webhook flows are not implemented yet.
                                    If you need PayPal or Paystack fully working, you will need a developer to implement the provider integration.
                                </p>
                                <ul class="list-disc pl-6 space-y-1">
                                    <li><span class="font-mono">PAYPAL_CLIENT_ID</span>, <span class="font-mono">PAYPAL_CLIENT_SECRET</span></li>
                                    <li><span class="font-mono">PAYSTACK_PUBLIC_KEY</span>, <span class="font-mono">PAYSTACK_SECRET</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">Advanced (env variables)</p>
                        <p class="mt-2 text-sm">
                            If you prefer environment variables, billing can also be controlled by <span class="font-mono">BILLING_PROVIDER</span>.
                            Admin Payment Methods stores the same values in Settings.
                        </p>
                    </div>
                </div>
            </section>

            <section id="monetization" class="space-y-4 scroll-mt-32">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Monetization</h2>
                <div class="space-y-6 text-gray-700 dark:text-gray-300">
                    <p>
                        Monetization features help you sell access to the platform using plans and subscriptions, apply discounts with coupons, and track charges using invoices.
                    </p>

                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">Before you start</p>
                        <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                            <li><span class="font-semibold">Stripe</span>, <span class="font-semibold">Razorpay</span>, and <span class="font-semibold">Flutterwave</span> are supported billing providers.</li>
                            <li><span class="font-semibold">PayPal / Paystack</span> appear in settings, but checkout/portal/webhooks are not implemented yet.</li>
                            <li>For billing to work reliably, your app must be reachable over the internet (for webhooks) and your <span class="font-mono">APP_URL</span> must be correct.</li>
                            <li>Invoices and subscription status updates depend on your provider configuration and webhooks.</li>
                        </ul>
                    </div>

                    <div class="rounded-lg border border-blue-200 dark:border-blue-900/50 bg-blue-50 dark:bg-blue-900/20 p-4">
                        <p class="font-semibold text-blue-900 dark:text-blue-200">Quick setup checklist (Stripe)</p>
                        <ol class="mt-2 list-decimal pl-6 space-y-2 text-sm text-blue-900/90 dark:text-blue-200/90">
                            <li>
                                Add Stripe keys:
                                <span class="font-mono">STRIPE_SECRET</span>, <span class="font-mono">STRIPE_PUBLIC_KEY</span>, and <span class="font-mono">STRIPE_WEBHOOK_SECRET</span>.
                            </li>
                            <li>
                                Create a Stripe webhook endpoint pointing to:
                                <span class="font-mono">https://your-domain.com/webhooks/stripe</span>.
                            </li>
                            <li>
                                In Stripe, enable events for the webhook (at minimum):
                                <span class="font-mono">customer.subscription.created</span>,
                                <span class="font-mono">customer.subscription.updated</span>,
                                <span class="font-mono">customer.subscription.deleted</span>,
                                <span class="font-mono">invoice.payment_succeeded</span>,
                                <span class="font-mono">invoice.payment_failed</span>.
                            </li>
                            <li>
                                Create Stripe Products/Prices, then copy the Stripe IDs into your plan.
                                Recommended: use a <span class="font-mono">price_...</span> ID.
                            </li>
                            <li>
                                Create Plans in Admin, connect them to a Customer Group (limits/features), and set <span class="font-mono">is_active</span>.
                            </li>
                            <li>
                                (Optional) Create Coupons in Admin. Coupons are synced to Stripe and become usable at checkout.
                            </li>
                            <li>
                                Test checkout from a customer account and confirm the subscription becomes <span class="font-mono">active</span> or <span class="font-mono">trialing</span>.
                            </li>
                        </ol>
                    </div>

                    <div id="monetization-invoices" class="space-y-3 scroll-mt-32">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Invoices</h3>
                        <div class="space-y-2">
                            <p>
                                Invoices represent payments made by customers (for example: subscription renewals). They are typically generated automatically when a payment succeeds.
                            </p>
                            <p>
                                In the current implementation, invoices are read from Stripe (and/or from stored Stripe webhook events) in the Admin invoices area.
                                Customers can also see recent billing history on their Billing page.
                            </p>
                            <ul class="list-disc pl-6 space-y-1">
                                <li>Invoice totals may include discounts (coupons) and taxes (VAT/Tax), depending on configuration</li>
                                <li>Keep your billing provider configured and webhooks reachable for accurate invoice status updates</li>
                            </ul>
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                <p class="font-semibold text-gray-900 dark:text-white">If invoices are missing</p>
                                <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                                    <li>Confirm Stripe keys are set and valid (<span class="font-mono">STRIPE_SECRET</span> is required).</li>
                                    <li>Confirm the Stripe webhook endpoint is reachable from the internet and the webhook secret matches.</li>
                                    <li>If you recently changed keys or webhook secret, update them and retry a test checkout.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div id="monetization-coupons" class="space-y-3 scroll-mt-32">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Coupons</h3>
                        <div class="space-y-2">
                            <p>
                                Coupons are discount rules that can be applied to a plan checkout or subscription.
                            </p>
                            <p>
                                Create coupons from the Admin area. When you save a coupon, the app attempts to sync it to Stripe and creates a Promotion Code.
                                Customers can enter the coupon code during checkout.
                            </p>
                            <ul class="list-disc pl-6 space-y-1">
                                <li>Use coupons for promotions (percentage or fixed amount depending on your provider integration)</li>
                                <li>Consider setting limits (expiry date, maximum redemptions, and eligible plans)</li>
                            </ul>
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                <p class="font-semibold text-gray-900 dark:text-white">Typical coupon settings (beginner-friendly)</p>
                                <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                                    <li><span class="font-semibold">Type</span>: percent discount or fixed amount discount.</li>
                                    <li><span class="font-semibold">Duration</span>: once, repeating (for N months), or forever.</li>
                                    <li><span class="font-semibold">Max redemptions</span>: how many total uses are allowed.</li>
                                    <li><span class="font-semibold">Start/end dates</span>: when the code becomes valid and when it expires.</li>
                                </ul>
                            </div>
                            <div class="rounded-lg border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/20 p-4">
                                <p class="font-semibold text-amber-900 dark:text-amber-200">If a coupon code is rejected at checkout</p>
                                <ul class="mt-2 list-disc pl-6 space-y-1 text-sm text-amber-900/90 dark:text-amber-200/90">
                                    <li>Ensure the coupon is active and not expired.</li>
                                    <li>Ensure it has a Stripe Promotion Code (Stripe sync must succeed).</li>
                                    <li>Coupon codes are stored uppercase; enter the same code shown in Admin.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div id="monetization-plans" class="space-y-3 scroll-mt-32">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Plans</h3>
                        <div class="space-y-2">
                            <p>
                                Plans define what customers can purchase (features/limits and pricing). Customers subscribe to a plan to access the app.
                            </p>
                            <p>
                                Plans are tied to a Customer Group which defines limits (like monthly email quota) and permissions/features. In other words:
                                <span class="font-semibold">Plans control pricing</span>, and <span class="font-semibold">Customer Groups control access/limits</span>.
                            </p>
                            <ul class="list-disc pl-6 space-y-1">
                                <li>Create plans in the Admin area, then assign/offer them to customers</li>
                                <li>Ensure plan pricing matches your billing provider products/prices, if applicable</li>
                            </ul>
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                <p class="font-semibold text-gray-900 dark:text-white">Stripe IDs (important)</p>
                                <div class="mt-2 space-y-2 text-sm">
                                    <p>
                                        The plan can store Stripe identifiers:
                                        <span class="font-mono">stripe_price_id</span> (recommended) and/or <span class="font-mono">stripe_product_id</span>.
                                    </p>
                                    <p>
                                        Best practice:
                                        create a recurring Price in Stripe (monthly/yearly), then paste the <span class="font-mono">price_...</span> ID into the plan.
                                        This ensures Stripe handles billing correctly and consistently.
                                    </p>
                                    <p>
                                        If you only provide a product ID (<span class="font-mono">prod_...</span>), checkout can still work, but you must ensure the local plan price/currency/billing cycle
                                        match what you expect to charge.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="monetization-payment-methods" class="space-y-3 scroll-mt-32">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Payment method</h3>
                        <div class="space-y-2">
                            <p>
                                A payment method is how a customer pays for their subscription (for example: a card via Stripe).
                            </p>
                            <p>
                                To choose what customers use for checkout, go to <span class="font-semibold">Admin</span> &rarr; <span class="font-semibold">Payment Methods</span> and set the <span class="font-mono">Default Provider</span>.
                                Customers then go to <span class="font-semibold">Customer</span> &rarr; <span class="font-semibold">Billing</span> and pick a plan.
                            </p>
                            <p>
                                Customers typically manage their card/payment method through the billing provider's customer portal.
                                In Stripe, this is the Stripe Billing Portal.
                            </p>
                            <ul class="list-disc pl-6 space-y-1">
                                <li>Customers must have an active payment method to enable renewals</li>
                                <li>If renewals fail, verify payment method status and your billing provider webhooks</li>
                            </ul>
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                <p class="font-semibold text-gray-900 dark:text-white">Testing checklist (beginner-friendly)</p>
                                <ol class="mt-2 list-decimal pl-6 space-y-1 text-sm">
                                    <li>Create at least one active Plan in Admin.</li>
                                    <li>Enable one billing provider and set it as the default provider.</li>
                                    <li>Log in as a customer and go to the Billing page.</li>
                                    <li>Start checkout and complete a test payment (sandbox/test mode).</li>
                                    <li>Confirm the subscription status becomes <span class="font-mono">active</span> or <span class="font-mono">trialing</span>.</li>
                                </ol>
                            </div>
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                <p class="font-semibold text-gray-900 dark:text-white">If renewals fail</p>
                                <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                                    <li>Ask the customer to update their card in the billing portal.</li>
                                    <li>Check the subscription status (e.g. <span class="font-mono">past_due</span>) and confirm Stripe webhooks are being received.</li>
                                    <li>Confirm your server time and SSL are correct; webhooks often require HTTPS in real deployments.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div id="monetization-vat-tax" class="space-y-3 scroll-mt-32">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">VAT/Tax</h3>
                        <div class="space-y-2">
                            <p>
                                If you charge VAT/Tax, confirm your billing provider tax settings and ensure invoices display the correct tax amounts.
                            </p>
                            <ul class="list-disc pl-6 space-y-1">
                                <li>Taxes may depend on customer billing country/state and tax ID (if collected)</li>
                                <li>Always verify tax rules with your accountant or local regulations</li>
                            </ul>
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                <p class="font-semibold text-gray-900 dark:text-white">Practical notes</p>
                                <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                                    <li>Stripe checkout can collect billing address; taxes are typically based on the address details.</li>
                                    <li>If you require VAT IDs, make sure your process collects and stores them for your customers.</li>
                                    <li>Test with a few different billing addresses to confirm the expected result before going live.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="servers" class="space-y-4 scroll-mt-32">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Servers</h2>
                <div class="space-y-6 text-gray-700 dark:text-gray-300">
                    <p>
                        Servers are integrations used to send emails (Delivery Servers) and to receive/parse bounce mailbox messages (Bounce Servers).
                    </p>

                    <div id="servers-delivery" class="space-y-3 scroll-mt-32">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delivery servers</h3>
                        <div class="space-y-2">
                            <p>
                                A delivery server is the outbound channel used to send campaigns and autoresponders. You typically configure one of:
                            </p>
                            <ul class="list-disc pl-6 space-y-1">
                                <li>SMTP credentials (host, port, username/password, encryption)</li>
                                <li>Provider API credentials (e.g. Mailgun/SES/SendGrid/Postmark/SparkPost)</li>
                            </ul>
                            <p>
                                Campaigns require selecting a delivery server. Make sure the server is verified/working before sending to real subscribers.
                            </p>
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                <p class="font-semibold text-gray-900 dark:text-white">Operational notes</p>
                                <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                                    <li>Sending runs through queues; ensure a worker is running (see <a href="#queues" class="underline">Background jobs</a>).</li>
                                    <li>For best deliverability, set up DNS (SPF/DKIM) according to your provider.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div id="servers-bounce" class="space-y-3 scroll-mt-32">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Bounce servers</h3>
                        <div class="space-y-2">
                            <p>
                                A bounce server is an IMAP/POP3 mailbox that receives delivery failure notifications and complaint messages.
                                The app periodically connects to this mailbox, parses messages, and marks subscribers as bounced/complained.
                            </p>
                            <ul class="list-disc pl-6 space-y-1">
                                <li>IMAP/POP3 host, port, username/password, encryption</li>
                                <li>Folder selection (if your provider stores bounces in a specific folder)</li>
                            </ul>
                            <div class="rounded-lg border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/20 p-4">
                                <p class="font-semibold text-amber-900 dark:text-amber-200">Requirements</p>
                                <p class="mt-1 text-sm text-amber-900/90 dark:text-amber-200/90">
                                    Bounce processing requires the PHP IMAP extension, and the scheduler must run regularly (see <a href="#scheduler" class="underline">Scheduler / Cron</a>).
                                </p>
                            </div>
                            <p>
                                If you also use provider webhooks for bounce/complaint events, see <a href="#webhooks" class="underline">Webhooks</a>.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="webhooks" class="space-y-4 scroll-mt-32">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Webhooks (bounces/complaints/tracking)</h2>
                <div class="space-y-3 text-gray-700 dark:text-gray-300">
                    <p>
                        If you use provider webhooks for bounces/opens/clicks, configure your provider to call these endpoints:
                    </p>
                    <ul class="list-disc pl-6 space-y-1">
                        <li><span class="font-mono">POST /webhooks/mailgun</span> (also <span class="font-mono">/webhooks/mailgun/bounce</span>, <span class="font-mono">/webhooks/mailgun/open</span>, <span class="font-mono">/webhooks/mailgun/click</span>)</li>
                        <li><span class="font-mono">POST /webhooks/ses</span> (also <span class="font-mono">/webhooks/ses/bounce</span>, <span class="font-mono">/webhooks/ses/open</span>, <span class="font-mono">/webhooks/ses/click</span>)</li>
                        <li><span class="font-mono">POST /webhooks/sendgrid</span> (also <span class="font-mono">/webhooks/sendgrid/bounce</span>, <span class="font-mono">/webhooks/sendgrid/open</span>, <span class="font-mono">/webhooks/sendgrid/click</span>)</li>
                    </ul>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Make sure <span class="font-mono">APP_URL</span> is set correctly so webhook URLs and tracking URLs are generated properly.
                    </p>
                </div>
            </section>

            <section id="usage" class="space-y-4 scroll-mt-32">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Using the app</h2>
                <div class="space-y-6 text-gray-700 dark:text-gray-300">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Admin area</h3>
                        <ul class="mt-2 list-disc pl-6 space-y-1">
                            <li>Manage customers, groups, and plans</li>
                            <li>Configure system settings</li>
                            <li>Manage delivery servers, bounce servers, sending domains, tracking domains</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Customer area</h3>
                        <ul class="mt-2 list-disc pl-6 space-y-1">
                            <li>Create email lists and add subscribers (manual or CSV import)</li>
                            <li>Create templates</li>
                            <li>Create campaigns and choose an email list + delivery server</li>
                            <li>Track results via analytics</li>
                        </ul>
                    </div>
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">CSV import notes</p>
                        <p class="mt-2 text-sm">
                            Subscriber CSV imports are queued. Ensure a queue worker is running.
                            Uploaded CSV files are stored under <span class="font-mono">storage/app/imports</span>.
                        </p>
                    </div>
                </div>
            </section>

            <section id="marketing" class="space-y-4 scroll-mt-32">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Marketing</h2>
                <div class="space-y-3 text-gray-700 dark:text-gray-300">
                    <p>
                        Marketing features are available in the Customer area and are centered around a simple flow:
                        build an audience (email list) -> verify addresses (validation) -> send messages (campaigns) -> automate follow-ups (autoresponders).
                    </p>
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">Operational requirements</p>
                        <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                            <li>Queue worker is required for imports, validations, and sending</li>
                            <li>Cron/scheduler is recommended for starting scheduled campaigns and automation</li>
                            <li>Use a verified delivery server and (recommended) tracking domain for best deliverability</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="marketing-email-lists" class="space-y-4 scroll-mt-32">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Email lists</h2>
                <div class="space-y-3 text-gray-700 dark:text-gray-300">
                    <p>
                        Email lists are your audiences. Campaigns and autoresponders target a list (and optionally segments, if enabled).
                        Each list contains subscribers with fields like email, name, status, and tags.
                    </p>
                    <div class="space-y-2">
                        <p class="font-semibold text-gray-900 dark:text-white">How to build a list</p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li>Manual add subscribers in the list Subscribers area</li>
                            <li>CSV import (recommended for large lists). Imports are queued and stored under <span class="font-mono">storage/app/imports</span></li>
                            <li>Subscription forms to collect signups (double opt-in is recommended for deliverability)</li>
                        </ul>
                    </div>
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">Best practices</p>
                        <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                            <li>Keep lists permission-based (only email people who opted in)</li>
                            <li>Regularly remove bounces/complaints and unsubscribe requests</li>
                            <li>Validate imported lists before sending high-volume campaigns</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="marketing-campaigns" class="space-y-4 scroll-mt-32">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Campaigns</h2>
                <div class="space-y-3 text-gray-700 dark:text-gray-300">
                    <p>
                        Campaigns send a message to subscribers in an email list using a delivery server. You can send immediately or schedule a start time.
                    </p>
                    <div class="space-y-2">
                        <p class="font-semibold text-gray-900 dark:text-white">Typical workflow</p>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Create or select an email list with confirmed/active subscribers</li>
                            <li>Create a template (or write your content) and confirm links and tracking settings</li>
                            <li>Create a campaign, select list + delivery server, and set subject/from details</li>
                            <li>Send a test email, then send now or schedule</li>
                        </ol>
                    </div>
                    <div class="rounded-lg border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/20 p-4">
                        <p class="font-semibold text-amber-900 dark:text-amber-200">Important</p>
                        <p class="mt-1 text-sm text-amber-900/90 dark:text-amber-200/90">
                            Sending runs via background jobs. If a queue worker is not running, campaigns will appear stuck and no emails will be delivered.
                        </p>
                    </div>
                </div>
            </section>

            <section id="marketing-validation" class="space-y-4 scroll-mt-32">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Validation</h2>
                <div class="space-y-3 text-gray-700 dark:text-gray-300">
                    <p>
                        Email validation helps improve deliverability by identifying invalid, risky, or disposable addresses before you send.
                        Validation runs in the background and results can be used to clean lists.
                    </p>
                    <div class="space-y-2">
                        <p class="font-semibold text-gray-900 dark:text-white">When to validate</p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li>After importing a large list</li>
                            <li>Before re-engagement or high-volume campaigns</li>
                            <li>Periodically for old lists (addresses decay over time)</li>
                        </ul>
                    </div>
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">Notes</p>
                        <ul class="mt-2 list-disc pl-6 space-y-1 text-sm">
                            <li>Validation is queued; ensure a worker is running</li>
                            <li>Different tools/providers may return different confidence levels; use your own sending thresholds</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="marketing-autoresponders" class="space-y-4 scroll-mt-32">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Autoresponders</h2>
                <div class="space-y-3 text-gray-700 dark:text-gray-300">
                    <p>
                        Autoresponders are automated sequences triggered by subscriber events (for example: when someone joins a list).
                        They are ideal for welcome sequences, onboarding, and drip campaigns.
                    </p>
                    <div class="space-y-2">
                        <p class="font-semibold text-gray-900 dark:text-white">How they work</p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li>Create an autoresponder for a list and choose a trigger</li>
                            <li>Add steps (emails) with delays (for example: send immediately, then after 2 days)</li>
                            <li>When the trigger fires, the app schedules and sends each step via background jobs</li>
                        </ul>
                    </div>
                    <div class="rounded-lg border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/20 p-4">
                        <p class="font-semibold text-amber-900 dark:text-amber-200">Important</p>
                        <p class="mt-1 text-sm text-amber-900/90 dark:text-amber-200/90">
                            Autoresponders require queues (and usually the scheduler) to process runs reliably.
                        </p>
                    </div>
                </div>
            </section>

            <section id="marketing-spintax-spam-scoring" class="space-y-4 scroll-mt-32">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Spintax & Spam Scoring</h2>
                <div class="space-y-4 text-gray-700 dark:text-gray-300">
                    <p>
                        MailZen includes advanced content optimization features to improve email deliverability and avoid spam filters.
                    </p>
                    
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-lg border border-blue-200 dark:border-blue-900/50 bg-blue-50 dark:bg-blue-900/20 p-4">
                            <h3 class="font-semibold text-blue-900 dark:text-blue-200">Spintax</h3>
                            <p class="mt-2 text-sm text-blue-900/90 dark:text-blue-200/90">
                                Create multiple content variations using {option1|option2|option3} syntax. Each recipient gets a unique version to avoid spam filters.
                            </p>
                        </div>
                        <div class="rounded-lg border border-green-200 dark:border-green-900/50 bg-green-50 dark:bg-green-900/20 p-4">
                            <h3 class="font-semibold text-green-900 dark:text-green-200">Spam Scoring</h3>
                            <p class="mt-2 text-sm text-green-900/90 dark:text-green-200/90">
                                Automatic content analysis before sending. High-scoring emails can be blocked to protect sender reputation.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h3 class="font-semibold text-gray-900 dark:text-white">How to Use Spintax</h3>
                        <div class="space-y-2">
                            <p>Enable "Spintax" in campaign settings and use this syntax:</p>
                            <div class="rounded-md bg-gray-100 dark:bg-gray-800 p-3">
                                <code class="text-sm">{Hello|Hi|Hey} {there|world},</code><br>
                                <code class="text-sm">{Check out|See|Discover} our {amazing|great|fantastic} {offer|deal|promotion}!</code>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Each recipient gets a random combination: "Hello world, Check out our amazing offer!"
                            </p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Spam Score Levels</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Score</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Risk Level</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr>
                                        <td class="px-4 py-2 text-sm">0-5</td>
                                        <td class="px-4 py-2 text-sm">Low Risk</td>
                                        <td class="px-4 py-2 text-sm">Sends normally</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-2 text-sm">6-10</td>
                                        <td class="px-4 py-2 text-sm">Medium Risk</td>
                                        <td class="px-4 py-2 text-sm">Warning logged</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-2 text-sm">11-14</td>
                                        <td class="px-4 py-2 text-sm">High Risk</td>
                                        <td class="px-4 py-2 text-sm">Critical warning</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-2 text-sm font-semibold">15+</td>
                                        <td class="px-4 py-2 text-sm font-semibold text-red-600">Very High Risk</td>
                                        <td class="px-4 py-2 text-sm font-semibold text-red-600">Blocked</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Common Spam Triggers</h3>
                        <div class="space-y-2">
                            <div class="rounded-md bg-red-50 dark:bg-red-900/20 p-3 border border-red-200 dark:border-red-900/50">
                                <p class="font-medium text-red-900 dark:text-red-200">❌ Subject Line Issues</p>
                                <ul class="mt-1 text-sm text-red-900/90 dark:text-red-200/90 list-disc pl-4">
                                    <li>ALL CAPS subjects</li>
                                    <li>Multiple !!! or ???</li>
                                    <li>Words: "free", "winner", "urgent", "act now"</li>
                                    <li>Dollar amounts: $100, 50%</li>
                                </ul>
                            </div>
                            <div class="rounded-md bg-amber-50 dark:bg-amber-900/20 p-3 border border-amber-200 dark:border-amber-900/50">
                                <p class="font-medium text-amber-900 dark:text-amber-200">⚠️ Content Issues</p>
                                <ul class="mt-1 text-sm text-amber-900/90 dark:text-amber-200/90 list-disc pl-4">
                                    <li>Hidden text or tiny fonts</li>
                                    <li>Low text-to-image ratio</li>
                                    <li>Phrases: "click here", "order now", "limited time"</li>
                                    <li>Excessive uppercase letters</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Configuration</h3>
                        <div class="space-y-2">
                            <p>Add to your <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">.env</code> file:</p>
                            <div class="rounded-md bg-gray-100 dark:bg-gray-800 p-3 font-mono text-sm">
                                <div>SPAM_BLOCKING_THRESHOLD=15</div>
                                <div>SPAM_SCORING_ENABLED_BY_DEFAULT=false</div>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Each campaign can independently enable/disable these features in settings.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Best Practices</h3>
                        <div class="grid gap-2 md:grid-cols-2">
                            <div class="space-y-1">
                                <p class="font-medium text-green-700 dark:text-green-300">✅ Spintax Tips</p>
                                <ul class="text-sm text-gray-600 dark:text-gray-400 list-disc pl-4">
                                    <li>Keep variations natural and readable</li>
                                    <li>Use in key places: subject, opening, CTA</li>
                                    <li>Test combinations before sending</li>
                                </ul>
                            </div>
                            <div class="space-y-1">
                                <p class="font-medium text-green-700 dark:text-green-300">✅ Spam Prevention</p>
                                <ul class="text-sm text-gray-600 dark:text-gray-400 list-disc pl-4">
                                    <li>Use normal capitalization</li>
                                    <li>Limit punctuation to 1-2 marks</li>
                                    <li>Use business email domains</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-blue-200 dark:border-blue-900/50 bg-blue-50 dark:bg-blue-900/20 p-4">
                        <p class="font-semibold text-blue-900 dark:text-blue-200">📖 Learn More</p>
                        <p class="mt-1 text-sm text-blue-900/90 dark:text-blue-200/90">
                            See the complete documentation: <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">SPINTAX_AND_SPAM_SCORING.md</code> for detailed examples, API usage, and troubleshooting.
                        </p>
                    </div>
                </div>
            </section>

            <section id="troubleshooting" class="space-y-4 scroll-mt-32">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Troubleshooting</h2>
                <div class="space-y-2 text-gray-700 dark:text-gray-300">
                    <ul class="list-disc pl-6 space-y-1">
                        <li>If campaigns do not send: verify a queue worker is running and the campaign has confirmed subscribers.</li>
                        <li>If scheduled campaigns do not start: verify cron is running <span class="font-mono">schedule:run</span>.</li>
                        <li>If bounce processing does not work: install PHP IMAP and ensure bounce servers are configured and active.</li>
                        <li>If webhooks do not arrive: verify your site is publicly accessible and provider settings are correct.</li>
                    </ul>
                </div>
            </section>
                </div>
            </main>

            <aside class="hidden xl:block xl:col-span-2">
                <div class="sticky top-24">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">On this page</div>
                    <nav class="mt-4 space-y-2">
                        <a href="#base-url" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Base URL</a>
                        <a href="#requirements" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Requirements</a>
                        <a href="#installation" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Installation</a>
                        <a href="#installation-mysql" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Local MySQL setup</a>
                        <a href="#installation-cpanel" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Install on cPanel</a>
                        <a href="#installation-cloud" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Install on cloud hosting</a>
                        <a href="#installation-docker" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Install using Docker</a>
                        <a href="#queues" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Background jobs</a>
                        <a href="#scheduler" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Scheduler / Cron</a>
                        <a href="#email-providers" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Email providers</a>
                        <a href="#google-integrations" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Google Sheets & Drive</a>
                        <a href="#billing" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Billing</a>
                        <a href="#monetization" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Monetization</a>
                        <a href="#monetization-invoices" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Invoices</a>
                        <a href="#monetization-coupons" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Coupons</a>
                        <a href="#monetization-plans" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Plans</a>
                        <a href="#monetization-payment-methods" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Payment method</a>
                        <a href="#monetization-vat-tax" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">VAT/Tax</a>
                        <a href="#servers" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Servers</a>
                        <a href="#servers-delivery" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Delivery servers</a>
                        <a href="#servers-bounce" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Bounce servers</a>
                        <a href="#webhooks" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Webhooks</a>
                        <a href="#usage" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Using the app</a>
                        <a href="#troubleshooting" class="block text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Troubleshooting</a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection

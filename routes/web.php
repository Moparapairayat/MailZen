<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\ActivityController as AdminActivityController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\BlogPostController as AdminBlogPostController;
use App\Http\Controllers\PublicBlogController;
use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Customer\SearchController as CustomerSearchController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/storage/{path}', [\App\Http\Controllers\PublicStorageController::class, 'show'])
    ->where('path', '.*');

Route::get('/public/storage/{path}', [\App\Http\Controllers\PublicStorageController::class, 'show'])
    ->where('path', '.*');

Route::prefix('install')
    ->name('install.')
    ->middleware(\App\Http\Middleware\RedirectIfInstalled::class)
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\InstallController::class, 'welcome'])->name('welcome');
        Route::get('/requirements', [\App\Http\Controllers\InstallController::class, 'requirements'])->name('requirements');
        Route::get('/setup', [\App\Http\Controllers\InstallController::class, 'setup'])->name('setup');
        Route::post('/setup', [\App\Http\Controllers\InstallController::class, 'storeSetup'])->name('setup.store');
        Route::get('/done', [\App\Http\Controllers\InstallController::class, 'done'])->name('done');
    });

// Unified Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);

    Route::get('/register', [\App\Http\Controllers\AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);

    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');

    Route::match(['GET', 'POST'], '/language', function (\Illuminate\Http\Request $request) {
        $locale = trim((string) $request->input('locale', ''));

        if ($locale === '') {
            return back();
        }

        $svc = app(\App\Translation\LocaleJsonService::class);

        if (!$svc->validateLocaleCode($locale) || !$svc->localeExists($locale)) {
            return back()->with('error', __('Invalid language.'));
        }

        try {
            $request->session()->put('locale', $locale);
        } catch (\Throwable $e) {
            //
        }

        return redirect()->to(url()->previous())->withCookie(cookie()->forever('locale', $locale));
    })->name('language.guest.update');
});

Route::middleware(['auth.any'])->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();

        if (\Illuminate\Support\Facades\Auth::guard('admin')->check()) {
            return redirect()->intended(route('admin.dashboard'))->with('success', 'Email verified successfully.');
        }

        return redirect()->intended(route('customer.dashboard'))->with('success', 'Email verified successfully.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        try {
            $request->user()->sendEmailVerificationNotification();
            return back()->with('success', 'Verification link sent.');
        } catch (\Throwable $e) {
            \Log::error('Failed to send verification email', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Email send failed.');
        }
    })->middleware(['throttle:6,1'])->name('verification.send');
});

Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

Route::get('/cron/run', [\App\Http\Controllers\CronController::class, 'run'])
    ->middleware(['throttle:6,1'])
    ->name('cron.run');

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login']);
    });

    Route::middleware(['auth:admin', 'user.active'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->middleware('admin.access:admin.dashboard')
            ->name('dashboard');

        Route::post('/language', function (\Illuminate\Http\Request $request) {
            $locale = trim((string) $request->input('locale', ''));

            if ($locale === '') {
                return back();
            }

            $svc = app(\App\Translation\LocaleJsonService::class);

            if (!$svc->validateLocaleCode($locale) || !$svc->localeExists($locale)) {
                return back()->with('error', __('Invalid language.'));
            }

            $user = $request->user('admin');
            if ($user) {
                $user->forceFill(['language' => $locale])->save();
            }

            return back();
        })->name('language.update');

        // Blog Posts
        Route::resource('blog-posts', AdminBlogPostController::class)
            ->middleware('admin.access:admin.blog_posts')
            ->except(['show']);
        Route::post('blog-posts/{blog_post}/publish', [AdminBlogPostController::class, 'publish'])
            ->middleware(['admin.access:admin.blog_posts.edit', 'demo.prevent'])
            ->name('blog-posts.publish');
        Route::post('blog-posts/{blog_post}/unpublish', [AdminBlogPostController::class, 'unpublish'])
            ->middleware(['admin.access:admin.blog_posts.edit', 'demo.prevent'])
            ->name('blog-posts.unpublish');

        Route::prefix('homepages')->name('homepages.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\HomepageTextController::class, 'index'])
                ->middleware('admin.access:admin.settings.access')
                ->name('index');
            Route::get('/{variant}', [\App\Http\Controllers\Admin\HomepageTextController::class, 'edit'])
                ->middleware('admin.access:admin.settings.access')
                ->where('variant', '[1-4]')
                ->name('edit');
            Route::post('/{variant}', [\App\Http\Controllers\Admin\HomepageTextController::class, 'update'])
                ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
                ->where('variant', '[1-4]')
                ->name('update');
        });

        Route::prefix('site-pages')->name('site-pages.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SitePageController::class, 'index'])
                ->middleware('admin.access:admin.settings.access')
                ->name('index');

            Route::get('/features', [\App\Http\Controllers\Admin\SitePageController::class, 'editFeatures'])
                ->middleware('admin.access:admin.settings.access')
                ->name('features.edit');
            Route::post('/features', [\App\Http\Controllers\Admin\SitePageController::class, 'updateFeatures'])
                ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
                ->name('features.update');

            Route::get('/pricing', [\App\Http\Controllers\Admin\SitePageController::class, 'editPricing'])
                ->middleware('admin.access:admin.settings.access')
                ->name('pricing.edit');
            Route::post('/pricing', [\App\Http\Controllers\Admin\SitePageController::class, 'updatePricing'])
                ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
                ->name('pricing.update');

            Route::get('/login', [\App\Http\Controllers\Admin\SitePageController::class, 'editLogin'])
                ->middleware('admin.access:admin.settings.access')
                ->name('login.edit');
            Route::post('/login', [\App\Http\Controllers\Admin\SitePageController::class, 'updateLogin'])
                ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
                ->name('login.update');

            Route::get('/register', [\App\Http\Controllers\Admin\SitePageController::class, 'editRegister'])
                ->middleware('admin.access:admin.settings.access')
                ->name('register.edit');
            Route::post('/register', [\App\Http\Controllers\Admin\SitePageController::class, 'updateRegister'])
                ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
                ->name('register.update');
        });

        Route::get('/accessibility-control', [\App\Http\Controllers\Admin\AccessibilityControlController::class, 'index'])
            ->middleware('admin.access:admin.accessibility_control')
            ->name('accessibility-control.index');
        Route::get('/accessibility-control/create', [\App\Http\Controllers\Admin\AccessibilityControlController::class, 'create'])
            ->middleware('admin.access:admin.accessibility_control')
            ->name('accessibility-control.create');
        Route::post('/accessibility-control', [\App\Http\Controllers\Admin\AccessibilityControlController::class, 'update'])
            ->middleware(['admin.access:admin.accessibility_control', 'demo.prevent'])
            ->name('accessibility-control.update');

        Route::get('/activities', [AdminActivityController::class, 'index'])
            ->middleware('admin.access:admin.activities')
            ->name('activities.index');
        
        // Notifications
        Route::get('/notifications/feed', [\App\Http\Controllers\Admin\NotificationController::class, 'feed'])
            ->middleware('admin.access:admin.notifications.access')
            ->name('notifications.feed');
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllRead'])
            ->middleware('admin.access:admin.notifications.edit')
            ->name('notifications.mark-all-read');
        
        // Invoices
        Route::get('/invoices', [\App\Http\Controllers\Admin\InvoiceController::class, 'index'])
            ->middleware('admin.access:admin.invoices')
            ->name('invoices.index');
        Route::get('/invoices/{invoice}', [\App\Http\Controllers\Admin\InvoiceController::class, 'show'])
            ->middleware('admin.access:admin.invoices')
            ->name('invoices.show');

        // Manual Payments
        Route::get('/manual-payments', [\App\Http\Controllers\Admin\ManualPaymentController::class, 'index'])
            ->middleware('admin.access:admin.payment_methods.access')
            ->name('manual-payments.index');
        Route::get('/manual-payments/{manual_payment}', [\App\Http\Controllers\Admin\ManualPaymentController::class, 'show'])
            ->middleware('admin.access:admin.payment_methods.access')
            ->name('manual-payments.show');
        Route::post('/manual-payments/{manual_payment}/approve', [\App\Http\Controllers\Admin\ManualPaymentController::class, 'approve'])
            ->middleware(['admin.access:admin.payment_methods.edit', 'demo.prevent'])
            ->name('manual-payments.approve');
        Route::post('/manual-payments/{manual_payment}/reject', [\App\Http\Controllers\Admin\ManualPaymentController::class, 'reject'])
            ->middleware(['admin.access:admin.payment_methods.edit', 'demo.prevent'])
            ->name('manual-payments.reject');

        // Coupons
        Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class)->middleware('admin.access:admin.coupons')->except(['show']);

        // Public Templates
        Route::resource('public-template-categories', \App\Http\Controllers\Admin\PublicTemplateCategoryController::class)
            ->middleware('admin.access:admin.public_template_categories')
            ->except(['show']);
        Route::resource('public-templates', \App\Http\Controllers\Admin\PublicTemplateController::class)
            ->middleware('admin.access:admin.public_templates')
            ->except(['show']);

        // Built-in Templates (File Gallery)
        Route::get('built-in-templates/{builtInTemplateSetting}/edit', [\App\Http\Controllers\Admin\BuiltInTemplateSettingController::class, 'edit'])
            ->middleware('admin.access:admin.public_templates.edit')
            ->name('built-in-templates.edit');
        Route::put('built-in-templates/{builtInTemplateSetting}', [\App\Http\Controllers\Admin\BuiltInTemplateSettingController::class, 'update'])
            ->middleware('admin.access:admin.public_templates.edit')
            ->name('built-in-templates.update');

        // Template Import (Admin)
        Route::get('templates/import/gallery', [\App\Http\Controllers\Admin\TemplateImportController::class, 'importGallery'])
            ->middleware('admin.access:admin.public_templates.access')
            ->name('templates.import.gallery');
        Route::get('templates/import/file/{key}/content', [\App\Http\Controllers\Admin\TemplateImportController::class, 'importFileContent'])
            ->middleware('admin.access:admin.public_templates.access')
            ->name('templates.import.file.content');
        Route::get('templates/import/public/{publicTemplate}/content', [\App\Http\Controllers\Admin\TemplateImportController::class, 'importPublicContent'])
            ->middleware('admin.access:admin.public_templates.access')
            ->name('templates.import.public.content');
        
        // Plans
        Route::resource('plans', \App\Http\Controllers\Admin\PlanController::class)->middleware('admin.access:admin.plans');
        Route::post('plans/pricing-settings', [\App\Http\Controllers\Admin\PlanController::class, 'updatePricingSettings'])
            ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
            ->name('plans.pricing-settings.update');

        // Users
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->middleware('admin.access:admin.users')->parameters([
            'users' => 'user',
        ]);
        
        // Customers
        Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class)->middleware('admin.access:admin.customers')->parameters([
            'customers' => 'customer'
        ]);
        Route::patch('customers/{customer}/email-verification', [\App\Http\Controllers\Admin\CustomerController::class, 'updateEmailVerification'])
            ->middleware('admin.access:admin.customers.edit')
            ->name('customers.email-verification.update');
        
        // Customer Groups
        Route::resource('customer-groups', \App\Http\Controllers\Admin\CustomerGroupController::class)->middleware('admin.access:admin.customer_groups')->parameters([
            'customer-groups' => 'customer_group'
        ]);
        
        // Settings
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])
            ->middleware('admin.access:admin.settings.access')
            ->name('settings.index');
        Route::get('/settings/logs', [\App\Http\Controllers\Admin\SettingController::class, 'logs'])
            ->middleware('admin.access:admin.settings.access')
            ->name('settings.logs');
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])
            ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
            ->name('settings.update');

        Route::post('/settings/templates/activate/{template}', [\App\Http\Controllers\Admin\SettingController::class, 'activateTemplate'])
            ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
            ->name('settings.templates.activate');
        Route::get('/settings/templates/{template}/edit', [\App\Http\Controllers\Admin\SettingController::class, 'editTemplate'])
            ->middleware('admin.access:admin.settings.access')
            ->name('settings.templates.edit');
        Route::get('/settings/templates/{template}/values', [\App\Http\Controllers\Admin\SettingController::class, 'templateValues'])
            ->middleware('admin.access:admin.settings.access')
            ->name('settings.templates.values');
        Route::post('/settings/templates/{template}', [\App\Http\Controllers\Admin\SettingController::class, 'updateTemplate'])
            ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
            ->name('settings.templates.update');
        Route::get('/settings/templates/{template}/preview', [\App\Http\Controllers\Admin\SettingController::class, 'previewTemplate'])
            ->middleware('admin.access:admin.settings.access')
            ->name('settings.templates.preview');

        Route::post('/settings/templates/external/sync', [\App\Http\Controllers\Admin\SettingController::class, 'syncExternalTemplates'])
            ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
            ->name('settings.templates.external.sync');
        Route::post('/settings/templates/external/{externalId}/fetch-json', [\App\Http\Controllers\Admin\SettingController::class, 'fetchExternalTemplateJson'])
            ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
            ->name('settings.templates.external.fetch-json');
        Route::post('/settings/templates/external-license/activate', [\App\Http\Controllers\Admin\SettingController::class, 'activateExternalTemplatesLicense'])
            ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
            ->name('settings.templates.external-license.activate');
        Route::post('/settings/templates/external-license/deactivate', [\App\Http\Controllers\Admin\SettingController::class, 'deactivateExternalTemplatesLicense'])
            ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
            ->name('settings.templates.external-license.deactivate');
        Route::post('/settings/update-download', [\App\Http\Controllers\Admin\SettingController::class, 'requestUpdateDownload'])
            ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
            ->name('settings.update-download');
        Route::post('/settings/install-update', [\App\Http\Controllers\Admin\SettingController::class, 'installUpdate'])
            ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
            ->name('settings.install-update');
        Route::post('/settings/license-activate', [\App\Http\Controllers\Admin\SettingController::class, 'activateLicense'])
            ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
            ->name('settings.license-activate');
        Route::post('/settings/license-deactivate', [\App\Http\Controllers\Admin\SettingController::class, 'deactivateLicense'])
            ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
            ->name('settings.license-deactivate');

        Route::post('/settings/cron/regenerate-token', [\App\Http\Controllers\Admin\SettingController::class, 'regenerateCronToken'])
            ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
            ->name('settings.cron.regenerate-token');
        Route::post('/settings/cron/run-now', [\App\Http\Controllers\Admin\SettingController::class, 'runCronNow'])
            ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
            ->name('settings.cron.run-now');

        Route::get('/settings/secret/{key}', [\App\Http\Controllers\Admin\SettingController::class, 'revealSecret'])
            ->middleware('admin.access:admin.settings.access')
            ->name('settings.secret');

        // Integrations
        Route::get('/integrations', [\App\Http\Controllers\Admin\IntegrationController::class, 'index'])
            ->middleware('admin.access:admin.delivery_servers')
            ->name('integrations.index');

        Route::get('/integrations/wordpress/plugin', [\App\Http\Controllers\Admin\IntegrationController::class, 'downloadWordpressPlugin'])
            ->middleware('admin.access:admin.delivery_servers')
            ->name('integrations.wordpress.plugin');

        // AI Tools
        Route::prefix('ai-tools')
            ->name('ai-tools.')
            ->middleware('admin.access:admin.ai_tools.access')
            ->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\AiToolController::class, 'index'])->name('index');
                Route::get('/dashboard', [\App\Http\Controllers\Admin\AiToolController::class, 'dashboard'])->name('dashboard');
                Route::get('/email-text-generator', [\App\Http\Controllers\Admin\AiToolController::class, 'emailTextGenerator'])->name('email-text-generator');
                Route::post('/email-text-generator', [\App\Http\Controllers\Admin\AiToolController::class, 'generateEmailText'])
                    ->middleware(['admin.access:admin.ai_tools.create', 'demo.prevent'])
                    ->name('email-text-generator.generate');
            });

        // API
        Route::get('/api', [\App\Http\Controllers\Admin\ApiTokenController::class, 'index'])
            ->middleware('admin.access:admin.api.access')
            ->name('api.index');
        Route::post('/api', [\App\Http\Controllers\Admin\ApiTokenController::class, 'store'])
            ->middleware(['admin.access:admin.api.create', 'demo.prevent'])
            ->name('api.store');
        Route::delete('/api/{tokenId}', [\App\Http\Controllers\Admin\ApiTokenController::class, 'destroy'])
            ->middleware(['admin.access:admin.api.delete', 'demo.prevent'])
            ->name('api.destroy');

        // Support Tickets
        Route::get('/support-tickets', [\App\Http\Controllers\Admin\SupportTicketController::class, 'index'])
            ->middleware('admin.access:admin.support_tickets.access')
            ->name('support-tickets.index');
        Route::get('/support-tickets/{support_ticket}', [\App\Http\Controllers\Admin\SupportTicketController::class, 'show'])
            ->middleware('admin.access:admin.support_tickets.access')
            ->name('support-tickets.show');
        Route::get('/support-tickets/{support_ticket}/drawer', [\App\Http\Controllers\Admin\SupportTicketController::class, 'drawer'])
            ->middleware('admin.access:admin.support_tickets.access')
            ->name('support-tickets.drawer');
        Route::post('/support-tickets/{support_ticket}/reply', [\App\Http\Controllers\Admin\SupportTicketController::class, 'reply'])
            ->middleware(['admin.access:admin.support_tickets.edit', 'demo.prevent'])
            ->name('support-tickets.reply');
        Route::post('/support-tickets/{support_ticket}/status', [\App\Http\Controllers\Admin\SupportTicketController::class, 'setStatus'])
            ->middleware(['admin.access:admin.support_tickets.edit', 'demo.prevent'])
            ->name('support-tickets.status');
        Route::post('/support-tickets/{support_ticket}/priority', [\App\Http\Controllers\Admin\SupportTicketController::class, 'setPriority'])
            ->middleware(['admin.access:admin.support_tickets.edit', 'demo.prevent'])
            ->name('support-tickets.priority');

        // Translations (file-based JSON)
        Route::prefix('translations')
            ->name('translations.')
            ->middleware('admin.access:admin.translations')
            ->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\TranslationJsonLocaleController::class, 'index'])
                    ->name('locales.index');

                Route::get('/download-main', [\App\Http\Controllers\Admin\TranslationJsonLocaleController::class, 'downloadMain'])
                    ->middleware('admin.access:admin.translations.edit')
                    ->name('locales.download_main');

                Route::get('/{locale}/download', [\App\Http\Controllers\Admin\TranslationJsonLocaleController::class, 'downloadLocale'])
                    ->middleware('admin.access:admin.translations.edit')
                    ->name('locales.download');

                Route::get('/{locale}/edit', [\App\Http\Controllers\Admin\TranslationJsonLocaleController::class, 'edit'])
                    ->middleware('admin.access:admin.translations.edit')
                    ->name('locales.edit');

                Route::put('/{locale}', [\App\Http\Controllers\Admin\TranslationJsonLocaleController::class, 'update'])
                    ->middleware(['admin.access:admin.translations.edit', 'demo.prevent'])
                    ->name('locales.update');

                Route::post('/{locale}/active', [\App\Http\Controllers\Admin\TranslationJsonLocaleController::class, 'setActive'])
                    ->middleware(['admin.access:admin.translations.edit', 'demo.prevent'])
                    ->name('locales.active');

                Route::post('/upload', [\App\Http\Controllers\Admin\TranslationJsonLocaleController::class, 'upload'])
                    ->middleware(['admin.access:admin.translations.create', 'demo.prevent'])
                    ->name('locales.upload');

                Route::get('/{locale}/bulk', [\App\Http\Controllers\Admin\TranslationJsonBulkController::class, 'edit'])
                    ->middleware('admin.access:admin.translations.edit')
                    ->name('bulk.edit');

                Route::post('/{locale}/bulk', [\App\Http\Controllers\Admin\TranslationJsonBulkController::class, 'update'])
                    ->middleware(['admin.access:admin.translations.edit', 'demo.prevent'])
                    ->name('bulk.update');
            });
        
        // Campaigns
        Route::resource('campaigns', \App\Http\Controllers\Admin\CampaignController::class)->middleware('admin.access:admin.campaigns');
        
        // Email Lists
        Route::resource('lists', \App\Http\Controllers\Admin\EmailListController::class)->middleware('admin.access:admin.lists')->parameters([
            'lists' => 'list'
        ]);
        
        // Email Validation
        Route::get('email-validation', [\App\Http\Controllers\Admin\EmailValidationController::class, 'index'])
            ->middleware('admin.access:admin.email_validation')
            ->name('email-validation.index');

        Route::prefix('email-validation')
            ->name('email-validation.')
            ->middleware('admin.access:admin.email_validation')
            ->group(function () {
                Route::get('tools/create', [\App\Http\Controllers\Admin\EmailValidationToolController::class, 'create'])
                    ->name('tools.create');
                Route::post('tools', [\App\Http\Controllers\Admin\EmailValidationToolController::class, 'store'])
                    ->name('tools.store');
                Route::get('tools/{tool}/edit', [\App\Http\Controllers\Admin\EmailValidationToolController::class, 'edit'])
                    ->name('tools.edit');
                Route::put('tools/{tool}', [\App\Http\Controllers\Admin\EmailValidationToolController::class, 'update'])
                    ->name('tools.update');
                Route::delete('tools/{tool}', [\App\Http\Controllers\Admin\EmailValidationToolController::class, 'destroy'])
                    ->name('tools.destroy');
            });
        
        // Delivery Servers
        Route::resource('delivery-servers', \App\Http\Controllers\Admin\DeliveryServerController::class)->middleware('admin.access:admin.delivery_servers')->parameters([
            'delivery-servers' => 'delivery_server'
        ]);

        Route::get('delivery-servers/{delivery_server}/secret', [\App\Http\Controllers\Admin\DeliveryServerController::class, 'revealSecret'])
            ->middleware('admin.access:admin.delivery_servers')
            ->name('delivery-servers.secret');
        Route::post('delivery-servers/{delivery_server}/make-primary', [\App\Http\Controllers\Admin\DeliveryServerController::class, 'makePrimary'])
            ->middleware(['admin.access:admin.delivery_servers.make_primary', 'demo.prevent'])
            ->name('delivery-servers.make-primary');

        Route::post('delivery-servers/{delivery_server}/clock-skew-check', [\App\Http\Controllers\Admin\DeliveryServerController::class, 'clockSkewCheck'])
            ->middleware(['admin.access:admin.delivery_servers', 'demo.prevent'])
            ->name('delivery-servers.clock-skew-check');

        Route::post('delivery-servers/{delivery_server}/restart-workers', [\App\Http\Controllers\Admin\DeliveryServerController::class, 'restartWorkers'])
            ->middleware(['admin.access:admin.delivery_servers', 'demo.prevent'])
            ->name('delivery-servers.restart-workers');
        Route::get('delivery-servers/test/connection', [\App\Http\Controllers\Admin\DeliveryServerController::class, 'showTest'])
            ->middleware('admin.access:admin.delivery_servers.test')
            ->name('delivery-servers.test');
        Route::post('delivery-servers/test/connection', [\App\Http\Controllers\Admin\DeliveryServerController::class, 'test'])
            ->middleware(['admin.access:admin.delivery_servers.test', 'demo.prevent'])
            ->name('delivery-servers.test.send');
        Route::get('delivery-servers/{delivery_server}/verify/{token}', [\App\Http\Controllers\Admin\DeliveryServerController::class, 'verify'])
            ->middleware('admin.access:admin.delivery_servers.access')
            ->name('delivery-servers.verify');
        Route::post('delivery-servers/{delivery_server}/resend-verification', [\App\Http\Controllers\Admin\DeliveryServerController::class, 'resendVerification'])
            ->middleware(['admin.access:admin.delivery_servers.resend_verification', 'demo.prevent'])
            ->name('delivery-servers.resend-verification');

        // Sending Domains
        Route::get('sending-domains', [\App\Http\Controllers\Admin\SendingDomainController::class, 'index'])
            ->middleware('admin.access:admin.sending_domains')
            ->name('sending-domains.index');
        Route::get('sending-domains/create', [\App\Http\Controllers\Admin\SendingDomainController::class, 'create'])
            ->middleware('admin.access:admin.sending_domains')
            ->name('sending-domains.create');
        Route::post('sending-domains', [\App\Http\Controllers\Admin\SendingDomainController::class, 'store'])
            ->middleware('admin.access:admin.sending_domains')
            ->name('sending-domains.store');
        Route::get('sending-domains/{sending_domain}/edit', [\App\Http\Controllers\Admin\SendingDomainController::class, 'edit'])
            ->middleware('admin.access:admin.sending_domains')
            ->name('sending-domains.edit');
        Route::put('sending-domains/{sending_domain}', [\App\Http\Controllers\Admin\SendingDomainController::class, 'update'])
            ->middleware('admin.access:admin.sending_domains')
            ->name('sending-domains.update');
        Route::delete('sending-domains/{sending_domain}', [\App\Http\Controllers\Admin\SendingDomainController::class, 'destroy'])
            ->middleware('admin.access:admin.sending_domains')
            ->name('sending-domains.destroy');
        Route::post('sending-domains/{sending_domain}/make-primary', [\App\Http\Controllers\Admin\SendingDomainController::class, 'makePrimary'])
            ->middleware('admin.access:admin.sending_domains.edit')
            ->name('sending-domains.make-primary');
        Route::get('sending-domains/{sending_domain}', [\App\Http\Controllers\Admin\SendingDomainController::class, 'show'])
            ->middleware('admin.access:admin.sending_domains')
            ->name('sending-domains.show');
        Route::post('sending-domains/{sending_domain}/verify', [\App\Http\Controllers\Admin\SendingDomainController::class, 'verify'])
            ->middleware('admin.access:admin.sending_domains.edit')
            ->name('sending-domains.verify');
        Route::post('sending-domains/{sending_domain}/mark-verified', [\App\Http\Controllers\Admin\SendingDomainController::class, 'markVerified'])
            ->middleware('admin.access:admin.sending_domains.edit')
            ->name('sending-domains.mark-verified');

        // Tracking Domains
        Route::get('tracking-domains', [\App\Http\Controllers\Admin\TrackingDomainController::class, 'index'])
            ->middleware('admin.access:admin.tracking_domains')
            ->name('tracking-domains.index');
        Route::get('tracking-domains/create', [\App\Http\Controllers\Admin\TrackingDomainController::class, 'create'])
            ->middleware('admin.access:admin.tracking_domains')
            ->name('tracking-domains.create');
        Route::post('tracking-domains', [\App\Http\Controllers\Admin\TrackingDomainController::class, 'store'])
            ->middleware('admin.access:admin.tracking_domains')
            ->name('tracking-domains.store');
        Route::get('tracking-domains/{tracking_domain}/edit', [\App\Http\Controllers\Admin\TrackingDomainController::class, 'edit'])
            ->middleware('admin.access:admin.tracking_domains')
            ->name('tracking-domains.edit');
        Route::put('tracking-domains/{tracking_domain}', [\App\Http\Controllers\Admin\TrackingDomainController::class, 'update'])
            ->middleware('admin.access:admin.tracking_domains')
            ->name('tracking-domains.update');
        Route::delete('tracking-domains/{tracking_domain}', [\App\Http\Controllers\Admin\TrackingDomainController::class, 'destroy'])
            ->middleware('admin.access:admin.tracking_domains')
            ->name('tracking-domains.destroy');
        Route::get('tracking-domains/{tracking_domain}', [\App\Http\Controllers\Admin\TrackingDomainController::class, 'show'])
            ->middleware('admin.access:admin.tracking_domains')
            ->name('tracking-domains.show');
        Route::post('tracking-domains/{tracking_domain}/verify', [\App\Http\Controllers\Admin\TrackingDomainController::class, 'verify'])
            ->middleware('admin.access:admin.tracking_domains.edit')
            ->name('tracking-domains.verify');
        Route::post('tracking-domains/{tracking_domain}/mark-verified', [\App\Http\Controllers\Admin\TrackingDomainController::class, 'markVerified'])
            ->middleware('admin.access:admin.tracking_domains.edit')
            ->name('tracking-domains.mark-verified');

        // Bounce Servers
        Route::resource('bounce-servers', \App\Http\Controllers\Admin\BounceServerController::class)->middleware('admin.access:admin.bounce_servers')->parameters([
            'bounce-servers' => 'bounce_server'
        ]);

        // Reply Servers
        Route::resource('reply-servers', \App\Http\Controllers\Admin\ReplyServerController::class)->middleware('admin.access:admin.reply_servers')->parameters([
            'reply-servers' => 'reply_server'
        ]);

        // Bounced Emails
        Route::get('bounced-emails', [\App\Http\Controllers\Admin\BouncedEmailController::class, 'index'])
            ->middleware('admin.access:admin.bounced_emails')
            ->name('bounced-emails.index');

        Route::get('bounced-emails/{bounced_email}', [\App\Http\Controllers\Admin\BouncedEmailController::class, 'show'])
            ->middleware('admin.access:admin.bounced_emails')
            ->name('bounced-emails.show');

        // Profile
        Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])
            ->middleware('admin.access:admin.profile')
            ->name('profile.edit');
        Route::put('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])
            ->middleware('admin.access:admin.profile')
            ->name('profile.update');

        // Search
        Route::get('/search/suggest', [\App\Http\Controllers\Admin\SearchController::class, 'suggest'])
            ->middleware('admin.access:admin.search.access')
            ->name('search.suggest');
        Route::get('/search', [\App\Http\Controllers\Admin\SearchController::class, 'index'])
            ->middleware('admin.access:admin.search.access')
            ->name('search.index');

        // Payment Methods
        Route::get('/payment-methods', [\App\Http\Controllers\Admin\PaymentMethodController::class, 'index'])
            ->middleware('admin.access:admin.payment_methods.access')
            ->name('payment-methods.index');
        Route::post('/payment-methods', [\App\Http\Controllers\Admin\PaymentMethodController::class, 'update'])
            ->middleware('admin.access:admin.payment_methods.edit')
            ->name('payment-methods.update');

        // Vat/Tax
        Route::get('/vat-tax', [\App\Http\Controllers\Admin\VatTaxController::class, 'index'])
            ->middleware('admin.access:admin.vat_tax.access')
            ->name('vat-tax.index');
        Route::post('/vat-tax', [\App\Http\Controllers\Admin\VatTaxController::class, 'update'])
            ->middleware('admin.access:admin.vat_tax.edit')
            ->name('vat-tax.update');

        // Affiliates
        Route::get('/affiliates', [\App\Http\Controllers\Admin\AffiliateController::class, 'index'])
            ->middleware('admin.access:admin.settings.access')
            ->name('affiliates.index');

        Route::get('/affiliates/referrals', [\App\Http\Controllers\Admin\AffiliateController::class, 'referrals'])
            ->middleware('admin.access:admin.settings.access')
            ->name('affiliates.referrals');

        Route::get('/affiliates/commissions', [\App\Http\Controllers\Admin\AffiliateController::class, 'commissions'])
            ->middleware('admin.access:admin.settings.access')
            ->name('affiliates.commissions');

        Route::get('/affiliates/payouts', [\App\Http\Controllers\Admin\AffiliateController::class, 'payouts'])
            ->middleware('admin.access:admin.settings.access')
            ->name('affiliates.payouts');

        Route::get('/affiliates/settings', [\App\Http\Controllers\Admin\AffiliateController::class, 'settings'])
            ->middleware('admin.access:admin.settings.access')
            ->name('affiliates.settings');

        Route::post('/affiliates/settings', [\App\Http\Controllers\Admin\AffiliateController::class, 'updateSettings'])
            ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
            ->name('affiliates.settings.update');

        Route::get('/affiliates/create', [\App\Http\Controllers\Admin\AffiliateController::class, 'create'])
            ->middleware('admin.access:admin.settings.edit')
            ->name('affiliates.create');

        Route::post('/affiliates', [\App\Http\Controllers\Admin\AffiliateController::class, 'store'])
            ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
            ->name('affiliates.store');

        Route::post('/affiliates/{affiliate}/approve', [\App\Http\Controllers\Admin\AffiliateController::class, 'approve'])
            ->middleware(['admin.access:admin.settings.edit', 'demo.prevent'])
            ->name('affiliates.approve');
    });
});

Route::get('/billing/flutterwave/callback', \App\Http\Controllers\Billing\FlutterwaveCallbackController::class)
    ->name('billing.flutterwave.callback')
    ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

Route::get('/billing/razorpay/callback', \App\Http\Controllers\Billing\RazorpayCallbackController::class)
    ->name('billing.razorpay.callback')
    ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

Route::get('/billing/paypal/callback', \App\Http\Controllers\Billing\PayPalCallbackController::class)
    ->name('billing.paypal.callback')
    ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/auth/google', [\App\Http\Controllers\Admin\GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
        Route::get('/auth/google/callback', [\App\Http\Controllers\Admin\GoogleAuthController::class, 'callback'])->name('auth.google.callback');
    });
});

// Customer Routes
Route::prefix('customer')->name('customer.')->group(function () {
    // Registration (still separate for customers)
    Route::middleware('guest:customer')->group(function () {
        Route::get('/register', function () {
            return redirect()->route('register');
        })->name('register');

        Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
        Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [CustomerAuthController::class, 'login'])->name('login.submit');

        Route::get('/auth/google', [\App\Http\Controllers\Customer\GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
        Route::get('/auth/google/callback', [\App\Http\Controllers\Customer\GoogleAuthController::class, 'callback'])->name('auth.google.callback');
    });

    Route::middleware(['auth:customer', 'customer.active', 'customer.verified_if_required'])->group(function () {
        Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
        Route::post('/language', function (\Illuminate\Http\Request $request) {
            $locale = trim((string) $request->input('locale', ''));

            if ($locale === '') {
                return back();
            }

            $svc = app(\App\Translation\LocaleJsonService::class);

            if (!$svc->validateLocaleCode($locale) || !$svc->localeExists($locale)) {
                return back()->with('error', __('Invalid language.'));
            }

            $user = $request->user('customer');
            if ($user) {
                $user->forceFill(['language' => $locale])->save();
            }

            return back();
        })->name('language.update');
        // Profile
        Route::get('/profile', [CustomerProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [CustomerProfileController::class, 'update'])->name('profile.update');
        // Global search
        Route::get('/search/suggest', [CustomerSearchController::class, 'suggest'])->name('search.suggest');
        Route::get('/search', [CustomerSearchController::class, 'index'])->name('search.index');
        
        // Notifications
        Route::get('/notifications/feed', [\App\Http\Controllers\Customer\NotificationController::class, 'feed'])->name('notifications.feed');
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Customer\NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

        // API
        Route::get('/api', [\App\Http\Controllers\Customer\ApiTokenController::class, 'index'])
            ->middleware('customer.access:api.permissions.can_access_api')
            ->name('api.index');
        Route::post('/api', [\App\Http\Controllers\Customer\ApiTokenController::class, 'store'])
            ->middleware(['customer.access:api.permissions.can_create_api_keys', 'demo.prevent'])
            ->name('api.store');
        Route::delete('/api/{tokenId}', [\App\Http\Controllers\Customer\ApiTokenController::class, 'destroy'])
            ->middleware(['customer.access:api.permissions.can_delete_api_keys', 'demo.prevent'])
            ->name('api.destroy');
        
        // Email Lists
        Route::resource('lists', \App\Http\Controllers\Customer\EmailListController::class)->parameters([
            'lists' => 'list'
        ]);
        Route::post('lists/{list}/tags', [\App\Http\Controllers\Customer\EmailListController::class, 'storeTag'])->name('lists.tags.store');
        Route::put('lists/{list}/tags', [\App\Http\Controllers\Customer\EmailListController::class, 'updateTag'])->name('lists.tags.update');
        Route::delete('lists/{list}/tags', [\App\Http\Controllers\Customer\EmailListController::class, 'destroyTag'])->name('lists.tags.destroy');
        Route::get('segments/create', [\App\Http\Controllers\Customer\ListSegmentController::class, 'create'])->name('segments.create');
        Route::post('segments', [\App\Http\Controllers\Customer\ListSegmentController::class, 'store'])->name('segments.store');
        Route::get('lists/{list}/settings', [\App\Http\Controllers\Customer\EmailListSettingsController::class, 'edit'])->name('lists.settings');
        Route::put('lists/{list}/settings', [\App\Http\Controllers\Customer\EmailListSettingsController::class, 'update'])->name('lists.settings.update');
        
        // List Subscribers
        // Import routes must be defined before resource route to avoid conflicts
        Route::get('lists/{list}/subscribers/import', [\App\Http\Controllers\Customer\ListSubscriberController::class, 'showImport'])->name('lists.subscribers.import');
        Route::post('lists/{list}/subscribers/import', [\App\Http\Controllers\Customer\ListSubscriberController::class, 'import'])->name('lists.subscribers.import.store');
        Route::get('lists/{list}/subscribers/export', [\App\Http\Controllers\Customer\ListSubscriberController::class, 'export'])->name('lists.subscribers.export');
        Route::post('lists/{list}/subscribers/import/ajax/start', [\App\Http\Controllers\Customer\ListSubscriberController::class, 'importAjaxStart'])->name('lists.subscribers.import.ajax.start');
        Route::post('lists/{list}/subscribers/import/ajax/step', [\App\Http\Controllers\Customer\ListSubscriberController::class, 'importAjaxStep'])->name('lists.subscribers.import.ajax.step');
        Route::get('lists/{list}/subscribers/import/stats', [\App\Http\Controllers\Customer\ListSubscriberController::class, 'importStats'])->name('lists.subscribers.import.stats');
        
        // Bulk operations
        Route::delete('lists/{list}/subscribers/bulk-delete', [\App\Http\Controllers\Customer\ListSubscriberController::class, 'bulkDelete'])->name('lists.subscribers.bulk-delete');
        Route::post('lists/{list}/subscribers/bulk-confirm', [\App\Http\Controllers\Customer\ListSubscriberController::class, 'bulkConfirm'])->name('lists.subscribers.bulk-confirm');
        Route::post('lists/{list}/subscribers/bulk-unsubscribe', [\App\Http\Controllers\Customer\ListSubscriberController::class, 'bulkUnsubscribe'])->name('lists.subscribers.bulk-unsubscribe');
        Route::post('lists/{list}/subscribers/bulk-resend', [\App\Http\Controllers\Customer\ListSubscriberController::class, 'bulkResend'])->name('lists.subscribers.bulk-resend');
        
        Route::resource('lists.subscribers', \App\Http\Controllers\Customer\ListSubscriberController::class)->parameters([
            'lists' => 'list',
            'subscribers' => 'subscriber'
        ]);
        Route::post('lists/{list}/subscribers/{subscriber}/confirm', [\App\Http\Controllers\Customer\ListSubscriberController::class, 'confirm'])->name('lists.subscribers.confirm');
        Route::post('lists/{list}/subscribers/{subscriber}/unsubscribe', [\App\Http\Controllers\Customer\ListSubscriberController::class, 'unsubscribe'])->name('lists.subscribers.unsubscribe');
        Route::post('lists/{list}/subscribers/{subscriber}/resend-confirmation', [\App\Http\Controllers\Customer\ListSubscriberController::class, 'resendConfirmation'])->name('lists.subscribers.resend-confirmation');
        
        // Subscription Forms
        Route::resource('lists.forms', \App\Http\Controllers\Customer\SubscriptionFormController::class)->parameters([
            'lists' => 'list',
            'forms' => 'form'
        ]);

        Route::post('lists/{list}/forms/upload-icon', [\App\Http\Controllers\Customer\SubscriptionFormController::class, 'uploadIcon'])
            ->name('lists.forms.upload-icon');

        // Forms (Global)
        Route::get('forms', [\App\Http\Controllers\Customer\FormsController::class, 'index'])->name('forms.index');
        Route::get('forms/create', [\App\Http\Controllers\Customer\FormsController::class, 'create'])->name('forms.create');
        Route::post('forms', [\App\Http\Controllers\Customer\FormsController::class, 'store'])->name('forms.store');
        
        // Campaigns
        Route::resource('campaigns', \App\Http\Controllers\Customer\CampaignController::class);
        Route::post('campaigns/{campaign}/duplicate', [\App\Http\Controllers\Customer\CampaignController::class, 'duplicate'])->name('campaigns.duplicate');
        Route::post('campaigns/{campaign}/start', [\App\Http\Controllers\Customer\CampaignController::class, 'start'])->name('campaigns.start');
        Route::post('campaigns/{campaign}/pause', [\App\Http\Controllers\Customer\CampaignController::class, 'pause'])->name('campaigns.pause');
        Route::post('campaigns/{campaign}/resume', [\App\Http\Controllers\Customer\CampaignController::class, 'resume'])->name('campaigns.resume');
        Route::post('campaigns/{campaign}/rerun', [\App\Http\Controllers\Customer\CampaignController::class, 'rerun'])->name('campaigns.rerun');
        Route::post('campaigns/spam-preview', [\App\Http\Controllers\Customer\CampaignController::class, 'previewSpamScore'])->name('campaigns.spam-preview');
        Route::get('campaigns/{campaign}/stats', [\App\Http\Controllers\Customer\CampaignController::class, 'stats'])->name('campaigns.stats');
        Route::get('campaigns/{campaign}/recipients', [\App\Http\Controllers\Customer\CampaignController::class, 'recipients'])->name('campaigns.recipients');
        Route::get('campaigns/{campaign}/replies', [\App\Http\Controllers\Customer\CampaignController::class, 'replies'])->name('campaigns.replies');
        Route::get('campaigns/{campaign}/ab-test', [\App\Http\Controllers\Customer\CampaignController::class, 'showAbTest'])->middleware('customer.access:campaigns.features.ab_testing')->name('campaigns.ab-test');
        Route::post('campaigns/{campaign}/ab-test', [\App\Http\Controllers\Customer\CampaignController::class, 'storeAbTest'])->middleware('customer.access:campaigns.features.ab_testing')->name('campaigns.ab-test.store');
        Route::post('campaigns/{campaign}/variants/{variant}/select-winner', [\App\Http\Controllers\Customer\CampaignController::class, 'selectWinner'])->middleware('customer.access:campaigns.features.ab_testing')->name('campaigns.variants.select-winner');
        
        // Auto Responders
        Route::resource('auto-responders', \App\Http\Controllers\Customer\AutoResponderController::class)->middleware('customer.access:autoresponders.enabled');

        Route::resource('automations', \App\Http\Controllers\Customer\AutomationController::class)->middleware('customer.access:automations.enabled');

        // Tracking Domains
        Route::resource('tracking-domains', \App\Http\Controllers\Customer\TrackingDomainController::class);
        Route::post('tracking-domains/{tracking_domain}/verify', [\App\Http\Controllers\Customer\TrackingDomainController::class, 'verify'])->name('tracking-domains.verify');
        Route::post('tracking-domains/{tracking_domain}/mark-verified', [\App\Http\Controllers\Customer\TrackingDomainController::class, 'markVerified'])->name('tracking-domains.mark-verified');
        
        // Sending Domains
        Route::resource('sending-domains', \App\Http\Controllers\Customer\SendingDomainController::class);
        Route::post('sending-domains/{sending_domain}/verify', [\App\Http\Controllers\Customer\SendingDomainController::class, 'verify'])->name('sending-domains.verify');
        Route::post('sending-domains/{sending_domain}/mark-verified', [\App\Http\Controllers\Customer\SendingDomainController::class, 'markVerified'])->name('sending-domains.mark-verified');
        
        // Transactional Emails
        Route::resource('transactional-emails', \App\Http\Controllers\Customer\TransactionalEmailController::class);
        
        // Subscriptions
        Route::resource('subscriptions', \App\Http\Controllers\Customer\SubscriptionController::class);
        Route::post('subscriptions/{subscription}/cancel', [\App\Http\Controllers\Customer\SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
        Route::post('subscriptions/{subscription}/renew', [\App\Http\Controllers\Customer\SubscriptionController::class, 'renew'])->name('subscriptions.renew');

        // Billing UI
        Route::get('billing', [\App\Http\Controllers\Customer\BillingController::class, 'index'])->name('billing.index');
        Route::get('billing/checkout/{plan}', [\App\Http\Controllers\Customer\BillingController::class, 'showCheckout'])->name('billing.checkout.show');
        Route::post('billing/checkout/{plan}', [\App\Http\Controllers\Customer\BillingController::class, 'checkout'])->name('billing.checkout');
        Route::get('billing/success', [\App\Http\Controllers\Customer\BillingController::class, 'success'])->name('billing.success');
        Route::delete('billing/history/{event}', [\App\Http\Controllers\Customer\BillingController::class, 'destroyInvoiceEvent'])->name('billing.history.destroy');

        Route::get('billing/manual/{subscription}', [\App\Http\Controllers\Customer\ManualPaymentController::class, 'show'])->name('billing.manual.show');
        Route::post('billing/manual/{subscription}/confirm', [\App\Http\Controllers\Customer\ManualPaymentController::class, 'confirm'])->name('billing.manual.confirm');

        // Affiliate
        Route::get('affiliate', [\App\Http\Controllers\Customer\AffiliateController::class, 'index'])
            ->middleware('affiliate.enabled')
            ->name('affiliate.index');

        Route::get('affiliate/payments', [\App\Http\Controllers\Customer\AffiliateController::class, 'payments'])
            ->middleware('affiliate.enabled')
            ->name('affiliate.payments');

        Route::post('affiliate/payout-settings', [\App\Http\Controllers\Customer\AffiliateController::class, 'updatePayoutSettings'])
            ->middleware(['demo.prevent', 'affiliate.enabled'])
            ->name('affiliate.payout-settings.update');

        Route::post('affiliate/apply', [\App\Http\Controllers\Customer\AffiliateController::class, 'apply'])
            ->middleware(['demo.prevent', 'affiliate.enabled'])
            ->name('affiliate.apply');
        
        // Email Verification
        Route::get('/verify-email/{token}', [\App\Http\Controllers\Customer\EmailVerificationController::class, 'verify'])->name('email.verify');
        
        // Templates
        Route::get('templates/unlayer/create', [\App\Http\Controllers\Customer\TemplateController::class, 'createUnlayer'])->name('templates.unlayer.create');
        Route::post('templates/unlayer', [\App\Http\Controllers\Customer\TemplateController::class, 'storeUnlayer'])->name('templates.unlayer.store');
        Route::get('templates/{template}/unlayer/edit', [\App\Http\Controllers\Customer\TemplateController::class, 'editUnlayer'])->name('templates.unlayer.edit');
        Route::put('templates/{template}/unlayer', [\App\Http\Controllers\Customer\TemplateController::class, 'updateUnlayer'])->name('templates.unlayer.update');
        Route::get('templates/import/gallery', [\App\Http\Controllers\Customer\TemplateController::class, 'importGallery'])->name('templates.import.gallery');
        Route::get('templates/import/file/{key}/content', [\App\Http\Controllers\Customer\TemplateController::class, 'importFileContent'])
            ->name('templates.import.file.content');
        Route::get('templates/import/{template}/content', [\App\Http\Controllers\Customer\TemplateController::class, 'importContent'])->name('templates.import.content');
        Route::get('templates/import/public/{publicTemplate}/content', [\App\Http\Controllers\Customer\TemplateController::class, 'importPublicContent'])->name('templates.import.public.content');
        Route::post('templates/ai-generate', [\App\Http\Controllers\Customer\TemplateController::class, 'aiGenerate'])->name('templates.ai-generate');
        Route::resource('templates', \App\Http\Controllers\Customer\TemplateController::class);
        Route::post('templates/{template}/duplicate', [\App\Http\Controllers\Customer\TemplateController::class, 'duplicate'])->name('templates.duplicate');
        Route::get('templates/{template}/preview', [\App\Http\Controllers\Customer\TemplateController::class, 'preview'])->name('templates.preview');
        Route::get('templates/{template}/content', [\App\Http\Controllers\Customer\TemplateController::class, 'getContent'])->name('templates.content');
        Route::post('templates/{template}/test-email', [\App\Http\Controllers\Customer\TemplateController::class, 'sendTestEmail'])->name('templates.test-email');

        // AI Tools
        Route::prefix('ai-tools')
            ->name('ai-tools.')
            ->middleware('customer.access:ai_tools.permissions.can_access_ai_tools')
            ->group(function () {
                Route::get('/', [\App\Http\Controllers\Customer\AiToolController::class, 'index'])->name('index');

                Route::get('/email-text-generator', [\App\Http\Controllers\Customer\AiToolController::class, 'emailTextGenerator'])
                    ->middleware('customer.access:ai_tools.permissions.can_use_email_text_generator')
                    ->name('email-text-generator');
                Route::post('/email-text-generator', [\App\Http\Controllers\Customer\AiToolController::class, 'generateEmailText'])
                    ->middleware(['customer.access:ai_tools.permissions.can_use_email_text_generator', 'demo.prevent'])
                    ->name('email-text-generator.generate');

                Route::post('/email-text-generator/export-to-template', [\App\Http\Controllers\Customer\AiToolController::class, 'exportEmailTextToTemplate'])
                    ->middleware(['customer.access:ai_tools.permissions.can_use_email_text_generator', 'customer.access:templates.permissions.can_create_templates', 'demo.prevent'])
                    ->name('email-text-generator.export-to-template');
            });
        
        // Analytics
        Route::get('/analytics', [\App\Http\Controllers\Customer\AnalyticsController::class, 'index'])->name('analytics.index');

        // Delivery Servers
        Route::resource('delivery-servers', \App\Http\Controllers\Customer\DeliveryServerController::class)->parameters([
            'delivery-servers' => 'delivery_server'
        ]);

        Route::get('delivery-servers/{delivery_server}/secret', [\App\Http\Controllers\Customer\DeliveryServerController::class, 'revealSecret'])
            ->middleware('customer.access:servers.permissions.can_access_delivery_servers')
            ->name('delivery-servers.secret');

        Route::post('delivery-servers/{delivery_server}/test-email', [\App\Http\Controllers\Customer\DeliveryServerController::class, 'sendTestEmail'])
            ->middleware(['demo.prevent', 'customer.access:servers.permissions.can_edit_delivery_servers'])
            ->name('delivery-servers.test-email');
        Route::get('delivery-servers/{delivery_server}/verify/{token}', [\App\Http\Controllers\Customer\DeliveryServerController::class, 'verify'])
            ->name('delivery-servers.verify');
        Route::post('delivery-servers/{delivery_server}/resend-verification', [\App\Http\Controllers\Customer\DeliveryServerController::class, 'resendVerification'])
            ->name('delivery-servers.resend-verification');

        // Email Warmups
        Route::resource('warmups', \App\Http\Controllers\Customer\EmailWarmupController::class)->parameters([
            'warmups' => 'warmup'
        ]);
        Route::post('warmups/{warmup}/start', [\App\Http\Controllers\Customer\EmailWarmupController::class, 'start'])
            ->name('warmups.start');
        Route::post('warmups/{warmup}/pause', [\App\Http\Controllers\Customer\EmailWarmupController::class, 'pause'])
            ->name('warmups.pause');
        Route::get('warmups/{warmup}/stats', [\App\Http\Controllers\Customer\EmailWarmupController::class, 'stats'])
            ->name('warmups.stats');

        // Bounce Servers
        Route::resource('bounce-servers', \App\Http\Controllers\Customer\BounceServerController::class)->parameters([
            'bounce-servers' => 'bounce_server'
        ]);

        // Reply Servers
        Route::resource('reply-servers', \App\Http\Controllers\Customer\ReplyServerController::class)->parameters([
            'reply-servers' => 'reply_server'
        ]);

        // Bounced Emails
        Route::get('bounced-emails', [\App\Http\Controllers\Customer\BouncedEmailController::class, 'index'])->name('bounced-emails.index');
        Route::get('bounced-emails/{bounced_email}', [\App\Http\Controllers\Customer\BouncedEmailController::class, 'show'])->name('bounced-emails.show');

        // Email Validation
        Route::prefix('email-validation')
            ->name('email-validation.')
            ->middleware('customer.access:email_validation.access')
            ->group(function () {
                Route::resource('tools', \App\Http\Controllers\Customer\EmailValidationToolController::class);
                Route::resource('runs', \App\Http\Controllers\Customer\EmailValidationRunController::class)->only(['index', 'create', 'store', 'show']);
                Route::get('runs/{run}/stats', [\App\Http\Controllers\Customer\EmailValidationRunController::class, 'stats'])->name('runs.stats');
                Route::get('runs/{run}/errors', [\App\Http\Controllers\Customer\EmailValidationRunController::class, 'errors'])->name('runs.errors');
                Route::post('runs/{run}/pause', [\App\Http\Controllers\Customer\EmailValidationRunController::class, 'pause'])->name('runs.pause');
                Route::post('runs/{run}/resume', [\App\Http\Controllers\Customer\EmailValidationRunController::class, 'resume'])->name('runs.resume');
                Route::post('runs/{run}/resume-failed', [\App\Http\Controllers\Customer\EmailValidationRunController::class, 'resumeFailed'])->name('runs.resume-failed');
            });

        // Usage
        Route::get('usage', [\App\Http\Controllers\Customer\UsageController::class, 'index'])->name('usage.index');

        // Settings
        Route::get('settings', [\App\Http\Controllers\Customer\SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [\App\Http\Controllers\Customer\SettingController::class, 'update'])->name('settings.update');
        Route::put('settings/email', [\App\Http\Controllers\Customer\SettingController::class, 'updateEmail'])->name('settings.email.update');
        Route::put('settings/password', [\App\Http\Controllers\Customer\SettingController::class, 'updatePassword'])->name('settings.password.update');

        Route::get('settings/secret/{key}', [\App\Http\Controllers\Customer\SettingController::class, 'revealSecret'])
            ->middleware('customer.access:settings.permissions.can_access_settings')
            ->name('settings.secret');

        // Integrations
        Route::get('integrations', [\App\Http\Controllers\Customer\IntegrationController::class, 'index'])
            ->middleware('customer.access:servers.permissions.can_access_delivery_servers')
            ->name('integrations.index');

        Route::get('integrations/wordpress/plugin', [\App\Http\Controllers\Customer\IntegrationController::class, 'downloadWordpressPlugin'])
            ->middleware('customer.access:servers.permissions.can_access_delivery_servers')
            ->name('integrations.wordpress.plugin');

        Route::prefix('integrations/google')
            ->name('integrations.google.')
            ->middleware('customer.access:integrations.permissions.can_access_google')
            ->group(function () {
                Route::get('{service}/connect', [\App\Http\Controllers\Customer\GoogleIntegrationController::class, 'connect'])
                    ->name('connect');
                Route::get('{service}/callback', [\App\Http\Controllers\Customer\GoogleIntegrationController::class, 'callback'])
                    ->name('callback');
                Route::post('{service}/disconnect', [\App\Http\Controllers\Customer\GoogleIntegrationController::class, 'disconnect'])
                    ->middleware('demo.prevent')
                    ->name('disconnect');
            });

        // Support Tickets
        Route::resource('support-tickets', \App\Http\Controllers\Customer\SupportTicketController::class)
            ->only(['index', 'create', 'store', 'show']);
        Route::post('support-tickets/{support_ticket}/reply', [\App\Http\Controllers\Customer\SupportTicketController::class, 'reply'])
            ->name('support-tickets.reply');
        Route::post('support-tickets/{support_ticket}/close', [\App\Http\Controllers\Customer\SupportTicketController::class, 'close'])
            ->name('support-tickets.close');
    });
});

// Public Routes
Route::get('/', [\App\Http\Controllers\PublicController::class, 'home'])->name('home');
Route::get('/home/{variant}', [\App\Http\Controllers\PublicController::class, 'homeVariant'])
    ->where('variant', '[1-5]')
    ->name('home.variant');
Route::get('/features', [\App\Http\Controllers\PublicController::class, 'features'])->name('features');
Route::get('/pricing', [\App\Http\Controllers\PublicController::class, 'pricing'])->name('pricing');
Route::get('/pricing/checkout/{plan}', [\App\Http\Controllers\PublicController::class, 'pricingCheckout'])
    ->name('pricing.checkout');
Route::get('/docs', [\App\Http\Controllers\PublicController::class, 'docs'])->name('docs');
 Route::get('/api-docs', [\App\Http\Controllers\PublicController::class, 'apiDocs'])->name('api.docs.public');
 Route::get('/roadmap', [\App\Http\Controllers\PublicController::class, 'roadmap'])->name('roadmap');
 Route::get('/openapi', \App\Http\Controllers\Api\OpenApiController::class)->name('openapi');
Route::get('/openapi.json', \App\Http\Controllers\Api\OpenApiController::class)->name('openapi.json');

// Public Blog
Route::get('/blog', [PublicBlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [PublicBlogController::class, 'show'])->name('blog.show');

// Public Subscription Forms
Route::get('/subscribe/{slug}', [\App\Http\Controllers\PublicSubscriptionController::class, 'show'])->name('public.subscribe');
Route::get('/subscribe/{slug}/popup.js', [\App\Http\Controllers\PublicSubscriptionController::class, 'popupScript'])->name('public.subscribe.popup_js');
Route::get('/subscribe/{slug}/popup', [\App\Http\Controllers\PublicSubscriptionController::class, 'popupScript'])->name('public.subscribe.popup');
Route::post('/subscribe/{slug}', [\App\Http\Controllers\PublicSubscriptionController::class, 'subscribe'])
    ->name('public.subscribe.store')
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
    ->middleware('throttle:30,1');
// API endpoint - excluded from CSRF in middleware
Route::post('/subscribe/{slug}/api', [\App\Http\Controllers\PublicSubscriptionController::class, 'subscribe'])
    ->name('public.subscribe.api')
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
    ->middleware('throttle:30,1');

// Public Subscription Actions
Route::get('/subscribe/confirm/{token}', [\App\Http\Controllers\PublicSubscriptionController::class, 'confirm'])->name('public.subscribe.confirm');
Route::get('/unsubscribe/{list}/{email}/{token}', [\App\Http\Controllers\PublicSubscriptionController::class, 'unsubscribe'])->name('public.unsubscribe');

// Legacy Campaign Tracking Routes (Public) - kept for backward compatibility
Route::get('/track/open/{uuid}', [\App\Http\Controllers\TrackingController::class, 'trackOpen'])->name('track.open.legacy');
Route::get('/track/click/{uuid}/{url}', [\App\Http\Controllers\TrackingController::class, 'trackClick'])->name('track.click.legacy');

// New hash-based tracking routes
Route::get('/t/open/{campaign}/{subscriber}', [\App\Http\Controllers\Tracking\V2TrackingController::class, 'open'])->name('track.open');
Route::get('/t/click/{campaign}/{subscriber}', [\App\Http\Controllers\Tracking\V2TrackingController::class, 'click'])->name('track.click');
Route::get('/unsubscribe/{uuid}', [\App\Http\Controllers\PublicSubscriptionController::class, 'unsubscribeByUuid'])->name('unsubscribe');

Route::post('/ses/sns', [\App\Http\Controllers\Webhook\SesWebhookController::class, 'handle'])
    ->name('ses.sns')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->middleware('throttle:60,1');

// Webhook Routes (No CSRF protection)
Route::prefix('webhooks')->name('webhooks.')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])->group(function () {
    Route::post('/mailgun', [\App\Http\Controllers\Webhook\MailgunWebhookController::class, 'handle'])->name('mailgun');
    Route::post('/mailgun/bounce', [\App\Http\Controllers\Webhook\MailgunWebhookController::class, 'handle'])->name('mailgun.bounce');
    Route::post('/mailgun/open', [\App\Http\Controllers\Webhook\MailgunWebhookController::class, 'handle'])->name('mailgun.open');
    Route::post('/mailgun/click', [\App\Http\Controllers\Webhook\MailgunWebhookController::class, 'handle'])->name('mailgun.click');

    Route::post('/ses', [\App\Http\Controllers\Webhook\SesWebhookController::class, 'handle'])->name('ses');
    Route::post('/ses/bounce', [\App\Http\Controllers\Webhook\SesWebhookController::class, 'handle'])->name('ses.bounce');
    Route::post('/ses/open', [\App\Http\Controllers\Webhook\SesWebhookController::class, 'handle'])->name('ses.open');
    Route::post('/ses/click', [\App\Http\Controllers\Webhook\SesWebhookController::class, 'handle'])->name('ses.click');

    Route::post('/sendgrid', [\App\Http\Controllers\Webhook\SendGridWebhookController::class, 'handle'])->name('sendgrid');
    Route::post('/sendgrid/bounce', [\App\Http\Controllers\Webhook\SendGridWebhookController::class, 'handle'])->name('sendgrid.bounce');
    Route::post('/sendgrid/open', [\App\Http\Controllers\Webhook\SendGridWebhookController::class, 'handle'])->name('sendgrid.open');
    Route::post('/sendgrid/click', [\App\Http\Controllers\Webhook\SendGridWebhookController::class, 'handle'])->name('sendgrid.click');

    Route::post('/stripe', [\App\Http\Controllers\Webhook\WebhookController::class, 'handleStripe'])->name('stripe');
    Route::post('/flutterwave', [\App\Http\Controllers\Webhook\FlutterwaveWebhookController::class, 'handle'])->name('flutterwave');
    Route::post('/razorpay', [\App\Http\Controllers\Webhook\RazorpayWebhookController::class, 'handle'])->name('razorpay');
    Route::post('/paypal', [\App\Http\Controllers\Webhook\PayPalWebhookController::class, 'handle'])->name('paypal');

    Route::post('/automations/{automation}', [\App\Http\Controllers\Webhook\AutomationWebhookController::class, 'handle'])
        ->name('automations.handle')
        ->middleware('throttle:60,1');
});


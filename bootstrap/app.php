<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->prependToGroup('web', [
            \App\Http\Middleware\EnsureInstalled::class,
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\SetLocaleFromUser::class,
        ]);

        $middleware->prependToGroup('api', [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        $middleware->alias([
            'auth.any' => \App\Http\Middleware\AuthenticateAny::class,
            'user.active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'customer.active' => \App\Http\Middleware\EnsureCustomerIsActive::class,
            'customer.verified_if_required' => \App\Http\Middleware\EnsureCustomerEmailVerifiedIfRequired::class,
            'customer.access' => \App\Http\Middleware\EnsureCustomerHasGroupAccess::class,
            'admin.access' => \App\Http\Middleware\EnsureAdminHasGroupAccess::class,
            'sanctum.customer' => \App\Http\Middleware\EnsureSanctumCustomer::class,
            'sanctum.admin' => \App\Http\Middleware\EnsureSanctumAdmin::class,
            'api.customer' => \App\Http\Middleware\EnsureApiCustomerAbility::class,
            'api.admin' => \App\Http\Middleware\EnsureApiAdminAbility::class,
            'subscription.gate' => \App\Http\Middleware\SubscriptionGate::class,
            'demo.prevent' => \App\Http\Middleware\PreventDemoActions::class,
            'affiliate.enabled' => \App\Http\Middleware\CheckAffiliateEnabled::class,
        ]);
    })
    ->withProviders([
        \App\Providers\MailServiceProvider::class,
        \App\Providers\AuthServiceProvider::class,
        \App\Providers\BillingServiceProvider::class,
        \App\Providers\LoginTrackingServiceProvider::class,
        \App\Providers\TranslationServiceProvider::class,
        \App\Providers\AppServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();


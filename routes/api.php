<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth:sanctum', 'subscription.gate'])->group(function () {
    Route::get('/billing/current', [\App\Http\Controllers\Api\BillingController::class, 'current'])->name('api.billing.current');
    Route::post('/billing/checkout/{plan}', [\App\Http\Controllers\Api\BillingController::class, 'checkout'])->name('api.billing.checkout');
    Route::post('/billing/cancel/{subscription}', [\App\Http\Controllers\Api\BillingController::class, 'cancel'])->name('api.billing.cancel');
    Route::post('/billing/resume/{subscription}', [\App\Http\Controllers\Api\BillingController::class, 'resume'])->name('api.billing.resume');
    Route::get('/billing/history', [\App\Http\Controllers\Api\BillingController::class, 'history'])->name('api.billing.history');
    Route::get('/billing/portal', [\App\Http\Controllers\Api\BillingController::class, 'portal'])->name('api.billing.portal');
});

Route::prefix('v1')->middleware(['auth:sanctum', 'sanctum.customer'])->group(function () {
    // Campaigns
    Route::get('/campaigns', [\App\Http\Controllers\Api\V1\Customer\CampaignController::class, 'index'])->middleware('api.customer:campaigns.permissions.can_access_campaigns');
    Route::post('/campaigns', [\App\Http\Controllers\Api\V1\Customer\CampaignController::class, 'store'])->middleware('api.customer:campaigns.permissions.can_create_campaigns');
    Route::get('/campaigns/{campaign}', [\App\Http\Controllers\Api\V1\Customer\CampaignController::class, 'show'])->middleware('api.customer:campaigns.permissions.can_access_campaigns');
    Route::put('/campaigns/{campaign}', [\App\Http\Controllers\Api\V1\Customer\CampaignController::class, 'update'])->middleware('api.customer:campaigns.permissions.can_edit_campaigns');
    Route::delete('/campaigns/{campaign}', [\App\Http\Controllers\Api\V1\Customer\CampaignController::class, 'destroy'])->middleware('api.customer:campaigns.permissions.can_delete_campaigns');

    Route::post('/campaigns/{campaign}/start', [\App\Http\Controllers\Api\V1\Customer\CampaignController::class, 'start'])->middleware('api.customer:campaigns.permissions.can_start_campaigns');
    Route::post('/campaigns/{campaign}/pause', [\App\Http\Controllers\Api\V1\Customer\CampaignController::class, 'pause'])->middleware('api.customer:campaigns.permissions.can_start_campaigns');
    Route::post('/campaigns/{campaign}/resume', [\App\Http\Controllers\Api\V1\Customer\CampaignController::class, 'resume'])->middleware('api.customer:campaigns.permissions.can_start_campaigns');
    Route::post('/campaigns/{campaign}/rerun', [\App\Http\Controllers\Api\V1\Customer\CampaignController::class, 'rerun'])->middleware('api.customer:campaigns.permissions.can_start_campaigns');
    Route::get('/campaigns/{campaign}/stats', [\App\Http\Controllers\Api\V1\Customer\CampaignController::class, 'stats'])->middleware('api.customer:campaigns.permissions.can_access_campaigns');
    Route::get('/campaigns/{campaign}/recipients', [\App\Http\Controllers\Api\V1\Customer\CampaignController::class, 'recipients'])->middleware('api.customer:campaigns.permissions.can_access_campaigns');

    // Auto Responders
    Route::get('/auto-responders', [\App\Http\Controllers\Api\V1\Customer\AutoResponderController::class, 'index'])->middleware('api.customer:autoresponders.enabled');
    Route::post('/auto-responders', [\App\Http\Controllers\Api\V1\Customer\AutoResponderController::class, 'store'])->middleware('api.customer:autoresponders.enabled');
    Route::get('/auto-responders/{autoResponder}', [\App\Http\Controllers\Api\V1\Customer\AutoResponderController::class, 'show'])->middleware('api.customer:autoresponders.enabled');
    Route::put('/auto-responders/{autoResponder}', [\App\Http\Controllers\Api\V1\Customer\AutoResponderController::class, 'update'])->middleware('api.customer:autoresponders.enabled');
    Route::delete('/auto-responders/{autoResponder}', [\App\Http\Controllers\Api\V1\Customer\AutoResponderController::class, 'destroy'])->middleware('api.customer:autoresponders.enabled');

    Route::post('/auto-responders/{autoResponder}/start', [\App\Http\Controllers\Api\V1\Customer\AutoResponderController::class, 'start'])->middleware('api.customer:autoresponders.enabled');
    Route::post('/auto-responders/{autoResponder}/pause', [\App\Http\Controllers\Api\V1\Customer\AutoResponderController::class, 'pause'])->middleware('api.customer:autoresponders.enabled');
    Route::post('/auto-responders/{autoResponder}/resume', [\App\Http\Controllers\Api\V1\Customer\AutoResponderController::class, 'resume'])->middleware('api.customer:autoresponders.enabled');
    Route::post('/auto-responders/{autoResponder}/rerun', [\App\Http\Controllers\Api\V1\Customer\AutoResponderController::class, 'rerun'])->middleware('api.customer:autoresponders.enabled');
    Route::get('/auto-responders/{autoResponder}/stats', [\App\Http\Controllers\Api\V1\Customer\AutoResponderController::class, 'stats'])->middleware('api.customer:autoresponders.enabled');
    Route::get('/auto-responders/{autoResponder}/recipients', [\App\Http\Controllers\Api\V1\Customer\AutoResponderController::class, 'recipients'])->middleware('api.customer:autoresponders.enabled');

    // Delivery Servers
    Route::get('/delivery-servers', [\App\Http\Controllers\Api\V1\Customer\DeliveryServerController::class, 'index'])->middleware('api.customer:servers.permissions.can_access_delivery_servers');
    Route::post('/delivery-servers', [\App\Http\Controllers\Api\V1\Customer\DeliveryServerController::class, 'store'])->middleware('api.customer:servers.permissions.can_create_delivery_servers');
    Route::get('/delivery-servers/{deliveryServer}', [\App\Http\Controllers\Api\V1\Customer\DeliveryServerController::class, 'show'])->middleware('api.customer:servers.permissions.can_access_delivery_servers');
    Route::put('/delivery-servers/{deliveryServer}', [\App\Http\Controllers\Api\V1\Customer\DeliveryServerController::class, 'update'])->middleware('api.customer:servers.permissions.can_edit_delivery_servers');
    Route::delete('/delivery-servers/{deliveryServer}', [\App\Http\Controllers\Api\V1\Customer\DeliveryServerController::class, 'destroy'])->middleware('api.customer:servers.permissions.can_delete_delivery_servers');
    Route::post('/delivery-servers/{deliveryServer}/test-email', [\App\Http\Controllers\Api\V1\Customer\DeliveryServerController::class, 'testEmail'])->middleware('api.customer:servers.permissions.can_edit_delivery_servers');
    Route::post('/delivery-servers/{deliveryServer}/verify', [\App\Http\Controllers\Api\V1\Customer\DeliveryServerController::class, 'verify'])->middleware('api.customer:servers.permissions.can_edit_delivery_servers');
    Route::post('/delivery-servers/{deliveryServer}/resend-verification', [\App\Http\Controllers\Api\V1\Customer\DeliveryServerController::class, 'resendVerification'])->middleware('api.customer:servers.permissions.can_edit_delivery_servers');

    // Bounce Servers
    Route::get('/bounce-servers', [\App\Http\Controllers\Api\V1\Customer\BounceServerController::class, 'index'])->middleware('api.customer:servers.permissions.can_add_bounce_servers');
    Route::post('/bounce-servers', [\App\Http\Controllers\Api\V1\Customer\BounceServerController::class, 'store'])->middleware('api.customer:servers.permissions.can_add_bounce_servers');
    Route::get('/bounce-servers/{bounceServer}', [\App\Http\Controllers\Api\V1\Customer\BounceServerController::class, 'show'])->middleware('api.customer:servers.permissions.can_add_bounce_servers');
    Route::put('/bounce-servers/{bounceServer}', [\App\Http\Controllers\Api\V1\Customer\BounceServerController::class, 'update'])->middleware('api.customer:servers.permissions.can_edit_bounce_servers');
    Route::delete('/bounce-servers/{bounceServer}', [\App\Http\Controllers\Api\V1\Customer\BounceServerController::class, 'destroy'])->middleware('api.customer:servers.permissions.can_delete_bounce_servers');

    // Reply Servers
    Route::get('/reply-servers', [\App\Http\Controllers\Api\V1\Customer\ReplyServerController::class, 'index'])->middleware('api.customer:servers.permissions.can_add_reply_servers');
    Route::post('/reply-servers', [\App\Http\Controllers\Api\V1\Customer\ReplyServerController::class, 'store'])->middleware('api.customer:servers.permissions.can_add_reply_servers');
    Route::get('/reply-servers/{replyServer}', [\App\Http\Controllers\Api\V1\Customer\ReplyServerController::class, 'show'])->middleware('api.customer:servers.permissions.can_add_reply_servers');
    Route::put('/reply-servers/{replyServer}', [\App\Http\Controllers\Api\V1\Customer\ReplyServerController::class, 'update'])->middleware('api.customer:servers.permissions.can_edit_reply_servers');
    Route::delete('/reply-servers/{replyServer}', [\App\Http\Controllers\Api\V1\Customer\ReplyServerController::class, 'destroy'])->middleware('api.customer:servers.permissions.can_delete_reply_servers');

    // Sending Domains
    Route::get('/sending-domains', [\App\Http\Controllers\Api\V1\Customer\SendingDomainController::class, 'index'])->middleware('api.customer:domains.sending_domains.permissions.can_access_sending_domains');
    Route::post('/sending-domains', [\App\Http\Controllers\Api\V1\Customer\SendingDomainController::class, 'store'])->middleware('api.customer:domains.sending_domains.permissions.can_create_sending_domains');
    Route::get('/sending-domains/{sendingDomain}', [\App\Http\Controllers\Api\V1\Customer\SendingDomainController::class, 'show'])->middleware('api.customer:domains.sending_domains.permissions.can_access_sending_domains');
    Route::put('/sending-domains/{sendingDomain}', [\App\Http\Controllers\Api\V1\Customer\SendingDomainController::class, 'update'])->middleware('api.customer:domains.sending_domains.permissions.can_edit_sending_domains');
    Route::delete('/sending-domains/{sendingDomain}', [\App\Http\Controllers\Api\V1\Customer\SendingDomainController::class, 'destroy'])->middleware('api.customer:domains.sending_domains.permissions.can_delete_sending_domains');
    Route::post('/sending-domains/{sendingDomain}/verify', [\App\Http\Controllers\Api\V1\Customer\SendingDomainController::class, 'verify'])->middleware('api.customer:domains.sending_domains.permissions.can_edit_sending_domains');
    Route::post('/sending-domains/{sendingDomain}/mark-verified', [\App\Http\Controllers\Api\V1\Customer\SendingDomainController::class, 'markVerified'])->middleware('api.customer:domains.sending_domains.permissions.can_edit_sending_domains');

    // Tracking Domains
    Route::get('/tracking-domains', [\App\Http\Controllers\Api\V1\Customer\TrackingDomainController::class, 'index'])->middleware('api.customer:domains.tracking_domains.permissions.can_access_tracking_domains');
    Route::post('/tracking-domains', [\App\Http\Controllers\Api\V1\Customer\TrackingDomainController::class, 'store'])->middleware('api.customer:domains.tracking_domains.permissions.can_create_tracking_domains');
    Route::get('/tracking-domains/{trackingDomain}', [\App\Http\Controllers\Api\V1\Customer\TrackingDomainController::class, 'show'])->middleware('api.customer:domains.tracking_domains.permissions.can_access_tracking_domains');
    Route::put('/tracking-domains/{trackingDomain}', [\App\Http\Controllers\Api\V1\Customer\TrackingDomainController::class, 'update'])->middleware('api.customer:domains.tracking_domains.permissions.can_edit_tracking_domains');
    Route::delete('/tracking-domains/{trackingDomain}', [\App\Http\Controllers\Api\V1\Customer\TrackingDomainController::class, 'destroy'])->middleware('api.customer:domains.tracking_domains.permissions.can_delete_tracking_domains');
    Route::post('/tracking-domains/{trackingDomain}/verify', [\App\Http\Controllers\Api\V1\Customer\TrackingDomainController::class, 'verify'])->middleware('api.customer:domains.tracking_domains.permissions.can_edit_tracking_domains');
    Route::post('/tracking-domains/{trackingDomain}/mark-verified', [\App\Http\Controllers\Api\V1\Customer\TrackingDomainController::class, 'markVerified'])->middleware('api.customer:domains.tracking_domains.permissions.can_edit_tracking_domains');

    // Lists
    Route::get('/lists', [\App\Http\Controllers\Api\V1\Customer\EmailListController::class, 'index'])->middleware('api.customer:lists.permissions.can_access_lists');
    Route::post('/lists', [\App\Http\Controllers\Api\V1\Customer\EmailListController::class, 'store'])->middleware('api.customer:lists.permissions.can_create_lists');
    Route::get('/lists/{list}', [\App\Http\Controllers\Api\V1\Customer\EmailListController::class, 'show'])->middleware('api.customer:lists.permissions.can_access_lists');
    Route::put('/lists/{list}', [\App\Http\Controllers\Api\V1\Customer\EmailListController::class, 'update'])->middleware('api.customer:lists.permissions.can_edit_lists');
    Route::delete('/lists/{list}', [\App\Http\Controllers\Api\V1\Customer\EmailListController::class, 'destroy'])->middleware('api.customer:lists.permissions.can_delete_lists');

    // Contacts (subscribers)
    Route::get('/lists/{list}/contacts', [\App\Http\Controllers\Api\V1\Customer\ListSubscriberController::class, 'index'])->middleware('api.customer:lists.permissions.can_access_lists');
    Route::post('/lists/{list}/contacts', [\App\Http\Controllers\Api\V1\Customer\ListSubscriberController::class, 'store'])->middleware('api.customer:lists.permissions.can_edit_lists');
    Route::get('/lists/{list}/contacts/{subscriber}', [\App\Http\Controllers\Api\V1\Customer\ListSubscriberController::class, 'show'])->middleware('api.customer:lists.permissions.can_access_lists');
    Route::put('/lists/{list}/contacts/{subscriber}', [\App\Http\Controllers\Api\V1\Customer\ListSubscriberController::class, 'update'])->middleware('api.customer:lists.permissions.can_edit_lists');
    Route::delete('/lists/{list}/contacts/{subscriber}', [\App\Http\Controllers\Api\V1\Customer\ListSubscriberController::class, 'destroy'])->middleware('api.customer:lists.permissions.can_delete_lists');

    // Import
    Route::post('/lists/{list}/contacts/import/json', [\App\Http\Controllers\Api\V1\Customer\ListSubscriberImportController::class, 'importJson'])->middleware('api.customer:lists.permissions.can_edit_lists');
    Route::post('/lists/{list}/contacts/import/csv', [\App\Http\Controllers\Api\V1\Customer\ListSubscriberImportController::class, 'importCsv'])->middleware('api.customer:lists.permissions.can_edit_lists');

    // Integrations
    Route::get('/integrations/wordpress/connection', [\App\Http\Controllers\Api\V1\Customer\Integrations\WordPressConnectionController::class, 'show'])
        ->middleware('throttle:60,1');
    Route::post('/integrations/wordpress/connection/rotate', [\App\Http\Controllers\Api\V1\Customer\Integrations\WordPressConnectionController::class, 'rotate'])
        ->middleware('throttle:20,1');
    Route::post('/integrations/wordpress/events', [\App\Http\Controllers\Api\V1\Customer\Integrations\WordPressEventController::class, 'store'])
        ->middleware(['throttle:120,1', 'wp.signature']);

    // Transactional Emails
    Route::get('/transactional-emails', [\App\Http\Controllers\Api\V1\Customer\TransactionalEmailController::class, 'index']);
    Route::post('/transactional-emails', [\App\Http\Controllers\Api\V1\Customer\TransactionalEmailController::class, 'store']);
    Route::get('/transactional-emails/{transactionalEmail}', [\App\Http\Controllers\Api\V1\Customer\TransactionalEmailController::class, 'show']);
    Route::put('/transactional-emails/{transactionalEmail}', [\App\Http\Controllers\Api\V1\Customer\TransactionalEmailController::class, 'update']);
    Route::delete('/transactional-emails/{transactionalEmail}', [\App\Http\Controllers\Api\V1\Customer\TransactionalEmailController::class, 'destroy']);
    Route::post('/transactional-emails/send', [\App\Http\Controllers\Api\V1\Customer\TransactionalEmailController::class, 'send']);
    Route::post('/transactional-emails/send-raw', [\App\Http\Controllers\Api\V1\Customer\TransactionalEmailController::class, 'sendRaw']);
    Route::post('/transactional-emails/send-bulk', [\App\Http\Controllers\Api\V1\Customer\TransactionalEmailController::class, 'sendBulk']);
});

Route::prefix('admin/v1')->middleware(['auth:sanctum', 'sanctum.admin'])->group(function () {
    // Customers
    Route::get('/customers', [\App\Http\Controllers\Api\V1\Admin\CustomerController::class, 'index'])->middleware('api.admin:admin.customers.access');

    // Plans
    Route::get('/plans', [\App\Http\Controllers\Api\V1\Admin\PlanController::class, 'index'])->middleware('api.admin:admin.plans.access');
    Route::post('/plans', [\App\Http\Controllers\Api\V1\Admin\PlanController::class, 'store'])->middleware('api.admin:admin.plans.access');
    Route::get('/plans/{plan}', [\App\Http\Controllers\Api\V1\Admin\PlanController::class, 'show'])->middleware('api.admin:admin.plans.access');
    Route::put('/plans/{plan}', [\App\Http\Controllers\Api\V1\Admin\PlanController::class, 'update'])->middleware('api.admin:admin.plans.access');
    Route::delete('/plans/{plan}', [\App\Http\Controllers\Api\V1\Admin\PlanController::class, 'destroy'])->middleware('api.admin:admin.plans.access');

    // Coupons
    Route::get('/coupons', [\App\Http\Controllers\Api\V1\Admin\CouponController::class, 'index'])->middleware('api.admin:admin.coupons.access');
    Route::post('/coupons', [\App\Http\Controllers\Api\V1\Admin\CouponController::class, 'store'])->middleware('api.admin:admin.coupons.access');
    Route::get('/coupons/{coupon}', [\App\Http\Controllers\Api\V1\Admin\CouponController::class, 'show'])->middleware('api.admin:admin.coupons.access');
    Route::put('/coupons/{coupon}', [\App\Http\Controllers\Api\V1\Admin\CouponController::class, 'update'])->middleware('api.admin:admin.coupons.access');
    Route::delete('/coupons/{coupon}', [\App\Http\Controllers\Api\V1\Admin\CouponController::class, 'destroy'])->middleware('api.admin:admin.coupons.access');

    // Blog Posts
    Route::get('/blog-posts', [\App\Http\Controllers\Api\V1\Admin\BlogPostController::class, 'index'])->middleware('api.admin:admin.blog.access');
    Route::post('/blog-posts', [\App\Http\Controllers\Api\V1\Admin\BlogPostController::class, 'store'])->middleware('api.admin:admin.blog.access');
    Route::get('/blog-posts/{blogPost}', [\App\Http\Controllers\Api\V1\Admin\BlogPostController::class, 'show'])->middleware('api.admin:admin.blog.access');
    Route::put('/blog-posts/{blogPost}', [\App\Http\Controllers\Api\V1\Admin\BlogPostController::class, 'update'])->middleware('api.admin:admin.blog.access');
    Route::delete('/blog-posts/{blogPost}', [\App\Http\Controllers\Api\V1\Admin\BlogPostController::class, 'destroy'])->middleware('api.admin:admin.blog.access');

    // Support Tickets
    Route::get('/support-tickets', [\App\Http\Controllers\Api\V1\Admin\SupportTicketController::class, 'index'])->middleware('api.admin:admin.support.access');
    Route::get('/support-tickets/{supportTicket}', [\App\Http\Controllers\Api\V1\Admin\SupportTicketController::class, 'show'])->middleware('api.admin:admin.support.access');
    Route::put('/support-tickets/{supportTicket}', [\App\Http\Controllers\Api\V1\Admin\SupportTicketController::class, 'update'])->middleware('api.admin:admin.support.access');
    Route::post('/support-tickets/{supportTicket}/reply', [\App\Http\Controllers\Api\V1\Admin\SupportTicketController::class, 'reply'])->middleware('api.admin:admin.support.access');
    Route::delete('/support-tickets/{supportTicket}', [\App\Http\Controllers\Api\V1\Admin\SupportTicketController::class, 'destroy'])->middleware('api.admin:admin.support.access');

    // Affiliates
    Route::get('/affiliates', [\App\Http\Controllers\Api\V1\Admin\AffiliateController::class, 'index'])->middleware('api.admin:admin.affiliates.access');
    Route::get('/affiliates/{affiliate}', [\App\Http\Controllers\Api\V1\Admin\AffiliateController::class, 'show'])->middleware('api.admin:admin.affiliates.access');
    Route::put('/affiliates/{affiliate}', [\App\Http\Controllers\Api\V1\Admin\AffiliateController::class, 'update'])->middleware('api.admin:admin.affiliates.access');
    Route::delete('/affiliates/{affiliate}', [\App\Http\Controllers\Api\V1\Admin\AffiliateController::class, 'destroy'])->middleware('api.admin:admin.affiliates.access');

    // Invoices/Subscriptions
    Route::get('/invoices', [\App\Http\Controllers\Api\V1\Admin\InvoiceController::class, 'index'])->middleware('api.admin:admin.invoices.access');
    Route::get('/invoices/{invoice}', [\App\Http\Controllers\Api\V1\Admin\InvoiceController::class, 'show'])->middleware('api.admin:admin.invoices.access');
});

Route::get('/openapi.json', \App\Http\Controllers\Api\OpenApiController::class)->name('api.openapi');

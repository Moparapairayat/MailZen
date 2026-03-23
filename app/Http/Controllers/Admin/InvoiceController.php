<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Subscription;
use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $status = trim((string) $request->get('status', ''));

        $mode = 'stripe';
        $invoices = collect();
        $stripePagination = null;
        $customersByStripeId = collect();
        $subscriptionsByStripeId = collect();
        $events = null;
        $stripeError = null;

        try {
            $stripe = app(StripeClient::class);

            $startingAfter = trim((string) $request->get('starting_after', ''));
            $endingBefore = trim((string) $request->get('ending_before', ''));

            if ($search !== '' && str_starts_with($search, 'in_')) {
                $invoice = $stripe->invoices->retrieve($search, []);
                $invoices = collect([$invoice])->map(fn ($i) => json_decode(json_encode($i), true));
                $stripePagination = [
                    'has_more' => false,
                    'next' => null,
                    'prev' => null,
                ];
            } else {
                $params = [
                    'limit' => 25,
                ];

                if ($status !== '') {
                    $params['status'] = $status;
                }

                if ($search !== '') {
                    if (str_starts_with($search, 'cus_')) {
                        $params['customer'] = $search;
                    } else {
                        $matchingStripeCustomerIds = Customer::query()
                            ->where(function ($q) use ($search) {
                                $q->where('email', 'like', '%' . $search . '%')
                                    ->orWhere('first_name', 'like', '%' . $search . '%')
                                    ->orWhere('last_name', 'like', '%' . $search . '%')
                                    ->orWhere('company_name', 'like', '%' . $search . '%');
                            })
                            ->whereNotNull('stripe_customer_id')
                            ->limit(200)
                            ->pluck('stripe_customer_id')
                            ->values()
                            ->all();

                        if (count($matchingStripeCustomerIds) === 1) {
                            $params['customer'] = $matchingStripeCustomerIds[0];
                        }
                    }
                }

                if ($startingAfter !== '') {
                    $params['starting_after'] = $startingAfter;
                } elseif ($endingBefore !== '') {
                    $params['ending_before'] = $endingBefore;
                }

                $result = $stripe->invoices->all($params);
                $invoices = collect($result->data ?? [])->map(fn ($i) => json_decode(json_encode($i), true));

                $firstId = (string) data_get($invoices->first(), 'id', '');
                $lastId = (string) data_get($invoices->last(), 'id', '');

                $stripePagination = [
                    'has_more' => (bool) ($result->has_more ?? false),
                    'next' => ($lastId !== '' && (bool) ($result->has_more ?? false)) ? $lastId : null,
                    'prev' => $firstId !== '' ? $firstId : null,
                ];
            }

            $stripeCustomerIds = $invoices
                ->map(fn (array $invoice) => data_get($invoice, 'customer'))
                ->filter()
                ->unique()
                ->values();

            $customersByStripeId = Customer::query()
                ->whereIn('stripe_customer_id', $stripeCustomerIds)
                ->get()
                ->keyBy('stripe_customer_id');

            $stripeSubscriptionIds = $invoices
                ->map(fn (array $invoice) => data_get($invoice, 'subscription'))
                ->filter()
                ->unique()
                ->values();

            $subscriptionsByStripeId = Subscription::query()
                ->whereIn('stripe_subscription_id', $stripeSubscriptionIds)
                ->get()
                ->keyBy('stripe_subscription_id');
        } catch (\Throwable $e) {
            $mode = 'events';
            $stripeError = $e->getMessage();
        }

        if ($mode === 'events') {
            $query = WebhookEvent::query()
                ->where('provider', 'stripe')
                ->where('type', 'like', 'invoice.%')
                ->orderByDesc('processed_at')
                ->orderByDesc('created_at');

            if ($status !== '') {
                $query->where('payload->status', $status);
            }

            if ($search !== '') {
                $matchingStripeCustomerIds = Customer::query()
                    ->where(function ($q) use ($search) {
                        $q->where('email', 'like', '%' . $search . '%')
                            ->orWhere('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('company_name', 'like', '%' . $search . '%');
                    })
                    ->whereNotNull('stripe_customer_id')
                    ->limit(200)
                    ->pluck('stripe_customer_id')
                    ->values()
                    ->all();

                $query->where(function ($q) use ($search, $matchingStripeCustomerIds) {
                    $q->where('payload->id', 'like', '%' . $search . '%')
                        ->orWhere('event_id', 'like', '%' . $search . '%');

                    if (count($matchingStripeCustomerIds) > 0) {
                        $q->orWhereIn('payload->customer', $matchingStripeCustomerIds);
                    }
                });
            }

            $events = $query->paginate(25)->withQueryString();

            $stripeCustomerIds = $events->getCollection()
                ->map(fn (WebhookEvent $event) => data_get($event->payload, 'customer'))
                ->filter()
                ->unique()
                ->values();

            $customersByStripeId = Customer::query()
                ->whereIn('stripe_customer_id', $stripeCustomerIds)
                ->get()
                ->keyBy('stripe_customer_id');

            $stripeSubscriptionIds = $events->getCollection()
                ->map(fn (WebhookEvent $event) => data_get($event->payload, 'subscription'))
                ->filter()
                ->unique()
                ->values();

            $subscriptionsByStripeId = Subscription::query()
                ->whereIn('stripe_subscription_id', $stripeSubscriptionIds)
                ->get()
                ->keyBy('stripe_subscription_id');
        }

        return view('admin.invoices.index', [
            'mode' => $mode,
            'invoices' => $invoices,
            'stripePagination' => $stripePagination,
            'stripeError' => $stripeError,
            'events' => $events,
            'customersByStripeId' => $customersByStripeId,
            'subscriptionsByStripeId' => $subscriptionsByStripeId,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function show(Request $request, string $invoice)
    {
        $mode = 'stripe';
        $invoiceId = $invoice;
        $invoiceData = null;
        $event = null;
        $stripeError = null;

        try {
            $stripe = app(StripeClient::class);
            $stripeInvoice = $stripe->invoices->retrieve($invoiceId, []);
            $invoiceData = json_decode(json_encode($stripeInvoice), true);
        } catch (\Throwable $e) {
            $mode = 'events';
            $stripeError = $e->getMessage();
        }

        if ($mode === 'events') {
            $event = WebhookEvent::query()
                ->where('provider', 'stripe')
                ->where('type', 'like', 'invoice.%')
                ->where('payload->id', $invoiceId)
                ->orderByDesc('processed_at')
                ->orderByDesc('created_at')
                ->first();

            if ($event) {
                $invoiceData = $event->payload;
            }
        }

        abort_unless(is_array($invoiceData), 404);

        $stripeCustomerId = data_get($invoiceData, 'customer');
        $stripeSubscriptionId = data_get($invoiceData, 'subscription');

        $customer = null;
        if (is_string($stripeCustomerId) && $stripeCustomerId !== '') {
            $customer = Customer::query()->where('stripe_customer_id', $stripeCustomerId)->first();
        }

        $subscription = null;
        if (is_string($stripeSubscriptionId) && $stripeSubscriptionId !== '') {
            $subscription = Subscription::query()->where('stripe_subscription_id', $stripeSubscriptionId)->first();
        }

        return view('admin.invoices.show', [
            'mode' => $mode,
            'invoiceId' => $invoiceId,
            'invoice' => $invoiceData,
            'event' => $event,
            'customer' => $customer,
            'subscription' => $subscription,
            'stripeError' => $stripeError,
        ]);
    }
}

@extends('layouts.customer')

@section('title', __('API Docs'))
@section('page-title', __('API Docs'))

@section('content')
<div class="-m-4 sm:-m-6">
    <div id="customer-api-docs" class="min-h-[calc(100vh-10rem)]"></div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@scalar/api-reference"></script>
    <script>
        (function () {
            const el = document.getElementById('customer-api-docs');
            if (!el || typeof Scalar === 'undefined') return;

            Scalar.createApiReference('#customer-api-docs', {
                url: '/openapi',
                proxyUrl: 'https://proxy.scalar.com',
                customCss: `
                    .sidebar { position: sticky; top: 0; height: 100vh; overflow: auto; }
                `,
                customHtml: `
                    <tr>
                        <td class="py-2 pr-4 font-mono">GET</td>
                        <td class="py-2 pr-4 font-mono">/api/billing/current</td>
                        <td class="py-2">{{ __('Get current subscription.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/billing/checkout/{plan}</td>
                        <td class="py-2">{{ __('Create a checkout session for a plan.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/billing/cancel/{subscription}</td>
                        <td class="py-2">{{ __('Cancel a subscription.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/billing/resume/{subscription}</td>
                        <td class="py-2">{{ __('Resume a subscription.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">GET</td>
                        <td class="py-2 pr-4 font-mono">/api/billing/history</td>
                        <td class="py-2">{{ __('List subscription history.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">GET</td>
                        <td class="py-2 pr-4 font-mono">/api/billing/portal</td>
                        <td class="py-2">{{ __('Create a customer billing portal URL.') }}</td>
                    </tr>

                    <tr>
                        <td class="py-2 pr-4 font-mono">GET</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/campaigns</td>
                        <td class="py-2">{{ __('List campaigns (supports ?search=, ?status=, ?type=).') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/campaigns</td>
                        <td class="py-2">{{ __('Create campaign.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">GET</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/campaigns/{campaign}</td>
                        <td class="py-2">{{ __('Get a single campaign.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">PUT</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/campaigns/{campaign}</td>
                        <td class="py-2">{{ __('Update campaign.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">DELETE</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/campaigns/{campaign}</td>
                        <td class="py-2">{{ __('Delete campaign.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/campaigns/{campaign}/start</td>
                        <td class="py-2">{{ __('Start or schedule campaign.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/campaigns/{campaign}/pause</td>
                        <td class="py-2">{{ __('Pause a running campaign.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/campaigns/{campaign}/resume</td>
                        <td class="py-2">{{ __('Resume a paused campaign.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/campaigns/{campaign}/rerun</td>
                        <td class="py-2">{{ __('Reset a failed/completed campaign back to draft.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">GET</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/campaigns/{campaign}/stats</td>
                        <td class="py-2">{{ __('Get campaign stats (open/click/bounce breakdown, top links).') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">GET</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/campaigns/{campaign}/recipients</td>
                        <td class="py-2">{{ __('List campaign recipients (supports ?status= and ?search=).') }}</td>
                    </tr>

                    <tr>
                        <td class="py-2 pr-4 font-mono">GET</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/delivery-servers</td>
                        <td class="py-2">{{ __('List delivery servers (supports ?search=, ?type=, ?status=).') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/delivery-servers</td>
                        <td class="py-2">{{ __('Create delivery server.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">GET</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/delivery-servers/{deliveryServer}</td>
                        <td class="py-2">{{ __('Get delivery server.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">PUT</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/delivery-servers/{deliveryServer}</td>
                        <td class="py-2">{{ __('Update delivery server.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">DELETE</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/delivery-servers/{deliveryServer}</td>
                        <td class="py-2">{{ __('Delete delivery server.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/delivery-servers/{deliveryServer}/test-email</td>
                        <td class="py-2">{{ __('Send a test email using a delivery server.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/delivery-servers/{deliveryServer}/verify</td>
                        <td class="py-2">{{ __('Verify SMTP delivery server (token in body).') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/delivery-servers/{deliveryServer}/resend-verification</td>
                        <td class="py-2">{{ __('Resend verification email for SMTP delivery server.') }}</td>
                    </tr>

                    <tr>
                        <td class="py-2 pr-4 font-mono">GET</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/bounce-servers</td>
                        <td class="py-2">{{ __('List bounce servers (supports ?search= and ?active=).') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/bounce-servers</td>
                        <td class="py-2">{{ __('Create bounce server.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">GET</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/bounce-servers/{bounceServer}</td>
                        <td class="py-2">{{ __('Get bounce server.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">PUT</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/bounce-servers/{bounceServer}</td>
                        <td class="py-2">{{ __('Update bounce server.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">DELETE</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/bounce-servers/{bounceServer}</td>
                        <td class="py-2">{{ __('Delete bounce server.') }}</td>
                    </tr>

                    <tr>
                        <td class="py-2 pr-4 font-mono">GET</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/sending-domains</td>
                        <td class="py-2">{{ __('List sending domains (supports ?search= and ?status=).') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/sending-domains</td>
                        <td class="py-2">{{ __('Create sending domain.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">GET</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/sending-domains/{sendingDomain}</td>
                        <td class="py-2">{{ __('Get sending domain (includes DNS records).') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">PUT</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/sending-domains/{sendingDomain}</td>
                        <td class="py-2">{{ __('Update sending domain (SPF/DMARC/notes).') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">DELETE</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/sending-domains/{sendingDomain}</td>
                        <td class="py-2">{{ __('Delete sending domain.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/sending-domains/{sendingDomain}/verify</td>
                        <td class="py-2">{{ __('Verify sending domain by checking DNS (DKIM/SPF/DMARC).') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/sending-domains/{sendingDomain}/mark-verified</td>
                        <td class="py-2">{{ __('Manually mark sending domain as verified.') }}</td>
                    </tr>

                    <tr>
                        <td class="py-2 pr-4 font-mono">GET</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/tracking-domains</td>
                        <td class="py-2">{{ __('List tracking domains (supports ?search= and ?status=).') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/tracking-domains</td>
                        <td class="py-2">{{ __('Create tracking domain.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">GET</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/tracking-domains/{trackingDomain}</td>
                        <td class="py-2">{{ __('Get tracking domain.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">PUT</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/tracking-domains/{trackingDomain}</td>
                        <td class="py-2">{{ __('Update tracking domain (notes).') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">DELETE</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/tracking-domains/{trackingDomain}</td>
                        <td class="py-2">{{ __('Delete tracking domain.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/tracking-domains/{trackingDomain}/verify</td>
                        <td class="py-2">{{ __('Verify tracking domain.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/tracking-domains/{trackingDomain}/mark-verified</td>
                        <td class="py-2">{{ __('Manually mark tracking domain as verified.') }}</td>
                    </tr>

                    <tr>
                        <td class="py-2 pr-4 font-mono">GET</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/lists</td>
                        <td class="py-2">{{ __('List email lists (supports ?search= and ?status=).') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/lists</td>
                        <td class="py-2">{{ __('Create email list.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">GET</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/lists/{list}</td>
                        <td class="py-2">{{ __('Get email list.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">PUT</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/lists/{list}</td>
                        <td class="py-2">{{ __('Update email list.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">DELETE</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/lists/{list}</td>
                        <td class="py-2">{{ __('Delete email list.') }}</td>
                    </tr>

                    <tr>
                        <td class="py-2 pr-4 font-mono">GET</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/lists/{list}/contacts</td>
                        <td class="py-2">{{ __('List contacts/subscribers for a list (supports ?search= and ?status=).') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/lists/{list}/contacts</td>
                        <td class="py-2">{{ __('Create contact/subscriber in a list.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">GET</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/lists/{list}/contacts/{subscriber}</td>
                        <td class="py-2">{{ __('Get contact/subscriber.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">PUT</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/lists/{list}/contacts/{subscriber}</td>
                        <td class="py-2">{{ __('Update contact/subscriber.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">DELETE</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/lists/{list}/contacts/{subscriber}</td>
                        <td class="py-2">{{ __('Delete contact/subscriber.') }}</td>
                    </tr>

                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/lists/{list}/contacts/import/csv</td>
                        <td class="py-2">{{ __('Import contacts from a CSV file.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-mono">POST</td>
                        <td class="py-2 pr-4 font-mono">/api/v1/lists/{list}/contacts/import/json</td>
                        <td class="py-2">{{ __('Import contacts from a JSON payload.') }}</td>
                    </tr>
                `,
            });
        })();
    </script>
@endpush
@endsection

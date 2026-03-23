<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DeliveryServer;
use App\Models\TransactionalEmail;
use App\Services\DeliveryServerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TransactionalEmailController extends Controller
{
    public function __construct(
        private readonly DeliveryServerService $deliveryServerService,
    ) {}

    public function index(Request $request)
    {
        $customer = $request->user('sanctum');
        $q = trim((string) $request->query('q', ''));

        $emails = TransactionalEmail::query()
            ->where('customer_id', $customer->id)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('key', 'like', "%{$q}%")
                        ->orWhere('subject', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(25);

        return response()->json([
            'data' => $emails->items(),
            'meta' => [
                'current_page' => $emails->currentPage(),
                'per_page' => $emails->perPage(),
                'total' => $emails->total(),
                'last_page' => $emails->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, TransactionalEmail $transactionalEmail)
    {
        $customer = $request->user('sanctum');

        if ($transactionalEmail->customer_id !== $customer->id) {
            abort(404);
        }

        return response()->json([
            'data' => $transactionalEmail,
        ]);
    }

    public function store(Request $request)
    {
        $customer = $request->user('sanctum');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'nullable|string|max:255|unique:transactional_emails,key',
            'subject' => 'required|string|max:255',
            'from_name' => 'nullable|string|max:255',
            'from_email' => 'nullable|email|max:255',
            'reply_to' => 'nullable|email|max:255',
            'html_content' => 'nullable|string',
            'plain_text_content' => 'nullable|string',
            'template_variables' => 'nullable|array',
            'description' => 'nullable|string',
            'track_opens' => 'nullable|boolean',
            'track_clicks' => 'nullable|boolean',
        ]);

        $validated['customer_id'] = $customer->id;
        $validated['status'] = 'active';

        if (empty($validated['key'])) {
            $validated['key'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        $email = TransactionalEmail::create($validated);

        return response()->json([
            'data' => $email,
            'message' => 'Transactional email template created successfully.',
        ], 201);
    }

    public function update(Request $request, TransactionalEmail $transactionalEmail)
    {
        $customer = $request->user('sanctum');

        if ($transactionalEmail->customer_id !== $customer->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'key' => 'nullable|string|max:255|unique:transactional_emails,key,' . $transactionalEmail->id,
            'subject' => 'sometimes|required|string|max:255',
            'from_name' => 'nullable|string|max:255',
            'from_email' => 'nullable|email|max:255',
            'reply_to' => 'nullable|email|max:255',
            'html_content' => 'nullable|string',
            'plain_text_content' => 'nullable|string',
            'template_variables' => 'nullable|array',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:active,inactive',
            'track_opens' => 'nullable|boolean',
            'track_clicks' => 'nullable|boolean',
        ]);

        $transactionalEmail->update($validated);

        return response()->json([
            'data' => $transactionalEmail->fresh(),
            'message' => 'Transactional email template updated successfully.',
        ]);
    }

    public function destroy(Request $request, TransactionalEmail $transactionalEmail)
    {
        $customer = $request->user('sanctum');

        if ($transactionalEmail->customer_id !== $customer->id) {
            abort(404);
        }

        $transactionalEmail->delete();

        return response()->json([
            'message' => 'Transactional email template deleted successfully.',
        ]);
    }

    public function send(Request $request)
    {
        $customer = $request->user('sanctum');

        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $validated = $request->validate([
            'template_id' => 'required_without:template_key|nullable|integer',
            'template_key' => 'required_without:template_id|nullable|string',
            'to' => 'required|email',
            'to_name' => 'nullable|string|max:255',
            'variables' => 'nullable|array',
            'from_email' => 'nullable|email|max:255',
            'from_name' => 'nullable|string|max:255',
            'reply_to' => 'nullable|email|max:255',
            'subject' => 'nullable|string|max:255',
            'delivery_server_id' => 'nullable|exists:delivery_servers,id',
        ]);

        // Find template
        $template = null;
        if (!empty($validated['template_key'])) {
            $template = TransactionalEmail::where('customer_id', $customer->id)
                ->where('key', $validated['template_key'])
                ->first();
        } elseif (!empty($validated['template_id'])) {
            $template = TransactionalEmail::where('customer_id', $customer->id)
                ->where('id', $validated['template_id'])
                ->first();
        }

        if (!$template) {
            return response()->json([
                'message' => 'Template not found.',
            ], 404);
        }

        if (!$template->isActive()) {
            return response()->json([
                'message' => 'Template is inactive.',
            ], 422);
        }

        // Find delivery server
        $server = null;
        if (!empty($validated['delivery_server_id'])) {
            $server = DeliveryServer::where('id', $validated['delivery_server_id'])
                ->where(function ($q) use ($customer, $canUseSystem) {
                    $q->where('customer_id', $customer->id)
                        ->when($canUseSystem, fn ($sub) => $sub->orWhereNull('customer_id'));
                })
                ->where('status', 'active')
                ->first();
        } else {
            // Get first available delivery server for customer
            $server = DeliveryServer::where(function ($q) use ($customer, $canUseSystem) {
                    $q->where('customer_id', $customer->id);
                    if ($canUseSystem) {
                        $q->orWhereNull('customer_id');
                    }
                })
                ->where('status', 'active')
                ->where('use_for', true)
                ->first();
        }

        if (!$server) {
            return response()->json([
                'message' => 'No delivery server available.',
            ], 422);
        }

        // Process variables in content
        $variables = $validated['variables'] ?? [];
        $htmlContent = $template->html_content ?? '';
        $plainContent = $template->plain_text_content ?? '';
        $subject = $validated['subject'] ?? $template->subject;

        foreach ($variables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $htmlContent = str_replace($placeholder, $value, $htmlContent);
            $plainContent = str_replace($placeholder, $value, $plainContent);
            $subject = str_replace($placeholder, $value, $subject);
        }

        // Configure mailer
        $this->deliveryServerService->configureMailFromServer($server);

        $fromEmail = $validated['from_email'] ?? $template->from_email ?? $server->from_email;
        $fromName = $validated['from_name'] ?? $template->from_name ?? $server->from_name ?? config('app.name');
        $replyTo = $validated['reply_to'] ?? $template->reply_to;

        try {
            Mail::send([], [], function ($message) use ($validated, $fromEmail, $fromName, $replyTo, $subject, $htmlContent, $plainContent) {
                $message->to($validated['to'], $validated['to_name'] ?? null)
                    ->subject($subject)
                    ->from($fromEmail, $fromName);

                if ($replyTo) {
                    $message->replyTo($replyTo);
                }

                if (!empty($htmlContent)) {
                    $message->html($htmlContent);
                }

                if (!empty($plainContent)) {
                    $message->text($plainContent);
                }
            });

            // Increment sent count
            $template->increment('sent_count');

            Log::info('Transactional email sent via API', [
                'template_id' => $template->id,
                'to' => $validated['to'],
                'customer_id' => $customer->id,
            ]);

            return response()->json([
                'message' => 'Email sent successfully.',
                'data' => [
                    'template_id' => $template->id,
                    'to' => $validated['to'],
                    'subject' => $subject,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send transactional email via API', [
                'template_id' => $template->id,
                'to' => $validated['to'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sendRaw(Request $request)
    {
        $customer = $request->user('sanctum');

        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $validated = $request->validate([
            'to' => 'required|email',
            'to_name' => 'nullable|string|max:255',
            'subject' => 'required|string|max:255',
            'html_content' => 'required_without:plain_text_content|nullable|string',
            'plain_text_content' => 'required_without:html_content|nullable|string',
            'from_email' => 'nullable|email|max:255',
            'from_name' => 'nullable|string|max:255',
            'reply_to' => 'nullable|email|max:255',
            'delivery_server_id' => 'nullable|exists:delivery_servers,id',
        ]);

        // Find delivery server
        $server = null;
        if (!empty($validated['delivery_server_id'])) {
            $server = DeliveryServer::where('id', $validated['delivery_server_id'])
                ->where(function ($q) use ($customer, $canUseSystem) {
                    $q->where('customer_id', $customer->id)
                        ->when($canUseSystem, fn ($sub) => $sub->orWhereNull('customer_id'));
                })
                ->where('status', 'active')
                ->first();
        } else {
            $server = DeliveryServer::where(function ($q) use ($customer, $canUseSystem) {
                    $q->where('customer_id', $customer->id);
                    if ($canUseSystem) {
                        $q->orWhereNull('customer_id');
                    }
                })
                ->where('status', 'active')
                ->where('use_for', true)
                ->first();
        }

        if (!$server) {
            return response()->json([
                'message' => 'No delivery server available.',
            ], 422);
        }

        $this->deliveryServerService->configureMailFromServer($server);

        $fromEmail = $validated['from_email'] ?? $server->from_email;
        $fromName = $validated['from_name'] ?? $server->from_name ?? config('app.name');

        try {
            Mail::send([], [], function ($message) use ($validated, $fromEmail, $fromName) {
                $message->to($validated['to'], $validated['to_name'] ?? null)
                    ->subject($validated['subject'])
                    ->from($fromEmail, $fromName);

                if (!empty($validated['reply_to'])) {
                    $message->replyTo($validated['reply_to']);
                }

                if (!empty($validated['html_content'])) {
                    $message->html($validated['html_content']);
                }

                if (!empty($validated['plain_text_content'])) {
                    $message->text($validated['plain_text_content']);
                }
            });

            Log::info('Raw transactional email sent via API', [
                'to' => $validated['to'],
                'subject' => $validated['subject'],
                'customer_id' => $customer->id,
            ]);

            return response()->json([
                'message' => 'Email sent successfully.',
                'data' => [
                    'to' => $validated['to'],
                    'subject' => $validated['subject'],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send raw transactional email via API', [
                'to' => $validated['to'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sendBulk(Request $request)
    {
        $customer = $request->user('sanctum');

        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $validated = $request->validate([
            'template_key' => 'required_without:template_id|nullable|string',
            'template_id' => 'required_without:template_key|nullable|integer',
            'recipients' => 'required|array|min:1|max:1000',
            'recipients.*.to' => 'required|email',
            'recipients.*.to_name' => 'nullable|string|max:255',
            'recipients.*.variables' => 'nullable|array',
            'from_email' => 'nullable|email|max:255',
            'from_name' => 'nullable|string|max:255',
            'reply_to' => 'nullable|email|max:255',
            'subject' => 'nullable|string|max:255',
            'delivery_server_id' => 'nullable|exists:delivery_servers,id',
        ]);

        // Find template
        $template = null;
        if (!empty($validated['template_key'])) {
            $template = TransactionalEmail::where('customer_id', $customer->id)
                ->where('key', $validated['template_key'])
                ->first();
        } elseif (!empty($validated['template_id'])) {
            $template = TransactionalEmail::where('customer_id', $customer->id)
                ->where('id', $validated['template_id'])
                ->first();
        }

        if (!$template) {
            return response()->json([
                'message' => 'Template not found.',
            ], 404);
        }

        if (!$template->isActive()) {
            return response()->json([
                'message' => 'Template is inactive.',
            ], 422);
        }

        // Find delivery server
        $server = null;
        if (!empty($validated['delivery_server_id'])) {
            $server = DeliveryServer::where('id', $validated['delivery_server_id'])
                ->where(function ($q) use ($customer, $canUseSystem) {
                    $q->where('customer_id', $customer->id)
                        ->when($canUseSystem, fn ($sub) => $sub->orWhereNull('customer_id'));
                })
                ->where('status', 'active')
                ->first();
        } else {
            $server = DeliveryServer::where(function ($q) use ($customer, $canUseSystem) {
                    $q->where('customer_id', $customer->id);
                    if ($canUseSystem) {
                        $q->orWhereNull('customer_id');
                    }
                })
                ->where('status', 'active')
                ->where('use_for', true)
                ->first();
        }

        if (!$server) {
            return response()->json([
                'message' => 'No delivery server available.',
            ], 422);
        }

        $this->deliveryServerService->configureMailFromServer($server);

        $fromEmail = $validated['from_email'] ?? $template->from_email ?? $server->from_email;
        $fromName = $validated['from_name'] ?? $template->from_name ?? $server->from_name ?? config('app.name');
        $replyTo = $validated['reply_to'] ?? $template->reply_to;
        $baseSubject = $validated['subject'] ?? $template->subject;

        $results = [
            'sent' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($validated['recipients'] as $index => $recipient) {
            $variables = $recipient['variables'] ?? [];
            $htmlContent = $template->html_content ?? '';
            $plainContent = $template->plain_text_content ?? '';
            $subject = $baseSubject;

            foreach ($variables as $key => $value) {
                $placeholder = '{{' . $key . '}}';
                $htmlContent = str_replace($placeholder, $value, $htmlContent);
                $plainContent = str_replace($placeholder, $value, $plainContent);
                $subject = str_replace($placeholder, $value, $subject);
            }

            try {
                Mail::send([], [], function ($message) use ($recipient, $fromEmail, $fromName, $replyTo, $subject, $htmlContent, $plainContent) {
                    $message->to($recipient['to'], $recipient['to_name'] ?? null)
                        ->subject($subject)
                        ->from($fromEmail, $fromName);

                    if ($replyTo) {
                        $message->replyTo($replyTo);
                    }

                    if (!empty($htmlContent)) {
                        $message->html($htmlContent);
                    }

                    if (!empty($plainContent)) {
                        $message->text($plainContent);
                    }
                });

                $results['sent']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'index' => $index,
                    'to' => $recipient['to'],
                    'error' => $e->getMessage(),
                ];

                Log::error('Failed to send bulk transactional email', [
                    'template_id' => $template->id,
                    'to' => $recipient['to'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Update sent count
        $template->increment('sent_count', $results['sent']);

        Log::info('Bulk transactional emails sent via API', [
            'template_id' => $template->id,
            'total' => count($validated['recipients']),
            'sent' => $results['sent'],
            'failed' => $results['failed'],
            'customer_id' => $customer->id,
        ]);

        return response()->json([
            'message' => "Sent {$results['sent']} of " . count($validated['recipients']) . " emails.",
            'data' => $results,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CampaignLog;
use App\Models\CampaignRecipient;
use App\Models\CampaignTracking;
use App\Models\EmailList;
use App\Models\ListSubscriber;
use App\Models\SubscriberImport;
use App\Services\ListSubscriberService;
use App\Services\SubscriberImportProcessor;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ListSubscriberController extends Controller
{
    public function __construct(
        protected ListSubscriberService $listSubscriberService
    ) {}

    /**
     * Display a listing of subscribers for a list.
     */
    public function index(Request $request, EmailList $list)
    {
        $filters = $request->only(['search', 'status']);
        $subscribers = $this->listSubscriberService->getPaginated($list, $filters);

        $performanceMap = $this->subscriberPerformanceMap(
            (int) $list->customer_id,
            (int) $list->id,
            $subscribers->getCollection()->pluck('email')->all()
        );
        $this->appendPerformanceToSubscribers($subscribers, $performanceMap);

        return view('customer.lists.subscribers.index', compact('list', 'subscribers', 'filters'));
    }

    private function subscriberPerformanceMap(int $customerId, int $listId, array $emails): array
    {
        $normalizedEmails = array_values(array_unique(array_filter(array_map(
            static fn ($email) => strtolower(trim((string) $email)),
            $emails
        ))));

        if ($normalizedEmails === []) {
            return [];
        }

        $rows = CampaignRecipient::query()
            ->whereHas('campaign', function ($query) use ($customerId, $listId) {
                $query->where('customer_id', $customerId)
                    ->where('list_id', $listId);
            })
            ->whereIn(DB::raw('LOWER(email)'), $normalizedEmails)
            ->selectRaw('LOWER(email) as email_key')
            ->selectRaw('COUNT(*) as total_campaigns')
            ->selectRaw('SUM(CASE WHEN sent_at IS NOT NULL OR status IN ("sent", "opened", "clicked", "bounced", "failed") THEN 1 ELSE 0 END) as sent_count')
            ->selectRaw('SUM(CASE WHEN opened_at IS NOT NULL OR status IN ("opened", "clicked") THEN 1 ELSE 0 END) as opened_count')
            ->selectRaw('SUM(CASE WHEN clicked_at IS NOT NULL OR status = "clicked" THEN 1 ELSE 0 END) as clicked_count')
            ->groupBy('email_key')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $key = strtolower((string) ($row->email_key ?? ''));
            if ($key === '') {
                continue;
            }

            $sent = (int) ($row->sent_count ?? 0);
            $opened = (int) ($row->opened_count ?? 0);
            $clicked = (int) ($row->clicked_count ?? 0);

            $map[$key] = [
                'total_campaigns' => (int) ($row->total_campaigns ?? 0),
                'sent_count' => $sent,
                'opened_count' => $opened,
                'clicked_count' => $clicked,
                'open_rate' => $sent > 0 ? round(($opened / $sent) * 100, 2) : 0.0,
                'click_rate' => $sent > 0 ? round(($clicked / $sent) * 100, 2) : 0.0,
            ];
        }

        return $map;
    }

    private function appendPerformanceToSubscribers(LengthAwarePaginator $subscribers, array $performanceMap): void
    {
        $collection = $subscribers->getCollection();
        if (!$collection instanceof Collection) {
            return;
        }

        $collection->transform(function (ListSubscriber $subscriber) use ($performanceMap) {
            $key = strtolower(trim((string) $subscriber->email));
            $metrics = $performanceMap[$key] ?? [
                'total_campaigns' => 0,
                'sent_count' => 0,
                'opened_count' => 0,
                'clicked_count' => 0,
                'open_rate' => 0.0,
                'click_rate' => 0.0,
            ];

            $subscriber->setAttribute('total_campaigns', $metrics['total_campaigns']);
            $subscriber->setAttribute('sent_count', $metrics['sent_count']);
            $subscriber->setAttribute('opened_count', $metrics['opened_count']);
            $subscriber->setAttribute('clicked_count', $metrics['clicked_count']);
            $subscriber->setAttribute('open_rate', $metrics['open_rate']);
            $subscriber->setAttribute('click_rate', $metrics['click_rate']);

            return $subscriber;
        });
    }

    public function export(Request $request, EmailList $list)
    {
        $customerId = auth('customer')->id();
        if (!$customerId || (int) $list->customer_id !== (int) $customerId) {
            abort(404);
        }

        $filters = $request->only(['search', 'status']);

        $fileName = 'subscribers_' . (string) $list->id . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($list, $filters) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }

            fputcsv($out, [
                'email',
                'first_name',
                'last_name',
                'status',
                'source',
                'subscribed_at',
                'confirmed_at',
                'unsubscribed_at',
                'tags',
                'custom_fields',
                'notes',
            ]);

            $this->listSubscriberService
                ->query($list, $filters)
                ->orderBy('id')
                ->chunk(1000, function ($chunk) use ($out) {
                    foreach ($chunk as $subscriber) {
                        fputcsv($out, [
                            (string) ($subscriber->email ?? ''),
                            (string) ($subscriber->first_name ?? ''),
                            (string) ($subscriber->last_name ?? ''),
                            (string) ($subscriber->status ?? ''),
                            (string) ($subscriber->source ?? ''),
                            optional($subscriber->subscribed_at)->toIso8601String(),
                            optional($subscriber->confirmed_at)->toIso8601String(),
                            optional($subscriber->unsubscribed_at)->toIso8601String(),
                            json_encode($subscriber->tags ?? [], JSON_UNESCAPED_UNICODE),
                            json_encode($subscriber->custom_fields ?? [], JSON_UNESCAPED_UNICODE),
                            (string) ($subscriber->notes ?? ''),
                        ]);
                    }
                });

            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Show the form for creating a new subscriber.
     */
    public function create(EmailList $list)
    {
        return view('customer.lists.subscribers.create', compact('list'));
    }

    /**
     * Store a newly created subscriber.
     */
    public function store(Request $request, EmailList $list)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable'],
        ]);

        $validated['tags'] = $this->normalizeTags($request->input('tags'));

        $subscriber = $this->listSubscriberService->create($list, $validated);

        return redirect()
            ->route('customer.lists.subscribers.index', $list)
            ->with('success', 'Subscriber added successfully.');
    }

    /**
     * Display the specified subscriber.
     */
    public function show(EmailList $list, ListSubscriber $subscriber)
    {
        $customerId = auth('customer')->id();
        if (!$customerId || (int) $list->customer_id !== (int) $customerId || (int) $subscriber->list_id !== (int) $list->id) {
            abort(404);
        }

        $email = strtolower((string) $subscriber->email);

        $campaignRecipientStats = CampaignRecipient::query()
            ->whereHas('campaign', function ($query) use ($customerId, $list) {
                $query->where('customer_id', (int) $customerId)
                    ->where('list_id', (int) $list->id);
            })
            ->whereRaw('LOWER(email) = ?', [$email])
            ->selectRaw('COUNT(*) as total_campaigns')
            ->selectRaw('SUM(CASE WHEN sent_at IS NOT NULL OR status IN ("sent", "opened", "clicked", "bounced", "failed") THEN 1 ELSE 0 END) as sent_count')
            ->selectRaw('SUM(CASE WHEN opened_at IS NOT NULL OR status IN ("opened", "clicked") THEN 1 ELSE 0 END) as opened_count')
            ->selectRaw('SUM(CASE WHEN clicked_at IS NOT NULL OR status = "clicked" THEN 1 ELSE 0 END) as clicked_count')
            ->selectRaw('SUM(CASE WHEN bounced_at IS NOT NULL OR status = "bounced" THEN 1 ELSE 0 END) as bounced_count')
            ->first();

        $sentCount = (int) ($campaignRecipientStats->sent_count ?? 0);
        $openedCount = (int) ($campaignRecipientStats->opened_count ?? 0);
        $clickedCount = (int) ($campaignRecipientStats->clicked_count ?? 0);

        $contactPerformance = [
            'total_campaigns' => (int) ($campaignRecipientStats->total_campaigns ?? 0),
            'sent_count' => $sentCount,
            'opened_count' => $openedCount,
            'clicked_count' => $clickedCount,
            'bounced_count' => (int) ($campaignRecipientStats->bounced_count ?? 0),
            'open_rate' => $sentCount > 0 ? round(($openedCount / $sentCount) * 100, 2) : 0.0,
            'click_rate' => $sentCount > 0 ? round(($clickedCount / $sentCount) * 100, 2) : 0.0,
        ];

        $trackingEvents = CampaignTracking::query()
            ->where(function ($query) use ($subscriber, $email) {
                $query->where('subscriber_id', (int) $subscriber->id)
                    ->orWhereRaw('LOWER(email) = ?', [$email]);
            })
            ->whereHas('campaign', function ($query) use ($customerId, $list) {
                $query->where('customer_id', (int) $customerId)
                    ->where('list_id', (int) $list->id);
            })
            ->with(['campaign:id,name'])
            ->orderByDesc('event_at')
            ->limit(100)
            ->get()
            ->map(function (CampaignTracking $event) {
                return [
                    'source' => 'tracking',
                    'event' => (string) $event->event_type,
                    'campaign_name' => (string) ($event->campaign->name ?? 'Campaign #' . $event->campaign_id),
                    'occurred_at' => $event->event_at ?? $event->created_at,
                    'url' => $event->url,
                    'ip_address' => $event->ip_address,
                    'user_agent' => $event->user_agent,
                    'details' => $event->bounce_reason ?: $event->complaint_reason,
                ];
            });

        $campaignLogEvents = CampaignLog::query()
            ->whereHas('campaign', function ($query) use ($customerId, $list) {
                $query->where('customer_id', (int) $customerId)
                    ->where('list_id', (int) $list->id);
            })
            ->whereHas('recipient', function ($query) use ($email) {
                $query->whereRaw('LOWER(email) = ?', [$email]);
            })
            ->with(['campaign:id,name'])
            ->latest('created_at')
            ->limit(100)
            ->get()
            ->map(function (CampaignLog $event) {
                return [
                    'source' => 'campaign_log',
                    'event' => (string) $event->event,
                    'campaign_name' => (string) ($event->campaign->name ?? 'Campaign #' . $event->campaign_id),
                    'occurred_at' => $event->created_at,
                    'url' => $event->url,
                    'ip_address' => $event->ip_address,
                    'user_agent' => $event->user_agent,
                    'details' => $event->error_message,
                ];
            });

        $activityHistory = $trackingEvents
            ->merge($campaignLogEvents)
            ->sortByDesc(function (array $item) {
                return optional($item['occurred_at'])->getTimestamp() ?? 0;
            })
            ->take(150)
            ->values();

        return view('customer.lists.subscribers.show', compact('list', 'subscriber', 'contactPerformance', 'activityHistory'));
    }

    /**
     * Show the form for editing the specified subscriber.
     */
    public function edit(EmailList $list, ListSubscriber $subscriber)
    {
        return view('customer.lists.subscribers.edit', compact('list', 'subscriber'));
    }

    /**
     * Update the specified subscriber.
     */
    public function update(Request $request, EmailList $list, ListSubscriber $subscriber)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:confirmed,unconfirmed,unsubscribed,blacklisted,bounced'],
            'tags' => ['nullable'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['tags'] = $this->normalizeTags($request->input('tags'));

        $this->listSubscriberService->update($subscriber, $validated);

        return redirect()
            ->route('customer.lists.subscribers.show', [$list, $subscriber])
            ->with('success', 'Subscriber updated successfully.');
    }

    /**
     * Remove the specified subscriber.
     */
    public function destroy(EmailList $list, ListSubscriber $subscriber)
    {
        $this->listSubscriberService->delete($subscriber);

        return redirect()
            ->route('customer.lists.subscribers.index', $list)
            ->with('success', 'Subscriber deleted successfully.');
    }

    /**
     * Show CSV import form.
     */
    public function showImport(EmailList $list)
    {
        return view('customer.lists.subscribers.import', compact('list'));
    }

    /**
     * Handle CSV import.
     */
    public function import(Request $request, EmailList $list)
    {
        $validated = $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:51200'],
            'column_mapping' => ['required', 'array'],
            'column_mapping.email' => ['required', 'string'],
            'column_mapping.first_name' => ['nullable', 'string'],
            'column_mapping.last_name' => ['nullable', 'string'],
            'column_mapping.custom_fields' => ['nullable', 'array'],
            'column_mapping.custom_fields.*' => ['nullable', 'string'],
            'column_mapping.capture_unmapped' => ['nullable', 'boolean'],
            'column_mapping.add_list_custom_fields' => ['nullable', 'boolean'],
            'skip_duplicates' => ['nullable', 'boolean'],
            'update_existing' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('csv_file');
        
        if (!$file || !$file->isValid()) {
            return redirect()
                ->route('customer.lists.subscribers.import', $list)
                ->with('error', 'Invalid file upload. Please try again.')
                ->withInput();
        }
        
        // Ensure imports directory exists using Storage facade
        try {
            if (!Storage::disk('local')->exists('imports')) {
                Storage::disk('local')->makeDirectory('imports');
            }
        } catch (\Exception $e) {
            Log::error("Failed to create imports directory: " . $e->getMessage());
            return redirect()
                ->route('customer.lists.subscribers.import', $list)
                ->with('error', 'Failed to create import directory. Please check storage permissions.')
                ->withInput();
        }
        
        // Store file and get full path
        $fileName = 'subscribers_' . time() . '_' . uniqid() . '.csv';
        
        try {
            // Use Storage facade for more reliable file handling
            $filePath = Storage::disk('local')->putFileAs('imports', $file, $fileName);
            
            if ($filePath === false) {
                Log::error("Storage::putFileAs() returned false for file: {$fileName}");
                return redirect()
                    ->route('customer.lists.subscribers.import', $list)
                    ->with('error', 'Failed to save uploaded file. Please try again.')
                    ->withInput();
            }
            
            $fullPath = Storage::disk('local')->path($filePath);

            // Verify file was saved
            if (!Storage::disk('local')->exists($filePath)) {
                Log::error("File not found after storage: {$filePath}");
                return redirect()
                    ->route('customer.lists.subscribers.import', $list)
                    ->with('error', 'File was not saved correctly. Please try again.')
                    ->withInput();
            }
            
            // Verify file is readable
            if (!is_readable($fullPath)) {
                Log::error("File is not readable: {$fullPath}");
                return redirect()
                    ->route('customer.lists.subscribers.import', $list)
                    ->with('error', 'File is not readable. Please check permissions.')
                    ->withInput();
            }
            
            Log::info("File successfully stored: {$fullPath}");
            
        } catch (\Exception $e) {
            Log::error("Exception during file storage: " . $e->getMessage(), [
                'file' => $fileName,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()
                ->route('customer.lists.subscribers.import', $list)
                ->with('error', 'Error saving file: ' . $e->getMessage())
                ->withInput();
        }

        $customerId = (int) ($list->customer_id ?? 0);

        $subscriberImport = SubscriberImport::create([
            'customer_id' => $customerId,
            'list_id' => (int) $list->id,
            'status' => 'queued',
            'source' => 'csv_import',
            'ip_address' => $request->ip(),
            'stored_path' => $filePath,
            'total_rows' => 0,
            'processed_count' => 0,
            'imported_count' => 0,
            'updated_count' => 0,
            'skipped_count' => 0,
            'error_count' => 0,
        ]);

        \App\Jobs\ImportSubscribersJob::dispatch(
            $list,
            $fullPath,
            $validated['column_mapping'],
            $validated['skip_duplicates'] ?? true,
            $validated['update_existing'] ?? false,
            'csv_import',
            $request->ip(),
            (int) $subscriberImport->id
        )->onQueue('imports');

        return redirect()
            ->route('customer.lists.subscribers.index', $list)
            ->with('success', 'Import started. You will be notified when it completes.');
    }

    public function importAjaxStart(Request $request, EmailList $list)
    {
        $customerId = auth('customer')->id();
        if (!$customerId || (int) $list->customer_id !== (int) $customerId) {
            abort(404);
        }

        $validated = $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:51200'],
            'column_mapping' => ['required', 'array'],
            'column_mapping.email' => ['required', 'string'],
            'column_mapping.first_name' => ['nullable', 'string'],
            'column_mapping.last_name' => ['nullable', 'string'],
            'column_mapping.custom_fields' => ['nullable', 'array'],
            'column_mapping.custom_fields.*' => ['nullable', 'string'],
            'column_mapping.capture_unmapped' => ['nullable', 'boolean'],
            'column_mapping.add_list_custom_fields' => ['nullable', 'boolean'],
            'skip_duplicates' => ['nullable', 'boolean'],
            'update_existing' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('csv_file');
        if (!$file || !$file->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file upload.',
            ], 422);
        }

        try {
            if (!Storage::disk('local')->exists('imports')) {
                Storage::disk('local')->makeDirectory('imports');
            }
        } catch (\Throwable $e) {
            Log::error('Failed to create imports directory', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create import directory. Please check storage permissions.',
            ], 500);
        }

        $fileName = 'subscribers_' . time() . '_' . uniqid() . '.csv';

        try {
            $filePath = Storage::disk('local')->putFileAs('imports', $file, $fileName);
            if ($filePath === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save uploaded file. Please try again.',
                ], 500);
            }

            if (!Storage::disk('local')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload completed but the file could not be found on the server. Please check storage permissions.',
                ], 500);
            }
        } catch (\Throwable $e) {
            Log::error('Exception during file storage', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error saving file: ' . $e->getMessage(),
            ], 500);
        }

        $subscriberImport = SubscriberImport::create([
            'customer_id' => (int) $customerId,
            'list_id' => (int) $list->id,
            'status' => 'queued',
            'source' => 'csv_import',
            'ip_address' => $request->ip(),
            'stored_path' => $filePath,
            'column_mapping' => $validated['column_mapping'],
            'skip_duplicates' => (bool) ($validated['skip_duplicates'] ?? true),
            'update_existing' => (bool) ($validated['update_existing'] ?? false),
            'file_offset' => 0,
            'total_rows' => 0,
            'processed_count' => 0,
            'imported_count' => 0,
            'updated_count' => 0,
            'skipped_count' => 0,
            'error_count' => 0,
        ]);

        try {
            app(SubscriberImportProcessor::class)->processImportNow($subscriberImport, 400, 6);
        } catch (\Throwable $e) {
            // Processor will mark failed itself; just continue.
        }

        return response()->json([
            'success' => true,
            'import_id' => (int) $subscriberImport->id,
        ]);
    }

    public function importAjaxStep(Request $request, EmailList $list)
    {
        $customerId = auth('customer')->id();
        if (!$customerId || (int) $list->customer_id !== (int) $customerId) {
            abort(404);
        }

        $validated = $request->validate([
            'import_id' => ['required', 'integer'],
        ]);

        $import = SubscriberImport::query()
            ->whereKey((int) $validated['import_id'])
            ->where('customer_id', (int) $customerId)
            ->where('list_id', (int) $list->id)
            ->firstOrFail();

        if (in_array((string) $import->status, ['completed', 'failed'], true)) {
            return $this->importStats($request, $list);
        }

        try {
            app(SubscriberImportProcessor::class)->processImportNow($import, 800, 6);
        } catch (\Throwable $e) {
            // Processor handles marking failed.
        }

        return $this->importStats($request, $list);
    }

    public function importStats(Request $request, EmailList $list)
    {
        $customerId = auth('customer')->id();
        if (!$customerId || (int) $list->customer_id !== (int) $customerId) {
            abort(404);
        }

        $import = SubscriberImport::query()
            ->where('customer_id', (int) $customerId)
            ->where('list_id', (int) $list->id)
            ->latest()
            ->first();

        if (!$import) {
            return response()->json([
                'success' => true,
                'import' => null,
            ]);
        }

        $total = max(0, (int) $import->total_rows);
        $processed = max(0, (int) $import->processed_count);
        $percent = $total > 0 ? round(($processed / $total) * 100, 1) : 0;

        if ((string) $import->status === 'failed'
            && (string) ($import->failure_reason ?? '') === 'Import file not found.'
            && $total > 0
            && $processed >= $total
        ) {
            $import->update([
                'status' => 'completed',
                'failure_reason' => null,
            ]);

            $import = $import->fresh();
        }

        return response()->json([
            'success' => true,
            'import' => [
                'id' => (int) $import->id,
                'status' => (string) $import->status,
                'total_rows' => $total,
                'processed_count' => $processed,
                'imported_count' => (int) $import->imported_count,
                'updated_count' => (int) $import->updated_count,
                'skipped_count' => (int) $import->skipped_count,
                'error_count' => (int) $import->error_count,
                'percent' => $percent,
                'failure_reason' => $import->failure_reason,
                'started_at' => $import->started_at?->toIso8601String(),
                'finished_at' => $import->finished_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Confirm a subscriber.
     */
    public function confirm(EmailList $list, ListSubscriber $subscriber)
    {
        $this->listSubscriberService->confirm($subscriber);

        return redirect()
            ->route('customer.lists.subscribers.show', [$list, $subscriber])
            ->with('success', 'Subscriber confirmed successfully.');
    }

    /**
     * Unsubscribe a subscriber.
     */
    public function unsubscribe(EmailList $list, ListSubscriber $subscriber)
    {
        $this->listSubscriberService->unsubscribe($subscriber);

        return redirect()
            ->route('customer.lists.subscribers.show', [$list, $subscriber])
            ->with('success', 'Subscriber unsubscribed successfully.');
    }

    /**
     * Resend confirmation email to subscriber.
     */
    public function resendConfirmation(EmailList $list, ListSubscriber $subscriber)
    {
        try {
            $this->listSubscriberService->resendConfirmationEmail($subscriber);

            return redirect()
                ->route('customer.lists.subscribers.show', [$list, $subscriber])
                ->with('success', 'Confirmation email sent successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('customer.lists.subscribers.show', [$list, $subscriber])
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Bulk delete subscribers.
     */
    public function bulkDelete(Request $request, EmailList $list)
    {
        $validated = $request->validate([
            'subscriber_ids' => ['nullable', 'string'],
            'all_matching' => ['nullable', 'boolean'],
            'search' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ]);

        $allMatching = (bool) ($validated['all_matching'] ?? false);
        $filters = [
            'search' => $validated['search'] ?? null,
            'status' => $validated['status'] ?? null,
        ];

        $ids = array_filter(array_map('intval', explode(',', (string) ($validated['subscriber_ids'] ?? ''))));

        if (!$allMatching && empty($ids)) {
            return redirect()
                ->route('customer.lists.subscribers.index', $list)
                ->with('error', 'No subscribers selected.');
        }

        $query = $this->listSubscriberService->query($list, $filters);
        if (!$allMatching) {
            $query->whereIn('id', $ids);
        }

        $count = $query->delete();

        return redirect()
            ->route('customer.lists.subscribers.index', $list)
            ->with('success', "Successfully deleted {$count} subscriber(s).");
    }

    /**
     * Bulk confirm subscribers.
     */
    public function bulkConfirm(Request $request, EmailList $list)
    {
        $validated = $request->validate([
            'subscriber_ids' => ['nullable', 'string'],
            'all_matching' => ['nullable', 'boolean'],
            'search' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ]);

        $allMatching = (bool) ($validated['all_matching'] ?? false);
        $filters = [
            'search' => $validated['search'] ?? null,
            'status' => $validated['status'] ?? null,
        ];

        $ids = array_filter(array_map('intval', explode(',', (string) ($validated['subscriber_ids'] ?? ''))));

        if (!$allMatching && empty($ids)) {
            return redirect()
                ->route('customer.lists.subscribers.index', $list)
                ->with('error', 'No subscribers selected.');
        }

        $query = $this->listSubscriberService
            ->query($list, $filters)
            ->where('status', 'unconfirmed');

        if (!$allMatching) {
            $query->whereIn('id', $ids);
        }

        $count = $query->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        return redirect()
            ->route('customer.lists.subscribers.index', $list)
            ->with('success', "Successfully confirmed {$count} subscriber(s).");
    }

    /**
     * Bulk unsubscribe subscribers.
     */
    public function bulkUnsubscribe(Request $request, EmailList $list)
    {
        $validated = $request->validate([
            'subscriber_ids' => ['nullable', 'string'],
            'all_matching' => ['nullable', 'boolean'],
            'search' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ]);

        $allMatching = (bool) ($validated['all_matching'] ?? false);
        $filters = [
            'search' => $validated['search'] ?? null,
            'status' => $validated['status'] ?? null,
        ];

        $ids = array_filter(array_map('intval', explode(',', (string) ($validated['subscriber_ids'] ?? ''))));

        if (!$allMatching && empty($ids)) {
            return redirect()
                ->route('customer.lists.subscribers.index', $list)
                ->with('error', 'No subscribers selected.');
        }

        $query = $this->listSubscriberService->query($list, $filters);
        if (!$allMatching) {
            $query->whereIn('id', $ids);
        }

        $subscribers = $query->get();

        $count = 0;
        foreach ($subscribers as $subscriber) {
            $this->listSubscriberService->unsubscribe($subscriber);
            $count++;
        }

        return redirect()
            ->route('customer.lists.subscribers.index', $list)
            ->with('success', "Successfully unsubscribed {$count} subscriber(s).");
    }

    /**
     * Bulk resend confirmation emails.
     */
    public function bulkResend(Request $request, EmailList $list)
    {
        $validated = $request->validate([
            'subscriber_ids' => ['nullable', 'string'],
            'all_matching' => ['nullable', 'boolean'],
            'search' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ]);

        $allMatching = (bool) ($validated['all_matching'] ?? false);
        $filters = [
            'search' => $validated['search'] ?? null,
            'status' => $validated['status'] ?? null,
        ];

        $ids = array_filter(array_map('intval', explode(',', (string) ($validated['subscriber_ids'] ?? ''))));

        if (!$allMatching && empty($ids)) {
            return redirect()
                ->route('customer.lists.subscribers.index', $list)
                ->with('error', 'No subscribers selected.');
        }

        $query = $this->listSubscriberService
            ->query($list, $filters)
            ->where('status', 'unconfirmed');

        if (!$allMatching) {
            $query->whereIn('id', $ids);
        }

        $subscribers = $query->get();

        $count = 0;
        $errors = 0;

        foreach ($subscribers as $subscriber) {
            try {
                $this->listSubscriberService->resendConfirmationEmail($subscriber);
                $count++;
            } catch (\Exception $e) {
                Log::error("Failed to resend confirmation to subscriber {$subscriber->id}: " . $e->getMessage());
                $errors++;
            }
        }

        $message = "Successfully sent confirmation emails to {$count} subscriber(s).";
        if ($errors > 0) {
            $message .= " {$errors} email(s) failed to send.";
        }

        return redirect()
            ->route('customer.lists.subscribers.index', $list)
            ->with($errors > 0 ? 'warning' : 'success', $message);
    }

    private function normalizeTags(mixed $tagsInput): array
    {
        $tags = [];

        if (is_string($tagsInput)) {
            $tags = preg_split('/[,\n]+/', $tagsInput) ?: [];
        } elseif (is_array($tagsInput)) {
            $tags = $tagsInput;
        }

        return array_values(array_unique(array_filter(array_map(
            static fn ($tag) => trim((string) $tag),
            $tags
        ), static fn (string $tag) => $tag !== '')));
    }
}

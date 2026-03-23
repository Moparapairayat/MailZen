<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Jobs\ImportSubscribersJob;
use App\Models\EmailList;
use App\Services\ListSubscriberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ListSubscriberImportController extends Controller
{
    public function __construct(
        protected ListSubscriberService $listSubscriberService
    ) {
    }

    protected function customer(Request $request)
    {
        return $request->user('sanctum');
    }

    protected function authorizeListOwnership(Request $request, EmailList $list): EmailList
    {
        $customer = $this->customer($request);
        if (!$customer || (int) $list->customer_id !== (int) $customer->id) {
            abort(404);
        }

        return $list;
    }

    public function importJson(Request $request, EmailList $list)
    {
        $list = $this->authorizeListOwnership($request, $list);

        $validated = $request->validate([
            'subscribers' => ['required', 'array', 'min:1'],
            'subscribers.*.email' => ['required', 'email', 'max:255'],
            'subscribers.*.first_name' => ['nullable', 'string', 'max:255'],
            'subscribers.*.last_name' => ['nullable', 'string', 'max:255'],
            'subscribers.*.tags' => ['nullable', 'array'],
            'subscribers.*.custom_fields' => ['nullable', 'array'],
        ]);

        $items = (array) ($validated['subscribers'] ?? []);

        $normalized = array_map(function (array $row) use ($request) {
            $row['source'] = 'api_import_json';
            $row['ip_address'] = $request->ip();
            $row['email'] = strtolower(trim((string) ($row['email'] ?? '')));
            return $row;
        }, $items);

        $result = $this->listSubscriberService->import($list, $normalized);

        return response()->json(['data' => $result]);
    }

    public function importCsv(Request $request, EmailList $list)
    {
        $list = $this->authorizeListOwnership($request, $list);

        $validated = $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
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
            return response()->json(['message' => 'Invalid file upload.'], 422);
        }

        $fileName = 'api_subscribers_' . time() . '_' . uniqid() . '.csv';
        $filePath = Storage::disk('local')->putFileAs('imports', $file, $fileName);
        if ($filePath === false) {
            return response()->json(['message' => 'Failed to store uploaded file.'], 500);
        }

        $fullPath = Storage::disk('local')->path($filePath);

        ImportSubscribersJob::dispatch(
            $list,
            $fullPath,
            $validated['column_mapping'],
            $validated['skip_duplicates'] ?? true,
            $validated['update_existing'] ?? false,
            'api_import_csv',
            $request->ip()
        );

        return response()->json([
            'data' => [
                'queued' => true,
                'message' => 'Import queued.',
            ],
        ], 202);
    }
}

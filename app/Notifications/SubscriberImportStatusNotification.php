<?php

namespace App\Notifications;

use App\Models\SubscriberImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SubscriberImportStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected SubscriberImport $import
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $status = (string) ($this->import->status ?? 'unknown');

        $title = match ($status) {
            'completed' => 'Subscriber import completed',
            'failed' => 'Subscriber import failed',
            default => 'Subscriber import updated',
        };

        $message = match ($status) {
            'completed' => 'Import completed: ' . ((int) $this->import->imported_count) . ' imported, ' . ((int) $this->import->updated_count) . ' updated, ' . ((int) $this->import->skipped_count) . ' skipped, ' . ((int) $this->import->error_count) . ' errors.',
            'failed' => 'Import failed: ' . ((string) ($this->import->failure_reason ?? 'Unknown error.')),
            default => 'Import status: ' . $status,
        };

        return [
            'type' => 'subscribers.import',
            'title' => $title,
            'message' => $message,
            'subscriber_import_id' => $this->import->id,
            'list_id' => $this->import->list_id,
            'status' => $status,
            'total_rows' => (int) $this->import->total_rows,
            'processed_count' => (int) $this->import->processed_count,
            'imported_count' => (int) $this->import->imported_count,
            'updated_count' => (int) $this->import->updated_count,
            'skipped_count' => (int) $this->import->skipped_count,
            'error_count' => (int) $this->import->error_count,
            'failure_reason' => $this->import->failure_reason,
        ];
    }
}

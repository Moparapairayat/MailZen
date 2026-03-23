<?php

namespace App\Notifications;

use App\Models\EmailList;
use App\Models\ListSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SubscriberUnsubscribedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected EmailList $list,
        protected ListSubscriber $subscriber
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscriber.unsubscribed',
            'title' => 'Subscriber unsubscribed',
            'message' => "{$this->subscriber->email} unsubscribed from {$this->list->name}.",
            'list_id' => $this->list->id,
            'list_name' => $this->list->name,
            'subscriber_id' => $this->subscriber->id,
            'subscriber_email' => $this->subscriber->email,
        ];
    }
}



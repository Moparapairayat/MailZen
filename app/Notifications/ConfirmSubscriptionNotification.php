<?php

namespace App\Notifications;

use App\Models\EmailList;
use App\Models\ListSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmSubscriptionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public EmailList $emailList,
        public ListSubscriber $subscriber,
        public string $confirmationToken
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = 'Please confirm your subscription';
        
        $confirmationUrl = route('public.subscribe.confirm', [
            'token' => $this->confirmationToken
        ]);

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . ($this->subscriber->first_name ?? 'there') . '!')
            ->line('Thank you for subscribing to ' . ($this->emailList->display_name ?? $this->emailList->name) . '.')
            ->line('Please confirm your subscription by clicking the button below:')
            ->action('Confirm Subscription', $confirmationUrl)
            ->line('If you did not subscribe to this list, you can safely ignore this email.');

        // Add footer
        $footer = $this->buildFooter();
        if ($footer) {
            $message->line($footer);
        }

        return $message;
    }

    /**
     * Build footer with compliance info.
     */
    protected function buildFooter(): ?string
    {
        $footer = $this->emailList->footer_text ?? '';
        
        // Add company address if available
        if ($this->emailList->company_address) {
            $footer .= "\n\n" . ($this->emailList->company_name ?? '') . "\n" . $this->emailList->company_address;
        }

        return $footer ?: null;
    }
}

<?php

namespace App\Notifications;

use App\Models\EmailList;
use App\Models\ListSubscriber;
use App\Services\PersonalizationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeSubscriberNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public EmailList $emailList,
        public ListSubscriber $subscriber
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
        $subject = $this->emailList->welcome_email_subject 
            ?? $this->emailList->default_subject 
            ?? 'Welcome to ' . ($this->emailList->display_name ?? $this->emailList->name);

        $content = $this->emailList->welcome_email_content 
            ?? 'Thank you for subscribing to ' . ($this->emailList->display_name ?? $this->emailList->name) . '!';

        // Replace placeholders
        $content = str_replace('{name}', $this->subscriber->first_name ?? $this->subscriber->email, $content);
        $content = str_replace('{email}', $this->subscriber->email, $content);
        $content = str_replace('{list_name}', $this->emailList->display_name ?? $this->emailList->name, $content);

        $custom = is_array($this->subscriber->custom_fields) ? $this->subscriber->custom_fields : [];
        $content = app(PersonalizationService::class)->replaceCustomFieldTags($content, $custom);
        $subject = app(PersonalizationService::class)->replaceCustomFieldTags($subject, $custom);

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . ($this->subscriber->first_name ?? 'there') . '!')
            ->line($content);

        // Add footer with unsubscribe link
        $footer = $this->buildFooter();
        if ($footer) {
            $message->line($footer);
        }

        return $message;
    }

    /**
     * Build footer with unsubscribe link and compliance info.
     */
    protected function buildFooter(): ?string
    {
        $footer = $this->emailList->footer_text ?? '';
        
        // Add unsubscribe link
        $token = $this->generateUnsubscribeToken();
        $unsubscribeUrl = route('public.unsubscribe', [
            'list' => $this->emailList->id,
            'email' => $this->subscriber->email,
            'token' => $token
        ]);
        
        $footer .= "\n\n" . "Unsubscribe: " . $unsubscribeUrl;
        
        // Add company address if available
        if ($this->emailList->company_address) {
            $footer .= "\n\n" . $this->emailList->company_name . "\n" . $this->emailList->company_address;
        }

        return $footer ?: null;
    }

    /**
     * Generate unsubscribe token.
     */
    protected function generateUnsubscribeToken(): string
    {
        return hash('sha256', $this->subscriber->email . $this->emailList->id . config('app.key'));
    }
}

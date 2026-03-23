<?php

namespace App\Mail;

use App\Models\AutoResponder;
use App\Models\AutoResponderStep;
use App\Models\ListSubscriber;
use App\Services\PersonalizationService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AutoResponderStepMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AutoResponder $autoResponder,
        public AutoResponderStep $step,
        public ListSubscriber $subscriber
    ) {
    }

    public function envelope(): Envelope
    {
        $fromEmail = (string) ($this->step->from_email ?? $this->autoResponder->from_email ?? config('mail.from.address'));
        $fromName = $this->step->from_name ?? $this->autoResponder->from_name ?? null;

        $from = $fromName ? new Address($fromEmail, (string) $fromName) : $fromEmail;

        $replyTo = (string) ($this->step->reply_to ?? $this->autoResponder->reply_to ?? $fromEmail);

        return new Envelope(
            subject: $this->personalizeContent($this->step->subject),
            from: $from,
            replyTo: $replyTo,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.auto-responder',
            text: 'emails.auto-responder-text',
            with: [
                'autoResponder' => $this->autoResponder,
                'step' => $this->step,
                'subscriber' => $this->subscriber,
                'htmlContent' => $this->prepareHtmlContent(),
                'plainTextContent' => $this->preparePlainTextContent(),
                'unsubscribeUrl' => $this->getUnsubscribeUrl(),
            ],
        );
    }

    private function prepareHtmlContent(): string
    {
        $content = $this->personalizeContent($this->step->html_content ?? $this->autoResponder->html_content ?? '');

        $unsubscribeLink = '<p style="font-size: 12px; color: #999; margin-top: 20px;">'
            . '<a href="' . $this->getUnsubscribeUrl() . '" style="color: #999;">Unsubscribe</a>'
            . '</p>';

        $content = str_replace('</body>', $unsubscribeLink . '</body>', $content);
        if (strpos($content, '</body>') === false) {
            $content .= $unsubscribeLink;
        }

        return $content;
    }

    private function preparePlainTextContent(): string
    {
        $content = $this->step->plain_text_content
            ?? $this->autoResponder->plain_text_content
            ?? strip_tags($this->step->html_content ?? $this->autoResponder->html_content ?? '');

        $content = $this->personalizeContent($content);

        $content .= "\n\n---\nUnsubscribe: " . $this->getUnsubscribeUrl();

        return $content;
    }

    private function personalizeContent(string $content): string
    {
        return app(PersonalizationService::class)->personalizeForSubscriber($content, $this->subscriber);
    }

    private function getUnsubscribeUrl(): string
    {
        $token = hash('sha256', $this->subscriber->email . $this->subscriber->list_id . config('app.key'));

        return route('public.unsubscribe', [
            'list' => $this->subscriber->list_id,
            'email' => $this->subscriber->email,
            'token' => $token,
        ]);
    }
}

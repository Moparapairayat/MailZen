<?php

namespace App\Services;

use App\Models\AutoResponder;
use App\Models\AutoResponderStep;
use App\Models\Customer;
use App\Models\Template;
use Illuminate\Pagination\LengthAwarePaginator;

class AutoResponderService
{
    /**
     * Get paginated list of auto responders for a customer.
     */
    public function getPaginated(Customer $customer, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = AutoResponder::where('customer_id', $customer->id)
            ->with(['emailList']);

        // Apply filters
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['trigger'])) {
            $query->where('trigger', $filters['trigger']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new auto responder.
     */
    public function create(Customer $customer, array $data): AutoResponder
    {
        // If template_id is provided, load template content
        $htmlContent = $data['html_content'] ?? null;
        $plainTextContent = $data['plain_text_content'] ?? null;
        $templateId = $data['template_id'] ?? null;

        if ($templateId && !$htmlContent) {
            $template = Template::find($templateId);
            if ($template) {
                $htmlContent = $template->html_content;
                $plainTextContent = $template->plain_text_content;
                // Increment template usage
                $template->incrementUsage();
            }
        }

        $autoResponder = AutoResponder::create([
            'customer_id' => $customer->id,
            'list_id' => $data['list_id'] ?? null,
            'delivery_server_id' => $data['delivery_server_id'] ?? null,
            'template_id' => $templateId,
            'name' => $data['name'],
            'subject' => $data['subject'],
            'from_name' => $data['from_name'] ?? null,
            'from_email' => $data['from_email'] ?? $customer->email,
            'reply_to' => $data['reply_to'] ?? null,
            'trigger' => $data['trigger'] ?? 'subscriber_confirmed',
            'trigger_settings' => $data['trigger_settings'] ?? [],
            'delay_value' => $data['delay_value'] ?? 0,
            'delay_unit' => $data['delay_unit'] ?? 'minutes',
            'status' => $data['status'] ?? 'active',
            'html_content' => $htmlContent,
            'plain_text_content' => $plainTextContent,
            'template_data' => $data['template_data'] ?? [],
            'track_opens' => $data['track_opens'] ?? true,
            'track_clicks' => $data['track_clicks'] ?? true,
        ]);

        AutoResponderStep::updateOrCreate(
            [
                'auto_responder_id' => $autoResponder->id,
                'step_order' => 1,
            ],
            [
                'name' => 'Step 1',
                'template_id' => $autoResponder->template_id,
                'delivery_server_id' => $autoResponder->delivery_server_id,
                'subject' => $autoResponder->subject,
                'from_name' => $autoResponder->from_name,
                'from_email' => $autoResponder->from_email,
                'reply_to' => $autoResponder->reply_to,
                'delay_value' => (int) ($autoResponder->delay_value ?? 0),
                'delay_unit' => (string) ($autoResponder->delay_unit ?? 'hours'),
                'status' => $autoResponder->status === 'active' ? 'active' : 'inactive',
                'html_content' => $autoResponder->html_content,
                'plain_text_content' => $autoResponder->plain_text_content,
                'template_data' => $autoResponder->template_data ?? [],
                'track_opens' => (bool) ($autoResponder->track_opens ?? true),
                'track_clicks' => (bool) ($autoResponder->track_clicks ?? true),
            ]
        );

        return $autoResponder;
    }

    /**
     * Update an existing auto responder.
     */
    public function update(AutoResponder $autoResponder, array $data): AutoResponder
    {
        // If template_id is provided and changed, load template content
        $templateId = $data['template_id'] ?? null;
        if ($templateId && $templateId != $autoResponder->template_id && empty($data['html_content'])) {
            $template = Template::find($templateId);
            if ($template) {
                $data['html_content'] = $template->html_content;
                $data['plain_text_content'] = $template->plain_text_content;
                // Increment template usage
                $template->incrementUsage();
            }
        }

        $autoResponder->update($data);
        $autoResponder = $autoResponder->fresh();

        AutoResponderStep::updateOrCreate(
            [
                'auto_responder_id' => $autoResponder->id,
                'step_order' => 1,
            ],
            [
                'name' => 'Step 1',
                'template_id' => $autoResponder->template_id,
                'delivery_server_id' => $autoResponder->delivery_server_id,
                'subject' => $autoResponder->subject,
                'from_name' => $autoResponder->from_name,
                'from_email' => $autoResponder->from_email,
                'reply_to' => $autoResponder->reply_to,
                'delay_value' => (int) ($autoResponder->delay_value ?? 0),
                'delay_unit' => (string) ($autoResponder->delay_unit ?? 'hours'),
                'status' => $autoResponder->status === 'active' ? 'active' : 'inactive',
                'html_content' => $autoResponder->html_content,
                'plain_text_content' => $autoResponder->plain_text_content,
                'template_data' => $autoResponder->template_data ?? [],
                'track_opens' => (bool) ($autoResponder->track_opens ?? true),
                'track_clicks' => (bool) ($autoResponder->track_clicks ?? true),
            ]
        );

        return $autoResponder;
    }

    /**
     * Delete an auto responder.
     */
    public function delete(AutoResponder $autoResponder): bool
    {
        return $autoResponder->delete();
    }
}


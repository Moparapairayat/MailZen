<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AiGeneration;
use App\Services\TemplateService;
use App\Services\AI\AiTextToolService;
use Illuminate\Http\Request;

class AiToolController extends Controller
{
    public function __construct(
        protected AiTextToolService $aiTextToolService,
        protected TemplateService $templateService
    ) {
    }

    public function index()
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            abort(403);
        }

        $generations = AiGeneration::query()
            ->where('customer_id', $customer->id)
            ->latest()
            ->paginate(15);

        return view('customer.ai-tools.index', [
            'generations' => $generations,
        ]);
    }

    public function emailTextGenerator()
    {
        return view('customer.ai-tools.email-text-generator');
    }

    public function generateEmailText(Request $request)
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            abort(403);
        }

        $tokenLimit = (int) $customer->groupSetting('ai.token_limit', 0);

        $validated = $request->validate([
            'provider' => ['required', 'in:chatgpt,gemini,claude'],
            'model' => ['nullable', 'string', 'max:100'],
            'email_type' => ['required', 'string', 'max:255'],
            'tone' => ['required', 'string', 'max:255'],
            'audience' => ['required', 'string', 'max:255'],
            'audience_custom' => ['nullable', 'string', 'max:255'],
            'length' => ['required', 'string', 'max:255'],
            'word_count' => ['nullable', 'integer', 'min:10', 'max:5000'],
            'objective' => ['required', 'string', 'max:255'],
            'subject_idea' => ['nullable', 'string', 'max:255'],
            'context' => ['nullable', 'string', 'max:5000'],
            'key_points' => ['nullable', 'string', 'max:5000'],
            'cta' => ['nullable', 'string', 'max:500'],
            'offer_details' => ['nullable', 'string', 'max:2000'],
        ]);

        $audience = $validated['audience'];
        if ($audience === 'custom' && is_string($validated['audience_custom'] ?? null) && trim((string) $validated['audience_custom']) !== '') {
            $audience = trim((string) $validated['audience_custom']);
        }

        $length = $validated['length'];
        $wordCount = null;
        if ($length === 'custom' && is_numeric($validated['word_count'] ?? null)) {
            $wordCount = (int) $validated['word_count'];
        }

        $subjectIdea = is_string($validated['subject_idea'] ?? null) ? trim((string) $validated['subject_idea']) : '';
        $context = is_string($validated['context'] ?? null) ? trim((string) $validated['context']) : '';
        $keyPoints = is_string($validated['key_points'] ?? null) ? trim((string) $validated['key_points']) : '';
        $cta = is_string($validated['cta'] ?? null) ? trim((string) $validated['cta']) : '';
        $offerDetails = is_string($validated['offer_details'] ?? null) ? trim((string) $validated['offer_details']) : '';

        $promptParts = [];
        $promptParts[] = 'Write an email.';
        $promptParts[] = 'Output plain text only.';
        $promptParts[] = 'Include a subject line and the email body.';
        $promptParts[] = 'Email type: ' . $validated['email_type'] . '.';
        $promptParts[] = 'Tone: ' . $validated['tone'] . '.';
        $promptParts[] = 'Target audience: ' . $audience . '.';
        $promptParts[] = 'Objective: ' . $validated['objective'] . '.';

        if ($wordCount !== null) {
            $promptParts[] = 'Length: approximately ' . $wordCount . ' words.';
        } else {
            $promptParts[] = 'Length: ' . $length . '.';
        }

        if ($subjectIdea !== '') {
            $promptParts[] = 'Subject idea: ' . $subjectIdea . '.';
        }
        if ($context !== '') {
            $promptParts[] = 'Context: ' . $context;
        }
        if ($keyPoints !== '') {
            $promptParts[] = 'Key points to include: ' . $keyPoints;
        }
        if ($cta !== '') {
            $promptParts[] = 'CTA: ' . $cta;
        }
        if ($offerDetails !== '') {
            $promptParts[] = 'Product/Offer details: ' . $offerDetails;
        }

        $prompt = implode("\n", $promptParts);

        $log = AiGeneration::create([
            'customer_id' => $customer->id,
            'admin_user_id' => null,
            'tool' => 'email_text_generator',
            'provider' => $validated['provider'],
            'model' => is_string($validated['model'] ?? null) && trim((string) $validated['model']) !== '' ? trim((string) $validated['model']) : null,
            'used_admin_keys' => false,
            'prompt' => $prompt,
            'input' => $validated,
            'success' => false,
            'output' => null,
            'tokens_used' => null,
            'error_message' => null,
        ]);

        $result = $this->aiTextToolService->generateEmailText(
            $customer,
            $validated['provider'],
            $prompt,
            is_string($validated['model'] ?? null) && trim((string) $validated['model']) !== '' ? trim((string) $validated['model']) : null
        );

        $log->used_admin_keys = (bool) ($result['used_admin_keys'] ?? false);
        $log->tokens_used = is_numeric($result['tokens'] ?? null) ? (int) $result['tokens'] : null;

        if (($result['success'] ?? false) === true) {
            $log->success = true;
            $log->output = (string) ($result['text'] ?? '');
            $log->error_message = null;
            $log->save();

            $freshCustomer = $customer->fresh();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'text' => (string) ($result['text'] ?? ''),
                    'tokens' => is_numeric($result['tokens'] ?? null) ? (int) $result['tokens'] : null,
                    'used_admin_keys' => (bool) ($result['used_admin_keys'] ?? false),
                    'ai_token_usage' => $freshCustomer ? (int) ($freshCustomer->ai_token_usage ?? 0) : null,
                    'ai_token_limit' => $tokenLimit,
                    'generation_id' => $log->id,
                ]);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('success', 'Email text generated.')
                ->with('generated_text', (string) ($result['text'] ?? ''));
        }

        $log->success = false;
        $log->output = null;
        $log->error_message = is_string($result['message'] ?? null) ? (string) $result['message'] : 'AI generation failed.';
        $log->save();

        if ($request->expectsJson()) {
            $status = is_numeric($result['status'] ?? null) ? (int) $result['status'] : 500;

            $freshCustomer = $customer->fresh();

            return response()->json([
                'success' => false,
                'message' => $log->error_message,
                'ai_token_usage' => $freshCustomer ? (int) ($freshCustomer->ai_token_usage ?? 0) : null,
                'ai_token_limit' => $tokenLimit,
                'generation_id' => $log->id,
            ], $status);
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('error', $log->error_message);
    }

    public function exportEmailTextToTemplate(Request $request)
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            abort(403);
        }

        $validated = $request->validate([
            'text' => ['required', 'string', 'max:50000'],
        ]);

        $rawText = trim((string) $validated['text']);

        $subject = null;
        $body = $rawText;

        if (preg_match('/^subject\s*[:\-]\s*(.+)$/im', $rawText, $m) === 1) {
            $subject = trim((string) ($m[1] ?? ''));
        }

        // If the text starts with a subject line, strip it from the body.
        $lines = preg_split('/\r\n|\r|\n/', $rawText);
        if (is_array($lines) && isset($lines[0]) && is_string($lines[0]) && preg_match('/^subject\s*[:\-]/i', $lines[0]) === 1) {
            array_shift($lines);
            $body = trim(implode("\n", $lines));
        }

        $templateName = $subject && $subject !== '' ? $subject : 'AI Email Template';

        $htmlBody = nl2br(e($body));
        $htmlContent = '<div>' . $htmlBody . '</div>';

        $unlayerDesign = [
            'counters' => [],
            'body' => [
                'id' => 'body_ai_export',
                'rows' => [
                    [
                        'id' => 'row_ai_export',
                        'cells' => [1],
                        'columns' => [
                            [
                                'id' => 'col_ai_export',
                                'contents' => [
                                    [
                                        'id' => 'content_ai_export',
                                        'type' => 'text',
                                        'values' => [
                                            'text' => $htmlContent,
                                            'textAlign' => 'left',
                                            'fontSize' => '14px',
                                            'containerPadding' => '10px',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $template = $this->templateService->create($customer, [
            'name' => $templateName,
            'type' => 'email',
            'html_content' => $htmlContent,
            'plain_text_content' => $rawText,
            'grapesjs_data' => [
                'builder' => 'unlayer',
                'unlayer' => $unlayerDesign,
            ],
            'settings' => [],
            'is_public' => false,
        ]);

        $viewUrl = route('customer.templates.show', $template);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'view_url' => $viewUrl,
                'template_id' => $template->id,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Template exported successfully.');
    }
}

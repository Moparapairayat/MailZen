<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiGeneration;
use App\Models\Setting;
use App\Services\AI\AiUsageService;
use App\Services\AI\GeminiClient;
use App\Services\AI\OpenAIClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiToolController extends Controller
{
    public function __construct(
        protected OpenAIClient $openai,
        protected GeminiClient $gemini,
        protected AiUsageService $aiUsage
    ) {
    }

    public function index()
    {
        return view('admin.ai-tools.index');
    }

    public function dashboard(Request $request)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            abort(403);
        }

        $range = (string) $request->query('range', '7d');
        $now = now();

        $start = null;
        $end = null;

        if ($range === '30d') {
            $start = $now->copy()->subDays(29)->startOfDay();
            $end = $now->copy()->endOfDay();
        } elseif ($range === 'custom') {
            $from = $request->query('from');
            $to = $request->query('to');

            $start = $from ? Carbon::parse($from)->startOfDay() : $now->copy()->subDays(6)->startOfDay();
            $end = $to ? Carbon::parse($to)->endOfDay() : $now->copy()->endOfDay();
        } else {
            $range = '7d';
            $start = $now->copy()->subDays(6)->startOfDay();
            $end = $now->copy()->endOfDay();
        }

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            $range = 'custom';
        }

        $byProviderModel = AiGeneration::query()
            ->where('used_admin_keys', true)
            ->where('success', true)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("provider, COALESCE(model, '') as model, SUM(COALESCE(tokens_used, 0)) as tokens")
            ->groupBy('provider', DB::raw("COALESCE(model, '')"))
            ->get();

        $providers = [
            'chatgpt' => 'ChatGPT',
            'gemini' => 'Gemini',
            'claude' => 'Claude',
        ];

        $providerStats = [];
        $providerDailyStats = [];
        $breakdownRows = [];

        $totalTokensUsed = 0;
        $totalCostCents = 0;
        $totalLimit = 0;
        $hasUnlimited = false;

        $totalTokensUsedToday = 0;
        $totalDailyLimit = 0;
        $hasUnlimitedDaily = false;

        foreach ($providers as $providerKey => $providerLabel) {
            $limit = (int) $this->aiUsage->getAdminMonthlyLimit($providerKey);

            $dailyLimit = (int) $this->aiUsage->getAdminDailyLimit($providerKey);
            $usedToday = (int) $this->aiUsage->getAdminTokensUsedToday($providerKey);
            $dailyPercent = null;
            if ($dailyLimit > 0) {
                $dailyPercent = min(100, ($usedToday / $dailyLimit) * 100);
            }

            $used = (int) $byProviderModel->where('provider', $providerKey)->sum('tokens');

            $estimatedCostCents = 0;
            $rowsForProvider = $byProviderModel->where('provider', $providerKey)->values();
            foreach ($rowsForProvider as $row) {
                $model = is_string($row->model ?? null) ? (string) $row->model : '';
                $tokens = is_numeric($row->tokens ?? null) ? (int) $row->tokens : 0;
                $costPer1m = (int) $this->aiUsage->getAdminCostPer1mCentsForModel($providerKey, $model !== '' ? $model : null);
                $estimatedCostCents += (int) round(($tokens / 1000000) * $costPer1m);
            }

            $costPer1mCents = (int) $this->aiUsage->getAdminCostPer1mCents($providerKey);

            $percent = null;
            if ($limit > 0) {
                $percent = $limit > 0 ? min(100, ($used / $limit) * 100) : null;
            }

            $providerStats[$providerKey] = [
                'label' => $providerLabel,
                'tokens_used' => $used,
                'token_limit' => $limit,
                'percent' => $percent,
                'cost_per_1m_cents' => $costPer1mCents,
                'estimated_cost_cents' => $estimatedCostCents,
            ];

            $providerDailyStats[$providerKey] = [
                'label' => $providerLabel,
                'tokens_used' => $usedToday,
                'token_limit' => $dailyLimit,
                'percent' => $dailyPercent,
            ];

            $totalTokensUsed += $used;
            $totalCostCents += $estimatedCostCents;

            if ($limit > 0) {
                $totalLimit += $limit;
            } else {
                $hasUnlimited = true;
            }

            $totalTokensUsedToday += $usedToday;
            if ($dailyLimit > 0) {
                $totalDailyLimit += $dailyLimit;
            } else {
                $hasUnlimitedDaily = true;
            }

            $rows = $rowsForProvider;
            foreach ($rows as $row) {
                $model = is_string($row->model ?? null) ? (string) $row->model : '';
                $tokens = is_numeric($row->tokens ?? null) ? (int) $row->tokens : 0;

                $modelCostPer1mCents = (int) $this->aiUsage->getAdminCostPer1mCentsForModel($providerKey, $model !== '' ? $model : null);

                $breakdownRows[] = [
                    'provider' => $providerKey,
                    'provider_label' => $providerLabel,
                    'model' => $model !== '' ? $model : 'Default',
                    'tokens' => $tokens,
                    'cost_per_1m_cents' => $modelCostPer1mCents,
                    'estimated_cost_cents' => (int) round(($tokens / 1000000) * $modelCostPer1mCents),
                ];
            }
        }

        usort($breakdownRows, function ($a, $b) {
            $p = strcmp((string) ($a['provider'] ?? ''), (string) ($b['provider'] ?? ''));
            if ($p !== 0) {
                return $p;
            }

            return ((int) ($b['tokens'] ?? 0)) <=> ((int) ($a['tokens'] ?? 0));
        });

        $totalPercent = null;
        if (!$hasUnlimited && $totalLimit > 0) {
            $totalPercent = min(100, ($totalTokensUsed / $totalLimit) * 100);
        }

        $totalDailyPercent = null;
        if (!$hasUnlimitedDaily && $totalDailyLimit > 0) {
            $totalDailyPercent = min(100, ($totalTokensUsedToday / $totalDailyLimit) * 100);
        }

        $rangeLabel = 'Last 7 Days';
        if ($range === '30d') {
            $rangeLabel = 'Last 30 Days';
        } elseif ($range === 'custom') {
            $rangeLabel = 'Custom Range';
        }

        return view('admin.ai-tools.dashboard', [
            'range' => $range,
            'startDate' => $start,
            'endDate' => $end,
            'rangeLabel' => $rangeLabel,
            'providerStats' => $providerStats,
            'providerDailyStats' => $providerDailyStats,
            'breakdownRows' => $breakdownRows,
            'totalTokensUsed' => $totalTokensUsed,
            'totalLimit' => $hasUnlimited ? null : $totalLimit,
            'totalPercent' => $totalPercent,
            'totalCostCents' => $totalCostCents,
            'totalTokensUsedToday' => $totalTokensUsedToday,
            'totalDailyLimit' => $hasUnlimitedDaily ? null : $totalDailyLimit,
            'totalDailyPercent' => $totalDailyPercent,
        ]);
    }

    public function emailTextGenerator()
    {
        return view('admin.ai-tools.email-text-generator');
    }

    public function generateEmailText(Request $request)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            abort(403);
        }

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
            'customer_id' => null,
            'admin_user_id' => $admin->id,
            'tool' => 'email_text_generator',
            'provider' => $validated['provider'],
            'model' => is_string($validated['model'] ?? null) && trim((string) $validated['model']) !== '' ? trim((string) $validated['model']) : null,
            'used_admin_keys' => true,
            'prompt' => $prompt,
            'input' => $validated,
            'success' => false,
            'output' => null,
            'tokens_used' => null,
            'error_message' => null,
        ]);

        if ($validated['provider'] === 'claude') {
            $log->success = false;
            $log->error_message = 'Claude is not supported yet.';
            $log->save();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $log->error_message,
                    'generation_id' => $log->id,
                ], 501);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $log->error_message);
        }

        $estimatedTokens = $this->aiUsage->estimateTokens($prompt, 1200);
        if (!$this->aiUsage->canUseAdminTokens($validated['provider'], $estimatedTokens)) {
            $log->success = false;
            $log->error_message = 'Admin AI monthly token limit reached. Please try again later or increase the limit in settings.';
            $log->save();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $log->error_message,
                    'generation_id' => $log->id,
                ], 429);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $log->error_message);
        }

        $apiKey = null;
        if ($validated['provider'] === 'chatgpt') {
            $apiKey = Setting::get('openai_api_key');
        } else {
            $apiKey = Setting::get('gemini_api_key');
        }

        if (!is_string($apiKey) || trim($apiKey) === '') {
            $log->success = false;
            $log->error_message = 'Admin AI API key is not configured yet.';
            $log->save();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $log->error_message,
                    'generation_id' => $log->id,
                ], 422);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $log->error_message);
        }

        $model = is_string($validated['model'] ?? null) && trim((string) $validated['model']) !== '' ? trim((string) $validated['model']) : null;

        $result = $validated['provider'] === 'chatgpt'
            ? $this->openai->generateText($apiKey, $prompt, 1200, $model)
            : $this->gemini->generateText($apiKey, $prompt, 1200, $model);

        $log->tokens_used = is_numeric($result['tokens'] ?? null) ? (int) $result['tokens'] : null;

        if (is_array($result) && ($result['success'] ?? false) === true) {
            $log->success = true;
            $log->output = (string) ($result['text'] ?? '');
            $log->error_message = null;
            $log->save();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'text' => (string) ($result['text'] ?? ''),
                    'tokens' => is_numeric($result['tokens'] ?? null) ? (int) $result['tokens'] : null,
                    'used_admin_keys' => true,
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
            return response()->json([
                'success' => false,
                'message' => $log->error_message,
                'generation_id' => $log->id,
            ], 502);
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('error', $log->error_message);
    }
}

<?php

namespace App\Services\AI;

use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class AiTextToolService
{
    public function __construct(
        protected OpenAIClient $openai,
        protected GeminiClient $gemini,
        protected AiUsageService $aiUsage
    ) {
    }

    public function generateEmailText(Customer $customer, string $provider, string $prompt, ?string $model = null): array
    {
        $provider = strtolower(trim($provider));
        $prompt = trim($prompt);

        if ($provider === 'claude') {
            return [
                'success' => false,
                'message' => 'Claude is not supported yet.',
                'status' => 501,
                'used_admin_keys' => false,
                'tokens' => null,
                'text' => null,
            ];
        }

        $mustUseOwnKeys = (bool) $customer->groupSetting('ai.must_use_own_keys', false);
        $tokenLimit = (int) $customer->groupSetting('ai.token_limit', 0);

        $apiKey = null;
        $usingAdminKeys = false;

        if ($provider === 'chatgpt') {
            $apiKey = $mustUseOwnKeys ? $customer->openai_api_key : Setting::get('openai_api_key');
            $usingAdminKeys = !$mustUseOwnKeys;
        } elseif ($provider === 'gemini') {
            $apiKey = $mustUseOwnKeys ? $customer->gemini_api_key : Setting::get('gemini_api_key');
            $usingAdminKeys = !$mustUseOwnKeys;
        } else {
            return [
                'success' => false,
                'message' => 'Invalid provider.',
                'status' => 422,
            ];
        }

        if (!is_string($apiKey) || trim($apiKey) === '') {
            return [
                'success' => false,
                'message' => $mustUseOwnKeys
                    ? 'You must configure your API key before using AI Tools.'
                    : 'Admin AI API key is not configured yet.',
                'status' => 422,
            ];
        }

        $inputEstimate = max(1, (int) ceil(strlen($prompt) / 4));
        $maxOutputTokens = 1200;

        if ($usingAdminKeys) {
            $budget = null;

            $dailyLimit = $this->aiUsage->getAdminDailyLimit($provider);
            if ($dailyLimit > 0) {
                $usedToday = $this->aiUsage->getAdminTokensUsedToday($provider);
                $budget = $dailyLimit - $usedToday;
            }

            $monthlyLimit = $this->aiUsage->getAdminMonthlyLimit($provider);
            if ($monthlyLimit > 0) {
                $usedMonth = $this->aiUsage->getAdminTokensUsedThisMonth($provider);
                $monthlyBudget = $monthlyLimit - $usedMonth;
                $budget = $budget === null ? $monthlyBudget : min($budget, $monthlyBudget);
            }

            $current = (int) ($customer->ai_token_usage ?? 0);
            if ($tokenLimit > 0) {
                $customerBudget = $tokenLimit - $current;
                $budget = $budget === null ? $customerBudget : min($budget, $customerBudget);
            }

            if ($budget !== null) {
                $budget = (int) $budget;
                if ($budget <= 0) {
                    return [
                        'success' => false,
                        'message' => $tokenLimit > 0 && ($tokenLimit - $current) <= 0
                            ? 'AI token limit reached for your plan. Please add your own API key or upgrade your plan.'
                            : 'Admin AI monthly token limit reached. Please try again later or increase the limit in settings.',
                        'status' => 429,
                        'used_admin_keys' => true,
                        'tokens' => null,
                        'text' => null,
                    ];
                }

                $availableForOutput = $budget - $inputEstimate;
                $minOutputBudget = 50;
                if ($provider === 'chatgpt' && is_string($model) && stripos($model, 'gpt-5') === 0) {
                    $minOutputBudget = 250;
                }

                if ($availableForOutput < $minOutputBudget) {
                    return [
                        'success' => false,
                        'message' => 'Not enough token budget remaining to generate a useful response. Please shorten the prompt, increase your plan limits, or use gpt-4.1.',
                        'status' => 422,
                        'used_admin_keys' => true,
                        'tokens' => null,
                        'text' => null,
                    ];
                }

                $maxCap = 1200;
                if ($provider === 'chatgpt' && is_string($model) && stripos($model, 'gpt-5') === 0) {
                    $maxCap = 2400;
                }

                $maxOutputTokens = min($maxCap, max(1, $availableForOutput));
            }

            $estimatedTokens = $inputEstimate + max(1, (int) $maxOutputTokens);

            if (!$this->aiUsage->canUseAdminTokens($provider, $estimatedTokens)) {
                return [
                    'success' => false,
                    'message' => 'Admin AI monthly token limit reached. Please try again later or increase the limit in settings.',
                    'status' => 429,
                    'used_admin_keys' => true,
                    'tokens' => null,
                    'text' => null,
                ];
            }
        } else {
            $estimatedTokens = $inputEstimate + max(1, (int) $maxOutputTokens);

            if (!$this->aiUsage->canUseCustomerOwnKeyTokens($customer, $estimatedTokens)) {
                return [
                    'success' => false,
                    'message' => 'Your daily or monthly AI usage limit has been reached. Increase your limits in settings or try again later.',
                    'status' => 429,
                    'used_admin_keys' => false,
                    'tokens' => null,
                    'text' => null,
                ];
            }
        }

        $result = null;
        if ($provider === 'chatgpt') {
            $result = $this->openai->generateText($apiKey, $prompt, $maxOutputTokens, $model);
        } elseif ($provider === 'gemini') {
            $result = $this->gemini->generateText($apiKey, $prompt, $maxOutputTokens, $model);
        }

        if (!is_array($result) || !($result['success'] ?? false)) {
            return [
                'success' => false,
                'message' => is_array($result) && is_string($result['message'] ?? null)
                    ? $result['message']
                    : 'Failed to generate email text.',
                'status' => 502,
                'used_admin_keys' => $usingAdminKeys,
                'tokens' => null,
                'text' => null,
            ];
        }

        $text = is_string($result['text'] ?? null) ? trim($result['text']) : '';
        if ($text === '') {
            return [
                'success' => false,
                'message' => 'AI returned an empty response.',
                'status' => 502,
                'used_admin_keys' => $usingAdminKeys,
                'tokens' => null,
                'text' => null,
            ];
        }

        $tokens = null;
        if (is_numeric($result['tokens'] ?? null)) {
            $tokens = (int) $result['tokens'];
        }
        if ($tokens === null) {
            $tokens = max(1, (int) ceil(strlen($prompt) / 4));
        }

        if ($usingAdminKeys) {
            $ok = DB::transaction(function () use ($customer, $tokenLimit, $tokens) {
                $locked = Customer::query()->whereKey($customer->id)->lockForUpdate()->first();
                if (!$locked) {
                    return false;
                }

                $current = (int) ($locked->ai_token_usage ?? 0);
                $next = $current + $tokens;

                if ($tokenLimit > 0 && $next > $tokenLimit) {
                    return false;
                }

                $locked->ai_token_usage = $next;
                $locked->save();

                return true;
            });

            if (!$ok && $tokenLimit > 0) {
                return [
                    'success' => false,
                    'message' => 'AI token limit reached for your plan. Please add your own API key or upgrade your plan.',
                    'status' => 429,
                    'used_admin_keys' => $usingAdminKeys,
                    'tokens' => $tokens,
                    'text' => null,
                ];
            }
        } else {
            $ok = $this->aiUsage->consumeCustomerOwnKeyTokens($customer, $tokens);
            if (!$ok) {
                return [
                    'success' => false,
                    'message' => 'Your daily or monthly AI usage limit has been reached. Increase your limits in settings or try again later.',
                    'status' => 429,
                    'used_admin_keys' => false,
                    'tokens' => $tokens,
                    'text' => null,
                ];
            }
        }

        return [
            'success' => true,
            'status' => 200,
            'message' => null,
            'used_admin_keys' => $usingAdminKeys,
            'tokens' => $tokens,
            'text' => $text,
        ];
    }
}

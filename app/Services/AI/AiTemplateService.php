<?php

namespace App\Services\AI;

use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class AiTemplateService
{
    public function __construct(
        protected OpenAIClient $openai,
        protected GeminiClient $gemini,
        protected AiUsageService $aiUsage
    ) {}

    public function generate(Customer $customer, string $provider, string $prompt, string $builder = 'grapesjs', ?string $model = null): array
    {
        $provider = strtolower(trim($provider));
        $builder = strtolower(trim($builder));
        $prompt = trim($prompt);
        $model = is_string($model) && trim($model) !== '' ? trim($model) : null;

        if (!in_array($builder, ['grapesjs', 'unlayer'], true)) {
            return [
                'success' => false,
                'message' => 'Invalid builder.',
                'status' => 422,
            ];
        }

        if ($provider === 'claude') {
            return [
                'success' => false,
                'message' => 'Claude is not supported yet.',
                'status' => 501,
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
                    ? 'You must configure your API key before using AI Creator.'
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
                    ];
                }

                $availableForOutput = $budget - $inputEstimate;
                if ($availableForOutput <= 0) {
                    return [
                        'success' => false,
                        'message' => 'Your prompt is too long for the remaining token budget. Please shorten the prompt or increase your plan limits.',
                        'status' => 422,
                    ];
                }

                $minOutputBudget = 50;
                if ($provider === 'chatgpt' && is_string($model) && stripos($model, 'gpt-5') === 0) {
                    $minOutputBudget = 250;
                }

                if ($availableForOutput < $minOutputBudget) {
                    return [
                        'success' => false,
                        'message' => 'Not enough token budget remaining to generate a useful response. Please shorten the prompt, increase your plan limits, or use gpt-4.1.',
                        'status' => 422,
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
                ];
            }
        } else {
            $estimatedTokens = $inputEstimate + max(1, (int) $maxOutputTokens);
            if (!$this->aiUsage->canUseCustomerOwnKeyTokens($customer, $estimatedTokens)) {
                return [
                    'success' => false,
                    'message' => 'Your daily or monthly AI usage limit has been reached. Increase your limits in settings or try again later.',
                    'status' => 429,
                ];
            }
        }

        $result = null;
        $html = '';
        $plainText = '';
        $tokens = null;
        $builderData = null;

        if ($builder === 'unlayer') {
            if ($provider === 'chatgpt') {
                $result = $this->openai->generateTemplateUnlayerDesign($apiKey, $prompt, $maxOutputTokens, $model);
            } elseif ($provider === 'gemini') {
                $result = $this->gemini->generateTemplateUnlayerDesign($apiKey, $prompt, $maxOutputTokens, $model);
            }

            if (!is_array($result) || !($result['success'] ?? false)) {
                return [
                    'success' => false,
                    'message' => is_array($result) && is_string($result['message'] ?? null)
                        ? $result['message']
                        : 'Failed to generate template.',
                    'status' => 502,
                ];
            }

            $builderData = is_array($result['design'] ?? null) ? $result['design'] : null;
            if (!is_array($builderData)) {
                return [
                    'success' => false,
                    'message' => 'AI returned an invalid Unlayer design.',
                    'status' => 502,
                ];
            }

            $designJson = json_encode($builderData);
            if (is_string($designJson) && stripos($designJson, 'data:image/') !== false) {
                return [
                    'success' => false,
                    'message' => 'Generated content contains inline base64 images (data:image/...). Please try again and ask for image URLs instead.',
                    'status' => 422,
                ];
            }

            $tokens = is_numeric($result['tokens'] ?? null) ? (int) $result['tokens'] : null;
            if ($tokens === null) {
                $tokens = max(1, (int) ceil(strlen($prompt) / 4));
            }

            // HTML/plain-text will be produced server-side for previewing.
            $html = '';
            $plainText = '';
        } else {
            if ($provider === 'chatgpt') {
                $result = $this->openai->generateTemplateHtml($apiKey, $prompt, $maxOutputTokens, $model);
            } elseif ($provider === 'gemini') {
                $result = $this->gemini->generateTemplateHtml($apiKey, $prompt, $maxOutputTokens, $model);
            }

            if (!is_array($result) || !($result['success'] ?? false)) {
                return [
                    'success' => false,
                    'message' => is_array($result) && is_string($result['message'] ?? null)
                        ? $result['message']
                        : 'Failed to generate template.',
                    'status' => 502,
                ];
            }

            $html = is_string($result['html'] ?? null) ? trim($result['html']) : '';
            if ($html === '') {
                return [
                    'success' => false,
                    'message' => 'AI returned an empty response.',
                    'status' => 502,
                ];
            }

            if (stripos($html, 'data:image/') !== false) {
                return [
                    'success' => false,
                    'message' => 'Generated content contains inline base64 images (data:image/...). Please try again and ask for image URLs instead.',
                    'status' => 422,
                ];
            }

            $plainText = $this->htmlToPlainText($html);

            $tokens = null;
            if (is_numeric($result['tokens'] ?? null)) {
                $tokens = (int) $result['tokens'];
            }
            if ($tokens === null) {
                $tokens = max(1, (int) ceil(strlen($prompt) / 4));
            }
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
                ];
            }
        } else {
            $ok = $this->aiUsage->consumeCustomerOwnKeyTokens($customer, $tokens);
            if (!$ok) {
                return [
                    'success' => false,
                    'message' => 'Your daily or monthly AI usage limit has been reached. Increase your limits in settings or try again later.',
                    'status' => 429,
                ];
            }
        }

        if ($builder === 'grapesjs') {
            $builderData = [
                'components' => $html,
                'styles' => [],
            ];
        }

        return [
            'success' => true,
            'status' => 200,
            'data' => [
                'name' => 'AI Generated',
                'description' => null,
                'html_content' => $html,
                'plain_text_content' => $plainText,
                'builder' => $builder,
                'builder_data' => $builderData,
                'tokens_used' => $tokens,
                'used_admin_keys' => $usingAdminKeys,
            ],
        ];
    }

    private function htmlToPlainText(string $html): string
    {
        $plain = $html;
        $plain = preg_replace('/<style[\s\S]*?<\/style>/i', ' ', $plain) ?? $plain;
        $plain = preg_replace('/<script[\s\S]*?<\/script>/i', ' ', $plain) ?? $plain;
        $plain = preg_replace('/<[^>]+>/', ' ', $plain) ?? $plain;
        $plain = preg_replace('/\s+/', ' ', $plain) ?? $plain;
        return trim($plain);
    }

    private function buildUnlayerDesignFromHtml(string $html): array
    {
        $html = trim($html);

        return [
            'counters' => [],
            'body' => [
                'id' => 'body_ai_generated',
                'rows' => [
                    [
                        'id' => 'row_ai_generated',
                        'cells' => [1],
                        'columns' => [
                            [
                                'id' => 'col_ai_generated',
                                'contents' => [
                                    [
                                        'id' => 'content_ai_generated',
                                        'type' => 'text',
                                        'values' => [
                                            'text' => $html,
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
    }
}

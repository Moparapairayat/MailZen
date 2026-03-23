<?php

namespace App\Services\AI;

use App\Models\AiGeneration;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class AiUsageService
{
    public function estimateTokens(string $prompt, int $maxOutputTokens = 1200): int
    {
        $prompt = trim($prompt);

        $inputEstimate = max(1, (int) ceil(strlen($prompt) / 4));

        return $inputEstimate + max(1, $maxOutputTokens);
    }

    public function getAdminCostPer1mCentsForModel(string $provider, ?string $model): int
    {
        $provider = strtolower(trim($provider));
        $model = is_string($model) && trim($model) !== '' ? trim($model) : null;

        if ($model === null) {
            return $this->getAdminCostPer1mCents($provider);
        }

        $newPrefix = match ($provider) {
            'chatgpt' => 'openai_cost_per_1m_cents_',
            'gemini' => 'gemini_cost_per_1m_cents_',
            'claude' => 'claude_cost_per_1m_cents_',
            default => null,
        };

        $oldPrefix = match ($provider) {
            'chatgpt' => 'openai_cost_per_1k_cents_',
            'gemini' => 'gemini_cost_per_1k_cents_',
            'claude' => 'claude_cost_per_1k_cents_',
            default => null,
        };

        if (!$newPrefix || !$oldPrefix) {
            return 0;
        }

        $slug = $this->modelKeySlug($model);

        $newKey = $newPrefix . $slug;
        $specific = (int) Setting::get($newKey, 0);
        if ($specific > 0) {
            return $specific;
        }

        $oldKey = $oldPrefix . $slug;
        $oldSpecific = (int) Setting::get($oldKey, 0);
        if ($oldSpecific > 0) {
            return $oldSpecific * 1000;
        }

        return $this->getAdminCostPer1mCents($provider);
    }

    public function getAdminCostPer1mCents(string $provider): int
    {
        $provider = strtolower(trim($provider));

        $newKey = match ($provider) {
            'chatgpt' => 'openai_cost_per_1m_cents',
            'gemini' => 'gemini_cost_per_1m_cents',
            'claude' => 'claude_cost_per_1m_cents',
            default => null,
        };

        if (!$newKey) {
            return 0;
        }

        $val = (int) Setting::get($newKey, 0);
        if ($val > 0) {
            return $val;
        }

        $oldKey = match ($provider) {
            'chatgpt' => 'openai_cost_per_1k_cents',
            'gemini' => 'gemini_cost_per_1k_cents',
            'claude' => 'claude_cost_per_1k_cents',
            default => null,
        };

        if (!$oldKey) {
            return 0;
        }

        $oldVal = (int) Setting::get($oldKey, 0);
        if ($oldVal > 0) {
            return $oldVal * 1000;
        }

        return 0;
    }

    public function getAdminMonthlyLimit(string $provider): int
    {
        $provider = strtolower(trim($provider));

        $key = match ($provider) {
            'chatgpt' => 'openai_token_limit_monthly',
            'gemini' => 'gemini_token_limit_monthly',
            'claude' => 'claude_token_limit_monthly',
            default => null,
        };

        if (!$key) {
            return 0;
        }

        return (int) Setting::get($key, 0);
    }

    public function getAdminDailyLimit(string $provider): int
    {
        $provider = strtolower(trim($provider));

        $key = match ($provider) {
            'chatgpt' => 'openai_token_limit_daily',
            'gemini' => 'gemini_token_limit_daily',
            'claude' => 'claude_token_limit_daily',
            default => null,
        };

        if (!$key) {
            return 0;
        }

        return (int) Setting::get($key, 0);
    }

    public function getAdminCostPer1kCentsForModel(string $provider, ?string $model): int
    {
        $provider = strtolower(trim($provider));
        $model = is_string($model) && trim($model) !== '' ? trim($model) : null;

        if ($model === null) {
            return $this->getAdminCostPer1kCents($provider);
        }

        $prefix = match ($provider) {
            'chatgpt' => 'openai_cost_per_1k_cents_',
            'gemini' => 'gemini_cost_per_1k_cents_',
            'claude' => 'claude_cost_per_1k_cents_',
            default => null,
        };

        if (!$prefix) {
            return 0;
        }

        $key = $prefix . $this->modelKeySlug($model);
        $specific = (int) Setting::get($key, 0);

        if ($specific > 0) {
            return $specific;
        }

        return $this->getAdminCostPer1kCents($provider);
    }

    public function getAdminCostPer1kCents(string $provider): int
    {
        $provider = strtolower(trim($provider));

        $key = match ($provider) {
            'chatgpt' => 'openai_cost_per_1k_cents',
            'gemini' => 'gemini_cost_per_1k_cents',
            'claude' => 'claude_cost_per_1k_cents',
            default => null,
        };

        if (!$key) {
            return 0;
        }

        return (int) Setting::get($key, 0);
    }

    public function getAdminTokensUsedThisMonth(string $provider): int
    {
        $provider = strtolower(trim($provider));

        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        return (int) AiGeneration::query()
            ->where('used_admin_keys', true)
            ->where('success', true)
            ->where('provider', $provider)
            ->whereBetween('created_at', [$start, $end])
            ->sum(DB::raw('COALESCE(tokens_used, 0)'));
    }

    public function getAdminTokensUsedToday(string $provider): int
    {
        $provider = strtolower(trim($provider));

        $start = now()->startOfDay();
        $end = now()->endOfDay();

        return (int) AiGeneration::query()
            ->where('used_admin_keys', true)
            ->where('success', true)
            ->where('provider', $provider)
            ->whereBetween('created_at', [$start, $end])
            ->sum(DB::raw('COALESCE(tokens_used, 0)'));
    }

    public function canUseAdminTokens(string $provider, int $estimatedTokens): bool
    {
        $estimatedTokens = max(1, (int) $estimatedTokens);

        $dailyLimit = $this->getAdminDailyLimit($provider);
        if ($dailyLimit > 0) {
            $usedToday = $this->getAdminTokensUsedToday($provider);
            if (($usedToday + $estimatedTokens) > $dailyLimit) {
                return false;
            }
        }

        $monthlyLimit = $this->getAdminMonthlyLimit($provider);
        if ($monthlyLimit > 0) {
            $usedMonth = $this->getAdminTokensUsedThisMonth($provider);
            if (($usedMonth + $estimatedTokens) > $monthlyLimit) {
                return false;
            }
        }

        return true;
    }

    public function canUseCustomerOwnKeyTokens(Customer $customer, int $estimatedTokens): bool
    {
        $estimatedTokens = max(1, (int) $estimatedTokens);

        return (bool) DB::transaction(function () use ($customer, $estimatedTokens) {
            $locked = Customer::query()->whereKey($customer->id)->lockForUpdate()->first();
            if (!$locked) {
                return false;
            }

            $this->resetOwnKeyCountersIfNeeded($locked);

            $dailyLimit = (int) ($locked->ai_own_daily_limit ?? 0);
            $monthlyLimit = (int) ($locked->ai_own_monthly_limit ?? 0);

            $dailyUsage = (int) ($locked->ai_own_daily_usage ?? 0);
            $monthlyUsage = (int) ($locked->ai_own_monthly_usage ?? 0);

            if ($dailyLimit > 0 && ($dailyUsage + $estimatedTokens) > $dailyLimit) {
                return false;
            }

            if ($monthlyLimit > 0 && ($monthlyUsage + $estimatedTokens) > $monthlyLimit) {
                return false;
            }

            return true;
        });
    }

    public function consumeCustomerOwnKeyTokens(Customer $customer, int $tokens): bool
    {
        $tokens = max(1, (int) $tokens);

        return (bool) DB::transaction(function () use ($customer, $tokens) {
            $locked = Customer::query()->whereKey($customer->id)->lockForUpdate()->first();
            if (!$locked) {
                return false;
            }

            $this->resetOwnKeyCountersIfNeeded($locked);

            $dailyLimit = (int) ($locked->ai_own_daily_limit ?? 0);
            $monthlyLimit = (int) ($locked->ai_own_monthly_limit ?? 0);

            $dailyUsage = (int) ($locked->ai_own_daily_usage ?? 0);
            $monthlyUsage = (int) ($locked->ai_own_monthly_usage ?? 0);

            $nextDaily = $dailyUsage + $tokens;
            $nextMonthly = $monthlyUsage + $tokens;

            if ($dailyLimit > 0 && $nextDaily > $dailyLimit) {
                return false;
            }

            if ($monthlyLimit > 0 && $nextMonthly > $monthlyLimit) {
                return false;
            }

            $locked->ai_own_daily_usage = $nextDaily;
            $locked->ai_own_monthly_usage = $nextMonthly;
            $locked->save();

            return true;
        });
    }

    private function resetOwnKeyCountersIfNeeded(Customer $customer): void
    {
        $today = now()->toDateString();
        $month = now()->format('Y-m');

        $storedDay = $customer->ai_own_daily_usage_date ? $customer->ai_own_daily_usage_date->toDateString() : null;
        if ($storedDay !== $today) {
            $customer->ai_own_daily_usage = 0;
            $customer->ai_own_daily_usage_date = $today;
        }

        $storedMonth = is_string($customer->ai_own_monthly_usage_month ?? null) ? (string) $customer->ai_own_monthly_usage_month : null;
        if ($storedMonth !== $month) {
            $customer->ai_own_monthly_usage = 0;
            $customer->ai_own_monthly_usage_month = $month;
        }

        if ($customer->isDirty()) {
            $customer->save();
        }
    }

    private function modelKeySlug(string $model): string
    {
        $model = strtolower(trim($model));
        $model = preg_replace('/[^a-z0-9]+/', '_', $model) ?? $model;
        $model = trim($model, '_');
        return $model;
    }
}

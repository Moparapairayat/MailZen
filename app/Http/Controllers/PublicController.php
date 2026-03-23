<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Plan;
use App\Models\Setting;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    /**
     * Show the home/landing page.
     */
    public function home(Request $request)
    {
        try {
            $redirectEnabled = (bool) Setting::get('home_redirect_enabled', 0);
        } catch (\Throwable $e) {
            $redirectEnabled = false;
        }

        if ($redirectEnabled) {
            try {
                $target = Setting::get('home_redirect_url', '');
            } catch (\Throwable $e) {
                $target = '';
            }

            $target = is_string($target) ? trim($target) : '';

            if ($target !== '' && $target !== '/') {
                $parsed = parse_url($target);
                $scheme = is_array($parsed) && is_string($parsed['scheme'] ?? null)
                    ? strtolower((string) $parsed['scheme'])
                    : null;

                if ($scheme === null && str_starts_with($target, '/')) {
                    $current = rtrim((string) $request->url(), '/');
                    $dest = rtrim((string) url($target), '/');
                    if ($dest !== '' && $dest !== $current) {
                        return redirect()->to($target);
                    }
                }

                if (in_array($scheme, ['http', 'https'], true)) {
                    $current = rtrim((string) $request->url(), '/');
                    $dest = rtrim($target, '/');
                    if ($dest !== '' && $dest !== $current) {
                        return redirect()->away($target);
                    }
                }
            }
        }

        try {
            $homeVersion = Setting::get('home_page_variant', '1');
        } catch (\Throwable $e) {
            $homeVersion = '1';
        }

        $homeVersion = is_string($homeVersion) ? trim($homeVersion) : '1';
        if ($homeVersion === 'all') {
            $homeVersion = '1';
        }

        if (!in_array($homeVersion, ['1', '2', '3', '4', '5'], true)) {
            $homeVersion = '1';
        }

        return $this->renderHomepageVariant($homeVersion);
    }

    public function homeVariant(string $variant)
    {
        $variant = trim($variant);
        if (!in_array($variant, ['1', '2', '3', '4', '5'], true)) {
            abort(404);
        }

        try {
            $selected = Setting::get('home_page_variant', '1');
        } catch (\Throwable $e) {
            $selected = '1';
        }

        $selected = is_string($selected) ? trim($selected) : '1';
        if (!in_array($selected, ['all', '1', '2', '3', '4', '5'], true)) {
            $selected = '1';
        }

        if ($selected !== 'all' && $selected !== $variant) {
            abort(404);
        }

        return $this->renderHomepageVariant($variant);
    }

    private function renderHomepageVariant(string $variant): Response
    {
        $view = match ($variant) {
            '2' => 'public.home-2',
            '3' => 'public.home-3',
            '4' => 'public.home-v2',
            '5' => 'public.home-5',
            default => 'public.home',
        };

        $html = view($view)->render();

        return response($html);
    }

    /**
     * Show the features page.
     */
    public function features()
    {
        return view('public.features');
    }

    /**
     * Show the pricing page.
     */
    public function pricing()
    {
        $defaultFaq = [
            [
                'q' => 'Can I change plans later?',
                'a' => 'Yes, you can upgrade or downgrade your plan at any time. Changes will be prorated and reflected in your next billing cycle.',
            ],
            [
                'q' => 'What payment methods do you accept?',
                'a' => 'We accept all major credit cards, PayPal, and bank transfers for annual plans.',
            ],
            [
                'q' => 'Is there a free trial?',
                'a' => 'Yes, all plans include a 14-day free trial. No credit card required to start.',
            ],
            [
                'q' => 'What happens if I exceed my plan limits?',
                'a' => "We'll notify you when you're approaching your limits. You can upgrade your plan or purchase additional credits as needed.",
            ],
        ];

        $plans = Plan::query()
            ->with('customerGroup')
            ->where('is_active', true)
            ->orderBy('price')
            ->get();

        try {
            $faq = \App\Models\Setting::get('pricing_faq', $defaultFaq);
        } catch (\Throwable $e) {
            $faq = $defaultFaq;
        }
        $faq = is_array($faq) ? $faq : $defaultFaq;

        return view('public.pricing', compact('plans', 'faq'));
    }

    public function pricingCheckout(Request $request, Plan $plan): View|RedirectResponse
    {
        if (auth('customer')->check()) {
            return view('public.pricing-checkout', compact('plan'));
        }

        $request->session()->put('url.intended', route('pricing.checkout', ['plan' => $plan->id]));

        return redirect()->route('register');
    }
    public function docs()
    {
        return view('public.docs');
    }

    public function apiDocs()
    {
        return view('public.api-docs');
    }

    public function roadmap()
    {
        return view('public.roadmap');
    }
}


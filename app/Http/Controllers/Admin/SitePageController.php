<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SitePageController extends Controller
{
    public function index(): View
    {
        $pages = [
            [
                'key' => 'homepages',
                'title' => __('Homepages'),
                'description' => __('Edit homepage variants (Home 1–4) in structured sections.'),
                'edit_url' => route('admin.homepages.index'),
                'preview_url' => route('home'),
            ],
            [
                'key' => 'features',
                'title' => __('Features'),
                'description' => __('Edit the public Features page strings in structured sections.'),
                'edit_url' => route('admin.site-pages.features.edit'),
                'preview_url' => route('features'),
            ],
            [
                'key' => 'pricing',
                'title' => __('Pricing'),
                'description' => __('Edit the public Pricing page strings, plans, and FAQ.'),
                'edit_url' => route('admin.site-pages.pricing.edit'),
                'preview_url' => route('pricing'),
            ],
            [
                'key' => 'login',
                'title' => __('Login'),
                'description' => __('Edit the public Login page strings and marketing panel.'),
                'edit_url' => route('admin.site-pages.login.edit'),
                'preview_url' => route('login'),
            ],
            [
                'key' => 'register',
                'title' => __('Register'),
                'description' => __('Edit the public Register page strings and marketing panel.'),
                'edit_url' => route('admin.site-pages.register.edit'),
                'preview_url' => route('register'),
            ],
        ];

        return view('admin.site-pages.index', compact('pages'));
    }

    public function editFeatures(): View
    {
        $form = $this->getFeaturesDefaults();
        foreach (array_keys($form) as $key) {
            $val = Setting::get('features_' . $key, $form[$key]);
            if (is_string($val)) {
                $form[$key] = $val;
            }
        }

        return view('admin.site-pages.features', compact('form'));
    }

    public function updateFeatures(Request $request): RedirectResponse
    {
        $defaults = $this->getFeaturesDefaults();

        $rules = [];
        foreach (array_keys($defaults) as $key) {
            $rules[$key] = ['nullable', 'string'];
        }

        $data = $request->validate($rules);

        foreach (array_keys($defaults) as $key) {
            $this->upsertSetting('features_' . $key, (string) ($data[$key] ?? ''));
        }

        return redirect()
            ->route('admin.site-pages.features.edit')
            ->with('success', __('Features page updated.'));
    }

    public function editPricing(): View
    {
        $form = $this->getPricingDefaults();

        foreach (array_keys($form) as $key) {
            $val = Setting::get('pricing_' . $key, $form[$key]);
            if (is_string($val)) {
                $form[$key] = $val;
            }
        }

        try {
            $featuredPlanId = (int) Setting::get('pricing_featured_plan_id', 0);
        } catch (\Throwable $e) {
            $featuredPlanId = 0;
        }

        $availablePlans = Plan::query()
            ->where('is_active', true)
            ->orderBy('price')
            ->get(['id', 'name', 'price', 'currency', 'billing_cycle']);

        $faq = Setting::get('pricing_faq', $this->getPricingDefaultFaq());
        $faq = is_array($faq) ? $faq : $this->getPricingDefaultFaq();

        return view('admin.site-pages.pricing', compact('form', 'faq', 'availablePlans', 'featuredPlanId'));
    }

    public function updatePricing(Request $request): RedirectResponse
    {
        $defaults = $this->getPricingDefaults();

        $rules = [];
        foreach (array_keys($defaults) as $key) {
            $rules[$key] = ['nullable', 'string'];
        }

        $rules['featured_plan_id'] = ['nullable', 'integer', 'min:0'];

        $rules['faq'] = ['nullable', 'array'];
        $rules['faq.*.q'] = ['nullable', 'string'];
        $rules['faq.*.a'] = ['nullable', 'string'];

        $data = $request->validate($rules);

        foreach (array_keys($defaults) as $key) {
            $this->upsertSetting('pricing_' . $key, (string) ($data[$key] ?? ''));
        }

        $featuredPlanId = (int) ($data['featured_plan_id'] ?? 0);
        $this->upsertSetting('pricing_featured_plan_id', (string) $featuredPlanId);

        $faqIn = $data['faq'] ?? [];
        $faqOut = [];
        foreach ($faqIn as $row) {
            if (!is_array($row)) {
                continue;
            }

            $faqOut[] = [
                'q' => (string) ($row['q'] ?? ''),
                'a' => (string) ($row['a'] ?? ''),
            ];
        }

        if (count($faqOut) === 0) {
            $faqOut = $this->getPricingDefaultFaq();
        }

        $this->upsertJsonSetting('pricing_faq', $faqOut);

        return redirect()
            ->route('admin.site-pages.pricing.edit')
            ->with('success', __('Pricing page updated.'));
    }

    public function editLogin(): View
    {
        $form = $this->getLoginDefaults();
        foreach (array_keys($form) as $key) {
            $val = Setting::get('login_' . $key, $form[$key]);
            if (is_string($val)) {
                $form[$key] = $val;
            }
        }

        return view('admin.site-pages.login', compact('form'));
    }

    public function updateLogin(Request $request): RedirectResponse
    {
        $defaults = $this->getLoginDefaults();

        $rules = [];
        foreach (array_keys($defaults) as $key) {
            $rules[$key] = ['nullable', 'string'];
        }

        $data = $request->validate($rules);

        foreach (array_keys($defaults) as $key) {
            $this->upsertSetting('login_' . $key, (string) ($data[$key] ?? ''));
        }

        return redirect()
            ->route('admin.site-pages.login.edit')
            ->with('success', __('Login page updated.'));
    }

    public function editRegister(): View
    {
        $form = $this->getRegisterDefaults();
        foreach (array_keys($form) as $key) {
            $val = Setting::get('register_' . $key, $form[$key]);
            if (is_string($val)) {
                $form[$key] = $val;
            }
        }

        return view('admin.site-pages.register', compact('form'));
    }

    public function updateRegister(Request $request): RedirectResponse
    {
        $defaults = $this->getRegisterDefaults();

        $rules = [];
        foreach (array_keys($defaults) as $key) {
            $rules[$key] = ['nullable', 'string'];
        }

        $data = $request->validate($rules);

        foreach (array_keys($defaults) as $key) {
            $this->upsertSetting('register_' . $key, (string) ($data[$key] ?? ''));
        }

        return redirect()
            ->route('admin.site-pages.register.edit')
            ->with('success', __('Register page updated.'));
    }

    private function upsertSetting(string $key, string $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            [
                'category' => 'site_pages',
                'value' => $value,
                'type' => 'string',
                'description' => null,
                'is_public' => true,
            ]
        );
    }

    private function upsertJsonSetting(string $key, array $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            [
                'category' => 'site_pages',
                'value' => $value,
                'type' => 'json',
                'description' => null,
                'is_public' => true,
            ]
        );
    }

    private function getFeaturesDefaults(): array
    {
        return [
            'hero_title' => 'Powerful Features for Email Marketing',
            'hero_subtitle' => 'Everything you need to create, send, and track successful email campaigns.',

            'section_1_title' => 'Email List Management',
            'section_1_description' => 'Organize and manage your subscribers with powerful list management tools. Keep your lists clean, segmented, and engaged.',
            'section_1_dt_1' => 'Subscriber Management',
            'section_1_dd_1' => 'Import, export, and manage subscribers with ease. Support for custom fields, tags, and segmentation.',
            'section_1_dt_2' => 'Double Opt-in',
            'section_1_dd_2' => 'Ensure list quality with double opt-in confirmation. Automatically verify email addresses and reduce bounces.',
            'section_1_dt_3' => 'List Segmentation',
            'section_1_dd_3' => 'Segment your audience based on behavior, preferences, or custom fields for targeted campaigns.',
            'section_1_bullet_1' => 'Unlimited lists',
            'section_1_bullet_2' => 'Custom fields & tags',
            'section_1_bullet_3' => 'Import/Export CSV',

            'section_2_title' => 'Email Campaigns',
            'section_2_description' => 'Create beautiful, responsive email campaigns that engage your audience and drive results.',
            'section_2_dt_1' => 'Drag & Drop Editor',
            'section_2_dd_1' => 'Build professional emails with our intuitive drag-and-drop editor. No coding required.',
            'section_2_dt_2' => 'Responsive Templates',
            'section_2_dd_2' => 'Choose from beautiful, mobile-responsive templates or create your own custom designs.',
            'section_2_dt_3' => 'Scheduling & Automation',
            'section_2_dd_3' => 'Schedule campaigns for the perfect time or set up automated sequences based on triggers.',
            'section_2_bullet_1' => 'Unlimited campaigns',
            'section_2_bullet_2' => 'A/B testing',
            'section_2_bullet_3' => 'Real-time tracking',

            'section_3_title' => 'Auto Responders',
            'section_3_description' => 'Automate your email marketing with triggered campaigns that engage subscribers at the right time.',
            'section_3_dt_1' => 'Welcome Series',
            'section_3_dd_1' => 'Automatically send welcome emails to new subscribers with customizable sequences.',
            'section_3_dt_2' => 'Triggered Campaigns',
            'section_3_dd_2' => 'Set up campaigns that trigger based on subscriber actions, dates, or field changes.',
            'section_3_dt_3' => 'Drip Campaigns',
            'section_3_dd_3' => 'Create multi-email sequences that nurture leads and guide them through your funnel.',
            'section_3_bullet_1' => 'Multiple triggers',
            'section_3_bullet_2' => 'Delay scheduling',
            'section_3_bullet_3' => 'Unlimited sequences',

            'section_4_title' => 'Analytics & Reporting',
            'section_4_description' => 'Track your campaign performance with detailed analytics and insights.',
            'section_4_dt_1' => 'Real-time Tracking',
            'section_4_dd_1' => 'Monitor opens, clicks, bounces, and unsubscribes in real-time as your campaigns send.',
            'section_4_dt_2' => 'Detailed Reports',
            'section_4_dd_2' => 'Get comprehensive reports on campaign performance, subscriber engagement, and ROI.',
            'section_4_dt_3' => 'Export Data',
            'section_4_dd_3' => 'Export your analytics data to CSV or PDF for further analysis and reporting.',
            'section_4_bullet_1' => 'Open & click rates',
            'section_4_bullet_2' => 'Bounce tracking',
            'section_4_bullet_3' => 'Subscriber insights',
        ];
    }

    private function getPricingDefaults(): array
    {
        return [
            'hero_title' => 'Simple, transparent pricing',
            'hero_subtitle' => "Choose the plan that's right for your business. All plans include a 14-day free trial.",
            'popular_badge' => 'Most Popular',
            'cta_auth' => 'Get Started',
            'cta_guest' => 'Start Free Trial',
            'faq_title' => 'Frequently asked questions',
        ];
    }

    private function getLoginDefaults(): array
    {
        return [
            'welcome_title' => 'Welcome Back!',
            'welcome_subtitle' => 'Sign in to access your dashboard and continue managing your email campaigns.',
            'password_forgot' => 'Forgot Password?',
            'button_sign_in' => 'Sign In',
            'or_label' => 'OR',
            'google_button' => 'Continue with Google',
            'no_account' => "Don't have an Account?",
            'sign_up' => 'Sign Up',

            'promo_title' => 'Revolutionize Email Marketing with Smarter Automation',
            'testimonial_quote' => '"MailZen has completely transformed our email marketing process. It\'s reliable, efficient, and ensures our campaigns are always top-notch."',
            'testimonial_name' => 'Michael Carter',
            'testimonial_role' => 'Marketing Director at TechCorp',
            'partners_title' => 'JOIN 1K+ TEAMS',
            'partner_1' => 'Discord',
            'partner_2' => 'Mailchimp',
            'partner_3' => 'Grammarly',
            'partner_4' => 'Attentive',
            'partner_5' => 'Hellosign',
            'partner_6' => 'Intercom',
            'partner_7' => 'Square',
            'partner_8' => 'Dropbox',
        ];
    }

    private function getRegisterDefaults(): array
    {
        return [
            'welcome_title' => 'Create Account',
            'welcome_subtitle' => 'Create your account to access your dashboard.',
            'button_register' => 'Register',
            'or_label' => 'OR',
            'google_button' => 'Register with Google',
            'have_account' => 'Already have an Account?',
            'sign_in' => 'Sign In',

            'promo_title' => 'Revolutionize Email Marketing with Smarter Automation',
            'testimonial_quote' => '"MailZen has completely transformed our email marketing process. It\'s reliable, efficient, and ensures our campaigns are always top-notch."',
            'testimonial_name' => 'Michael Carter',
            'testimonial_role' => 'Marketing Director at TechCorp',
            'partners_title' => 'JOIN 1K+ TEAMS',
            'partner_1' => 'Discord',
            'partner_2' => 'Mailchimp',
            'partner_3' => 'Grammarly',
            'partner_4' => 'Attentive',
            'partner_5' => 'Hellosign',
            'partner_6' => 'Intercom',
            'partner_7' => 'Square',
            'partner_8' => 'Dropbox',
        ];
    }

    private function getPricingDefaultPlans(): array
    {
        return [
            [
                'name' => 'Starter',
                'price' => 29,
                'billing_cycle' => 'month',
                'description' => 'Perfect for small businesses getting started',
                'features' => [
                    'Up to 1,000 subscribers',
                    '10,000 emails per month',
                    'Email campaigns',
                    'Basic analytics',
                    'Email support',
                ],
                'popular' => false,
            ],
            [
                'name' => 'Professional',
                'price' => 79,
                'billing_cycle' => 'month',
                'description' => 'For growing businesses with advanced needs',
                'features' => [
                    'Up to 10,000 subscribers',
                    '100,000 emails per month',
                    'Unlimited campaigns',
                    'Advanced analytics',
                    'Auto responders',
                    'A/B testing',
                    'Priority support',
                ],
                'popular' => true,
            ],
            [
                'name' => 'Enterprise',
                'price' => 199,
                'billing_cycle' => 'month',
                'description' => 'For large organizations with custom requirements',
                'features' => [
                    'Unlimited subscribers',
                    'Unlimited emails',
                    'All features',
                    'Custom integrations',
                    'Dedicated account manager',
                    'SLA guarantee',
                    '24/7 phone support',
                ],
                'popular' => false,
            ],
        ];
    }

    private function getPricingDefaultFaq(): array
    {
        return [
            [
                'q' => 'Can I change plans later?',
                'a' => "Yes, you can upgrade or downgrade your plan at any time. Changes will be prorated and reflected in your next billing cycle.",
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
    }
}

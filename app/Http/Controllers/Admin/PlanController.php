<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use App\Models\Plan;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::latest()->paginate(15);

        $pricingSettings = [
            'badge' => (string) Setting::get('pricing_section_badge', (string) Setting::get('home_pricing_badge', 'Our Pricing')),
            'title' => (string) Setting::get('pricing_section_title', (string) Setting::get('pricing_hero_title', 'Simple, transparent pricing')),
            'subtitle' => (string) Setting::get('pricing_section_subtitle', (string) Setting::get('pricing_hero_subtitle', "Choose the plan that's right for your business. All plans include a 14-day free trial.")),
            'toggle_monthly' => (string) Setting::get('pricing_section_toggle_monthly', (string) Setting::get('home_pricing_toggle_monthly', 'Pay Monthly')),
            'toggle_annual' => (string) Setting::get('pricing_section_toggle_annual', (string) Setting::get('home_pricing_toggle_annual', 'Pay Annually')),
            'toggle_save' => (string) Setting::get('pricing_section_toggle_save', (string) Setting::get('home_pricing_toggle_save', '(save 20%)')),
            'show_all' => (bool) Setting::get('pricing_section_show_all', false),
            'columns' => (int) Setting::get('pricing_section_columns', 3),
            'popular_badge' => (string) Setting::get('pricing_section_popular_badge', (string) Setting::get('home_pricing_popular_badge', 'Popular')),
        ];

        return view('admin.plans.index', compact('plans', 'pricingSettings'));
    }

    public function create()
    {
        $groups = CustomerGroup::all();
        return view('admin.plans.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $plan = Plan::create($data);

        if (($data['is_popular'] ?? false) === true) {
            Plan::query()
                ->where('id', '!=', $plan->id)
                ->where('billing_cycle', $plan->billing_cycle)
                ->update(['is_popular' => false]);
        }

        return redirect()->route('admin.plans.index')->with('success', __('Plan created.'));
    }

    public function edit(Plan $plan)
    {
        $groups = CustomerGroup::all();
        return view('admin.plans.edit', compact('plan', 'groups'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $this->validateData($request, $plan->id);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $plan->update($data);

        if (($data['is_popular'] ?? false) === true) {
            Plan::query()
                ->where('id', '!=', $plan->id)
                ->where('billing_cycle', $plan->billing_cycle)
                ->update(['is_popular' => false]);
        }

        return redirect()->route('admin.plans.index')->with('success', __('Plan updated.'));
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return redirect()->route('admin.plans.index')->with('success', __('Plan deleted.'));
    }

    public function updatePricingSettings(Request $request)
    {
        $data = $request->validate([
            'badge' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'toggle_monthly' => ['nullable', 'string', 'max:255'],
            'toggle_annual' => ['nullable', 'string', 'max:255'],
            'toggle_save' => ['nullable', 'string', 'max:255'],
            'show_all' => ['nullable', 'boolean'],
            'columns' => ['nullable', 'integer', 'min:1', 'max:5'],
            'popular_badge' => ['nullable', 'string', 'max:255'],
        ]);

        Setting::set('pricing_section_badge', (string) ($data['badge'] ?? ''), 'pricing');
        Setting::set('pricing_section_title', (string) ($data['title'] ?? ''), 'pricing');
        Setting::set('pricing_section_subtitle', (string) ($data['subtitle'] ?? ''), 'pricing');
        Setting::set('pricing_section_toggle_monthly', (string) ($data['toggle_monthly'] ?? ''), 'pricing');
        Setting::set('pricing_section_toggle_annual', (string) ($data['toggle_annual'] ?? ''), 'pricing');
        Setting::set('pricing_section_toggle_save', (string) ($data['toggle_save'] ?? ''), 'pricing');
        Setting::set('pricing_section_show_all', (bool) ($data['show_all'] ?? false), 'pricing');
        Setting::set('pricing_section_columns', (int) ($data['columns'] ?? 3), 'pricing');
        Setting::set('pricing_section_popular_badge', (string) ($data['popular_badge'] ?? ''), 'pricing');

        return redirect()
            ->route('admin.plans.index')
            ->with('success', __('Pricing section updated.'));
    }

    private function validateData(Request $request, ?int $planId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('plans', 'slug')->ignore($planId)],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'billing_cycle' => ['required', Rule::in(['monthly', 'yearly'])],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'customer_group_id' => ['required', 'exists:customer_groups,id'],
            'stripe_price_id' => ['nullable', 'string', 'max:255'],
            'stripe_product_id' => ['nullable', 'string', 'max:255'],
            'cta_text' => ['nullable', 'string', 'max:255'],
            'features_text' => ['nullable', 'string'],
            'features_pros' => ['nullable', 'array'],
            'features_pros.*' => ['nullable', 'string'],
            'features_cons' => ['nullable', 'array'],
            'features_cons.*' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'is_popular' => ['boolean'],
        ]);

        $data['trial_days'] = $data['trial_days'] ?? 0;

        $featuresPros = $data['features_pros'] ?? null;
        $featuresCons = $data['features_cons'] ?? null;
        $featuresText = (string) ($data['features_text'] ?? '');

        unset($data['features_pros'], $data['features_cons'], $data['features_text']);

        $pros = [];
        if (is_array($featuresPros)) {
            foreach ($featuresPros as $f) {
                $f = is_string($f) ? trim($f) : '';
                if ($f !== '') {
                    $pros[] = $f;
                }
            }
        }

        $cons = [];
        if (is_array($featuresCons)) {
            foreach ($featuresCons as $f) {
                $f = is_string($f) ? trim($f) : '';
                if ($f !== '') {
                    $cons[] = $f;
                }
            }
        }

        // Backward compatible fallback: if no pros/cons provided, accept newline textarea as pros.
        if (count($pros) === 0 && count($cons) === 0 && trim($featuresText) !== '') {
            $lines = preg_split("/\r\n|\r|\n/", $featuresText) ?: [];
            foreach ($lines as $line) {
                $line = is_string($line) ? trim($line) : '';
                if ($line !== '') {
                    $pros[] = $line;
                }
            }
        }

        $data['features'] = (count($pros) > 0 || count($cons) > 0)
            ? ['pros' => array_values($pros), 'cons' => array_values($cons)]
            : null;

        return $data;
    }
}


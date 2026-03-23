<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HomepageTextController extends Controller
{
    private const LOGO_SLOTS = 6;
    private const FAQ_SLOTS = 6;
    private const HOME4_STATS = 4;

    public function index(): View
    {
        $variants = [
            ['key' => '1', 'label' => 'Home 1'],
            ['key' => '2', 'label' => 'Home 2'],
            ['key' => '3', 'label' => 'Home 3'],
            ['key' => '4', 'label' => 'Home 4'],
        ];

        return view('admin.homepages.index', compact('variants'));
    }

    public function edit(string $variant): View
    {
        $variant = trim($variant);
        abort_unless(in_array($variant, ['1', '2', '3', '4'], true), 404);

        $variantLabel = 'Home ' . $variant;
        $defaults = $this->getHeroDefaults($variant);
        $home1Defaults = $this->getHome1SectionDefaults();
        $home4Defaults = $variant === '4' ? $this->getHome4SectionDefaults() : [];
        $faqDefaults = $this->getFaqDefaults();
        $pricingDefaults = $this->getPricingDefaults();
        $ctaDefaults = $this->getCtaDefaults();

        $form = [
            'hero_description' => $this->stringSetting('home_' . $variant . '_hero_description', $defaults['hero_description']),
            'hero_scroll_text' => $this->stringSetting('home_' . $variant . '_hero_scroll_text', $defaults['hero_scroll_text']),
            'hero_button_text' => $this->stringSetting('home_' . $variant . '_hero_button_text', $defaults['hero_button_text']),
            'hero_button_type' => $this->stringSetting('home_' . $variant . '_hero_button_type', $defaults['hero_button_type']),
            'hero_button_url' => $this->stringSetting('home_' . $variant . '_hero_button_url', $defaults['hero_button_url']),
            'hero_image' => $this->stringSetting('home_' . $variant . '_hero_image', ''),

            'hero_badge' => $variant === '4'
                ? $this->stringSetting('home_4_hero_badge', (string) ($home4Defaults['hero_badge'] ?? ''))
                : '',
            'hero_title_prefix' => $variant === '4'
                ? $this->stringSetting('home_4_hero_title_prefix', (string) ($home4Defaults['hero_title_prefix'] ?? ''))
                : '',
            'hero_title_highlight' => $variant === '4'
                ? $this->stringSetting('home_4_hero_title_highlight', (string) ($home4Defaults['hero_title_highlight'] ?? ''))
                : '',
            'hero_secondary_button_text' => $variant === '4'
                ? $this->stringSetting('home_4_hero_secondary_button_text', (string) ($home4Defaults['hero_secondary_button_text'] ?? ''))
                : '',
            'hero_secondary_button_url' => $variant === '4'
                ? $this->stringSetting('home_4_hero_secondary_button_url', (string) ($home4Defaults['hero_secondary_button_url'] ?? ''))
                : '',

            'logos_title' => $variant === '4'
                ? $this->stringSetting('home_4_logos_title', (string) ($home4Defaults['logos_title'] ?? ''))
                : '',

            'benefits_title' => $variant === '4'
                ? $this->stringSetting('home_4_benefits_title', (string) ($home4Defaults['benefits_title'] ?? ''))
                : '',
            'benefits_subtitle' => $variant === '4'
                ? $this->stringSetting('home_4_benefits_subtitle', (string) ($home4Defaults['benefits_subtitle'] ?? ''))
                : '',
            'benefits_1_title' => $variant === '4'
                ? $this->stringSetting('home_4_benefits_1_title', (string) ($home4Defaults['benefits_1_title'] ?? ''))
                : '',
            'benefits_1_description' => $variant === '4'
                ? $this->stringSetting('home_4_benefits_1_description', (string) ($home4Defaults['benefits_1_description'] ?? ''))
                : '',
            'benefits_2_title' => $variant === '4'
                ? $this->stringSetting('home_4_benefits_2_title', (string) ($home4Defaults['benefits_2_title'] ?? ''))
                : '',
            'benefits_2_description' => $variant === '4'
                ? $this->stringSetting('home_4_benefits_2_description', (string) ($home4Defaults['benefits_2_description'] ?? ''))
                : '',
            'benefits_3_title' => $variant === '4'
                ? $this->stringSetting('home_4_benefits_3_title', (string) ($home4Defaults['benefits_3_title'] ?? ''))
                : '',
            'benefits_3_description' => $variant === '4'
                ? $this->stringSetting('home_4_benefits_3_description', (string) ($home4Defaults['benefits_3_description'] ?? ''))
                : '',

            'features_title' => $this->stringSetting(
                'home_' . $variant . '_features_title',
                $variant === '1'
                    ? $home1Defaults['features_title']
                    : ($variant === '4' ? (string) ($home4Defaults['features_title'] ?? '') : '')
            ),
            'features_subtitle' => $this->stringSetting(
                'home_' . $variant . '_features_subtitle',
                $variant === '1'
                    ? $home1Defaults['features_subtitle']
                    : ($variant === '4' ? (string) ($home4Defaults['features_subtitle'] ?? '') : '')
            ),
            'features_1_title' => $this->stringSetting(
                'home_' . $variant . '_features_1_title',
                $variant === '1'
                    ? $home1Defaults['features_1_title']
                    : ($variant === '4' ? (string) ($home4Defaults['features_1_title'] ?? '') : '')
            ),
            'features_1_description' => $this->stringSetting(
                'home_' . $variant . '_features_1_description',
                $variant === '1'
                    ? $home1Defaults['features_1_description']
                    : ($variant === '4' ? (string) ($home4Defaults['features_1_description'] ?? '') : '')
            ),
            'features_2_title' => $this->stringSetting(
                'home_' . $variant . '_features_2_title',
                $variant === '1'
                    ? $home1Defaults['features_2_title']
                    : ($variant === '4' ? (string) ($home4Defaults['features_2_title'] ?? '') : '')
            ),
            'features_2_description' => $this->stringSetting(
                'home_' . $variant . '_features_2_description',
                $variant === '1'
                    ? $home1Defaults['features_2_description']
                    : ($variant === '4' ? (string) ($home4Defaults['features_2_description'] ?? '') : '')
            ),
            'features_3_title' => $this->stringSetting(
                'home_' . $variant . '_features_3_title',
                $variant === '1'
                    ? $home1Defaults['features_3_title']
                    : ($variant === '4' ? (string) ($home4Defaults['features_3_title'] ?? '') : '')
            ),
            'features_3_description' => $this->stringSetting(
                'home_' . $variant . '_features_3_description',
                $variant === '1'
                    ? $home1Defaults['features_3_description']
                    : ($variant === '4' ? (string) ($home4Defaults['features_3_description'] ?? '') : '')
            ),
            'features_4_title' => $this->stringSetting(
                'home_' . $variant . '_features_4_title',
                $variant === '1'
                    ? $home1Defaults['features_4_title']
                    : ($variant === '4' ? (string) ($home4Defaults['features_4_title'] ?? '') : '')
            ),
            'features_4_description' => $this->stringSetting(
                'home_' . $variant . '_features_4_description',
                $variant === '1'
                    ? $home1Defaults['features_4_description']
                    : ($variant === '4' ? (string) ($home4Defaults['features_4_description'] ?? '') : '')
            ),
            'features_5_title' => $this->stringSetting(
                'home_' . $variant . '_features_5_title',
                $variant === '1'
                    ? $home1Defaults['features_5_title']
                    : ($variant === '4' ? (string) ($home4Defaults['features_5_title'] ?? '') : '')
            ),
            'features_5_description' => $this->stringSetting(
                'home_' . $variant . '_features_5_description',
                $variant === '1'
                    ? $home1Defaults['features_5_description']
                    : ($variant === '4' ? (string) ($home4Defaults['features_5_description'] ?? '') : '')
            ),
            'features_6_title' => $this->stringSetting(
                'home_' . $variant . '_features_6_title',
                $variant === '1'
                    ? $home1Defaults['features_6_title']
                    : ($variant === '4' ? (string) ($home4Defaults['features_6_title'] ?? '') : '')
            ),
            'features_6_description' => $this->stringSetting(
                'home_' . $variant . '_features_6_description',
                $variant === '1'
                    ? $home1Defaults['features_6_description']
                    : ($variant === '4' ? (string) ($home4Defaults['features_6_description'] ?? '') : '')
            ),

            'features_cta_text' => $variant === '4'
                ? $this->stringSetting('home_4_features_cta_text', (string) ($home4Defaults['features_cta_text'] ?? ''))
                : '',
            'features_cta_url' => $variant === '4'
                ? $this->stringSetting('home_4_features_cta_url', (string) ($home4Defaults['features_cta_url'] ?? ''))
                : '',

            'ai_badge' => $this->stringSetting(
                'home_' . $variant . '_ai_badge',
                $variant === '1'
                    ? $home1Defaults['ai_badge']
                    : ($variant === '4' ? (string) ($home4Defaults['ai_badge'] ?? '') : '')
            ),
            'ai_title' => $this->stringSetting(
                'home_' . $variant . '_ai_title',
                $variant === '1'
                    ? $home1Defaults['ai_title']
                    : ($variant === '4' ? (string) ($home4Defaults['ai_title'] ?? '') : '')
            ),
            'ai_title_highlight' => $variant === '4'
                ? $this->stringSetting('home_4_ai_title_highlight', (string) ($home4Defaults['ai_title_highlight'] ?? ''))
                : '',
            'ai_subtitle' => $this->stringSetting(
                'home_' . $variant . '_ai_subtitle',
                $variant === '1'
                    ? $home1Defaults['ai_subtitle']
                    : ($variant === '4' ? (string) ($home4Defaults['ai_subtitle'] ?? '') : '')
            ),
            'ai_1_title' => $this->stringSetting(
                'home_' . $variant . '_ai_1_title',
                $variant === '1'
                    ? $home1Defaults['ai_1_title']
                    : ($variant === '4' ? (string) ($home4Defaults['ai_1_title'] ?? '') : '')
            ),
            'ai_1_description' => $this->stringSetting(
                'home_' . $variant . '_ai_1_description',
                $variant === '1'
                    ? $home1Defaults['ai_1_description']
                    : ($variant === '4' ? (string) ($home4Defaults['ai_1_description'] ?? '') : '')
            ),
            'ai_2_title' => $this->stringSetting(
                'home_' . $variant . '_ai_2_title',
                $variant === '1'
                    ? $home1Defaults['ai_2_title']
                    : ($variant === '4' ? (string) ($home4Defaults['ai_2_title'] ?? '') : '')
            ),
            'ai_2_description' => $this->stringSetting(
                'home_' . $variant . '_ai_2_description',
                $variant === '1'
                    ? $home1Defaults['ai_2_description']
                    : ($variant === '4' ? (string) ($home4Defaults['ai_2_description'] ?? '') : '')
            ),

            'ai_3_title' => $variant === '4'
                ? $this->stringSetting('home_4_ai_3_title', (string) ($home4Defaults['ai_3_title'] ?? ''))
                : '',
            'ai_3_description' => $variant === '4'
                ? $this->stringSetting('home_4_ai_3_description', (string) ($home4Defaults['ai_3_description'] ?? ''))
                : '',
            'ai_4_title' => $variant === '4'
                ? $this->stringSetting('home_4_ai_4_title', (string) ($home4Defaults['ai_4_title'] ?? ''))
                : '',
            'ai_4_description' => $variant === '4'
                ? $this->stringSetting('home_4_ai_4_description', (string) ($home4Defaults['ai_4_description'] ?? ''))
                : '',
            'ai_cta_text' => $variant === '4'
                ? $this->stringSetting('home_4_ai_cta_text', (string) ($home4Defaults['ai_cta_text'] ?? ''))
                : '',
            'ai_cta_url' => $variant === '4'
                ? $this->stringSetting('home_4_ai_cta_url', (string) ($home4Defaults['ai_cta_url'] ?? ''))
                : '',

            'how_title' => $this->stringSetting('home_' . $variant . '_how_title', $variant === '1' ? $home1Defaults['how_title'] : ''),
            'how_subtitle' => $this->stringSetting('home_' . $variant . '_how_subtitle', $variant === '1' ? $home1Defaults['how_subtitle'] : ''),
            'how_1_title' => $this->stringSetting('home_' . $variant . '_how_1_title', $variant === '1' ? $home1Defaults['how_1_title'] : ''),
            'how_1_description' => $this->stringSetting('home_' . $variant . '_how_1_description', $variant === '1' ? $home1Defaults['how_1_description'] : ''),
            'how_2_title' => $this->stringSetting('home_' . $variant . '_how_2_title', $variant === '1' ? $home1Defaults['how_2_title'] : ''),
            'how_2_description' => $this->stringSetting('home_' . $variant . '_how_2_description', $variant === '1' ? $home1Defaults['how_2_description'] : ''),
            'how_3_title' => $this->stringSetting('home_' . $variant . '_how_3_title', $variant === '1' ? $home1Defaults['how_3_title'] : ''),
            'how_3_description' => $this->stringSetting('home_' . $variant . '_how_3_description', $variant === '1' ? $home1Defaults['how_3_description'] : ''),

            'faq_title' => $this->stringSetting('home_faq_title', $faqDefaults['faq_title']),
            'faq_subtitle' => $this->stringSetting('home_faq_subtitle', $faqDefaults['faq_subtitle']),

            'pricing_badge' => $this->stringSetting('home_pricing_badge', $pricingDefaults['pricing_badge']),
            'pricing_title' => $this->stringSetting('home_pricing_title', $pricingDefaults['pricing_title']),
            'pricing_subtitle' => $this->stringSetting('home_pricing_subtitle', $pricingDefaults['pricing_subtitle']),
            'pricing_toggle_monthly' => $this->stringSetting('home_pricing_toggle_monthly', $pricingDefaults['pricing_toggle_monthly']),
            'pricing_toggle_annual' => $this->stringSetting('home_pricing_toggle_annual', $pricingDefaults['pricing_toggle_annual']),
            'pricing_toggle_save' => $this->stringSetting('home_pricing_toggle_save', $pricingDefaults['pricing_toggle_save']),
            'pricing_popular_badge' => $this->stringSetting('home_pricing_popular_badge', $pricingDefaults['pricing_popular_badge']),
            'pricing_card_cta_text' => $this->stringSetting('home_pricing_card_cta_text', $pricingDefaults['pricing_card_cta_text']),
            'pricing_card_1_title' => $this->stringSetting('home_pricing_card_1_title', $pricingDefaults['pricing_card_1_title']),
            'pricing_card_1_description' => $this->stringSetting('home_pricing_card_1_description', $pricingDefaults['pricing_card_1_description']),
            'pricing_card_1_cta_text' => $this->stringSetting('home_pricing_card_1_cta_text', $pricingDefaults['pricing_card_1_cta_text']),
            'pricing_card_2_title' => $this->stringSetting('home_pricing_card_2_title', $pricingDefaults['pricing_card_2_title']),
            'pricing_card_2_description' => $this->stringSetting('home_pricing_card_2_description', $pricingDefaults['pricing_card_2_description']),
            'pricing_card_2_cta_text' => $this->stringSetting('home_pricing_card_2_cta_text', $pricingDefaults['pricing_card_2_cta_text']),
            'pricing_card_3_title' => $this->stringSetting('home_pricing_card_3_title', $pricingDefaults['pricing_card_3_title']),
            'pricing_card_3_description' => $this->stringSetting('home_pricing_card_3_description', $pricingDefaults['pricing_card_3_description']),
            'pricing_card_3_cta_text' => $this->stringSetting('home_pricing_card_3_cta_text', $pricingDefaults['pricing_card_3_cta_text']),
            'pricing_compare_text' => $this->stringSetting('home_pricing_compare_text', $pricingDefaults['pricing_compare_text']),

            'cta_badge' => $this->stringSetting('home_cta_badge', $ctaDefaults['cta_badge']),
            'cta_title' => $this->stringSetting('home_cta_title', $ctaDefaults['cta_title']),
            'cta_subtitle' => $this->stringSetting('home_cta_subtitle', $ctaDefaults['cta_subtitle']),
            'cta_primary_text' => $this->stringSetting('home_cta_primary_text', $ctaDefaults['cta_primary_text']),
            'cta_primary_url' => $this->stringSetting('home_cta_primary_url', $ctaDefaults['cta_primary_url']),
            'cta_secondary_text' => $this->stringSetting('home_cta_secondary_text', $ctaDefaults['cta_secondary_text']),
            'cta_secondary_url' => $this->stringSetting('home_cta_secondary_url', $ctaDefaults['cta_secondary_url']),
            'cta_note' => $this->stringSetting('home_cta_note', $ctaDefaults['cta_note']),

            'testimonials_title' => $variant === '4'
                ? $this->stringSetting('home_4_testimonials_title', (string) ($home4Defaults['testimonials_title'] ?? ''))
                : '',
            'testimonials_subtitle' => $variant === '4'
                ? $this->stringSetting('home_4_testimonials_subtitle', (string) ($home4Defaults['testimonials_subtitle'] ?? ''))
                : '',
            'testimonial_1_quote' => $variant === '4'
                ? $this->stringSetting('home_4_testimonial_1_quote', (string) ($home4Defaults['testimonial_1_quote'] ?? ''))
                : '',
            'testimonial_1_name' => $variant === '4'
                ? $this->stringSetting('home_4_testimonial_1_name', (string) ($home4Defaults['testimonial_1_name'] ?? ''))
                : '',
            'testimonial_1_role' => $variant === '4'
                ? $this->stringSetting('home_4_testimonial_1_role', (string) ($home4Defaults['testimonial_1_role'] ?? ''))
                : '',
            'testimonial_1_initial' => $variant === '4'
                ? $this->stringSetting('home_4_testimonial_1_initial', (string) ($home4Defaults['testimonial_1_initial'] ?? ''))
                : '',
            'testimonial_2_quote' => $variant === '4'
                ? $this->stringSetting('home_4_testimonial_2_quote', (string) ($home4Defaults['testimonial_2_quote'] ?? ''))
                : '',
            'testimonial_2_name' => $variant === '4'
                ? $this->stringSetting('home_4_testimonial_2_name', (string) ($home4Defaults['testimonial_2_name'] ?? ''))
                : '',
            'testimonial_2_role' => $variant === '4'
                ? $this->stringSetting('home_4_testimonial_2_role', (string) ($home4Defaults['testimonial_2_role'] ?? ''))
                : '',
            'testimonial_2_initial' => $variant === '4'
                ? $this->stringSetting('home_4_testimonial_2_initial', (string) ($home4Defaults['testimonial_2_initial'] ?? ''))
                : '',
            'testimonial_3_quote' => $variant === '4'
                ? $this->stringSetting('home_4_testimonial_3_quote', (string) ($home4Defaults['testimonial_3_quote'] ?? ''))
                : '',
            'testimonial_3_name' => $variant === '4'
                ? $this->stringSetting('home_4_testimonial_3_name', (string) ($home4Defaults['testimonial_3_name'] ?? ''))
                : '',
            'testimonial_3_role' => $variant === '4'
                ? $this->stringSetting('home_4_testimonial_3_role', (string) ($home4Defaults['testimonial_3_role'] ?? ''))
                : '',
            'testimonial_3_initial' => $variant === '4'
                ? $this->stringSetting('home_4_testimonial_3_initial', (string) ($home4Defaults['testimonial_3_initial'] ?? ''))
                : '',
        ];

        if ($variant === '4') {
            for ($i = 1; $i <= self::HOME4_STATS; $i++) {
                $form['stat_' . $i . '_value'] = $this->stringSetting('home_4_stat_' . $i . '_value', (string) ($home4Defaults['stat_' . $i . '_value'] ?? ''));
                $form['stat_' . $i . '_label'] = $this->stringSetting('home_4_stat_' . $i . '_label', (string) ($home4Defaults['stat_' . $i . '_label'] ?? ''));
            }
        }

        for ($i = 1; $i <= self::FAQ_SLOTS; $i++) {
            $form['faq_' . $i . '_question'] = $this->stringSetting('home_faq_' . $i . '_question', (string) ($faqDefaults['faq_' . $i . '_question'] ?? ''));
            $form['faq_' . $i . '_answer'] = $this->stringSetting('home_faq_' . $i . '_answer', (string) ($faqDefaults['faq_' . $i . '_answer'] ?? ''));
        }

        $brandingDisk = (string) config('filesystems.branding_disk', 'public');
        $heroImageUrl = null;
        if (is_string($form['hero_image']) && trim($form['hero_image']) !== '') {
            $heroImageUrl = Storage::disk($brandingDisk)->url($form['hero_image']);
        }

        $logos = $this->getLogoSlots($variant);

        return view('admin.homepages.edit', compact('variant', 'variantLabel', 'form', 'heroImageUrl', 'logos'));
    }

    public function update(Request $request, string $variant): RedirectResponse
    {
        $variant = trim($variant);
        abort_unless(in_array($variant, ['1', '2', '3', '4'], true), 404);

        $data = $request->validate([
            'hero_description' => ['nullable', 'string'],
            'hero_scroll_text' => ['nullable', 'string'],
            'hero_button_text' => ['nullable', 'string'],
            'hero_button_type' => ['nullable', 'string', 'in:link,video'],
            'hero_button_url' => ['nullable', 'string'],

            'hero_badge' => ['nullable', 'string'],
            'hero_title_prefix' => ['nullable', 'string'],
            'hero_title_highlight' => ['nullable', 'string'],
            'hero_secondary_button_text' => ['nullable', 'string'],
            'hero_secondary_button_url' => ['nullable', 'string'],
            'remove_hero_image' => ['nullable'],
            'hero_image' => ['nullable', 'file', 'image'],
            'remove_logo_1' => ['nullable'],
            'remove_logo_2' => ['nullable'],
            'remove_logo_3' => ['nullable'],
            'remove_logo_4' => ['nullable'],
            'remove_logo_5' => ['nullable'],
            'remove_logo_6' => ['nullable'],
            'logo_1' => ['nullable', 'file', 'image'],
            'logo_2' => ['nullable', 'file', 'image'],
            'logo_3' => ['nullable', 'file', 'image'],
            'logo_4' => ['nullable', 'file', 'image'],
            'logo_5' => ['nullable', 'file', 'image'],
            'logo_6' => ['nullable', 'file', 'image'],

            'logos_title' => ['nullable', 'string'],

            'benefits_title' => ['nullable', 'string'],
            'benefits_subtitle' => ['nullable', 'string'],
            'benefits_1_title' => ['nullable', 'string'],
            'benefits_1_description' => ['nullable', 'string'],
            'benefits_2_title' => ['nullable', 'string'],
            'benefits_2_description' => ['nullable', 'string'],
            'benefits_3_title' => ['nullable', 'string'],
            'benefits_3_description' => ['nullable', 'string'],

            'features_title' => ['nullable', 'string'],
            'features_subtitle' => ['nullable', 'string'],
            'features_1_title' => ['nullable', 'string'],
            'features_1_description' => ['nullable', 'string'],
            'features_2_title' => ['nullable', 'string'],
            'features_2_description' => ['nullable', 'string'],
            'features_3_title' => ['nullable', 'string'],
            'features_3_description' => ['nullable', 'string'],
            'features_4_title' => ['nullable', 'string'],
            'features_4_description' => ['nullable', 'string'],
            'features_5_title' => ['nullable', 'string'],
            'features_5_description' => ['nullable', 'string'],
            'features_6_title' => ['nullable', 'string'],
            'features_6_description' => ['nullable', 'string'],

            'features_cta_text' => ['nullable', 'string'],
            'features_cta_url' => ['nullable', 'string'],

            'ai_badge' => ['nullable', 'string'],
            'ai_title' => ['nullable', 'string'],
            'ai_title_highlight' => ['nullable', 'string'],
            'ai_subtitle' => ['nullable', 'string'],
            'ai_1_title' => ['nullable', 'string'],
            'ai_1_description' => ['nullable', 'string'],
            'ai_2_title' => ['nullable', 'string'],
            'ai_2_description' => ['nullable', 'string'],

            'ai_3_title' => ['nullable', 'string'],
            'ai_3_description' => ['nullable', 'string'],
            'ai_4_title' => ['nullable', 'string'],
            'ai_4_description' => ['nullable', 'string'],
            'ai_cta_text' => ['nullable', 'string'],
            'ai_cta_url' => ['nullable', 'string'],

            'how_title' => ['nullable', 'string'],
            'how_subtitle' => ['nullable', 'string'],
            'how_1_title' => ['nullable', 'string'],
            'how_1_description' => ['nullable', 'string'],
            'how_2_title' => ['nullable', 'string'],
            'how_2_description' => ['nullable', 'string'],
            'how_3_title' => ['nullable', 'string'],
            'how_3_description' => ['nullable', 'string'],

            'faq_title' => ['nullable', 'string'],
            'faq_subtitle' => ['nullable', 'string'],
            'faq_1_question' => ['nullable', 'string'],
            'faq_1_answer' => ['nullable', 'string'],
            'faq_2_question' => ['nullable', 'string'],
            'faq_2_answer' => ['nullable', 'string'],
            'faq_3_question' => ['nullable', 'string'],
            'faq_3_answer' => ['nullable', 'string'],
            'faq_4_question' => ['nullable', 'string'],
            'faq_4_answer' => ['nullable', 'string'],
            'faq_5_question' => ['nullable', 'string'],
            'faq_5_answer' => ['nullable', 'string'],
            'faq_6_question' => ['nullable', 'string'],
            'faq_6_answer' => ['nullable', 'string'],

            'pricing_badge' => ['nullable', 'string'],
            'pricing_title' => ['nullable', 'string'],
            'pricing_subtitle' => ['nullable', 'string'],
            'pricing_toggle_monthly' => ['nullable', 'string'],
            'pricing_toggle_annual' => ['nullable', 'string'],
            'pricing_toggle_save' => ['nullable', 'string'],
            'pricing_popular_badge' => ['nullable', 'string'],
            'pricing_card_cta_text' => ['nullable', 'string'],
            'pricing_card_1_title' => ['nullable', 'string'],
            'pricing_card_1_description' => ['nullable', 'string'],
            'pricing_card_1_cta_text' => ['nullable', 'string'],
            'pricing_card_2_title' => ['nullable', 'string'],
            'pricing_card_2_description' => ['nullable', 'string'],
            'pricing_card_2_cta_text' => ['nullable', 'string'],
            'pricing_card_3_title' => ['nullable', 'string'],
            'pricing_card_3_description' => ['nullable', 'string'],
            'pricing_card_3_cta_text' => ['nullable', 'string'],
            'pricing_compare_text' => ['nullable', 'string'],

            'cta_badge' => ['nullable', 'string'],
            'cta_title' => ['nullable', 'string'],
            'cta_subtitle' => ['nullable', 'string'],
            'cta_primary_text' => ['nullable', 'string'],
            'cta_primary_url' => ['nullable', 'string'],
            'cta_secondary_text' => ['nullable', 'string'],
            'cta_secondary_url' => ['nullable', 'string'],
            'cta_note' => ['nullable', 'string'],

            'testimonials_title' => ['nullable', 'string'],
            'testimonials_subtitle' => ['nullable', 'string'],
            'testimonial_1_quote' => ['nullable', 'string'],
            'testimonial_1_name' => ['nullable', 'string'],
            'testimonial_1_role' => ['nullable', 'string'],
            'testimonial_1_initial' => ['nullable', 'string'],
            'testimonial_2_quote' => ['nullable', 'string'],
            'testimonial_2_name' => ['nullable', 'string'],
            'testimonial_2_role' => ['nullable', 'string'],
            'testimonial_2_initial' => ['nullable', 'string'],
            'testimonial_3_quote' => ['nullable', 'string'],
            'testimonial_3_name' => ['nullable', 'string'],
            'testimonial_3_role' => ['nullable', 'string'],
            'testimonial_3_initial' => ['nullable', 'string'],
        ]);

        if ($variant === '4') {
            for ($i = 1; $i <= self::HOME4_STATS; $i++) {
                $data['stat_' . $i . '_value'] = (string) ($request->input('stat_' . $i . '_value', ''));
                $data['stat_' . $i . '_label'] = (string) ($request->input('stat_' . $i . '_label', ''));
            }
        }

        $this->upsertHomepageSetting('home_' . $variant . '_hero_description', (string) ($data['hero_description'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_hero_scroll_text', (string) ($data['hero_scroll_text'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_hero_button_text', (string) ($data['hero_button_text'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_hero_button_type', (string) ($data['hero_button_type'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_hero_button_url', (string) ($data['hero_button_url'] ?? ''));

        if ($variant === '4') {
            $this->upsertHomepageSetting('home_4_hero_badge', (string) ($data['hero_badge'] ?? ''));
            $this->upsertHomepageSetting('home_4_hero_title_prefix', (string) ($data['hero_title_prefix'] ?? ''));
            $this->upsertHomepageSetting('home_4_hero_title_highlight', (string) ($data['hero_title_highlight'] ?? ''));
            $this->upsertHomepageSetting('home_4_hero_secondary_button_text', (string) ($data['hero_secondary_button_text'] ?? ''));
            $this->upsertHomepageSetting('home_4_hero_secondary_button_url', (string) ($data['hero_secondary_button_url'] ?? ''));

            for ($i = 1; $i <= self::HOME4_STATS; $i++) {
                $this->upsertHomepageSetting('home_4_stat_' . $i . '_value', (string) ($data['stat_' . $i . '_value'] ?? ''));
                $this->upsertHomepageSetting('home_4_stat_' . $i . '_label', (string) ($data['stat_' . $i . '_label'] ?? ''));
            }

            $this->upsertHomepageSetting('home_4_logos_title', (string) ($data['logos_title'] ?? ''));

            $this->upsertHomepageSetting('home_4_benefits_title', (string) ($data['benefits_title'] ?? ''));
            $this->upsertHomepageSetting('home_4_benefits_subtitle', (string) ($data['benefits_subtitle'] ?? ''));
            $this->upsertHomepageSetting('home_4_benefits_1_title', (string) ($data['benefits_1_title'] ?? ''));
            $this->upsertHomepageSetting('home_4_benefits_1_description', (string) ($data['benefits_1_description'] ?? ''));
            $this->upsertHomepageSetting('home_4_benefits_2_title', (string) ($data['benefits_2_title'] ?? ''));
            $this->upsertHomepageSetting('home_4_benefits_2_description', (string) ($data['benefits_2_description'] ?? ''));
            $this->upsertHomepageSetting('home_4_benefits_3_title', (string) ($data['benefits_3_title'] ?? ''));
            $this->upsertHomepageSetting('home_4_benefits_3_description', (string) ($data['benefits_3_description'] ?? ''));
        }

        $this->upsertHomepageSetting('home_' . $variant . '_features_title', (string) ($data['features_title'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_features_subtitle', (string) ($data['features_subtitle'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_features_1_title', (string) ($data['features_1_title'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_features_1_description', (string) ($data['features_1_description'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_features_2_title', (string) ($data['features_2_title'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_features_2_description', (string) ($data['features_2_description'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_features_3_title', (string) ($data['features_3_title'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_features_3_description', (string) ($data['features_3_description'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_features_4_title', (string) ($data['features_4_title'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_features_4_description', (string) ($data['features_4_description'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_features_5_title', (string) ($data['features_5_title'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_features_5_description', (string) ($data['features_5_description'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_features_6_title', (string) ($data['features_6_title'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_features_6_description', (string) ($data['features_6_description'] ?? ''));

        if ($variant === '4') {
            $this->upsertHomepageSetting('home_4_features_cta_text', (string) ($data['features_cta_text'] ?? ''));
            $this->upsertHomepageSetting('home_4_features_cta_url', (string) ($data['features_cta_url'] ?? ''));
        }

        $this->upsertHomepageSetting('home_' . $variant . '_ai_badge', (string) ($data['ai_badge'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_ai_title', (string) ($data['ai_title'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_ai_subtitle', (string) ($data['ai_subtitle'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_ai_1_title', (string) ($data['ai_1_title'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_ai_1_description', (string) ($data['ai_1_description'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_ai_2_title', (string) ($data['ai_2_title'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_ai_2_description', (string) ($data['ai_2_description'] ?? ''));

        if ($variant === '4') {
            $this->upsertHomepageSetting('home_4_ai_title_highlight', (string) ($data['ai_title_highlight'] ?? ''));
            $this->upsertHomepageSetting('home_4_ai_3_title', (string) ($data['ai_3_title'] ?? ''));
            $this->upsertHomepageSetting('home_4_ai_3_description', (string) ($data['ai_3_description'] ?? ''));
            $this->upsertHomepageSetting('home_4_ai_4_title', (string) ($data['ai_4_title'] ?? ''));
            $this->upsertHomepageSetting('home_4_ai_4_description', (string) ($data['ai_4_description'] ?? ''));
            $this->upsertHomepageSetting('home_4_ai_cta_text', (string) ($data['ai_cta_text'] ?? ''));
            $this->upsertHomepageSetting('home_4_ai_cta_url', (string) ($data['ai_cta_url'] ?? ''));
        }

        $this->upsertHomepageSetting('home_' . $variant . '_how_title', (string) ($data['how_title'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_how_subtitle', (string) ($data['how_subtitle'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_how_1_title', (string) ($data['how_1_title'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_how_1_description', (string) ($data['how_1_description'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_how_2_title', (string) ($data['how_2_title'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_how_2_description', (string) ($data['how_2_description'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_how_3_title', (string) ($data['how_3_title'] ?? ''));
        $this->upsertHomepageSetting('home_' . $variant . '_how_3_description', (string) ($data['how_3_description'] ?? ''));

        $this->upsertHomepageSetting('home_faq_title', (string) ($data['faq_title'] ?? ''));
        $this->upsertHomepageSetting('home_faq_subtitle', (string) ($data['faq_subtitle'] ?? ''));
        for ($i = 1; $i <= self::FAQ_SLOTS; $i++) {
            $this->upsertHomepageSetting('home_faq_' . $i . '_question', (string) ($data['faq_' . $i . '_question'] ?? ''));
            $this->upsertHomepageSetting('home_faq_' . $i . '_answer', (string) ($data['faq_' . $i . '_answer'] ?? ''));
        }

        $this->upsertHomepageSetting('home_pricing_badge', (string) ($data['pricing_badge'] ?? ''));
        $this->upsertHomepageSetting('home_pricing_title', (string) ($data['pricing_title'] ?? ''));
        $this->upsertHomepageSetting('home_pricing_subtitle', (string) ($data['pricing_subtitle'] ?? ''));
        $this->upsertHomepageSetting('home_pricing_toggle_monthly', (string) ($data['pricing_toggle_monthly'] ?? ''));
        $this->upsertHomepageSetting('home_pricing_toggle_annual', (string) ($data['pricing_toggle_annual'] ?? ''));
        $this->upsertHomepageSetting('home_pricing_toggle_save', (string) ($data['pricing_toggle_save'] ?? ''));
        $this->upsertHomepageSetting('home_pricing_popular_badge', (string) ($data['pricing_popular_badge'] ?? ''));
        $this->upsertHomepageSetting('home_pricing_card_cta_text', (string) ($data['pricing_card_cta_text'] ?? ''));
        $this->upsertHomepageSetting('home_pricing_card_1_title', (string) ($data['pricing_card_1_title'] ?? ''));
        $this->upsertHomepageSetting('home_pricing_card_1_description', (string) ($data['pricing_card_1_description'] ?? ''));
        $this->upsertHomepageSetting('home_pricing_card_1_cta_text', (string) ($data['pricing_card_1_cta_text'] ?? ''));
        $this->upsertHomepageSetting('home_pricing_card_2_title', (string) ($data['pricing_card_2_title'] ?? ''));
        $this->upsertHomepageSetting('home_pricing_card_2_description', (string) ($data['pricing_card_2_description'] ?? ''));
        $this->upsertHomepageSetting('home_pricing_card_2_cta_text', (string) ($data['pricing_card_2_cta_text'] ?? ''));
        $this->upsertHomepageSetting('home_pricing_card_3_title', (string) ($data['pricing_card_3_title'] ?? ''));
        $this->upsertHomepageSetting('home_pricing_card_3_description', (string) ($data['pricing_card_3_description'] ?? ''));
        $this->upsertHomepageSetting('home_pricing_card_3_cta_text', (string) ($data['pricing_card_3_cta_text'] ?? ''));
        $this->upsertHomepageSetting('home_pricing_compare_text', (string) ($data['pricing_compare_text'] ?? ''));

        $this->upsertHomepageSetting('home_cta_badge', (string) ($data['cta_badge'] ?? ''));
        $this->upsertHomepageSetting('home_cta_title', (string) ($data['cta_title'] ?? ''));
        $this->upsertHomepageSetting('home_cta_subtitle', (string) ($data['cta_subtitle'] ?? ''));
        $this->upsertHomepageSetting('home_cta_primary_text', (string) ($data['cta_primary_text'] ?? ''));
        $this->upsertHomepageSetting('home_cta_primary_url', (string) ($data['cta_primary_url'] ?? ''));
        $this->upsertHomepageSetting('home_cta_secondary_text', (string) ($data['cta_secondary_text'] ?? ''));
        $this->upsertHomepageSetting('home_cta_secondary_url', (string) ($data['cta_secondary_url'] ?? ''));
        $this->upsertHomepageSetting('home_cta_note', (string) ($data['cta_note'] ?? ''));

        if ($variant === '4') {
            $this->upsertHomepageSetting('home_4_testimonials_title', (string) ($data['testimonials_title'] ?? ''));
            $this->upsertHomepageSetting('home_4_testimonials_subtitle', (string) ($data['testimonials_subtitle'] ?? ''));
            $this->upsertHomepageSetting('home_4_testimonial_1_quote', (string) ($data['testimonial_1_quote'] ?? ''));
            $this->upsertHomepageSetting('home_4_testimonial_1_name', (string) ($data['testimonial_1_name'] ?? ''));
            $this->upsertHomepageSetting('home_4_testimonial_1_role', (string) ($data['testimonial_1_role'] ?? ''));
            $this->upsertHomepageSetting('home_4_testimonial_1_initial', (string) ($data['testimonial_1_initial'] ?? ''));
            $this->upsertHomepageSetting('home_4_testimonial_2_quote', (string) ($data['testimonial_2_quote'] ?? ''));
            $this->upsertHomepageSetting('home_4_testimonial_2_name', (string) ($data['testimonial_2_name'] ?? ''));
            $this->upsertHomepageSetting('home_4_testimonial_2_role', (string) ($data['testimonial_2_role'] ?? ''));
            $this->upsertHomepageSetting('home_4_testimonial_2_initial', (string) ($data['testimonial_2_initial'] ?? ''));
            $this->upsertHomepageSetting('home_4_testimonial_3_quote', (string) ($data['testimonial_3_quote'] ?? ''));
            $this->upsertHomepageSetting('home_4_testimonial_3_name', (string) ($data['testimonial_3_name'] ?? ''));
            $this->upsertHomepageSetting('home_4_testimonial_3_role', (string) ($data['testimonial_3_role'] ?? ''));
            $this->upsertHomepageSetting('home_4_testimonial_3_initial', (string) ($data['testimonial_3_initial'] ?? ''));
        }

        $brandingDisk = (string) config('filesystems.branding_disk', 'public');
        $imageKey = 'home_' . $variant . '_hero_image';

        if ($request->boolean('remove_hero_image')) {
            $oldPath = Setting::get($imageKey);
            if (is_string($oldPath) && $oldPath !== '') {
                Storage::disk($brandingDisk)->delete($oldPath);
            }
            $this->upsertHomepageSetting($imageKey, '');
        }

        if ($request->hasFile('hero_image')) {
            $file = $request->file('hero_image');

            if ($file && $file->isValid()) {
                $oldPath = Setting::get($imageKey);
                if (is_string($oldPath) && $oldPath !== '') {
                    Storage::disk($brandingDisk)->delete($oldPath);
                }

                $path = $file->storePublicly('homepages', $brandingDisk);
                $this->upsertHomepageSetting($imageKey, $path);
            }
        }

        $logosKey = 'home_' . $variant . '_logos';
        $existingLogos = Setting::get($logosKey, []);
        $existingLogos = is_array($existingLogos) ? $existingLogos : [];
        $existingLogos = array_values(array_filter($existingLogos, fn ($v) => is_string($v)));

        $newLogos = [];
        for ($i = 1; $i <= self::LOGO_SLOTS; $i++) {
            $oldPath = is_string($existingLogos[$i - 1] ?? null) ? $existingLogos[$i - 1] : '';
            $path = $oldPath;

            if ($request->boolean('remove_logo_' . $i)) {
                if ($oldPath !== '') {
                    Storage::disk($brandingDisk)->delete($oldPath);
                }
                $path = '';
            }

            if ($request->hasFile('logo_' . $i)) {
                $file = $request->file('logo_' . $i);
                if ($file && $file->isValid()) {
                    if ($oldPath !== '') {
                        Storage::disk($brandingDisk)->delete($oldPath);
                    }

                    $path = $file->storePublicly('homepages/logos', $brandingDisk);
                }
            }

            $newLogos[] = $path;
        }

        $this->upsertHomepageJsonSetting($logosKey, $newLogos);

        return redirect()
            ->route('admin.homepages.edit', ['variant' => $variant])
            ->with('success', __('Homepage content updated.'));
    }

    private function upsertHomepageSetting(string $key, string $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            [
                'category' => 'homepage',
                'value' => $value,
                'type' => 'string',
                'description' => null,
                'is_public' => true,
            ]
        );
    }

    private function upsertHomepageJsonSetting(string $key, array $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            [
                'category' => 'homepage',
                'value' => $value,
                'type' => 'json',
                'description' => null,
                'is_public' => true,
            ]
        );
    }

    private function stringSetting(string $key, string $default = ''): string
    {
        $val = Setting::get($key, $default);
        return is_string($val) ? $val : $default;
    }

    private function getHeroDefaults(string $variant): array
    {
        return match ($variant) {
            '2' => [
                'hero_description' => 'Build, automate, and scale your email marketing without the recurring costs. Self-host on your own server and keep 100% of your profits.',
                'hero_scroll_text' => '',
                'hero_button_text' => 'Start Free Trial',
                'hero_button_type' => 'link',
                'hero_button_url' => route('register'),
            ],
            '3' => [
                'hero_description' => 'Self-hosted email automation software with white-label branding, multi-tenant support, and built-in billing. One-time purchase, no monthly fees.',
                'hero_scroll_text' => '',
                'hero_button_text' => 'Get Started — $29',
                'hero_button_type' => 'link',
                'hero_button_url' => route('register'),
            ],
            '4' => [
                'hero_description' => 'The all-in-one email marketing platform that helps you create stunning campaigns, automate your workflows, and grow your audience — without the complexity.',
                'hero_scroll_text' => 'No credit card required · Free 14-day trial · Cancel anytime',
                'hero_button_text' => 'Start Free Trial',
                'hero_button_type' => 'link',
                'hero_button_url' => route('register'),
            ],
            default => [
                'hero_description' => 'Host it yourself, run it as SaaS, or manage clients. MailZen gives you complete control over your email infrastructure with enterprise-grade features.',
                'hero_scroll_text' => '',
                'hero_button_text' => 'Get Started Free',
                'hero_button_type' => 'link',
                'hero_button_url' => route('register'),
            ],
        };
    }

    private function getLogoSlots(string $variant): array
    {
        $brandingDisk = (string) config('filesystems.branding_disk', 'public');
        $key = 'home_' . $variant . '_logos';

        try {
            $paths = Setting::get($key, []);
        } catch (\Throwable $e) {
            $paths = [];
        }

        $paths = is_array($paths) ? $paths : [];

        $slots = [];
        for ($i = 1; $i <= self::LOGO_SLOTS; $i++) {
            $path = $paths[$i - 1] ?? '';
            $path = is_string($path) ? trim($path) : '';
            $url = $path !== '' ? Storage::disk($brandingDisk)->url($path) : null;

            $slots[] = [
                'path' => $path,
                'url' => $url,
                'index' => $i,
            ];
        }

        return $slots;
    }

    private function getHome1SectionDefaults(): array
    {
        return [
            'features_title' => 'Everything you need to run email marketing at scale',
            'features_subtitle' => "Whether you're sending for yourself or running a full SaaS business, MailZen has you covered.",
            'features_1_title' => 'Multi-Tenant SaaS Ready',
            'features_1_description' => 'Run your own email marketing SaaS. Manage customers, plans, billing, and permissions from a powerful admin panel.',
            'features_2_title' => 'Campaigns & Automation',
            'features_2_description' => 'Create one-time campaigns, recurring sends, or automated drip sequences. Drag-and-drop editor with responsive templates.',
            'features_3_title' => 'List Management',
            'features_3_description' => 'Unlimited lists with custom fields, tags, and segments. Import/export CSV, double opt-in, and GDPR compliance built-in.',
            'features_4_title' => 'Multiple Delivery Servers',
            'features_4_description' => 'Connect Amazon SES, Mailgun, SendGrid, Postmark, SparkPost, or any SMTP. Load balance and rotate for maximum deliverability.',
            'features_5_title' => 'Real-Time Analytics',
            'features_5_description' => 'Track opens, clicks, bounces, and unsubscribes in real-time. Detailed reports with geographic and device insights.',
            'features_6_title' => 'Built-in Billing',
            'features_6_description' => 'Accept payments via Stripe, PayPal, or Paystack. Create plans, manage subscriptions, generate invoices automatically.',

            'ai_badge' => 'AI-Powered',
            'ai_title' => 'Write better emails with AI',
            'ai_subtitle' => 'Generate compelling subject lines, email copy, and calls-to-action in seconds.',
            'ai_1_title' => 'AI Content Generator',
            'ai_1_description' => 'Describe what you want to say and let AI craft the perfect email copy. Supports multiple tones and styles.',
            'ai_2_title' => 'Subject Line Optimizer',
            'ai_2_description' => 'Generate multiple subject line variations optimized for opens. A/B test with confidence.',

            'how_title' => 'Get started in minutes',
            'how_subtitle' => 'Deploy on your own server and start sending emails right away.',
            'how_1_title' => 'Install & Configure',
            'how_1_description' => 'Upload to your server, run the installer, and configure your settings. Works on any PHP 8.2+ hosting.',
            'how_2_title' => 'Connect Email Providers',
            'how_2_description' => 'Add your delivery servers — Amazon SES, Mailgun, SendGrid, or any SMTP. Configure sending domains.',
            'how_3_title' => 'Start Sending',
            'how_3_description' => 'Create lists, import subscribers, design campaigns, and start sending. Or invite customers to your SaaS.',
        ];
    }

    private function getHome4SectionDefaults(): array
    {
        return [
            'hero_badge' => 'Trusted by 10,000+ businesses worldwide',
            'hero_title_prefix' => 'Send emails that',
            'hero_title_highlight' => 'convert',
            'hero_secondary_button_text' => 'Watch Demo',
            'hero_secondary_button_url' => route('pricing'),

            'stat_1_value' => '99.9%',
            'stat_1_label' => 'Delivery Rate',
            'stat_2_value' => '45%',
            'stat_2_label' => 'Avg. Open Rate',
            'stat_3_value' => '10M+',
            'stat_3_label' => 'Emails Sent',
            'stat_4_value' => '24/7',
            'stat_4_label' => 'Expert Support',

            'logos_title' => 'Trusted by leading companies around the world',

            'benefits_title' => 'Why businesses choose ' . config('app.name', 'MailZen'),
            'benefits_subtitle' => 'Everything you need to run successful email campaigns, all in one powerful platform.',
            'benefits_1_title' => 'Lightning Fast Delivery',
            'benefits_1_description' => 'Send millions of emails in minutes with our high-performance infrastructure. 99.9% delivery rate guaranteed.',
            'benefits_2_title' => 'Drag & Drop Builder',
            'benefits_2_description' => 'Create stunning emails without any coding. Our intuitive editor makes designing beautiful campaigns effortless.',
            'benefits_3_title' => 'Advanced Analytics',
            'benefits_3_description' => 'Track opens, clicks, and conversions in real-time. Make data-driven decisions to optimize your campaigns.',

            'features_title' => 'Powerful features for modern marketers',
            'features_subtitle' => "From list management to automation, we've got everything covered.",
            'features_1_title' => 'Smart Segmentation',
            'features_1_description' => 'Target the right audience with powerful segmentation based on behavior, demographics, and custom fields.',
            'features_2_title' => 'Marketing Automation',
            'features_2_description' => 'Set up automated email sequences triggered by user actions. Nurture leads on autopilot.',
            'features_3_title' => 'Domain Authentication',
            'features_3_description' => 'Improve deliverability with SPF, DKIM, and DMARC. Keep your emails out of spam folders.',
            'features_4_title' => 'Template Library',
            'features_4_description' => 'Choose from hundreds of professionally designed templates. Customize them to match your brand.',
            'features_5_title' => 'Developer API',
            'features_5_description' => 'Integrate with your apps using our RESTful API. Send transactional emails programmatically.',
            'features_6_title' => 'Detailed Reports',
            'features_6_description' => 'Get comprehensive reports on campaign performance. Export data for further analysis.',
            'features_cta_text' => 'View all features',
            'features_cta_url' => route('features'),

            'ai_badge' => 'Powered by AI',
            'ai_title' => 'Supercharge your emails with',
            'ai_title_highlight' => 'artificial intelligence',
            'ai_subtitle' => 'Let AI handle the heavy lifting. Generate compelling content, optimize send times, and personalize at scale.',
            'ai_1_title' => 'AI Content Generation',
            'ai_1_description' => 'Generate engaging subject lines, email copy, and CTAs in seconds. Our AI understands your brand voice and creates content that converts.',
            'ai_2_title' => 'Smart Send Time Optimization',
            'ai_2_description' => 'AI analyzes subscriber behavior to determine the perfect send time for each recipient. Maximize opens and clicks automatically.',

            'ai_3_title' => 'Hyper-Personalization',
            'ai_3_description' => 'Go beyond {first_name}. AI creates unique content variations for each subscriber based on their preferences and past interactions.',
            'ai_4_title' => 'Predictive Analytics',
            'ai_4_description' => 'Predict which subscribers are likely to convert, churn, or engage. Take proactive action with AI-powered insights.',
            'ai_cta_text' => 'Try AI Features Free',
            'ai_cta_url' => route('register'),

            'testimonials_title' => 'Loved by marketers worldwide',
            'testimonials_subtitle' => 'See what our customers have to say about their experience.',
            'testimonial_1_quote' => '"' . config('app.name', 'MailZen') . " transformed our email marketing. We've seen a 40% increase in open rates since switching. The automation features are incredible!\"",
            'testimonial_1_name' => 'Sarah Johnson',
            'testimonial_1_role' => 'Marketing Director, TechCorp',
            'testimonial_1_initial' => 'S',
            'testimonial_2_quote' => '"The drag-and-drop editor is so intuitive. I can create professional emails in minutes. Best investment we\'ve made for our marketing stack."',
            'testimonial_2_name' => 'Michael Chen',
            'testimonial_2_role' => 'Founder, StartupXYZ',
            'testimonial_2_initial' => 'M',
            'testimonial_3_quote' => '"Customer support is outstanding. They helped us migrate from our old platform seamlessly. The analytics dashboard gives us insights we never had before."',
            'testimonial_3_name' => 'Emily Rodriguez',
            'testimonial_3_role' => 'CMO, GlobalCo',
            'testimonial_3_initial' => 'E',
        ];
    }

    private function getFaqDefaults(): array
    {
        return [
            'faq_title' => 'Frequently asked questions',
            'faq_subtitle' => 'Quick answers to common questions.',
            'faq_1_question' => 'What are the server requirements?',
            'faq_1_answer' => 'PHP 8.2+, MySQL database, and a web server (Apache/Nginx). Redis is recommended for queues. Works on shared hosting, VPS, or dedicated servers.',
            'faq_2_question' => 'Can I run this as a SaaS for my clients?',
            'faq_2_answer' => 'Absolutely! MailZen is built for multi-tenancy. Create customer accounts, define plans with limits, accept payments via Stripe/PayPal/Paystack, and let customers manage their own lists and campaigns.',
            'faq_3_question' => 'Which email providers are supported?',
            'faq_3_answer' => 'Amazon SES, Mailgun, SendGrid, Postmark, SparkPost, and any standard SMTP server. You can configure multiple providers and rotate between them for better deliverability.',
            'faq_4_question' => 'Is there a limit on subscribers or emails?',
            'faq_4_answer' => 'No limits from our side. You can send as many emails as your server and email provider allow. The only limits are what you define in your customer plans.',
            'faq_5_question' => 'Do I get updates and support?',
            'faq_5_answer' => 'Yes! Your purchase includes 6 months of free updates and support. After that, you can optionally renew for continued updates, or keep using your current version forever.',
            'faq_6_question' => '',
            'faq_6_answer' => '',
        ];
    }

    private function getPricingDefaults(): array
    {
        return [
            'pricing_badge' => 'Our Pricing',
            'pricing_title' => 'Choose Your Perfect Plan',
            'pricing_subtitle' => 'Pick the MailZen plan that fits your email marketing goals',
            'pricing_toggle_monthly' => 'Pay Monthly',
            'pricing_toggle_annual' => 'Pay Annually',
            'pricing_toggle_save' => '(save 20%)',
            'pricing_popular_badge' => 'Popular',
            'pricing_card_cta_text' => 'Get Started',
            'pricing_card_1_title' => 'Starter',
            'pricing_card_1_description' => 'For individuals, and early-stage startups',
            'pricing_card_1_cta_text' => 'Get Started',
            'pricing_card_2_title' => 'Growth',
            'pricing_card_2_description' => 'For individuals, and early-stage startups',
            'pricing_card_2_cta_text' => 'Get Started',
            'pricing_card_3_title' => 'Scale',
            'pricing_card_3_description' => 'For individuals, and early-stage startups',
            'pricing_card_3_cta_text' => 'Get Started',
            'pricing_compare_text' => 'Compare all plans',
        ];
    }

    private function getCtaDefaults(): array
    {
        return [
            'cta_badge' => 'One-time license. Self-hosted.',
            'cta_title' => 'Take control of your email marketing',
            'cta_subtitle' => 'Stop paying monthly fees. Own your platform, own your data, and scale without limits.',
            'cta_primary_text' => 'Get Started Free',
            'cta_primary_url' => route('register'),
            'cta_secondary_text' => 'View on CodeCanyon',
            'cta_secondary_url' => 'https://codecanyon.net/item/mailpurse-selfhosted-email-automation-marketing-saas/61213414',
            'cta_note' => '',
        ];
    }
}

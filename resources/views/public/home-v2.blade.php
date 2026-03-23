@extends('layouts.public')

@section('title', 'Email Marketing Platform')
@section('pageId', 'home-4')

@section('content')
@php
    try {
        $heroDescription = (string) \App\Models\Setting::get('home_4_hero_description', 'The all-in-one email marketing platform that helps you create stunning campaigns, automate your workflows, and grow your audience — without the complexity.');
        $heroScrollText = (string) \App\Models\Setting::get('home_4_hero_scroll_text', 'No credit card required · Free 14-day trial · Cancel anytime');
        $heroButtonText = (string) \App\Models\Setting::get('home_4_hero_button_text', 'Start Free Trial');
        $heroButtonType = (string) \App\Models\Setting::get('home_4_hero_button_type', 'link');
        $heroButtonUrl = (string) \App\Models\Setting::get('home_4_hero_button_url', route('register'));
        $heroImagePath = (string) \App\Models\Setting::get('home_4_hero_image', '');

        $heroBadge = (string) \App\Models\Setting::get('home_4_hero_badge', 'Trusted by 10,000+ businesses worldwide');
        $heroTitlePrefix = (string) \App\Models\Setting::get('home_4_hero_title_prefix', 'Send emails that');
        $heroTitleHighlight = (string) \App\Models\Setting::get('home_4_hero_title_highlight', 'convert');
        $heroSecondaryButtonText = (string) \App\Models\Setting::get('home_4_hero_secondary_button_text', 'Watch Demo');
        $heroSecondaryButtonUrl = (string) \App\Models\Setting::get('home_4_hero_secondary_button_url', route('pricing'));

        $stat1Value = (string) \App\Models\Setting::get('home_4_stat_1_value', '99.9%');
        $stat1Label = (string) \App\Models\Setting::get('home_4_stat_1_label', 'Delivery Rate');
        $stat2Value = (string) \App\Models\Setting::get('home_4_stat_2_value', '45%');
        $stat2Label = (string) \App\Models\Setting::get('home_4_stat_2_label', 'Avg. Open Rate');
        $stat3Value = (string) \App\Models\Setting::get('home_4_stat_3_value', '10M+');
        $stat3Label = (string) \App\Models\Setting::get('home_4_stat_3_label', 'Emails Sent');
        $stat4Value = (string) \App\Models\Setting::get('home_4_stat_4_value', '24/7');
        $stat4Label = (string) \App\Models\Setting::get('home_4_stat_4_label', 'Expert Support');

        $logosTitle = (string) \App\Models\Setting::get('home_4_logos_title', 'Trusted by leading companies around the world');

        $benefitsTitle = (string) \App\Models\Setting::get('home_4_benefits_title', 'Why businesses choose ' . config('app.name', 'MailZen'));
        $benefitsSubtitle = (string) \App\Models\Setting::get('home_4_benefits_subtitle', 'Everything you need to run successful email campaigns, all in one powerful platform.');
        $benefit1Title = (string) \App\Models\Setting::get('home_4_benefits_1_title', 'Lightning Fast Delivery');
        $benefit1Description = (string) \App\Models\Setting::get('home_4_benefits_1_description', 'Send millions of emails in minutes with our high-performance infrastructure. 99.9% delivery rate guaranteed.');
        $benefit2Title = (string) \App\Models\Setting::get('home_4_benefits_2_title', 'Drag & Drop Builder');
        $benefit2Description = (string) \App\Models\Setting::get('home_4_benefits_2_description', 'Create stunning emails without any coding. Our intuitive editor makes designing beautiful campaigns effortless.');
        $benefit3Title = (string) \App\Models\Setting::get('home_4_benefits_3_title', 'Advanced Analytics');
        $benefit3Description = (string) \App\Models\Setting::get('home_4_benefits_3_description', 'Track opens, clicks, and conversions in real-time. Make data-driven decisions to optimize your campaigns.');

        $aiBadge = (string) \App\Models\Setting::get('home_4_ai_badge', 'Powered by AI');
        $aiTitle = (string) \App\Models\Setting::get('home_4_ai_title', 'Supercharge your emails with');
        $aiTitleHighlight = (string) \App\Models\Setting::get('home_4_ai_title_highlight', 'artificial intelligence');
        $aiSubtitle = (string) \App\Models\Setting::get('home_4_ai_subtitle', 'Let AI handle the heavy lifting. Generate compelling content, optimize send times, and personalize at scale.');

        $ai1Title = (string) \App\Models\Setting::get('home_4_ai_1_title', 'AI Content Generation');
        $ai1Description = (string) \App\Models\Setting::get('home_4_ai_1_description', 'Generate engaging subject lines, email copy, and CTAs in seconds. Our AI understands your brand voice and creates content that converts.');
        $ai2Title = (string) \App\Models\Setting::get('home_4_ai_2_title', 'Smart Send Time Optimization');
        $ai2Description = (string) \App\Models\Setting::get('home_4_ai_2_description', 'AI analyzes subscriber behavior to determine the perfect send time for each recipient. Maximize opens and clicks automatically.');
        $ai3Title = (string) \App\Models\Setting::get('home_4_ai_3_title', 'Hyper-Personalization');
        $ai3Description = (string) \App\Models\Setting::get('home_4_ai_3_description', 'Go beyond {first_name}. AI creates unique content variations for each subscriber based on their preferences and past interactions.');
        $ai4Title = (string) \App\Models\Setting::get('home_4_ai_4_title', 'Predictive Analytics');
        $ai4Description = (string) \App\Models\Setting::get('home_4_ai_4_description', 'Predict which subscribers are likely to convert, churn, or engage. Take proactive action with AI-powered insights.');
        $aiCtaText = (string) \App\Models\Setting::get('home_4_ai_cta_text', 'Try AI Features Free');
        $aiCtaUrl = (string) \App\Models\Setting::get('home_4_ai_cta_url', route('register'));

        $featuresTitle = (string) \App\Models\Setting::get('home_4_features_title', 'Powerful features for modern marketers');
        $featuresSubtitle = (string) \App\Models\Setting::get('home_4_features_subtitle', "From list management to automation, we've got everything covered.");
        $featuresCtaText = (string) \App\Models\Setting::get('home_4_features_cta_text', 'View all features');
        $featuresCtaUrl = (string) \App\Models\Setting::get('home_4_features_cta_url', route('features'));

        $feature1Title = (string) \App\Models\Setting::get('home_4_features_1_title', 'Smart Segmentation');
        $feature1Description = (string) \App\Models\Setting::get('home_4_features_1_description', 'Target the right audience with powerful segmentation based on behavior, demographics, and custom fields.');
        $feature2Title = (string) \App\Models\Setting::get('home_4_features_2_title', 'Marketing Automation');
        $feature2Description = (string) \App\Models\Setting::get('home_4_features_2_description', 'Set up automated email sequences triggered by user actions. Nurture leads on autopilot.');
        $feature3Title = (string) \App\Models\Setting::get('home_4_features_3_title', 'Domain Authentication');
        $feature3Description = (string) \App\Models\Setting::get('home_4_features_3_description', 'Improve deliverability with SPF, DKIM, and DMARC. Keep your emails out of spam folders.');
        $feature4Title = (string) \App\Models\Setting::get('home_4_features_4_title', 'Template Library');
        $feature4Description = (string) \App\Models\Setting::get('home_4_features_4_description', 'Choose from hundreds of professionally designed templates. Customize them to match your brand.');
        $feature5Title = (string) \App\Models\Setting::get('home_4_features_5_title', 'Developer API');
        $feature5Description = (string) \App\Models\Setting::get('home_4_features_5_description', 'Integrate with your apps using our RESTful API. Send transactional emails programmatically.');
        $feature6Title = (string) \App\Models\Setting::get('home_4_features_6_title', 'Detailed Reports');
        $feature6Description = (string) \App\Models\Setting::get('home_4_features_6_description', 'Get comprehensive reports on campaign performance. Export data for further analysis.');

        $testimonialsTitle = (string) \App\Models\Setting::get('home_4_testimonials_title', 'Loved by marketers worldwide');
        $testimonialsSubtitle = (string) \App\Models\Setting::get('home_4_testimonials_subtitle', 'See what our customers have to say about their experience.');
        $testimonial1Quote = (string) \App\Models\Setting::get('home_4_testimonial_1_quote', '"' . config('app.name', 'MailZen') . " transformed our email marketing. We've seen a 40% increase in open rates since switching. The automation features are incredible!\"");
        $testimonial1Name = (string) \App\Models\Setting::get('home_4_testimonial_1_name', 'Sarah Johnson');
        $testimonial1Role = (string) \App\Models\Setting::get('home_4_testimonial_1_role', 'Marketing Director, TechCorp');
        $testimonial1Initial = (string) \App\Models\Setting::get('home_4_testimonial_1_initial', 'S');
        $testimonial2Quote = (string) \App\Models\Setting::get('home_4_testimonial_2_quote', '"The drag-and-drop editor is so intuitive. I can create professional emails in minutes. Best investment we\'ve made for our marketing stack."');
        $testimonial2Name = (string) \App\Models\Setting::get('home_4_testimonial_2_name', 'Michael Chen');
        $testimonial2Role = (string) \App\Models\Setting::get('home_4_testimonial_2_role', 'Founder, StartupXYZ');
        $testimonial2Initial = (string) \App\Models\Setting::get('home_4_testimonial_2_initial', 'M');
        $testimonial3Quote = (string) \App\Models\Setting::get('home_4_testimonial_3_quote', '"Customer support is outstanding. They helped us migrate from our old platform seamlessly. The analytics dashboard gives us insights we never had before."');
        $testimonial3Name = (string) \App\Models\Setting::get('home_4_testimonial_3_name', 'Emily Rodriguez');
        $testimonial3Role = (string) \App\Models\Setting::get('home_4_testimonial_3_role', 'CMO, GlobalCo');
        $testimonial3Initial = (string) \App\Models\Setting::get('home_4_testimonial_3_initial', 'E');
    } catch (\Throwable $e) {
        $heroDescription = 'The all-in-one email marketing platform that helps you create stunning campaigns, automate your workflows, and grow your audience — without the complexity.';
        $heroScrollText = 'No credit card required · Free 14-day trial · Cancel anytime';
        $heroButtonText = 'Start Free Trial';
        $heroButtonType = 'link';
        $heroButtonUrl = route('register');
        $heroImagePath = '';

        $heroBadge = 'Trusted by 10,000+ businesses worldwide';
        $heroTitlePrefix = 'Send emails that';
        $heroTitleHighlight = 'convert';
        $heroSecondaryButtonText = 'Watch Demo';
        $heroSecondaryButtonUrl = route('pricing');

        $stat1Value = '99.9%';
        $stat1Label = 'Delivery Rate';
        $stat2Value = '45%';
        $stat2Label = 'Avg. Open Rate';
        $stat3Value = '10M+';
        $stat3Label = 'Emails Sent';
        $stat4Value = '24/7';
        $stat4Label = 'Expert Support';

        $logosTitle = 'Trusted by leading companies around the world';

        $benefitsTitle = 'Why businesses choose ' . config('app.name', 'MailZen');
        $benefitsSubtitle = 'Everything you need to run successful email campaigns, all in one powerful platform.';
        $benefit1Title = 'Lightning Fast Delivery';
        $benefit1Description = 'Send millions of emails in minutes with our high-performance infrastructure. 99.9% delivery rate guaranteed.';
        $benefit2Title = 'Drag & Drop Builder';
        $benefit2Description = 'Create stunning emails without any coding. Our intuitive editor makes designing beautiful campaigns effortless.';
        $benefit3Title = 'Advanced Analytics';
        $benefit3Description = 'Track opens, clicks, and conversions in real-time. Make data-driven decisions to optimize your campaigns.';

        $aiBadge = 'Powered by AI';
        $aiTitle = 'Supercharge your emails with';
        $aiTitleHighlight = 'artificial intelligence';
        $aiSubtitle = 'Let AI handle the heavy lifting. Generate compelling content, optimize send times, and personalize at scale.';
        $ai1Title = 'AI Content Generation';
        $ai1Description = 'Generate engaging subject lines, email copy, and CTAs in seconds. Our AI understands your brand voice and creates content that converts.';
        $ai2Title = 'Smart Send Time Optimization';
        $ai2Description = 'AI analyzes subscriber behavior to determine the perfect send time for each recipient. Maximize opens and clicks automatically.';
        $ai3Title = 'Hyper-Personalization';
        $ai3Description = 'Go beyond {first_name}. AI creates unique content variations for each subscriber based on their preferences and past interactions.';
        $ai4Title = 'Predictive Analytics';
        $ai4Description = 'Predict which subscribers are likely to convert, churn, or engage. Take proactive action with AI-powered insights.';
        $aiCtaText = 'Try AI Features Free';
        $aiCtaUrl = route('register');

        $featuresTitle = 'Powerful features for modern marketers';
        $featuresSubtitle = "From list management to automation, we've got everything covered.";
        $featuresCtaText = 'View all features';
        $featuresCtaUrl = route('features');

        $feature1Title = 'Smart Segmentation';
        $feature1Description = 'Target the right audience with powerful segmentation based on behavior, demographics, and custom fields.';
        $feature2Title = 'Marketing Automation';
        $feature2Description = 'Set up automated email sequences triggered by user actions. Nurture leads on autopilot.';
        $feature3Title = 'Domain Authentication';
        $feature3Description = 'Improve deliverability with SPF, DKIM, and DMARC. Keep your emails out of spam folders.';
        $feature4Title = 'Template Library';
        $feature4Description = 'Choose from hundreds of professionally designed templates. Customize them to match your brand.';
        $feature5Title = 'Developer API';
        $feature5Description = 'Integrate with your apps using our RESTful API. Send transactional emails programmatically.';
        $feature6Title = 'Detailed Reports';
        $feature6Description = 'Get comprehensive reports on campaign performance. Export data for further analysis.';

        $testimonialsTitle = 'Loved by marketers worldwide';
        $testimonialsSubtitle = 'See what our customers have to say about their experience.';
        $testimonial1Quote = '"' . config('app.name', 'MailZen') . " transformed our email marketing. We've seen a 40% increase in open rates since switching. The automation features are incredible!\"";
        $testimonial1Name = 'Sarah Johnson';
        $testimonial1Role = 'Marketing Director, TechCorp';
        $testimonial1Initial = 'S';
        $testimonial2Quote = '"The drag-and-drop editor is so intuitive. I can create professional emails in minutes. Best investment we\'ve made for our marketing stack."';
        $testimonial2Name = 'Michael Chen';
        $testimonial2Role = 'Founder, StartupXYZ';
        $testimonial2Initial = 'M';
        $testimonial3Quote = '"Customer support is outstanding. They helped us migrate from our old platform seamlessly. The analytics dashboard gives us insights we never had before."';
        $testimonial3Name = 'Emily Rodriguez';
        $testimonial3Role = 'CMO, GlobalCo';
        $testimonial3Initial = 'E';
    }

    $brandingDisk = (string) config('filesystems.branding_disk', 'public');
    $heroImageUrl = (is_string($heroImagePath) && trim($heroImagePath) !== '')
        ? \Illuminate\Support\Facades\Storage::disk($brandingDisk)->url($heroImagePath)
        : null;

    $heroButtonUrl = is_string($heroButtonUrl) && trim($heroButtonUrl) !== '' ? $heroButtonUrl : route('register');
    $heroButtonType = in_array($heroButtonType, ['link', 'video'], true) ? $heroButtonType : 'link';

    $heroSecondaryButtonUrl = is_string($heroSecondaryButtonUrl) && trim($heroSecondaryButtonUrl) !== '' ? $heroSecondaryButtonUrl : route('pricing');
    $featuresCtaUrl = is_string($featuresCtaUrl) && trim($featuresCtaUrl) !== '' ? $featuresCtaUrl : route('features');
    $aiCtaUrl = is_string($aiCtaUrl) && trim($aiCtaUrl) !== '' ? $aiCtaUrl : route('register');

    try {
        $logoPaths = \App\Models\Setting::get('home_4_logos', []);
    } catch (\Throwable $e) {
        $logoPaths = [];
    }

    $logoPaths = is_array($logoPaths) ? $logoPaths : [];
    $logoUrls = [];
    foreach ($logoPaths as $p) {
        if (!is_string($p) || trim($p) === '') {
            continue;
        }
        $logoUrls[] = \Illuminate\Support\Facades\Storage::disk($brandingDisk)->url($p);
    }

    try {
        $faqTitle = (string) \App\Models\Setting::get('home_faq_title', 'Frequently asked questions');
        $faqSubtitle = (string) \App\Models\Setting::get('home_faq_subtitle', 'Quick answers to common questions.');
        $faq1Question = (string) \App\Models\Setting::get('home_faq_1_question', 'What are the server requirements?');
        $faq1Answer = (string) \App\Models\Setting::get('home_faq_1_answer', 'PHP 8.2+, MySQL database, and a web server (Apache/Nginx). Redis is recommended for queues. Works on shared hosting, VPS, or dedicated servers.');
        $faq2Question = (string) \App\Models\Setting::get('home_faq_2_question', 'Can I run this as a SaaS for my clients?');
        $faq2Answer = (string) \App\Models\Setting::get('home_faq_2_answer', 'Absolutely! MailZen is built for multi-tenancy. Create customer accounts, define plans with limits, accept payments via Stripe/PayPal/Paystack, and let customers manage their own lists and campaigns.');
        $faq3Question = (string) \App\Models\Setting::get('home_faq_3_question', 'Which email providers are supported?');
        $faq3Answer = (string) \App\Models\Setting::get('home_faq_3_answer', 'Amazon SES, Mailgun, SendGrid, Postmark, SparkPost, and any standard SMTP server. You can configure multiple providers and rotate between them for better deliverability.');
        $faq4Question = (string) \App\Models\Setting::get('home_faq_4_question', 'Is there a limit on subscribers or emails?');
        $faq4Answer = (string) \App\Models\Setting::get('home_faq_4_answer', 'No limits from our side. You can send as many emails as your server and email provider allow. The only limits are what you define in your customer plans.');
    } catch (\Throwable $e) {
        $faqTitle = 'Frequently asked questions';
        $faqSubtitle = 'Quick answers to common questions.';
        $faq1Question = 'What are the server requirements?';
        $faq1Answer = 'PHP 8.2+, MySQL database, and a web server (Apache/Nginx). Redis is recommended for queues. Works on shared hosting, VPS, or dedicated servers.';
        $faq2Question = 'Can I run this as a SaaS for my clients?';
        $faq2Answer = 'Absolutely! MailZen is built for multi-tenancy. Create customer accounts, define plans with limits, accept payments via Stripe/PayPal/Paystack, and let customers manage their own lists and campaigns.';
        $faq3Question = 'Which email providers are supported?';
        $faq3Answer = 'Amazon SES, Mailgun, SendGrid, Postmark, SparkPost, and any standard SMTP server. You can configure multiple providers and rotate between them for better deliverability.';
        $faq4Question = 'Is there a limit on subscribers or emails?';
        $faq4Answer = 'No limits from our side. You can send as many emails as your server and email provider allow. The only limits are what you define in your customer plans.';
    }

    try {
        $pricingTitle = (string) \App\Models\Setting::get('home_pricing_title', 'Simple, transparent pricing');
        $pricingSubtitle = (string) \App\Models\Setting::get('home_pricing_subtitle', "Start free, upgrade when you're ready. No hidden fees.");
        $pricingCompareText = (string) \App\Models\Setting::get('home_pricing_compare_text', 'Compare all plans');
        $pricingPopularBadge = (string) \App\Models\Setting::get('home_pricing_popular_badge', 'MOST POPULAR');
        $pricingCard1Title = (string) \App\Models\Setting::get('home_pricing_card_1_title', 'Starter');
        $pricingCard1Description = (string) \App\Models\Setting::get('home_pricing_card_1_description', 'Perfect for getting started');
        $pricingCard1CtaText = (string) \App\Models\Setting::get('home_pricing_card_1_cta_text', 'Get Started Free');
        $pricingCard2Title = (string) \App\Models\Setting::get('home_pricing_card_2_title', 'Professional');
        $pricingCard2Description = (string) \App\Models\Setting::get('home_pricing_card_2_description', 'For growing businesses');
        $pricingCard2CtaText = (string) \App\Models\Setting::get('home_pricing_card_2_cta_text', 'Start Free Trial');
        $pricingCard3Title = (string) \App\Models\Setting::get('home_pricing_card_3_title', 'Enterprise');
        $pricingCard3Description = (string) \App\Models\Setting::get('home_pricing_card_3_description', 'For large organizations');
        $pricingCard3CtaText = (string) \App\Models\Setting::get('home_pricing_card_3_cta_text', 'Contact Sales');

        $ctaTitle = (string) \App\Models\Setting::get('home_cta_title', 'Ready to grow your business?');
        $ctaSubtitle = (string) \App\Models\Setting::get('home_cta_subtitle', 'Join thousands of businesses already using MailZen to connect with their audience and drive results.');
        $ctaPrimaryText = (string) \App\Models\Setting::get('home_cta_primary_text', 'Start Your Free Trial');
        $ctaPrimaryUrl = (string) \App\Models\Setting::get('home_cta_primary_url', route('register'));
        $ctaSecondaryText = (string) \App\Models\Setting::get('home_cta_secondary_text', 'View Pricing');
        $ctaSecondaryUrl = (string) \App\Models\Setting::get('home_cta_secondary_url', route('pricing'));
        $ctaNote = (string) \App\Models\Setting::get('home_cta_note', 'No credit card required · Free 14-day trial · Cancel anytime');
    } catch (\Throwable $e) {
        $pricingTitle = 'Simple, transparent pricing';
        $pricingSubtitle = "Start free, upgrade when you're ready. No hidden fees.";
        $pricingCompareText = 'Compare all plans';
        $pricingPopularBadge = 'MOST POPULAR';
        $pricingCard1Title = 'Starter';
        $pricingCard1Description = 'Perfect for getting started';
        $pricingCard1CtaText = 'Get Started Free';
        $pricingCard2Title = 'Professional';
        $pricingCard2Description = 'For growing businesses';
        $pricingCard2CtaText = 'Start Free Trial';
        $pricingCard3Title = 'Enterprise';
        $pricingCard3Description = 'For large organizations';
        $pricingCard3CtaText = 'Contact Sales';

        $ctaTitle = 'Ready to grow your business?';
        $ctaSubtitle = 'Join thousands of businesses already using MailZen to connect with their audience and drive results.';
        $ctaPrimaryText = 'Start Your Free Trial';
        $ctaPrimaryUrl = route('register');
        $ctaSecondaryText = 'View Pricing';
        $ctaSecondaryUrl = route('pricing');
        $ctaNote = 'No credit card required · Free 14-day trial · Cancel anytime';
    }

    $ctaPrimaryUrl = is_string($ctaPrimaryUrl) && trim($ctaPrimaryUrl) !== '' ? $ctaPrimaryUrl : route('register');
    $ctaSecondaryUrl = is_string($ctaSecondaryUrl) && trim($ctaSecondaryUrl) !== '' ? $ctaSecondaryUrl : route('pricing');

    $pricingPlans = \App\Models\Plan::query()
        ->where('is_active', true)
        ->orderBy('price')
        ->limit(3)
        ->get();

    try {
        $pricingSectionTitle = (string) \App\Models\Setting::get('pricing_section_title', $pricingTitle);
        $pricingSectionSubtitle = (string) \App\Models\Setting::get('pricing_section_subtitle', $pricingSubtitle);
        $pricingSectionPopularBadge = (string) \App\Models\Setting::get('pricing_section_popular_badge', $pricingPopularBadge);
    } catch (\Throwable $e) {
        $pricingSectionTitle = $pricingTitle;
        $pricingSectionSubtitle = $pricingSubtitle;
        $pricingSectionPopularBadge = $pricingPopularBadge;
    }
@endphp

<!-- Hero Section -->
<section class="relative overflow-hidden bg-white dark:bg-gray-900">
    <!-- Grid Background -->
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#e5e7eb_1px,transparent_1px),linear-gradient(to_bottom,#e5e7eb_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#1f2937_1px,transparent_1px),linear-gradient(to_bottom,#1f2937_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_110%)]"></div>
    <!-- Gradient Overlays -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 h-[500px] w-[500px] rounded-full bg-primary-200/40 blur-3xl dark:bg-primary-500/10"></div>
        <div class="absolute -bottom-40 -left-40 h-[500px] w-[500px] rounded-full bg-indigo-200/40 blur-3xl dark:bg-indigo-500/10"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-24 lg:pt-32 lg:pb-32">
        <div class="text-center max-w-4xl mx-auto">
            <div class="inline-flex items-center gap-2 rounded-full bg-primary-100 dark:bg-primary-900/30 px-4 py-1.5 text-sm font-medium text-primary-700 dark:text-primary-300 mb-8">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                </span>
                {{ $heroBadge }}
            </div>
            <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-5xl lg:text-6xl">
                {{ $heroTitlePrefix }}
                <span class="relative whitespace-nowrap">
                    <svg aria-hidden="true" viewBox="0 0 418 42" class="absolute left-0 top-2/3 h-[0.58em] w-full fill-primary-300/70 dark:fill-primary-500/30" preserveAspectRatio="none"><path d="M203.371.916c-26.013-2.078-76.686 1.963-124.73 9.946L67.3 12.749C35.421 18.062 18.2 21.766 6.004 25.934 1.244 27.561.828 27.778.874 28.61c.07 1.214.828 1.121 9.595-1.176 9.072-2.377 17.15-3.92 39.246-7.496C123.565 7.986 157.869 4.492 195.942 5.046c7.461.108 19.25 1.696 19.17 2.582-.107 1.183-7.874 4.31-25.75 10.366-21.992 7.45-35.43 12.534-36.701 13.884-2.173 2.308-.202 4.407 4.442 4.734 2.654.187 3.263.157 15.593-.78 35.401-2.686 57.944-3.488 88.365-3.143 46.327.526 75.721 2.23 130.788 7.584 19.787 1.924 20.814 1.98 24.557 1.332l.066-.011c1.201-.203 1.53-1.825.399-2.335-2.911-1.31-4.893-1.604-22.048-3.261-57.509-5.556-87.871-7.36-132.059-7.842-23.239-.254-33.617-.116-50.627.674-11.629.54-42.371 2.494-46.696 2.967-2.359.259 8.133-3.625 26.504-9.81 23.239-7.825 27.934-10.149 28.304-14.005.417-4.348-3.529-6-16.878-7.066Z"></path></svg>
                    <span class="relative text-primary-600 dark:text-primary-400">{{ $heroTitleHighlight }}</span>
                </span>
            </h1>
            <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                {{ $heroDescription }}
            </p>
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ $heroButtonUrl }}" class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-primary-600 px-8 py-4 text-base font-semibold text-white shadow-lg shadow-primary-500/25 hover:bg-primary-700 hover:shadow-xl hover:shadow-primary-500/30 transition-all duration-200">
                    {{ $heroButtonText }}
                    @if($heroButtonType === 'video')
                        <svg class="ml-2 h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    @else
                        <svg class="ml-2 h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14" /><path d="m12 5 7 7-7 7" /></svg>
                    @endif
                </a>
                <a href="{{ $heroSecondaryButtonUrl }}" class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl border-2 border-gray-200 bg-white px-8 py-4 text-base font-semibold text-gray-900 hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700">
                    <svg class="mr-2 h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><polygon points="10 8 16 12 10 16 10 8" /></svg>
                    {{ $heroSecondaryButtonText }}
                </a>
            </div>
            @if(is_string($heroScrollText) && trim($heroScrollText) !== '')
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">{{ $heroScrollText }}</p>
            @endif
        </div>

        <!-- Hero Stats -->
        <div class="mt-16 grid grid-cols-2 gap-4 sm:grid-cols-4 max-w-3xl mx-auto">
            <div class="rounded-2xl bg-white/80 dark:bg-gray-800/50 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 p-6 text-center">
                <div class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $stat1Value }}</div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $stat1Label }}</div>
            </div>
            <div class="rounded-2xl bg-white/80 dark:bg-gray-800/50 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 p-6 text-center">
                <div class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $stat2Value }}</div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $stat2Label }}</div>
            </div>
            <div class="rounded-2xl bg-white/80 dark:bg-gray-800/50 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 p-6 text-center">
                <div class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $stat3Value }}</div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $stat3Label }}</div>
            </div>
            <div class="rounded-2xl bg-white/80 dark:bg-gray-800/50 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 p-6 text-center">
                <div class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $stat4Value }}</div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $stat4Label }}</div>
            </div>
        </div>

        @if(!empty($heroImageUrl))
            <div class="mt-12 max-w-5xl mx-auto">
                <img src="{{ $heroImageUrl }}" alt="" class="w-full rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-2xl">
            </div>
        @endif
    </div>
</section>

<!-- Logos / Social Proof -->
<section class="py-12 bg-gray-50 dark:bg-gray-800/50 border-y border-gray-200 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-center text-sm font-medium text-gray-500 dark:text-gray-400 mb-8">{{ $logosTitle }}</p>
        @if(isset($logoUrls) && count($logoUrls) > 0)
            <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-8 opacity-80">
                @foreach($logoUrls as $url)
                    <img src="{{ $url }}" alt="" class="h-10 w-auto">
                @endforeach
            </div>
        @else
            <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-8 opacity-70 grayscale hover:grayscale-0 transition-all duration-500">
                <!-- Stripe -->
                <svg class="h-8 w-auto text-gray-400" viewBox="0 0 60 25" fill="currentColor"><path d="M59.64 14.28h-8.06c.19 1.93 1.6 2.55 3.2 2.55 1.64 0 2.96-.37 4.05-.95v3.32a8.33 8.33 0 0 1-4.56 1.1c-4.01 0-6.83-2.5-6.83-7.48 0-4.19 2.39-7.52 6.3-7.52 3.92 0 5.96 3.28 5.96 7.5 0 .4-.02 1.04-.06 1.48zm-6.3-5.63c-1.03 0-1.87.73-2.1 2.4h4.2c-.07-1.59-.77-2.4-2.1-2.4zm-9.4-2.66h4.18v14h-4.18V6zm-4.17 14.28c-4.44 0-7.6-3.28-7.6-7.48 0-4.19 3.16-7.52 7.6-7.52 4.45 0 7.6 3.33 7.6 7.52 0 4.2-3.15 7.48-7.6 7.48zm0-11.12c-1.77 0-3.2 1.48-3.2 3.64 0 2.17 1.43 3.6 3.2 3.6 1.78 0 3.2-1.43 3.2-3.6 0-2.16-1.42-3.64-3.2-3.64zm-8.76-3.16h4.18v14H31V6zM13.94 20h4.18V6h-4.18v14zm-4.17 0H5.6V6h4.17v14zM0 6h4.18v14H0V6z"/></svg>
                <!-- Slack -->
                <svg class="h-8 w-auto text-gray-400" viewBox="0 0 54 54" fill="currentColor"><path d="M19.712.133a5.381 5.381 0 0 0-5.376 5.387 5.381 5.381 0 0 0 5.376 5.386h5.376V5.52A5.381 5.381 0 0 0 19.712.133m0 14.365H5.376A5.381 5.381 0 0 0 0 19.884a5.381 5.381 0 0 0 5.376 5.387h14.336a5.381 5.381 0 0 0 5.376-5.387 5.381 5.381 0 0 0-5.376-5.386"/><path d="M53.76 19.884a5.381 5.381 0 0 0-5.376-5.386 5.381 5.381 0 0 0-5.376 5.386v5.387h5.376a5.381 5.381 0 0 0 5.376-5.387m-14.336 0V5.52A5.381 5.381 0 0 0 34.048.133a5.381 5.381 0 0 0-5.376 5.387v14.364a5.381 5.381 0 0 0 5.376 5.387 5.381 5.381 0 0 0 5.376-5.387"/><path d="M34.048 54a5.381 5.381 0 0 0 5.376-5.387 5.381 5.381 0 0 0-5.376-5.386h-5.376v5.386A5.381 5.381 0 0 0 34.048 54m0-14.365h14.336a5.381 5.381 0 0 0 5.376-5.386 5.381 5.381 0 0 0-5.376-5.387H34.048a5.381 5.381 0 0 0-5.376 5.387 5.381 5.381 0 0 0 5.376 5.386"/><path d="M0 34.249a5.381 5.381 0 0 0 5.376 5.386 5.381 5.381 0 0 0 5.376-5.386v-5.387H5.376A5.381 5.381 0 0 0 0 34.25m14.336-.001v14.364A5.381 5.381 0 0 0 19.712 54a5.381 5.381 0 0 0 5.376-5.387V34.25a5.381 5.381 0 0 0-5.376-5.387 5.381 5.381 0 0 0-5.376 5.387"/></svg>
                <!-- Shopify -->
                <svg class="h-8 w-auto text-gray-400" viewBox="0 0 109 40" fill="currentColor"><path d="M25.517 8.26c-.064-.384-.32-.576-.576-.608-.256-.032-5.472-.384-5.472-.384s-3.648-3.584-4.032-3.968c-.384-.384-1.12-.256-1.408-.192-.032 0-.768.224-1.984.608-.192-.576-.448-1.28-.8-2.016C9.965.192 8.205 0 6.861 0 3.085 0 .573 2.816.125 6.944c-.576 5.312 3.296 8.128 3.296 8.128s-2.016 8.576-2.368 10.112c-.352 1.536.256 2.56 1.344 2.816 1.088.256 8.576 2.368 8.576 2.368s.544.128.928-.128c.384-.256.64-.736.64-.736l8.576-17.28s.256-.512.256-.864c0-.352-.256-.64-.256-.64s-3.584-2.688-3.584-4.352c0-1.664 1.152-2.816 2.88-2.816 1.728 0 2.88 1.088 2.88 2.816 0 1.728-1.408 2.624-1.408 2.624l1.632 1.28s2.304-1.472 2.304-4.128c0-2.656-1.856-4.8-5.312-4.8-3.456 0-6.016 2.496-6.016 5.76 0 3.264 2.112 4.992 2.112 4.992l-6.4 12.864-5.888-1.632 1.984-8.448s2.56-1.024 2.56-3.648c0-2.624-2.176-4.48-5.312-4.48S.125 9.376.125 12.64c0 3.264 2.368 5.312 5.568 5.312.64 0 1.216-.064 1.728-.192l-2.304 9.792 8.192 2.272 9.28-18.688 2.624 2.048.304-4.928z"/></svg>
                <!-- Vercel -->
                <svg class="h-6 w-auto text-gray-400" viewBox="0 0 76 65" fill="currentColor"><path d="M37.5274 0L75.0548 65H0L37.5274 0Z"/></svg>
                <!-- Notion -->
                <svg class="h-8 w-auto text-gray-400" viewBox="0 0 100 100" fill="currentColor"><path d="M6.017 4.313l55.333-4.087c6.797-.583 8.543-.19 12.817 2.917l17.663 12.443c2.913 2.14 3.883 2.723 3.883 5.053v68.243c0 4.277-1.553 6.807-6.99 7.193L24.467 99.967c-4.08.193-6.023-.39-8.16-3.113L3.3 79.94c-2.333-3.113-3.3-5.443-3.3-8.167V11.113c0-3.497 1.553-6.413 6.017-6.8z"/><path fill="#fff" d="M61.35 16.903l-46.1 3.41c-1.863.193-2.206.58-2.206 2.333v58.36c0 1.75.776 2.723 2.723 2.53l47.267-2.917c1.94-.193 2.333-1.167 2.333-2.723V19.24c0-1.553-.583-2.53-4.017-2.337zM52.5 28.84c.39 1.75 0 3.5-1.75 3.693l-1.943.39v43.26c-1.75.97-3.307.97-4.47.193-3.693-2.723-9.72-8.75-14.967-14.58l10.887 2.14v-25.48l-5.443.583c-.583-3.887 2.14-7.387 6.027-7.58l11.66-.62z"/></svg>
                <!-- Linear -->
                <svg class="h-7 w-auto text-gray-400" viewBox="0 0 100 100" fill="currentColor"><path d="M1.22541 61.5228c-.2225-.9485.90748-1.5459 1.59638-.857L39.3342 97.1782c.6889.6889.0915 1.8189-.857 1.5765C20.0515 94.4522 5.54779 79.9485 1.22541 61.5228ZM.00189135 46.8891c-.01764375.2833.08887215.5599.28957165.7606L52.3503 99.7085c.2007.2007.4773.3072.7606.2896 2.3692-.1476 4.6938-.46 6.9624-.9259.7645-.157 1.0301-1.0963.4782-1.6481L2.57595 39.4485c-.55186-.5765-1.49117-.2863-1.648174.4782-.465915 2.2686-.77832 4.5932-.92588465 6.9624ZM4.21093 29.7054c-.16649.3738-.08169.8106.20765 1.1l64.77602 64.776c.2894.2894.7262.3742 1.1.2077 1.7861-.7946 3.5171-1.6936 5.1855-2.684.5521-.328.6373-1.0867.1832-1.5765L8.43566 24.3367c-.48976-.4541-1.24843-.369-1.57645.1832-.99046 1.6684-1.88946 3.3994-2.68398 5.1855ZM12.6587 18.074c-.3701.3701-.393.9637-.0443 1.3541L74.5765 87.3858c.3904.3487.984.3258 1.3541-.0443 1.6758-1.6977 3.2444-3.5063 4.6949-5.4134.4227-.5556.3427-1.3428-.1518-1.8373L17.8894 17.5765c-.4945-.4945-1.2817-.5765-1.8373-.1518-1.9071 1.4505-3.7157 3.0191-5.3934 4.6493ZM24.5939 8.18208c-.4454.34343-.5765.96931-.2784 1.45166l58.052 58.05206c.4823.4823 1.1077.4937 1.4517.2784 1.7529-1.0963 3.4253-2.3098 5.0062-3.6324.4945-.4138.5765-1.1281.1713-1.6226L29.1202 2.83469c-.4945-.49446-1.2088-.32324-1.6226.17127-1.3226 1.58093-2.5765 3.25333-3.6324 5.00622l-.2713.17117ZM40.6148 1.02016c-.5765.24724-.7606.96931-.4231 1.50856L88.4715 59.8089c.5389.5389 1.2612.4823 1.5086-.4231.4231-1.5765.7606-3.1765 1.0117-4.8c.0941-.6089-.1882-1.2178-.6827-1.5765L32.2148.41935c-.3587-.49446-.9676-.77693-1.5765-.68273-1.6235.25106-3.2235.58858-4.8 1.01177l-.2235.27177ZM52.5765.00057629c-.3015-.01882629-.5765.09411401-.7606.28957671L99.7085 52.1826c.1955.1841.3084.4591.2896.7606-.0706 1.1346-.1882 2.2609-.3529 3.3765-.0706.4823-.5765.7371-.9882.5765L1.34345 9.58554c-.16059-.16059-.18824-.65765.57647-.98824C2.99947.282324 4.12729.094117 5.26182.023534 6.39635-.047765 7.54353-.011765 8.681...[36 bytes truncated]</path></svg>
            </div>
        @endif
    </div>
</section>

<!-- Benefits Section -->
<section class="py-20 lg:py-28 bg-white dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                {{ $benefitsTitle }}
            </h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">
                {{ $benefitsSubtitle }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="relative p-8 rounded-2xl bg-gradient-to-br from-primary-50 to-white dark:from-primary-900/20 dark:to-gray-900 border border-primary-100 dark:border-primary-800/30">
                <div class="w-12 h-12 rounded-xl bg-primary-600 flex items-center justify-center mb-6">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ $benefit1Title }}</h3>
                <p class="text-gray-600 dark:text-gray-300">{{ $benefit1Description }}</p>
            </div>

            <div class="relative p-8 rounded-2xl bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-900/20 dark:to-gray-900 border border-indigo-100 dark:border-indigo-800/30">
                <div class="w-12 h-12 rounded-xl bg-indigo-600 flex items-center justify-center mb-6">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ $benefit2Title }}</h3>
                <p class="text-gray-600 dark:text-gray-300">{{ $benefit2Description }}</p>
            </div>

            <div class="relative p-8 rounded-2xl bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-900/20 dark:to-gray-900 border border-emerald-100 dark:border-emerald-800/30">
                <div class="w-12 h-12 rounded-xl bg-emerald-600 flex items-center justify-center mb-6">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ $benefit3Title }}</h3>
                <p class="text-gray-600 dark:text-gray-300">{{ $benefit3Description }}</p>
            </div>
        </div>
    </div>
</section>

<!-- AI-Powered Section -->
<section class="py-20 lg:py-28 bg-gradient-to-b from-gray-50 to-white dark:from-gray-800/50 dark:to-gray-900 relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#e5e7eb_1px,transparent_1px),linear-gradient(to_bottom,#e5e7eb_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#1f2937_1px,transparent_1px),linear-gradient(to_bottom,#1f2937_1px,transparent_1px)] bg-[size:3rem_3rem] opacity-50"></div>
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-gradient-to-b from-violet-500/20 to-transparent blur-3xl dark:from-violet-500/10"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 rounded-full bg-violet-100 dark:bg-violet-900/30 px-4 py-1.5 text-sm font-medium text-violet-700 dark:text-violet-300 mb-6">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" /></svg>
                {{ $aiBadge }}
            </div>
            <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl lg:text-5xl">
                {{ $aiTitle }}
                <span class="bg-gradient-to-r from-violet-600 to-indigo-600 bg-clip-text text-transparent">{{ $aiTitleHighlight }}</span>
            </h2>
            <p class="mt-6 text-lg text-gray-600 dark:text-gray-300">
                {{ $aiSubtitle }}
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
            <!-- AI Feature 1 -->
            <div class="group relative p-8 rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-xl transition-all duration-300">
                <div class="absolute -inset-px rounded-3xl bg-gradient-to-r from-violet-500 to-indigo-500 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                <div class="relative">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center mb-6 shadow-lg shadow-violet-500/25">
                        <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ $ai1Title }}</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">{{ $ai1Description }}</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 rounded-full bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 text-xs font-medium">Subject Lines</span>
                        <span class="px-3 py-1 rounded-full bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 text-xs font-medium">Email Copy</span>
                        <span class="px-3 py-1 rounded-full bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 text-xs font-medium">CTAs</span>
                    </div>
                </div>
            </div>

            <!-- AI Feature 2 -->
            <div class="group relative p-8 rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-xl transition-all duration-300">
                <div class="absolute -inset-px rounded-3xl bg-gradient-to-r from-violet-500 to-indigo-500 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                <div class="relative">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mb-6 shadow-lg shadow-indigo-500/25">
                        <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ $ai2Title }}</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">{{ $ai2Description }}</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs font-medium">Behavior Analysis</span>
                        <span class="px-3 py-1 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs font-medium">Time Zones</span>
                        <span class="px-3 py-1 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs font-medium">Auto-Schedule</span>
                    </div>
                </div>
            </div>

            <!-- AI Feature 3 -->
            <div class="group relative p-8 rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-xl transition-all duration-300">
                <div class="absolute -inset-px rounded-3xl bg-gradient-to-r from-violet-500 to-indigo-500 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                <div class="relative">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center mb-6 shadow-lg shadow-purple-500/25">
                        <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ $ai3Title }}</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">{{ $ai3Description }}</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-xs font-medium">Dynamic Content</span>
                        <span class="px-3 py-1 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-xs font-medium">Product Recs</span>
                        <span class="px-3 py-1 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-xs font-medium">1:1 Messaging</span>
                    </div>
                </div>
            </div>

            <!-- AI Feature 4 -->
            <div class="group relative p-8 rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-xl transition-all duration-300">
                <div class="absolute -inset-px rounded-3xl bg-gradient-to-r from-violet-500 to-indigo-500 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                <div class="relative">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-pink-500 to-rose-600 flex items-center justify-center mb-6 shadow-lg shadow-pink-500/25">
                        <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ $ai4Title }}</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">{{ $ai4Description }}</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 rounded-full bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-300 text-xs font-medium">Churn Prediction</span>
                        <span class="px-3 py-1 rounded-full bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-300 text-xs font-medium">Lead Scoring</span>
                        <span class="px-3 py-1 rounded-full bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-300 text-xs font-medium">Engagement Forecast</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI CTA -->
        <div class="mt-16 text-center">
            <a href="{{ $aiCtaUrl }}" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 px-8 py-4 text-base font-semibold text-white shadow-lg shadow-violet-500/25 hover:shadow-xl hover:shadow-violet-500/30 transition-all duration-200">
                {{ $aiCtaText }}
                <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
            </a>
        </div>
    </div>
</section>

<!-- Features Grid -->
<section class="py-20 lg:py-28 bg-gray-50 dark:bg-gray-800/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                {{ $featuresTitle }}
            </h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">
                {{ $featuresSubtitle }}
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Feature 1 -->
            <div class="group relative overflow-hidden p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 hover:shadow-lg hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-200">
                <div aria-hidden="true" class="pointer-events-none absolute -inset-6 opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-[radial-gradient(circle_at_top,rgba(var(--brand-rgb),0.22),transparent_60%)] blur-2xl"></div>
                <div class="relative z-10">
                    <div class="w-10 h-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center mb-4">
                        <svg class="h-5 w-5 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $feature1Title }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $feature1Description }}</p>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="group relative overflow-hidden p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 hover:shadow-lg hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-200">
                <div aria-hidden="true" class="pointer-events-none absolute -inset-6 opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-[radial-gradient(circle_at_top,rgba(var(--brand-rgb),0.22),transparent_60%)] blur-2xl"></div>
                <div class="relative z-10">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center mb-4">
                        <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $feature2Title }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $feature2Description }}</p>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="group relative overflow-hidden p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 hover:shadow-lg hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-200">
                <div aria-hidden="true" class="pointer-events-none absolute -inset-6 opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-[radial-gradient(circle_at_top,rgba(var(--brand-rgb),0.22),transparent_60%)] blur-2xl"></div>
                <div class="relative z-10">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center mb-4">
                        <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $feature3Title }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $feature3Description }}</p>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="group relative overflow-hidden p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 hover:shadow-lg hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-200">
                <div aria-hidden="true" class="pointer-events-none absolute -inset-6 opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-[radial-gradient(circle_at_top,rgba(var(--brand-rgb),0.22),transparent_60%)] blur-2xl"></div>
                <div class="relative z-10">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mb-4">
                        <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" /></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $feature4Title }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $feature4Description }}</p>
                </div>
            </div>

            <!-- Feature 5 -->
            <div class="group relative overflow-hidden p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 hover:shadow-lg hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-200">
                <div aria-hidden="true" class="pointer-events-none absolute -inset-6 opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-[radial-gradient(circle_at_top,rgba(var(--brand-rgb),0.22),transparent_60%)] blur-2xl"></div>
                <div class="relative z-10">
                    <div class="w-10 h-10 rounded-lg bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center mb-4">
                        <svg class="h-5 w-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $feature5Title }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $feature5Description }}</p>
                </div>
            </div>

            <!-- Feature 6 -->
            <div class="group relative overflow-hidden p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 hover:shadow-lg hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-200">
                <div aria-hidden="true" class="pointer-events-none absolute -inset-6 opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-[radial-gradient(circle_at_top,rgba(var(--brand-rgb),0.22),transparent_60%)] blur-2xl"></div>
                <div class="relative z-10">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mb-4">
                        <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $feature6Title }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $feature6Description }}</p>
                </div>
            </div>
        </div>

        <div class="mt-12 text-center">
            <a href="{{ $featuresCtaUrl }}" class="inline-flex items-center text-primary-600 dark:text-primary-400 font-semibold hover:text-primary-700 dark:hover:text-primary-300">
                {{ $featuresCtaText }}
                <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </a>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-20 lg:py-28 bg-white dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                {{ $testimonialsTitle }}
            </h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">
                {{ $testimonialsSubtitle }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-8 rounded-2xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-1 mb-4">
                    @for($i = 0; $i < 5; $i++)
                    <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <p class="text-gray-600 dark:text-gray-300 mb-6">{{ $testimonial1Quote }}</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold">{{ $testimonial1Initial }}</div>
                    <div>
                        <div class="font-semibold text-gray-900 dark:text-white">{{ $testimonial1Name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $testimonial1Role }}</div>
                    </div>
                </div>
            </div>

            <div class="p-8 rounded-2xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-1 mb-4">
                    @for($i = 0; $i < 5; $i++)
                    <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <p class="text-gray-600 dark:text-gray-300 mb-6">{{ $testimonial2Quote }}</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">{{ $testimonial2Initial }}</div>
                    <div>
                        <div class="font-semibold text-gray-900 dark:text-white">{{ $testimonial2Name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $testimonial2Role }}</div>
                    </div>
                </div>
            </div>

            <div class="p-8 rounded-2xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-1 mb-4">
                    @for($i = 0; $i < 5; $i++)
                    <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <p class="text-gray-600 dark:text-gray-300 mb-6">{{ $testimonial3Quote }}</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold">{{ $testimonial3Initial }}</div>
                    <div>
                        <div class="font-semibold text-gray-900 dark:text-white">{{ $testimonial3Name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $testimonial3Role }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Preview -->
<section class="py-20 lg:py-28 bg-gradient-to-b from-gray-50 to-white dark:from-gray-800/50 dark:to-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                {{ $pricingSectionTitle }}
            </h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">
                {{ $pricingSectionSubtitle }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            @foreach($pricingPlans as $idx => $plan)
                @php
                    $isFeatured = ($plan->is_popular ?? false) === true;
                    if (!$isFeatured && $pricingPlans->where('is_popular', true)->count() === 0) {
                        $isFeatured = $idx === 1;
                    }
                    $cycle = $plan->billing_cycle === 'yearly' ? 'year' : 'month';
                    $ctaText = is_string($plan->cta_text ?? null) && trim((string) $plan->cta_text) !== '' ? (string) $plan->cta_text : 'Get Started';
                    $planFeatures = $plan->features ?? null;
                    $pros = [];
                    $cons = [];
                    if (is_array($planFeatures) && (array_key_exists('pros', $planFeatures) || array_key_exists('cons', $planFeatures))) {
                        $pros = is_array($planFeatures['pros'] ?? null) ? $planFeatures['pros'] : [];
                        $cons = is_array($planFeatures['cons'] ?? null) ? $planFeatures['cons'] : [];
                    } elseif (is_array($planFeatures)) {
                        $pros = $planFeatures;
                    }
                @endphp

                @if($isFeatured)
                    <div class="p-8 rounded-2xl bg-primary-600 text-white relative shadow-[0_0_0_2px_rgba(251,191,36,0.60),0_0_45px_rgba(251,191,36,0.35)]">
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 bg-amber-400 text-amber-900 text-xs font-bold rounded-full">{{ $pricingSectionPopularBadge }}</div>
                        <h3 class="text-lg font-bold">{{ $plan->name }}</h3>
                        <p class="mt-2 text-sm text-primary-100">{{ $plan->description }}</p>
                        <div class="mt-6">
                            <span class="text-4xl font-extrabold"><span class="text-sm font-semibold align-top">{{ $plan->currency }}</span> {{ number_format((float) $plan->price, 2) }}</span>
                            <span class="text-primary-200">/{{ $cycle }}</span>
                        </div>
                        <div class="mt-8">
                            <div class="text-sm font-semibold mb-4">{{ __('Features') }}</div>
                            <ul class="space-y-3">
                                @foreach(array_slice($pros, 0, 4) as $f)
                                    @if(is_string($f) && trim($f) !== '')
                                        <li class="flex items-center gap-3 text-sm">
                                            <svg class="h-5 w-5 text-primary-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                            {{ $f }}
                                        </li>
                                    @endif
                                @endforeach

                                @foreach(array_slice($cons, 0, 4) as $f)
                                    @if(is_string($f) && trim($f) !== '')
                                        <li class="flex items-center gap-3 text-sm">
                                            <svg class="h-5 w-5 text-primary-200/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                            {{ $f }}
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                        <a href="{{ route('pricing') }}" class="mt-8 block w-full text-center py-3 px-4 rounded-xl bg-white text-primary-600 font-semibold hover:bg-primary-50 transition-colors">
                            {{ $ctaText }}
                        </a>
                    </div>
                @else
                    <div class="p-8 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $plan->description }}</p>
                        <div class="mt-6">
                            <span class="text-4xl font-extrabold text-gray-900 dark:text-white"><span class="text-sm font-semibold align-top">{{ $plan->currency }}</span> {{ number_format((float) $plan->price, 2) }}</span>
                            <span class="text-gray-500 dark:text-gray-400">/{{ $cycle }}</span>
                        </div>
                        <div class="mt-8">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white mb-4">{{ __('Features') }}</div>
                            <ul class="space-y-3">
                                @foreach(array_slice($pros, 0, 4) as $f)
                                    @if(is_string($f) && trim($f) !== '')
                                        <li class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
                                            <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                            {{ $f }}
                                        </li>
                                    @endif
                                @endforeach

                                @foreach(array_slice($cons, 0, 4) as $f)
                                    @if(is_string($f) && trim($f) !== '')
                                        <li class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                            {{ $f }}
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                        <a href="{{ route('pricing') }}" class="mt-8 block w-full text-center py-3 px-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 font-semibold text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            {{ $ctaText }}
                        </a>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-12 text-center">
            <a href="{{ route('pricing') }}" class="inline-flex items-center text-primary-600 dark:text-primary-400 font-semibold hover:text-primary-700 dark:hover:text-primary-300">
                {{ $pricingCompareText }}
                <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </a>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-20 lg:py-28 bg-white dark:bg-gray-900">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                {{ $faqTitle }}
            </h2>
            @if(is_string($faqSubtitle) && trim($faqSubtitle) !== '')
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">{{ $faqSubtitle }}</p>
            @endif
        </div>

        <div class="space-y-4" x-data="{ open: null }">
            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <button @click="open = open === 1 ? null : 1" class="w-full px-6 py-4 text-left flex items-center justify-between bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $faq1Question }}</span>
                    <svg class="h-5 w-5 text-gray-500 transition-transform" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div x-show="open === 1" x-collapse class="px-6 pb-4 text-gray-600 dark:text-gray-300">
                    {{ $faq1Answer }}
                </div>
            </div>

            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <button @click="open = open === 2 ? null : 2" class="w-full px-6 py-4 text-left flex items-center justify-between bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $faq2Question }}</span>
                    <svg class="h-5 w-5 text-gray-500 transition-transform" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div x-show="open === 2" x-collapse class="px-6 pb-4 text-gray-600 dark:text-gray-300">
                    {{ $faq2Answer }}
                </div>
            </div>

            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <button @click="open = open === 3 ? null : 3" class="w-full px-6 py-4 text-left flex items-center justify-between bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $faq3Question }}</span>
                    <svg class="h-5 w-5 text-gray-500 transition-transform" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div x-show="open === 3" x-collapse class="px-6 pb-4 text-gray-600 dark:text-gray-300">
                    {{ $faq3Answer }}
                </div>
            </div>

            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <button @click="open = open === 4 ? null : 4" class="w-full px-6 py-4 text-left flex items-center justify-between bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $faq4Question }}</span>
                    <svg class="h-5 w-5 text-gray-500 transition-transform" :class="{ 'rotate-180': open === 4 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div x-show="open === 4" x-collapse class="px-6 pb-4 text-gray-600 dark:text-gray-300">
                    {{ $faq4Answer }}
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Final CTA -->
<section class="relative py-20 lg:py-28 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-r from-primary-600 to-indigo-600"></div>
    <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\"30\" height=\"30\" viewBox=\"0 0 30 30\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cpath d=\"M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z\" fill=\"rgba(255,255,255,0.07)\"%3E%3C/path%3E%3C/svg%3E')] opacity-50"></div>
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl lg:text-5xl">
            {{ $ctaTitle }}
        </h2>
        <p class="mt-6 text-xl text-primary-100 max-w-2xl mx-auto">
            {{ $ctaSubtitle }}
        </p>
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ $ctaPrimaryUrl }}" class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-white px-8 py-4 text-base font-semibold text-primary-600 shadow-lg hover:bg-primary-50 transition-all duration-200">
                {{ $ctaPrimaryText }}
                <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </a>
            <a href="{{ $ctaSecondaryUrl }}" class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl border-2 border-white/30 px-8 py-4 text-base font-semibold text-white hover:bg-white/10 transition-all duration-200">
                {{ $ctaSecondaryText }}
            </a>
        </div>
        @if(is_string($ctaNote) && trim($ctaNote) !== '')
            <p class="mt-6 text-sm text-primary-200">{{ $ctaNote }}</p>
        @endif
    </div>
</section>
@endsection

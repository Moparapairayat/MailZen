<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\SubscriptionForm;
use App\Models\EmailList;
use App\Models\ListSubscriber;
use App\Services\ListSubscriberService;
use App\Services\EmailVerificationService;
use Illuminate\Http\Request;
use App\Notifications\NewSubscriberNotification;
use App\Notifications\SubscriberUnsubscribedNotification;
use Illuminate\Support\Facades\Log;

class PublicSubscriptionController extends Controller
{
    public function __construct(
        protected ListSubscriberService $listSubscriberService
    ) {}

    /**
     * Display the subscription form (embedded/popup).
     */
    public function show(string $slug)
    {
        $form = SubscriptionForm::where('slug', $slug)
            ->where('is_active', true)
            ->with('emailList')
            ->firstOrFail();

        return view('public.subscribe', compact('form'));
    }

    public function popupScript(string $slug)
    {
        $form = SubscriptionForm::where('slug', $slug)
            ->where('is_active', true)
            ->with('emailList')
            ->firstOrFail();

        $settings = is_array($form->settings) ? $form->settings : [];
        $delaySeconds = (int) ($settings['popup_delay_seconds'] ?? 5);
        if ($delaySeconds < 0) {
            $delaySeconds = 0;
        }
        $showOnce = (bool) ($settings['popup_show_once'] ?? false);

        $popupWidth = (int) ($settings['popup_width'] ?? 600);
        $popupHeight = (int) ($settings['popup_height'] ?? 420);
        $popupBg = (string) ($settings['popup_bg_color'] ?? '#ffffff');
        $popupOverlay = (string) ($settings['popup_overlay_color'] ?? '#000000');

        if ($popupWidth < 200) {
            $popupWidth = 200;
        }
        if ($popupWidth > 1400) {
            $popupWidth = 1400;
        }
        if ($popupHeight < 200) {
            $popupHeight = 200;
        }
        if ($popupHeight > 1400) {
            $popupHeight = 1400;
        }

        $slugJson = json_encode((string) $form->slug, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $embedUrl = url('/subscribe/' . $form->slug) . '?embed=1';
        $embedUrlJson = json_encode((string) $embedUrl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $delayJson = json_encode($delaySeconds);
        $showOnceJson = json_encode($showOnce);

        $widthJson = json_encode($popupWidth);
        $heightJson = json_encode($popupHeight);
        $bgJson = json_encode($popupBg, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $overlayJson = json_encode($popupOverlay, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $js = "(function(){\n" .
            "  var slug = {$slugJson};\n" .
            "  var embedUrl = {$embedUrlJson};\n" .
            "  var defaultDelay = {$delayJson};\n" .
            "  var defaultShowOnce = {$showOnceJson};\n" .
            "  var defaultWidth = {$widthJson};\n" .
            "  var defaultHeight = {$heightJson};\n" .
            "  var defaultBg = {$bgJson};\n" .
            "  var defaultOverlay = {$overlayJson};\n" .
            "\n" .
            "  function getCurrentScript(){\n" .
            "    return document.currentScript || (function(){\n" .
            "      var scripts = document.getElementsByTagName('script');\n" .
            "      return scripts[scripts.length - 1];\n" .
            "    })();\n" .
            "  }\n" .
            "\n" .
            "  function closePopup(){\n" .
            "    var container = document.getElementById('mailpurse-popup-container');\n" .
            "    if (container) container.remove();\n" .
            "  }\n" .
            "\n" .
            "  function openPopup(opts){\n" .
            "    opts = opts || {};\n" .
            "    closePopup();\n" .
            "\n" .
            "    var width = typeof opts.width === 'number' ? opts.width : defaultWidth;\n" .
            "    var height = typeof opts.height === 'number' ? opts.height : defaultHeight;\n" .
            "    var bg = typeof opts.bg === 'string' ? opts.bg : defaultBg;\n" .
            "    var overlay = typeof opts.overlay === 'string' ? opts.overlay : defaultOverlay;\n" .
            "\n" .
            "    var container = document.createElement('div');\n" .
            "    container.id = 'mailpurse-popup-container';\n" .
            "    container.style.position = 'fixed';\n" .
            "    container.style.inset = '0';\n" .
            "    container.style.zIndex = '999999';\n" .
            "\n" .
            "    var overlayEl = document.createElement('div');\n" .
            "    overlayEl.style.position = 'absolute';\n" .
            "    overlayEl.style.inset = '0';\n" .
            "    overlayEl.style.background = overlay;\n" .
            "    overlayEl.style.opacity = '0.6';\n" .
            "    overlayEl.addEventListener('click', closePopup);\n" .
            "\n" .
            "    var modalWrap = document.createElement('div');\n" .
            "    modalWrap.style.position = 'absolute';\n" .
            "    modalWrap.style.inset = '0';\n" .
            "    modalWrap.style.display = 'flex';\n" .
            "    modalWrap.style.alignItems = 'center';\n" .
            "    modalWrap.style.justifyContent = 'center';\n" .
            "    modalWrap.style.padding = '24px';\n" .
            "\n" .
            "    var modal = document.createElement('div');\n" .
            "    modal.style.position = 'relative';\n" .
            "    modal.style.width = width + 'px';\n" .
            "    modal.style.maxWidth = 'calc(100vw - 48px)';\n" .
            "    modal.style.height = height + 'px';\n" .
            "    modal.style.maxHeight = 'calc(100vh - 48px)';\n" .
            "    modal.style.background = bg;\n" .
            "    modal.style.borderRadius = '14px';\n" .
            "    modal.style.boxShadow = '0 25px 50px -12px rgba(0,0,0,0.25)';\n" .
            "    modal.style.overflow = 'hidden';\n" .
            "\n" .
            "    var closeBtn = document.createElement('button');\n" .
            "    closeBtn.type = 'button';\n" .
            "    closeBtn.textContent = '×';\n" .
            "    closeBtn.setAttribute('aria-label', 'Close');\n" .
            "    closeBtn.style.position = 'absolute';\n" .
            "    closeBtn.style.top = '8px';\n" .
            "    closeBtn.style.right = '12px';\n" .
            "    closeBtn.style.zIndex = '2';\n" .
            "    closeBtn.style.fontSize = '26px';\n" .
            "    closeBtn.style.lineHeight = '26px';\n" .
            "    closeBtn.style.background = 'transparent';\n" .
            "    closeBtn.style.border = '0';\n" .
            "    closeBtn.style.cursor = 'pointer';\n" .
            "    closeBtn.addEventListener('click', closePopup);\n" .
            "\n" .
            "    var iframe = document.createElement('iframe');\n" .
            "    iframe.src = embedUrl;\n" .
            "    iframe.style.border = '0';\n" .
            "    iframe.style.width = '100%';\n" .
            "    iframe.style.height = '100%';\n" .
            "    iframe.style.background = 'transparent';\n" .
            "\n" .
            "    modal.appendChild(closeBtn);\n" .
            "    modal.appendChild(iframe);\n" .
            "    modalWrap.appendChild(modal);\n" .
            "\n" .
            "    container.appendChild(overlayEl);\n" .
            "    container.appendChild(modalWrap);\n" .
            "\n" .
            "    document.body.appendChild(container);\n" .
            "\n" .
            "    document.addEventListener('keydown', function onKey(e){\n" .
            "      if (e.key === 'Escape') { closePopup(); document.removeEventListener('keydown', onKey); }\n" .
            "    });\n" .
            "  }\n" .
            "\n" .
            "  function shouldShow(showOnce){\n" .
            "    if (!showOnce) return true;\n" .
            "    try {\n" .
            "      return !localStorage.getItem('mailpurse_popup_shown_' + slug);\n" .
            "    } catch (e) {\n" .
            "      return true;\n" .
            "    }\n" .
            "  }\n" .
            "\n" .
            "  function markShown(showOnce){\n" .
            "    if (!showOnce) return;\n" .
            "    try { localStorage.setItem('mailpurse_popup_shown_' + slug, '1'); } catch (e) {}\n" .
            "  }\n" .
            "\n" .
            "  function parseIntSafe(v){\n" .
            "    var n = parseInt(v, 10);\n" .
            "    return isNaN(n) ? null : n;\n" .
            "  }\n" .
            "\n" .
            "  function boot(){\n" .
            "    var s = getCurrentScript();\n" .
            "    var delay = defaultDelay;\n" .
            "    var showOnce = defaultShowOnce;\n" .
            "    var width = defaultWidth;\n" .
            "    var height = defaultHeight;\n" .
            "    var bg = defaultBg;\n" .
            "    var overlay = defaultOverlay;\n" .
            "\n" .
            "    if (s) {\n" .
            "      var d = parseIntSafe(s.getAttribute('data-delay'));\n" .
            "      if (d !== null) delay = d;\n" .
            "      var so = s.getAttribute('data-show-once');\n" .
            "      if (so === '1' || so === 'true') showOnce = true;\n" .
            "      if (so === '0' || so === 'false') showOnce = false;\n" .
            "      var w = parseIntSafe(s.getAttribute('data-width'));\n" .
            "      if (w !== null) width = w;\n" .
            "      var h = parseIntSafe(s.getAttribute('data-height'));\n" .
            "      if (h !== null) height = h;\n" .
            "      var bgAttr = s.getAttribute('data-bg');\n" .
            "      if (bgAttr) bg = bgAttr;\n" .
            "      var ovAttr = s.getAttribute('data-overlay');\n" .
            "      if (ovAttr) overlay = ovAttr;\n" .
            "    }\n" .
            "\n" .
            "    if (delay < 0) delay = 0;\n" .
            "    if (!shouldShow(showOnce)) return;\n" .
            "\n" .
            "    setTimeout(function(){\n" .
            "      if (!shouldShow(showOnce)) return;\n" .
            "      openPopup({ width: width, height: height, bg: bg, overlay: overlay });\n" .
            "      markShown(showOnce);\n" .
            "    }, delay * 1000);\n" .
            "  }\n" .
            "\n" .
            "  if (document.readyState === 'loading') {\n" .
            "    document.addEventListener('DOMContentLoaded', boot);\n" .
            "  } else {\n" .
            "    boot();\n" .
            "  }\n" .
            "})();\n";

        return response($js, 200)
            ->header('Content-Type', 'application/javascript; charset=UTF-8')
            ->header('Cache-Control', 'no-store, max-age=0');
    }

    /**
     * Handle subscription form submission (API).
     */
    public function subscribe(Request $request, string $slug)
    {
        $form = SubscriptionForm::where('slug', $slug)
            ->where('is_active', true)
            ->with('emailList')
            ->firstOrFail();

        $selectedFields = $form->fields;
        if (!is_array($selectedFields) || count($selectedFields) === 0) {
            $selectedFields = ['email'];
        }

        if (!in_array('email', $selectedFields, true)) {
            $selectedFields[] = 'email';
        }

        $customDefs = $form->emailList->custom_fields;
        if (!is_array($customDefs)) {
            $customDefs = [];
        }

        $customDefsByKey = [];
        foreach ($customDefs as $def) {
            if (!is_array($def)) {
                continue;
            }
            $key = isset($def['key']) ? trim((string) $def['key']) : '';
            if ($key === '') {
                continue;
            }
            $customDefsByKey[$key] = $def;
        }

        $designRequiredFields = [];
        $htmlForRules = is_string($form->html_content) ? $form->html_content : '';
        if ($htmlForRules !== '') {
            $matches = [];
            preg_match_all('/data-mailpurse-(field|textarea)=["\']([^"\']+)["\'][^>]*data-mailpurse-required=["\']1["\']/i', $htmlForRules, $matches, PREG_SET_ORDER);
            foreach ($matches as $m) {
                $fieldKey = isset($m[2]) ? trim((string) $m[2]) : '';
                if ($fieldKey !== '') {
                    $designRequiredFields[] = $fieldKey;
                }
            }
        }
        $designRequiredFields = array_values(array_unique($designRequiredFields));

        // Check if this is an API request (from /api endpoint)
        $isApiRequest = $request->route()->getName() === 'public.subscribe.api' || 
                       $request->is('subscribe/*/api') ||
                       $request->wantsJson() ||
                       $request->expectsJson();

        try {
            $rules = [
                'email' => ['required', 'email', 'max:255'],
            ];

            if (in_array('first_name', $selectedFields, true)) {
                $rules['first_name'] = [
                    in_array('first_name', $designRequiredFields, true) ? 'required' : 'nullable',
                    'string',
                    'max:255',
                ];
            }
            if (in_array('last_name', $selectedFields, true)) {
                $rules['last_name'] = [
                    in_array('last_name', $designRequiredFields, true) ? 'required' : 'nullable',
                    'string',
                    'max:255',
                ];
            }

            foreach ($selectedFields as $field) {
                if (!is_string($field)) {
                    continue;
                }
                if (!str_starts_with($field, 'cf:')) {
                    continue;
                }
                $key = trim(substr($field, 3));
                if ($key === '' || !isset($customDefsByKey[$key])) {
                    continue;
                }
                $def = $customDefsByKey[$key];
                $required = (bool) ($def['required'] ?? false);
                if (in_array('cf:' . $key, $designRequiredFields, true)) {
                    $required = true;
                }

                $fieldName = 'cf_' . $key;
                $rules[$fieldName] = array_values(array_filter([
                    $required ? 'required' : 'nullable',
                    'string',
                    'max:2000',
                ]));
            }

            if ($form->gdpr_checkbox) {
                $rules['gdpr_consent'] = ['accepted'];
            }

            $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($isApiRequest) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                    'status' => 'error'
                ], 422);
            }
            throw $e;
        }

        $customValues = [];
        foreach ($selectedFields as $field) {
            if (!is_string($field) || !str_starts_with($field, 'cf:')) {
                continue;
            }
            $key = trim(substr($field, 3));
            if ($key === '' || !isset($customDefsByKey[$key])) {
                continue;
            }

            $inputName = 'cf_' . $key;
            $val = $request->input($inputName);
            if (is_string($val)) {
                $val = trim($val);
            }
            if ($val === null || $val === '') {
                $customValues[$key] = null;
            } else {
                $customValues[$key] = $val;
            }
        }

        $normalizedEmail = strtolower(trim($validated['email']));

        // Check if subscriber already exists (including soft-deleted)
        $existing = $form->emailList->subscribers()
            ->withTrashed()
            ->where('email', $normalizedEmail)
            ->first();

        $wasCreated = false;

        if ($existing) {
            $shouldResubscribe = (method_exists($existing, 'trashed') && $existing->trashed())
                || $existing->status === 'unsubscribed';

            if ($existing->status === 'blacklisted') {
                if ($isApiRequest) {
                    return response()->json([
                        'message' => 'This email address cannot be subscribed to this list.',
                        'status' => 'blocked'
                    ], 403);
                }

                return back()->with('error', 'This email address cannot be subscribed to this list.');
            }

            if (!$shouldResubscribe) {
                if ($isApiRequest) {
                    return response()->json([
                        'message' => 'You are already subscribed to this list.',
                        'status' => 'exists'
                    ], 200);
                }

                return back()->with('error', 'You are already subscribed to this list.');
            }

            $subscriber = $this->listSubscriberService->resubscribe($form->emailList, $existing, [
                'email' => $normalizedEmail,
                'first_name' => $validated['first_name'] ?? null,
                'last_name' => $validated['last_name'] ?? null,
                'custom_fields' => $customValues,
                'source' => 'form',
                'ip_address' => $request->ip(),
            ]);
        } else {
            // Create subscriber
            $subscriber = $this->listSubscriberService->create($form->emailList, [
                'email' => $normalizedEmail,
                'first_name' => $validated['first_name'] ?? null,
                'last_name' => $validated['last_name'] ?? null,
                'custom_fields' => $customValues,
                'source' => 'form',
                'ip_address' => $request->ip(),
            ]);

            $wasCreated = true;
        }

        // Notify list owner (customer) about new subscriber
        $customer = $form->emailList->customer;
        if ($customer) {
            $customer->notify(new NewSubscriberNotification($form->emailList, $subscriber));
        }

        // Increment form submissions count
        $form->increment('submissions_count');

        if ($isApiRequest) {
            return response()->json([
                'message' => 'Successfully subscribed! Please check your email to confirm.',
                'status' => 'success',
                'subscriber' => [
                    'email' => $subscriber->email,
                    'status' => $subscriber->status,
                ]
            ], $wasCreated ? 201 : 200);
        }

        return back()->with('success', 'Successfully subscribed! Please check your email to confirm.');
    }

    /**
     * Confirm subscription via email token.
     */
    public function confirm(string $token)
    {
        $verificationService = app(EmailVerificationService::class);
        $verification = $verificationService->verify($token);

        if (!$verification || !$verification->subscriber) {
            return view('public.subscription-confirmed', [
                'success' => false,
                'message' => 'Invalid or expired confirmation link.'
            ]);
        }

        return view('public.subscription-confirmed', [
            'success' => true,
            'message' => 'Your subscription has been confirmed!',
            'list' => $verification->subscriber->list
        ]);
    }

    /**
     * Unsubscribe from a list.
     */
    public function unsubscribe(EmailList $list, string $email, string $token)
    {
        $decodedEmail = urldecode($email);

        // Verify token (tolerate legacy links that double-encoded the email)
        $expectedToken = hash('sha256', $email . $list->id . config('app.key'));
        $expectedTokenDecoded = $decodedEmail !== $email
            ? hash('sha256', $decodedEmail . $list->id . config('app.key'))
            : $expectedToken;

        if (!hash_equals($expectedToken, $token) && !hash_equals($expectedTokenDecoded, $token)) {
            return view('public.unsubscribe', [
                'success' => false,
                'message' => 'Invalid unsubscribe link.'
            ]);
        }

        $subscriber = ListSubscriber::where('list_id', $list->id)
            ->where('email', $decodedEmail)
            ->first();

        if (!$subscriber) {
            return view('public.unsubscribe', [
                'success' => false,
                'message' => 'Subscriber not found.'
            ]);
        }

        // Unsubscribe
        app(ListSubscriberService::class)->unsubscribe($subscriber);

        // Notify list owner about unsubscribe
        if ($list->customer) {
            $list->customer->notify(new SubscriberUnsubscribedNotification($list, $subscriber));
        }

        // Redirect if URL is set
        if ($list->unsubscribe_redirect_url) {
            return redirect($list->unsubscribe_redirect_url);
        }

        return view('public.unsubscribe', [
            'success' => true,
            'message' => 'You have been successfully unsubscribed.',
            'list' => $list
        ]);
    }

    /**
     * Unsubscribe from campaign using UUID.
     */
    public function unsubscribeByUuid(string $uuid)
    {
        try {
            $recipient = CampaignRecipient::query()
                ->where('uuid', $uuid)
                ->first();

            if (!$recipient) {
                return view('public.unsubscribe', [
                    'success' => false,
                    'message' => 'Invalid or expired unsubscribe link.',
                ]);
            }

            $campaign = Campaign::withTrashed()->find($recipient->campaign_id);

            if (!$campaign) {
                return view('public.unsubscribe', [
                    'success' => false,
                    'message' => 'Invalid or expired unsubscribe link.',
                ]);
            }

            $subscriber = null;
            if (!empty($campaign->list_id) && !empty($recipient->email)) {
                $subscriber = ListSubscriber::query()
                    ->where('list_id', $campaign->list_id)
                    ->where('email', $recipient->email)
                    ->first();
            }

            if ($subscriber) {
                app(ListSubscriberService::class)->unsubscribe($subscriber);

                $listForNotification = $subscriber->emailList;
                if ($listForNotification && $listForNotification->customer) {
                    $listForNotification->customer->notify(
                        new SubscriberUnsubscribedNotification($listForNotification, $subscriber)
                    );
                }
            }

            $recipient->update(['status' => 'unsubscribed']);

            $list = $campaign->emailList;

            if ($list && $list->unsubscribe_redirect_url) {
                return redirect($list->unsubscribe_redirect_url);
            }

            return view('public.unsubscribe', [
                'success' => true,
                'message' => 'You have been successfully unsubscribed.',
                'list' => $list,
            ]);
        } catch (\Throwable $e) {
            Log::error('Campaign unsubscribe failed', [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);

            return view('public.unsubscribe', [
                'success' => false,
                'message' => 'Unable to process unsubscribe request.',
            ]);
        }
    }
}


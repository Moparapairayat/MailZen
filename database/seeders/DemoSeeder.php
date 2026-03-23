<?php

namespace Database\Seeders;

use App\Models\BounceLog;
use App\Models\BounceServer;
use App\Models\Campaign;
use App\Models\Complaint;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\DeliveryServer;
use App\Models\EmailList;
use App\Models\Plan;
use App\Models\SendingDomain;
use App\Models\Template;
use App\Models\Subscription;
use App\Models\TrackingDomain;
use App\Models\UsageLog;
use App\Models\WebhookEvent;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder 
{
    public function run(): void
    {
        $customer = Customer::where('email', 'customer@mailzen.com')->first();
        if (!$customer) {
            return;
        }

        Template::query()
            ->where('customer_id', $customer->id)
            ->whereIn('name', ['Demo Welcome Email', 'Demo Product Update'])
            ->delete();

        Template::updateOrCreate(
            ['customer_id' => $customer->id, 'name' => 'Zylker - Thanks for Your Subscription'],
            [
                'description' => 'Subscription thank-you email (Unlayer).',
                'type' => 'email',
                'html_content' => '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head><body style="margin:0;padding:0;background:#111827;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#111827;padding:24px 0;"><tr><td align="center"><table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background:#ffffff;overflow:hidden;"><tr><td style="background:#233ad6;padding:28px;text-align:center;font-family:Arial,sans-serif;color:#ffffff;"><div style="font-weight:800;font-size:44px;line-height:48px;">THANKS</div><div style="font-weight:700;font-size:20px;line-height:28px;">for your subscription!</div></td></tr><tr><td style="padding:22px 28px;font-family:Arial,sans-serif;color:#111827;font-size:14px;line-height:22px;">Hello ${FNAME|Customer|Guest},<br/><br/>We\'re happy to welcome you. Watch for our Tuesday newsletter with product news and updates.<br/><br/><div style="text-align:center;margin:22px 0;"><a href="https://example.com" style="display:inline-block;background:#233ad6;color:#ffffff;text-decoration:none;font-weight:700;padding:12px 28px;border-radius:6px;">Let\'s Get Started</a></div><div style="font-size:12px;line-height:18px;color:#6b7280;text-align:center;">Unsubscribe: ${UNSUB} · Update: ${UPDATEPROFILE}</div></td></tr></table></td></tr></table></body></html>',
                'plain_text_content' => 'THANKS for your subscription!' . "\n\n" .
                    'Hello ${FNAME|Customer|Guest},' . "\n\n" .
                    'Let\'s get started: https://example.com' . "\n" .
                    'Unsubscribe: ${UNSUB}' . "\n" .
                    'Update: ${UPDATEPROFILE}',
                'grapesjs_data' => [
                    'builder' => 'unlayer',
                    'unlayer' => [
                        'schemaVersion' => 20,
                        'counters' => [],
                        'body' => [
                            'id' => 'body_zylker',
                            'rows' => [
                                [
                                    'id' => 'row_zylker_1',
                                    'cells' => [1],
                                    'columns' => [
                                        [
                                            'id' => 'col_zylker_1',
                                            'contents' => [
                                                [
                                                    'id' => 'z_text_1',
                                                    'type' => 'text',
                                                    'values' => [
                                                        'text' => '<div style="background:#233ad6;color:#ffffff;text-align:center;padding:28px;">'
                                                            . '<div style="font-weight:800;font-size:44px;line-height:48px;">THANKS</div>'
                                                            . '<div style="font-weight:700;font-size:20px;line-height:28px;">for your subscription!</div>'
                                                            . '</div>'
                                                            . '<div style="padding:18px 0 0 0;">Hello ${FNAME|Customer|Guest},<br/><br/>We\'re happy to welcome you. Watch for our Tuesday newsletter with product news and updates.</div>'
                                                            . '<div style="margin-top:16px;font-size:12px;line-height:18px;color:#6b7280;text-align:center;">Unsubscribe: ${UNSUB}<br/>Update: ${UPDATEPROFILE}</div>',
                                                        'textAlign' => 'left',
                                                    ],
                                                ],
                                                [
                                                    'id' => 'z_btn_1',
                                                    'type' => 'button',
                                                    'values' => [
                                                        'text' => "Let's Get Started",
                                                        'href' => 'https://example.com',
                                                        'textAlign' => 'center',
                                                        'backgroundColor' => '#233ad6',
                                                        'color' => '#ffffff',
                                                        'borderRadius' => '6px',
                                                        'padding' => '12px 28px',
                                                    ],
                                                ],
                                            ],
                                            'values' => [
                                                'backgroundColor' => '#ffffff',
                                                'padding' => '22px 28px 22px 28px',
                                            ],
                                        ],
                                    ],
                                    'values' => [
                                        'backgroundColor' => '#ffffff',
                                        'padding' => '0px',
                                    ],
                                ],
                            ],
                            'values' => [
                                'backgroundColor' => '#f3f4f6',
                                'contentWidth' => '600px',
                                'fontFamily' => [
                                    'label' => 'Arial',
                                    'value' => 'arial,helvetica,sans-serif',
                                ],
                            ],
                        ],
                    ],
                ],
                'settings' => [],
                'thumbnail' => null,
                'is_public' => false,
                'is_system' => true,
                'usage_count' => 0,
            ]
        );

        Template::updateOrCreate(
            ['customer_id' => $customer->id, 'name' => 'Millala - Product Digest'],
            [
                'description' => 'Modern image + text newsletter (Unlayer).',
                'type' => 'email',
                'html_content' => '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head><body style="margin:0;padding:0;background:#ffffff;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#ffffff;padding:18px 0;"><tr><td align="center"><table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background:#ffffff;">
                <tr><td style="padding:18px 28px;font-family:Arial,sans-serif;"><div style="font-weight:800;font-size:18px;">millala</div><div style="margin-top:12px;color:#111827;font-size:14px;">Hi Daniel Joseph,</div></td></tr>
                <tr><td style="padding:0 28px 18px 28px;"><img alt="" src="https://via.placeholder.com/544x260.png?text=Hero+Image" width="544" style="display:block;width:100%;height:auto;border-radius:10px;" /></td></tr>
                <tr><td style="padding:0 28px 18px 28px;font-family:Arial,sans-serif;color:#111827;font-size:14px;line-height:22px;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut enim ad minim veniam. Read more: <a href="https://example.com" style="color:#2563eb;">https://example.com</a></td></tr>
                <tr><td style="padding:0 28px 18px 28px;"><img alt="" src="https://via.placeholder.com/544x220.png?text=Section+Image" width="544" style="display:block;width:100%;height:auto;border-radius:10px;" /></td></tr>
                <tr><td style="padding:0 28px 28px 28px;font-family:Arial,sans-serif;color:#111827;font-size:14px;line-height:22px;">More updates and a short product story. Thanks,<br/>The Millala Team</td></tr>
                <tr><td style="padding:0 28px 24px 28px;font-family:Arial,sans-serif;text-align:center;font-size:12px;line-height:18px;color:#6b7280;">Download our app:<br/><a href="https://example.com" style="color:#111827;">App Store</a> · <a href="https://example.com" style="color:#111827;">Google Play</a></td></tr>
                </table></td></tr></table></body></html>',
                'plain_text_content' => 'Hi Daniel Joseph,' . "\n\n" .
                    'Read more: https://example.com' . "\n\n" .
                    'The Millala Team',
                'grapesjs_data' => [
                    'builder' => 'unlayer',
                    'unlayer' => [
                        'schemaVersion' => 20,
                        'counters' => [],
                        'body' => [
                            'id' => 'body_millala',
                            'rows' => [
                                [
                                    'id' => 'row_m_1',
                                    'cells' => [1],
                                    'columns' => [
                                        [
                                            'id' => 'col_m_1',
                                            'contents' => [
                                                [
                                                    'type' => 'text',
                                                    'values' => [
                                                        'text' => '<div style="font-weight:800;font-size:18px;">millala</div><div style="margin-top:12px;">Hi Daniel Joseph,</div>'
                                                            . '<div style="margin-top:14px;"><img alt="" src="https://via.placeholder.com/544x260.png?text=Hero+Image" style="max-width:100%;border-radius:10px;" /></div>'
                                                            . '<div style="margin-top:14px;color:#111827;">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div>'
                                                            . '<div style="margin-top:14px;"><img alt="" src="https://via.placeholder.com/544x220.png?text=Section+Image" style="max-width:100%;border-radius:10px;" /></div>'
                                                            . '<div style="margin-top:14px;">Thanks,<br/>The Millala Team</div>',
                                                        'textAlign' => 'left',
                                                    ],
                                                ],
                                                [
                                                    'type' => 'button',
                                                    'values' => [
                                                        'text' => 'Read More',
                                                        'href' => 'https://example.com',
                                                        'textAlign' => 'center',
                                                        'backgroundColor' => '#111827',
                                                        'color' => '#ffffff',
                                                        'borderRadius' => '8px',
                                                        'padding' => '12px 18px',
                                                    ],
                                                ],
                                                [
                                                    'type' => 'text',
                                                    'values' => [
                                                        'text' => '<p style="margin:16px 0 0 0;font-size:12px;line-height:18px;color:#6b7280;text-align:center;">App Store · Google Play</p>',
                                                        'textAlign' => 'left',
                                                    ],
                                                ],
                                            ],
                                            'values' => [
                                                'backgroundColor' => '#ffffff',
                                                'padding' => '18px 28px 24px 28px',
                                            ],
                                        ],
                                    ],
                                    'values' => [
                                        'backgroundColor' => '#ffffff',
                                        'padding' => '0px',
                                    ],
                                ],
                            ],
                            'values' => [
                                'backgroundColor' => '#ffffff',
                                'contentWidth' => '600px',
                                'fontFamily' => [
                                    'label' => 'Arial',
                                    'value' => 'arial,helvetica,sans-serif',
                                ],
                            ],
                        ],
                    ],
                ],
                'settings' => [],
                'thumbnail' => null,
                'is_public' => false,
                'is_system' => true,
                'usage_count' => 0,
            ]
        );

        $alreadySeeded = Campaign::query()
            ->where('customer_id', $customer->id)
            ->where('name', 'like', 'Demo %')
            ->exists();

        if ($alreadySeeded) {
            return;
        }

        $firstNames = [
            'Alex', 'Sam', 'Taylor', 'Jordan', 'Chris', 'Jamie', 'Morgan', 'Casey', 'Riley', 'Avery',
            'Noah', 'Liam', 'Emma', 'Olivia', 'Mia', 'Sophia', 'Amelia', 'Ethan', 'Lucas', 'Aiden',
        ];
        $lastNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
            'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin',
        ];

        $randElement = static function (array $items) {
            return $items[array_rand($items)];
        };

        $randomIp = static function (): string {
            return implode('.', [
                random_int(1, 255),
                random_int(0, 255),
                random_int(0, 255),
                random_int(1, 254),
            ]);
        };

        $randomFirstName = static function () use ($randElement, $firstNames): string {
            return (string) $randElement($firstNames);
        };

        $randomLastName = static function () use ($randElement, $lastNames): string {
            return (string) $randElement($lastNames);
        };

        $customer->update([
            'timezone' => $customer->timezone ?: config('app.timezone'),
            'status' => 'active',
            'stripe_customer_id' => $customer->stripe_customer_id ?: ('cus_demo_' . Str::lower(Str::random(12))),
            'company_name' => $customer->company_name ?: 'MailZen Demo Co.',
            'website_url' => $customer->website_url ?: 'https://example.com',
        ]);

        $group = CustomerGroup::firstOrCreate(
            ['name' => 'Demo'],
            [
                'description' => 'Demo group',
                'permissions' => ['*'],
                'settings' => [
                    'sending_quota' => ['monthly_quota' => 50000],
                    'lists' => ['limits' => ['max_lists' => 50, 'max_subscribers' => 100000]],
                    'campaigns' => ['limits' => ['max_campaigns' => 500]],
                    'servers' => ['permissions' => ['can_use_system_servers' => true]],
                    'automations' => ['enabled' => true],
                    'domains' => [
                        'sending_domains' => ['can_manage' => true, 'must_add' => false],
                        'tracking_domains' => ['can_manage' => true, 'must_add' => false],
                    ],
                ],
                'quota' => 50000,
                'max_lists' => 50,
                'max_subscribers' => 100000,
                'max_campaigns' => 500,
                'is_system' => true,
            ]
        );

        $customer->customerGroups()->syncWithoutDetaching([$group->id]);

        $sendingDomain = SendingDomain::firstOrCreate(
            ['domain' => 'demo-mailzen.test'],
            [
                'customer_id' => $customer->id,
                'is_primary' => true,
                'status' => 'verified',
                'verified_at' => now()->subDays(10),
                'dns_records' => [
                    ['type' => 'TXT', 'host' => '@', 'value' => 'v=spf1 include:mailgun.org ~all'],
                    ['type' => 'TXT', 'host' => 'default._domainkey', 'value' => 'k=rsa; p=...'],
                ],
            ]
        );

        $trackingDomain = TrackingDomain::firstOrCreate(
            ['domain' => 'trk.demo-mailzen.test'],
            [
                'customer_id' => $customer->id,
                'status' => 'verified',
                'verified_at' => now()->subDays(10),
                'dns_records' => [
                    ['type' => 'CNAME', 'host' => 'trk', 'value' => 'tracking.example.com'],
                ],
            ]
        );

        $bounceServer = BounceServer::firstOrCreate(
            ['customer_id' => $customer->id, 'hostname' => 'imap.demo-mailzen.test', 'username' => 'bounces@demo-mailzen.test'],
            [
                'name' => 'Demo Bounce Server',
                'protocol' => 'imap',
                'port' => 993,
                'encryption' => 'ssl',
                'password' => 'password',
                'mailbox' => 'INBOX',
                'active' => true,
                'delete_after_processing' => false,
                'max_emails_per_batch' => 100,
            ]
        );

        $deliveryServer = DeliveryServer::firstOrCreate(
            ['name' => 'Demo SMTP Server'],
            [
                'customer_id' => $customer->id,
                'is_primary' => true,
                'type' => 'smtp',
                'status' => 'active',
                'hostname' => 'smtp.demo-mailzen.test',
                'port' => 587,
                'username' => 'smtp-user',
                'password' => 'smtp-pass',
                'encryption' => 'tls',
                'from_email' => 'hello@demo-mailzen.test',
                'from_name' => 'MailZen Demo',
                'timeout' => 30,
                'max_connection_messages' => 100,
                'hourly_quota' => 0,
                'daily_quota' => 0,
                'monthly_quota' => 0,
                'pause_after_send' => 0,
                'settings' => [],
                'locked' => false,
                'use_for' => true,
                'use_for_email_to_list' => false,
                'use_for_transactional' => false,
                'bounce_server_id' => $bounceServer->id,
                'tracking_domain_id' => $trackingDomain->id,
                'verified_at' => now()->subDays(10),
                'verification_token' => null,
            ]
        );

        $listA = EmailList::firstOrCreate(
            ['customer_id' => $customer->id, 'name' => 'Demo Newsletter'],
            [
                'sending_domain_id' => $sendingDomain->id,
                'display_name' => 'Demo Newsletter',
                'description' => 'Demo list for analytics',
                'from_name' => 'MailZen Demo',
                'from_email' => 'hello@demo-mailzen.test',
                'reply_to' => 'support@demo-mailzen.test',
                'status' => 'active',
                'opt_in' => 'double',
                'opt_out' => 'single',
                'welcome_email_enabled' => true,
                'unsubscribe_email_enabled' => true,
                'custom_fields' => [],
                'tags' => ['demo'],
            ]
        );

        $listB = EmailList::firstOrCreate(
            ['customer_id' => $customer->id, 'name' => 'Demo Product Updates'],
            [
                'sending_domain_id' => $sendingDomain->id,
                'display_name' => 'Product Updates',
                'description' => 'Demo list for campaigns',
                'from_name' => 'MailZen Demo',
                'from_email' => 'updates@demo-mailzen.test',
                'reply_to' => 'support@demo-mailzen.test',
                'status' => 'active',
                'opt_in' => 'double',
                'opt_out' => 'single',
                'welcome_email_enabled' => true,
                'unsubscribe_email_enabled' => true,
                'custom_fields' => [],
                'tags' => ['demo'],
            ]
        );

        $allLists = [$listA, $listB];

        $subscriberCount = 480;
        $subscribersByList = [];

        foreach ($allLists as $list) {
            $subscribersByList[$list->id] = [];
        }

        for ($i = 0; $i < $subscriberCount; $i++) {
            $list = $allLists[$i % count($allLists)];

            $email = sprintf(
                '%s.%s.%s@%s',
                Str::lower($randomFirstName()),
                Str::lower($randomLastName()),
                Str::padLeft((string) ($i + 1), 3, '0'),
                'example.test'
            );

            $createdAt = now()->subDays(random_int(1, 90))->setTime(random_int(8, 20), random_int(0, 59));

            $statusRoll = random_int(1, 100);
            $status = 'confirmed';
            $confirmedAt = (clone $createdAt)->addMinutes(random_int(5, 1440));
            $unsubscribedAt = null;
            $blacklistedAt = null;
            $bouncedAt = null;
            $isBounced = false;
            $isComplained = false;
            $softBounceCount = 0;
            $suppressedAt = null;

            if ($statusRoll <= 6) {
                $status = 'unsubscribed';
                $unsubscribedAt = now()->subDays(random_int(0, 29))->setTime(random_int(8, 22), random_int(0, 59));
            } elseif ($statusRoll <= 9) {
                $status = 'bounced';
                $bouncedAt = now()->subDays(random_int(0, 29))->setTime(random_int(8, 22), random_int(0, 59));
                $isBounced = true;
                $softBounceCount = random_int(0, 2);
                $suppressedAt = $bouncedAt;
            } elseif ($statusRoll <= 10) {
                $status = 'blacklisted';
                $blacklistedAt = now()->subDays(random_int(1, 120));
                $suppressedAt = $blacklistedAt;
            }

            $id = DB::table('list_subscribers')->insertGetId([
                'list_id' => $list->id,
                'email' => $email,
                'first_name' => $randomFirstName(),
                'last_name' => $randomLastName(),
                'status' => $status,
                'source' => 'import',
                'ip_address' => $randomIp(),
                'subscribed_at' => $createdAt,
                'confirmed_at' => $status === 'confirmed' ? $confirmedAt : null,
                'unsubscribed_at' => $unsubscribedAt,
                'blacklisted_at' => $blacklistedAt,
                'bounced_at' => $bouncedAt,
                'is_bounced' => $isBounced,
                'is_complained' => $isComplained,
                'soft_bounce_count' => $softBounceCount,
                'suppressed_at' => $suppressedAt,
                'custom_fields' => json_encode(['plan' => $randElement(['Free', 'Pro', 'Business'])]),
                'tags' => json_encode(['demo', $randElement(['beta', 'active', 'vip'])]),
                'notes' => null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ]);

            $subscribersByList[$list->id][] = ['id' => $id, 'email' => $email];
        }

        foreach ($allLists as $list) {
            $confirmed = DB::table('list_subscribers')
                ->where('list_id', $list->id)
                ->where('status', 'confirmed')
                ->count();

            $unsubscribed = DB::table('list_subscribers')
                ->where('list_id', $list->id)
                ->where('status', 'unsubscribed')
                ->count();

            $bounced = DB::table('list_subscribers')
                ->where('list_id', $list->id)
                ->where('status', 'bounced')
                ->count();

            $total = DB::table('list_subscribers')
                ->where('list_id', $list->id)
                ->count();

            $lastSubscriberAt = DB::table('list_subscribers')
                ->where('list_id', $list->id)
                ->max('created_at');

            $list->update([
                'subscribers_count' => $total,
                'confirmed_subscribers_count' => $confirmed,
                'unsubscribed_count' => $unsubscribed,
                'bounced_count' => $bounced,
                'last_subscriber_at' => $lastSubscriberAt ? Carbon::parse($lastSubscriberAt) : null,
            ]);
        }

        $campaignDefs = [
            ['name' => 'Demo Welcome Series', 'subject' => 'Welcome to MailZen Demo'],
            ['name' => 'Demo December Promo', 'subject' => 'Limited-time offer just for you'],
            ['name' => 'Demo Product Launch', 'subject' => 'New features are here'],
            ['name' => 'Demo Weekly Digest', 'subject' => 'Your weekly updates'],
            ['name' => 'Demo Re-engagement', 'subject' => 'We miss you — come back'],
        ];

        $campaignIds = [];

        foreach ($campaignDefs as $idx => $def) {
            $list = $allLists[$idx % count($allLists)];

            $sendAt = now()->subDays(random_int(0, 28))->setTime(random_int(9, 19), random_int(0, 59));
            $startedAt = (clone $sendAt)->subMinutes(random_int(5, 20));
            $finishedAt = (clone $sendAt)->addMinutes(random_int(10, 180));

            $campaign = Campaign::create([
                'customer_id' => $customer->id,
                'list_id' => $list->id,
                'delivery_server_id' => $deliveryServer->id,
                'sending_domain_id' => $sendingDomain->id,
                'tracking_domain_id' => $trackingDomain->id,
                'name' => $def['name'],
                'subject' => $def['subject'],
                'from_name' => 'MailZen Demo',
                'from_email' => 'hello@demo-mailzen.test',
                'reply_to' => 'support@demo-mailzen.test',
                'type' => 'regular',
                'status' => 'completed',
                'html_content' => '<h1>MailZen Demo</h1><p>This is demo content.</p>',
                'plain_text_content' => 'MailZen Demo - This is demo content.',
                'template_data' => [],
                'scheduled_at' => null,
                'send_at' => $sendAt,
                'started_at' => $startedAt,
                'finished_at' => $finishedAt,
                'track_opens' => true,
                'track_clicks' => true,
                'segments' => [],
                'settings' => [],
            ]);

            $campaignIds[] = ['id' => $campaign->id, 'list_id' => $list->id, 'send_at' => $sendAt];
        }

        $userAgents = [
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Linux; Android 14; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
        ];

        foreach ($campaignIds as $cInfo) {
            $listSubscriberPool = $subscribersByList[$cInfo['list_id']];
            shuffle($listSubscriberPool);

            $recipientTotal = random_int(120, 220);
            $recipientTotal = min($recipientTotal, count($listSubscriberPool));

            $recipientRows = [];
            $trackingRows = [];
            $logRows = [];
            $bounceRows = [];
            $complaintRows = [];

            $baseSend = Carbon::parse($cInfo['send_at']);

            for ($i = 0; $i < $recipientTotal; $i++) {
                $sub = $listSubscriberPool[$i];

                $statusRoll = random_int(1, 100);
                $status = 'sent';
                $sentAt = (clone $baseSend)->addSeconds(random_int(0, 1800));
                $openedAt = null;
                $clickedAt = null;
                $bouncedAt = null;
                $failedAt = null;
                $failureReason = null;

                if ($statusRoll <= 8) {
                    $status = 'bounced';
                    $bouncedAt = (clone $sentAt)->addMinutes(random_int(1, 30));
                } elseif ($statusRoll <= 12) {
                    $status = 'failed';
                    $failedAt = (clone $sentAt)->addMinutes(random_int(1, 10));
                    $failureReason = $randElement(['SMTP connection timeout', 'Invalid recipient address', 'Rate limited by provider']);
                } elseif ($statusRoll <= 55) {
                    $status = 'opened';
                    $openedAt = (clone $sentAt)->addMinutes(random_int(2, 180));
                } elseif ($statusRoll <= 78) {
                    $status = 'clicked';
                    $openedAt = (clone $sentAt)->addMinutes(random_int(2, 180));
                    $clickedAt = (clone $openedAt)->addMinutes(random_int(1, 60));
                }

                $uuid = (string) Str::uuid();
                $createdAt = $sentAt;

                $recipientRows[] = [
                    'campaign_id' => $cInfo['id'],
                    'email' => $sub['email'],
                    'uuid' => $uuid,
                    'first_name' => $randomFirstName(),
                    'last_name' => $randomLastName(),
                    'status' => $status,
                    'sent_at' => $sentAt,
                    'opened_at' => $openedAt,
                    'clicked_at' => $clickedAt,
                    'bounced_at' => $bouncedAt,
                    'failed_at' => $failedAt,
                    'failure_reason' => $failureReason,
                    'meta' => json_encode(['subscriber_id' => $sub['id']]),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                $userAgent = $userAgents[array_rand($userAgents)];

                $trackingRows[] = [
                    'campaign_id' => $cInfo['id'],
                    'subscriber_id' => $sub['id'],
                    'email' => $sub['email'],
                    'event_type' => 'sent',
                    'url' => null,
                    'ip_address' => $randomIp(),
                    'user_agent' => $userAgent,
                    'bounce_reason' => null,
                    'complaint_reason' => null,
                    'event_at' => $sentAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                $logRows[] = [
                    'campaign_id' => $cInfo['id'],
                    'recipient_id' => null,
                    'event' => 'sent',
                    'meta' => json_encode(['email' => $sub['email']]),
                    'ip_address' => $randomIp(),
                    'user_agent' => $userAgent,
                    'url' => null,
                    'error_message' => null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                if ($openedAt) {
                    $trackingRows[] = [
                        'campaign_id' => $cInfo['id'],
                        'subscriber_id' => $sub['id'],
                        'email' => $sub['email'],
                        'event_type' => 'opened',
                        'url' => null,
                        'ip_address' => $randomIp(),
                        'user_agent' => $userAgent,
                        'bounce_reason' => null,
                        'complaint_reason' => null,
                        'event_at' => $openedAt,
                        'created_at' => $openedAt,
                        'updated_at' => $openedAt,
                    ];

                    $logRows[] = [
                        'campaign_id' => $cInfo['id'],
                        'recipient_id' => null,
                        'event' => 'opened',
                        'meta' => json_encode(['email' => $sub['email']]),
                        'ip_address' => $randomIp(),
                        'user_agent' => $userAgent,
                        'url' => null,
                        'error_message' => null,
                        'created_at' => $openedAt,
                        'updated_at' => $openedAt,
                    ];
                }

                if ($clickedAt) {
                    $url = $randElement([
                        'https://example.com/pricing',
                        'https://example.com/features',
                        'https://example.com/blog/new',
                        'https://example.com/docs',
                    ]);

                    $trackingRows[] = [
                        'campaign_id' => $cInfo['id'],
                        'subscriber_id' => $sub['id'],
                        'email' => $sub['email'],
                        'event_type' => 'clicked',
                        'url' => $url,
                        'ip_address' => $randomIp(),
                        'user_agent' => $userAgent,
                        'bounce_reason' => null,
                        'complaint_reason' => null,
                        'event_at' => $clickedAt,
                        'created_at' => $clickedAt,
                        'updated_at' => $clickedAt,
                    ];

                    $logRows[] = [
                        'campaign_id' => $cInfo['id'],
                        'recipient_id' => null,
                        'event' => 'clicked',
                        'meta' => json_encode(['email' => $sub['email']]),
                        'ip_address' => $randomIp(),
                        'user_agent' => $userAgent,
                        'url' => $url,
                        'error_message' => null,
                        'created_at' => $clickedAt,
                        'updated_at' => $clickedAt,
                    ];
                }

                if ($status === 'bounced' && $bouncedAt) {
                    $bounceType = $randElement(['hard', 'soft']);

                    $bounceRows[] = [
                        'bounce_server_id' => $bounceServer->id,
                        'subscriber_id' => $sub['id'],
                        'campaign_id' => $cInfo['id'],
                        'list_id' => $cInfo['list_id'],
                        'recipient_id' => null,
                        'email' => $sub['email'],
                        'bounce_type' => $bounceType,
                        'bounce_code' => $bounceType === 'hard' ? '550' : '421',
                        'diagnostic_code' => $bounceType === 'hard' ? '550 5.1.1 User unknown' : '421 4.4.2 Connection timed out',
                        'reason' => $bounceType === 'hard' ? 'Mailbox does not exist' : 'Temporary delivery failure',
                        'raw_message' => null,
                        'message_id' => 'msg_' . Str::lower(Str::random(16)),
                        'bounced_at' => $bouncedAt,
                        'meta' => json_encode([]),
                        'created_at' => $bouncedAt,
                        'updated_at' => $bouncedAt,
                    ];

                    $trackingRows[] = [
                        'campaign_id' => $cInfo['id'],
                        'subscriber_id' => $sub['id'],
                        'email' => $sub['email'],
                        'event_type' => 'bounced',
                        'url' => null,
                        'ip_address' => $randomIp(),
                        'user_agent' => $userAgent,
                        'bounce_reason' => 'Mailbox does not exist',
                        'complaint_reason' => null,
                        'event_at' => $bouncedAt,
                        'created_at' => $bouncedAt,
                        'updated_at' => $bouncedAt,
                    ];

                    $logRows[] = [
                        'campaign_id' => $cInfo['id'],
                        'recipient_id' => null,
                        'event' => 'bounced',
                        'meta' => json_encode(['email' => $sub['email']]),
                        'ip_address' => $randomIp(),
                        'user_agent' => $userAgent,
                        'url' => null,
                        'error_message' => null,
                        'created_at' => $bouncedAt,
                        'updated_at' => $bouncedAt,
                    ];
                }

                if ($status === 'opened' || $status === 'clicked') {
                    $complaintRoll = random_int(1, 1000);
                    if ($complaintRoll <= 4) {
                        $complainedAt = (clone ($clickedAt ?: $openedAt ?: $sentAt))->addMinutes(random_int(5, 120));
                        $providerMessageId = 'pm_' . Str::lower(Str::random(18));

                        $complaintRows[] = [
                            'subscriber_id' => $sub['id'],
                            'campaign_id' => $cInfo['id'],
                            'email' => $sub['email'],
                            'source' => 'webhook',
                            'provider' => 'mailgun',
                            'provider_message_id' => $providerMessageId,
                            'feedback_id' => 'fb_' . Str::lower(Str::random(10)),
                            'complained_at' => $complainedAt,
                            'raw_data' => null,
                            'meta' => json_encode([]),
                            'created_at' => $complainedAt,
                            'updated_at' => $complainedAt,
                        ];

                        $trackingRows[] = [
                            'campaign_id' => $cInfo['id'],
                            'subscriber_id' => $sub['id'],
                            'email' => $sub['email'],
                            'event_type' => 'complained',
                            'url' => null,
                            'ip_address' => $randomIp(),
                            'user_agent' => $userAgent,
                            'bounce_reason' => null,
                            'complaint_reason' => 'Marked as spam',
                            'event_at' => $complainedAt,
                            'created_at' => $complainedAt,
                            'updated_at' => $complainedAt,
                        ];
                    }
                }
            }

            DB::table('campaign_recipients')->insert($recipientRows);
            DB::table('campaign_tracking')->insert($trackingRows);
            DB::table('campaign_logs')->insert($logRows);

            if (!empty($bounceRows)) {
                DB::table('bounce_logs')->insert($bounceRows);
            }

            if (!empty($complaintRows)) {
                DB::table('complaints')->insert($complaintRows);
            }

            $campaign = Campaign::find($cInfo['id']);
            if ($campaign) {
                $campaign->syncStats();
                $campaign->update([
                    'delivered_count' => max(0, (int) $campaign->sent_count - (int) $campaign->bounced_count),
                ]);
            }
        }

        $planStarter = Plan::firstOrCreate(
            ['slug' => 'starter'],
            [
                'name' => 'Starter',
                'description' => 'Starter demo plan',
                'price' => 19.00,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'trial_days' => 7,
                'customer_group_id' => $group->id,
                'stripe_price_id' => 'price_demo_starter',
                'stripe_product_id' => 'prod_demo_starter',
                'is_active' => true,
            ]
        );

        Plan::firstOrCreate(
            ['slug' => 'pro'],
            [
                'name' => 'Pro',
                'description' => 'Pro demo plan',
                'price' => 49.00,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'trial_days' => 14,
                'customer_group_id' => $group->id,
                'stripe_price_id' => 'price_demo_pro',
                'stripe_product_id' => 'prod_demo_pro',
                'is_active' => true,
            ]
        );

        Plan::firstOrCreate(
            ['slug' => 'business'],
            [
                'name' => 'Business',
                'description' => 'Business demo plan',
                'price' => 129.00,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'trial_days' => 14,
                'customer_group_id' => $group->id,
                'stripe_price_id' => 'price_demo_business',
                'stripe_product_id' => 'prod_demo_business',
                'is_active' => true,
            ]
        );

        $periodStart = Carbon::now()->startOfMonth();
        $periodEnd = Carbon::now()->endOfMonth();

        Subscription::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'status' => 'active',
            ],
            [
                'plan_id' => $planStarter->stripe_price_id,
                'plan_db_id' => $planStarter->id,
                'plan_name' => $planStarter->name,
                'billing_cycle' => 'monthly',
                'price' => $planStarter->price,
                'currency' => 'USD',
                'starts_at' => now()->subDays(20),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'trial_ends_at' => null,
                'cancelled_at' => null,
                'cancel_at_period_end' => false,
                'auto_renew' => true,
                'provider' => 'stripe',
                'payment_gateway' => 'stripe_checkout',
                'stripe_customer_id' => $customer->stripe_customer_id,
                'stripe_subscription_id' => 'sub_demo_' . Str::lower(Str::random(14)),
                'stripe_checkout_session_id' => 'cs_demo_' . Str::lower(Str::random(14)),
                'stripe_price_id' => $planStarter->stripe_price_id,
                'payment_reference' => 'pi_demo_' . Str::lower(Str::random(14)),
                'last_payment_status' => 'active',
                'limits' => [
                    'emails_sent_this_month' => 50000,
                    'subscribers_count' => 100000,
                    'campaigns_count' => 500,
                ],
                'features' => ['*'],
                'payment_method' => 'card',
                'renewal_count' => 2,
            ]
        );

        $metrics = [
            'emails_sent_this_month' => DB::table('campaign_recipients')
                ->whereIn('status', ['sent', 'opened', 'clicked'])
                ->whereBetween('sent_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()->endOfDay()])
                ->whereIn('campaign_id', Campaign::query()->where('customer_id', $customer->id)->pluck('id')->all())
                ->count(),
            'subscribers_count' => DB::table('list_subscribers')
                ->whereIn('list_id', EmailList::query()->where('customer_id', $customer->id)->pluck('id')->all())
                ->where('status', 'confirmed')
                ->count(),
            'campaigns_count' => Campaign::query()->where('customer_id', $customer->id)->count(),
        ];

        foreach ($metrics as $metric => $amount) {
            UsageLog::updateOrCreate(
                [
                    'customer_id' => $customer->id,
                    'metric' => $metric,
                    'period_start' => $periodStart->toDateString(),
                    'period_end' => $periodEnd->toDateString(),
                ],
                [
                    'amount' => (int) $amount,
                    'context' => ['source' => 'demo_seeder'],
                ]
            );
        }

        $invoicePayloads = [
            [
                'type' => 'invoice.paid',
                'amount_paid' => 1900,
                'status' => 'paid',
                'hosted_invoice_url' => 'https://example.com/invoices/demo-001',
                'invoice_pdf' => 'https://example.com/invoices/demo-001.pdf',
            ],
            [
                'type' => 'invoice.payment_succeeded',
                'amount_paid' => 1900,
                'status' => 'paid',
                'hosted_invoice_url' => 'https://example.com/invoices/demo-002',
                'invoice_pdf' => 'https://example.com/invoices/demo-002.pdf',
            ],
            [
                'type' => 'invoice.finalized',
                'amount_due' => 1900,
                'status' => 'open',
                'hosted_invoice_url' => 'https://example.com/invoices/demo-003',
                'invoice_pdf' => 'https://example.com/invoices/demo-003.pdf',
            ],
        ];

        foreach ($invoicePayloads as $idx => $p) {
            $eventId = 'evt_demo_' . Str::lower(Str::random(18));
            WebhookEvent::firstOrCreate(
                ['event_id' => $eventId],
                [
                    'provider' => 'stripe',
                    'type' => $p['type'],
                    'payload' => array_merge($p, [
                        'id' => $eventId,
                        'customer' => $customer->stripe_customer_id,
                        'created' => now()->subDays(2 + $idx)->timestamp,
                    ]),
                    'processed_at' => now()->subDays(2 + $idx),
                ]
            );
        }

        $this->command?->info('Demo data seeded.');
    }
}

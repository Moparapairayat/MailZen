# MailZen - Email Marketing SaaS Platform

A complete email marketing SaaS application similar to MailWizz, built with Laravel 12, TailwindCSS, and Alpine.js.

This document is intended for operators (installing/deploying the app) and end users (using the admin/customer areas).

## Maintainer

- Maintained by **MOPARA PAIR AYAT** & **Fatema Binte Mariam**

## Tech Stack

- **Backend**: Laravel 12
- **Frontend**: Blade + Alpine.js + TailwindCSS
- **Database**: MySQL
- **Queue**: Laravel queues (recommended: Redis)
- **Authentication**: Laravel Sanctum

## Quick links

- **Public Docs page (in-app)**: `GET /docs`
- **Admin login**: `/admin/login`
- **Customer login**: `/login` or `/customer/login`

## Project Structure

```
app/
├── Actions/          # Atomic business tasks
├── Http/
│   ├── Controllers/
│   │   ├── Admin/    # Admin area controllers
│   │   └── Customer/ # Customer area controllers
│   └── Middleware/   # Custom middleware
├── Jobs/             # Queue jobs
├── Models/           # Eloquent models
└── Services/         # Business logic services

resources/
├── views/
│   ├── admin/        # Admin area views
│   ├── customer/     # Customer area views
│   └── components/   # Reusable UI components
├── css/
└── js/
```

## Requirements

- PHP 8.2+
- Composer
- Node.js + npm
- MySQL (or compatible) database
- Redis (recommended for queues)

Optional:

- PHP IMAP extension (required for bounce mailbox processing)

## Installation (local / self-hosted)

1. Clone the repository
2. Install dependencies:

   ```bash
   composer install
   npm install
   ```

3. Create your `.env`

   Copy the committed `.env.example` file to `.env`.

   ```bash
   cp .env.example .env
   ```

   Windows PowerShell:

   ```powershell
   Copy-Item .env.example .env
   ```

   At minimum you must configure:

   - `APP_NAME`, `APP_URL`
   - DB connection (`DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)
   - Mail settings (`MAIL_MAILER`, etc.)
   - Queue settings (recommended: Redis)

4. Generate application key:

   ```bash
   php artisan key:generate
   ```

5. Run migrations:

   ```bash
   php artisan migrate
   ```

6. (Recommended) Seed demo accounts:

   ```bash
   php artisan db:seed
   ```

7. Build frontend assets:

   ```bash
   npm run dev
   # or for production
   npm run build
   ```

## Default seeded accounts

When you run `php artisan db:seed`, the seeder creates:

- Admin:
  - Email: `admin@mailzen.com`
  - Password: `password`
- Customer:
  - Email: `customer@mailzen.com`
  - Password: `password`

Change these credentials immediately in any non-local environment.

## Running the app

### App server

Development:

```bash
php artisan serve
```

Production: use a proper web server (Nginx/Apache) pointing to `public/index.php`.

#### Deployment note: `index.php` paths

Some deployment setups move `vendor/` and `bootstrap/` into the same directory as `public/index.php` (for example, when the platform expects a single web root folder).

If you do this, you must update the paths in `public/index.php`:

- **Before** (default Laravel structure):
  - `require __DIR__.'/../vendor/autoload.php';`
  - `(require_once __DIR__.'/../bootstrap/app.php')`
- **After** (`vendor/` and `bootstrap/` are in the same directory as `index.php`):
  - `require __DIR__.'/vendor/autoload.php';`
  - `(require_once __DIR__.'/bootstrap/app.php')`

### Queues (required for sending/imports)

Campaign sending and subscriber imports run via queued jobs.

Run a worker:

```bash
php artisan queue:work
```

If you prefer Horizon, ensure it is configured for your environment and run:

```bash
php artisan horizon
```

### Scheduler / Cron (recommended)

The scheduler is used to:

- Start scheduled campaigns every minute (`campaigns:start-scheduled`)
- Process bounces every 5 minutes (`email:process-bounces --all`)

It is also used to check for updates (`updates:check`).

Configure a cron entry on your server:

```bash
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

## Automatic updates (Admin → Settings → Updates)

MailZen includes an automatic updater that can download and install new versions from the configured update server.

### Requirements (production)

- A running scheduler (cron): `php /path/to/artisan schedule:run` every minute.
- A running queue worker. **Important:** the update installer runs while the site is in maintenance mode, so your queue worker must run with `--force`:

```bash
php artisan queue:work --force
```

If the worker is not running, the install will remain stuck in the “queued” state.

### Configuration

In Admin → Settings → Updates, configure:

- Update server Base URL
- Product ID (if applicable)
- Product Name
- Product Secret
- License / Purchase code

Make sure `APP_URL` is correct because the updater uses it to derive the domain for license validation.

### How install works

- Click **Install Update** and confirm.
- The installer runs as a queued job.
- The app enters **maintenance mode** during installation.
- It downloads and extracts the update package, backs up existing files, applies the update, runs migrations, then brings the site back up.

### Failure reporting & troubleshooting

- The Updates tab shows the last failure reason and timestamp.
- Check `storage/logs` for the detailed error.
- If using database queues, check the `failed_jobs` table.

### Git hygiene (important)

Do not commit generated/compiled runtime files:

- `storage/framework/views/*.php` (compiled Blade views)

These files are generated by Laravel and should be ignored.

## Configuration

### Authentication model

This app has two main areas:

- **Admin** (guard: `admin`): manage platform settings, customers, plans, delivery infrastructure.
- **Customer** (guard: `customer`): manage lists, subscribers, campaigns, templates, analytics, billing.

### Email providers (env variables)

The application supports multiple delivery server types. Provider credentials are read from:

- Google OAuth:
  - `GOOGLE_CLIENT_ID`
  - `GOOGLE_CLIENT_SECRET`
  - `GOOGLE_REDIRECT_URI`
- Mailgun:
  - `MAILGUN_DOMAIN`
  - `MAILGUN_SECRET`
  - `MAILGUN_ENDPOINT` (default: `api.mailgun.net`)
- Amazon SES:
  - `AWS_ACCESS_KEY_ID`
  - `AWS_SECRET_ACCESS_KEY`
  - `AWS_DEFAULT_REGION` (default: `us-east-1`)
- SendGrid:
  - `SENDGRID_API_KEY`
- Postmark:
  - `POSTMARK_TOKEN`
- SparkPost:
  - `SPARKPOST_SECRET`

### Billing providers (env variables)

Billing is controlled by `BILLING_PROVIDER` (default: `stripe`).

- Stripe:
  - `STRIPE_SECRET`
  - `STRIPE_PUBLIC_KEY`
  - `STRIPE_WEBHOOK_SECRET`
- PayPal:
  - `PAYPAL_CLIENT_ID`
  - `PAYPAL_CLIENT_SECRET`
- Paystack:
  - `PAYSTACK_PUBLIC_KEY`
  - `PAYSTACK_SECRET`

Stripe webhook endpoint:

- `POST /webhooks/stripe`

### Webhooks (delivery events)

If you use provider webhooks for bounces/opens/clicks, configure your provider to call:

- Mailgun:
  - `POST /webhooks/mailgun`
  - `POST /webhooks/mailgun/bounce`
  - `POST /webhooks/mailgun/open`
  - `POST /webhooks/mailgun/click`
- Amazon SES:
  - `POST /webhooks/ses`
  - `POST /webhooks/ses/bounce`
  - `POST /webhooks/ses/open`
  - `POST /webhooks/ses/click`
- SendGrid:
  - `POST /webhooks/sendgrid`
  - `POST /webhooks/sendgrid/bounce`
  - `POST /webhooks/sendgrid/open`
  - `POST /webhooks/sendgrid/click`

Make sure `APP_URL` is set correctly so generated URLs match your public domain.

## Bounce processing

Bounce mailbox processing is implemented via `php artisan email:process-bounces`.

To process all active bounce servers:

```bash
php artisan email:process-bounces --all
```

To process a specific bounce server:

```bash
php artisan email:process-bounces --server=1
```

Bounce processing requires PHP IMAP (`imap_open` must be available).

For a detailed operational guide, see `BOUNCE_AND_COMPLAINT_SETUP.md`.

## User guide

## WordPress & WooCommerce Integration

MailZen can receive WordPress and WooCommerce events and use them as Automation Builder triggers (examples: `wp_user_registered`, `woo_order_completed`).

### Create an API key (MailZen)

1. Log in to the **Customer** area of MailZen.
2. Open the **API** page from the dashboard menu.
3. Click **Create API Key**.
4. Copy the key (it may be shown only once) and store it securely.

Notes:

- If your API key is lost, create a new one.
- The WordPress plugin uses this key for:
  - Testing the connection.
  - Fetching lists for dropdowns.
  - Sending events to MailZen.

### Install & configure the WordPress plugin

1. In WordPress admin, install the plugin ZIP from:
   - `wp-mailzen-integration.zip`
2. Activate the plugin.
3. Go to **WordPress Admin → Settings → MailZen Integration**.
4. Configure:
   - **Base URL**: Your MailZen URL (example: `https://mail.example.com`).
   - **API Key**: The key you created in MailZen (Dashboard → API → Create API Key).
5. Click **Save Settings**.
6. Click **Test Connection**.

Test Connection will fetch and store a signing secret used to sign event requests.

### Enable events

On the plugin settings page, enable the WordPress/WooCommerce events you want MailZen to receive.

### List routing (default, per-event, per-site)

MailZen supports routing different events to different lists.

Routing precedence:

1. **Per-site → per-event** mapping
2. **Per-event** mapping
3. **Default list**

You can also select:

- **No list (system)**: the plugin omits `list_id` and MailZen routes the event into a hidden per-customer system list (recommended when you want to trigger automations without choosing a marketing list).

### Build an Automation Builder flow (WordPress/WooCommerce)

1. Go to **Customer → Automations → Create**.
2. Choose a trigger under **WordPress** or **WooCommerce** (examples: `wp_user_registered`, `wp_user_updated`, `woo_order_completed`).
3. (Optional) Select a list in the trigger settings:
   - If a **list is selected**, the automation triggers only for events routed to that list.
   - If **no list is selected**, the automation can trigger for WordPress/WooCommerce events regardless of list routing.
4. Build your flow using nodes like:
   - **Delay** (example: wait 1 day)
   - **Email** (send message)
   - **Condition** (branch based on subscriber fields or event payload)

#### Example: “Order completed → Day 1/2/5 emails”

1. Trigger: `woo_order_completed` (list optional)
2. Email: “Thanks for your order”
3. Delay: 1 day
4. Email: “How was your delivery?”
5. Delay: 3 days
6. Email: “Review request / Upsell”

#### Using event payload in Conditions

Condition nodes support `payload.*` fields for WordPress/WooCommerce triggers.

- Example field formats:
  - `payload.user_id`
  - `payload.order_id`
  - `payload.site.url`

The exact payload keys depend on the event. If you’re unsure what is available, review the WordPress integration hook definitions in the plugin source.

### Admin area

- Login: `/admin/login`
- Primary modules:
  - Customers, customer groups
  - Plans, coupons, invoices
  - Delivery servers, bounce servers
  - Sending domains, tracking domains
  - Settings

### Customer area

- Login: `/login` or `/customer/login`
- Typical workflow:
  1. Create an Email List
  2. Add Subscribers (manual or CSV import)
  3. Create a Template (optional)
  4. Create a Campaign
  5. Start campaign (queues must be running)
  6. Review analytics

CSV imports are queued and uploaded files are stored under `storage/app/imports`.

## Development

- Dev server: `php artisan serve`
- Queue worker: `php artisan queue:work`
- Scheduler (manual run): `php artisan schedule:run`
- Assets: `npm run dev`

## Features

### Admin Area
- Dashboard with statistics
- User management with groups and permissions
- Customer management
- Settings system
- And more...

### Customer Area
- Dashboard with activity feed
- Email Lists management
- Campaigns (regular, autoresponder, recurring)
- Templates
- Analytics and tracking
- **Spintax support** for content variation
- **Spam scoring** to avoid spam filters
- And more...

## Advanced Features

### Spintax and Spam Scoring

MailZen includes advanced content optimization features to improve email deliverability:

- **Spintax**: Create multiple content variations using `{option1|option2|option3}` syntax
- **Spam Scoring**: Automatic content analysis to prevent spam-like emails
- **Configurable Thresholds**: Set custom spam score limits
- **Detailed Logging**: Track spam scores and blocked emails


## License

MIT

# NinjaCRM — Phased Implementation Plan

## Context

**Problem:** Coaches, consultants, and DFY outreach agencies doing manual LinkedIn prospecting have no CRM designed for their specific workflow. They cobble together spreadsheets, Notion boards, and generic CRMs (Pipedrive, Breakcold) that don't mirror how relationship-first outreach actually works.

**Solution:** NinjaCRM — a LinkedIn-native CRM built on Laravel 12 + Filament 3 with Supabase (PostgreSQL + Storage) that tracks conversations, follow-ups, and offers at a granular level, with built-in playbooks, daily action views, and agency support.

**Approach:** Fresh Laravel 12 project. Supabase for database (PostgreSQL) and file storage. Laravel native auth. Patterns proven in MediaVault (multi-tenant Filament, service layer, enum system, Cashier billing) copied into new domain models.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| Admin Panel | Filament 3 (multi-tenant) |
| Database | Supabase PostgreSQL |
| File Storage | Supabase Storage (S3-compatible) |
| Auth | Laravel Fortify + Sanctum + Socialite (OAuth SSO) |
| Billing | Laravel Cashier (Stripe) |
| Permissions | spatie/laravel-permission |
| Activity Log | spatie/laravel-activitylog |
| CSV/Excel | pxlrbt/filament-excel + Laravel Excel |
| Webhooks | spatie/laravel-webhook-server |
| Testing | Pest PHP |
| Code Quality | Pint + Larastan |
| Queue | Laravel Queue (database driver) |
| Cache | Redis (or database) |
| Deployment | Laravel Forge / Vapor + Supabase |

---

## Phase 0: Project Setup & Infrastructure (Week 1)

### 0.1 Fresh Laravel Install
```
laravel new ninjacrm
```
- Install Filament 3: `composer require filament/filament`
- Install core packages (see package list below)
- Configure `.env` for Supabase PostgreSQL connection
- Configure Supabase Storage as S3-compatible filesystem disk
- Set up Filament panel at `/app` with multi-tenant workspace

### 0.2 Packages to Install
```
filament/filament ^3.3
laravel/cashier ^16.5
laravel/fortify ^1.24
laravel/sanctum ^4.0
laravel/socialite ^5.0
spatie/laravel-permission ^7.2
spatie/laravel-activitylog ^4.12
spatie/laravel-medialibrary ^11.0
pxlrbt/filament-excel ^2.0
maatwebsite/excel ^3.1
spatie/laravel-webhook-server ^3.0
pestphp/pest ^3.0
larastan/larastan ^3.0
laravel/pint ^1.0
barryvdh/laravel-ide-helper ^3.0
```

### 0.3 Auth Scaffolding

**Standard Auth (Fortify):**
- Registration, login, email verification, password reset, 2FA
- Sanctum for API token auth (future Chrome Extension)
- Custom registration flow: creates User + Workspace + owner membership + generates referral code

**OAuth / SSO (Socialite):**
- Laravel Socialite for third-party authentication
- Supported providers (configurable, add more later):
  - **Google** — primary SSO option, most universal
  - **LinkedIn** — natural fit for the target audience
  - **Microsoft** — for enterprise/agency users on Microsoft 365
  - **GitHub** — optional, for developer-adjacent users
- Provider credentials stored in `.env` (client ID, client secret, redirect URI per provider)
- Each provider enabled/disabled via config — disabled providers hide their login buttons automatically

**Registration Flows (two paths, same outcome):**

Path A — Email/Password:
1. User fills registration form (name, email, password)
2. System creates User + Workspace + owner membership + referral code
3. Email verification sent → user verifies → full access

Path B — OAuth SSO:
1. User clicks "Sign in with Google" (or LinkedIn, etc.) on login/register page
2. Redirected to provider → authorizes → callback with profile data
3. System checks: does a user with this email already exist?
   - **New user:** creates User (password nullable), `social_accounts` record, Workspace + owner membership + referral code, email auto-verified (trusted from provider)
   - **Existing user, no linked social:** links `social_accounts` record to existing user, logs in
   - **Existing user, already linked:** logs in directly
4. Redirected to Filament dashboard

**Account Linking (post-registration):**
- Users can link/unlink OAuth providers from their profile settings page
- A user must always have at least one auth method (password OR linked social account) — cannot unlink last social if no password set, cannot remove password if no social linked
- "Set Password" option on profile for OAuth-only users who want to add email/password login
- "Link Google Account" / "Link LinkedIn Account" buttons on profile settings

**Security Considerations:**
- OAuth users who haven't set a password: password reset flow prompts them to set an initial password (not "reset")
- Email collision handling: if OAuth email matches existing unverified account, merge into OAuth account and mark email as verified
- If OAuth email matches existing verified account with a different auth method, prompt user to log in with existing method first, then link from profile
- CSRF protection on all OAuth callback routes
- State parameter validation to prevent OAuth redirect attacks
- Provider tokens stored encrypted in `social_accounts` table (for potential future API access to provider, e.g., LinkedIn profile enrichment)

### 0.4 Filament Multi-Tenant Panel
- Tenant model: `Workspace`
- Panel path: `/app`
- Tenant registration: `RegisterWorkspace` page
- Tenant profile: `WorkspaceSettings` page
- Navigation groups: Contacts, Pipeline, Outreach, Reporting, Settings

### 0.5 Supabase Configuration
```php
// config/database.php — Supabase PostgreSQL
'pgsql' => [
    'driver' => 'pgsql',
    'url' => env('SUPABASE_DB_URL'),
    'host' => env('DB_HOST'), // db.xxxx.supabase.co
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'postgres'),
    'username' => env('DB_USERNAME', 'postgres'),
    'password' => env('DB_PASSWORD'),
    'sslmode' => 'require',
]

// config/filesystems.php — Supabase Storage (S3-compatible)
'supabase' => [
    'driver' => 's3',
    'key' => env('SUPABASE_STORAGE_KEY'),
    'secret' => env('SUPABASE_STORAGE_SECRET'),
    'region' => env('SUPABASE_STORAGE_REGION'),
    'bucket' => env('SUPABASE_STORAGE_BUCKET'),
    'endpoint' => env('SUPABASE_STORAGE_ENDPOINT'),
]
```

---

## Phase 1: Core CRM MVP (Weeks 2-8)

### Phase 1A: Data Modeling (Week 2)

#### Enums
| Enum | Values |
|---|---|
| `WorkspaceRole` | `owner`, `admin`, `member` |
| `ContactTemperature` | `hot`, `warm`, `cold` |
| `ContactSource` | `linkedin`, `email`, `referral`, `website`, `event`, `other` |
| `LifecycleStage` | `prospect`, `lead`, `opportunity`, `customer`, `churned`, `disqualified` |
| `PipelineStageType` | `new`, `connected`, `in_conversation`, `qualified`, `call_booked`, `proposal_sent`, `negotiation`, `closed_won`, `closed_lost`, `not_a_fit` |
| `DealStatus` | `open`, `won`, `lost`, `abandoned` |
| `LeadQualification` | `unqualified`, `mql` (marketing), `sql` (sales), `pql` (product) |
| `ConversionEventType` | `prospect_to_lead`, `lead_to_opportunity`, `opportunity_to_customer`, `customer_to_churned`, `reactivation` |
| `TouchpointType` | `connection_request`, `message_sent`, `message_received`, `comment`, `like`, `invite`, `call`, `proposal`, `email`, `note` |
| `TaskStatus` | `pending`, `completed`, `cancelled` |
| `TaskPriority` | `low`, `medium`, `high`, `urgent` |
| `CampaignStatus` | `draft`, `active`, `paused`, `completed`, `archived` |
| `SubscriptionTier` | `free`, `solo`, `pro`, `agency`, `lifetime` |
| `SubscriptionStatus` | `trialing`, `active`, `past_due`, `canceled`, `expired` |
| `ReferralStatus` | `pending`, `active`, `paying`, `paid_out`, `expired` |
| `CommissionStatus` | `pending`, `approved`, `paid`, `canceled` |
| `PayoutStatus` | `pending_approval`, `approved`, `processing`, `completed`, `failed` |
| `CommissionType` | `percentage`, `flat` |

All enums: string-backed PHP 8.1+, with `label()`, `color()`, `options()` methods.

#### Migrations & Models

**`users`** (Laravel default, extended)
```
id: ulid (pk)
name: string
email: string (unique)
email_verified_at: timestamp (nullable)
password: string (nullable) — nullable for OAuth-only users who registered via SSO
avatar_url: string (nullable) — populated from OAuth provider profile photo
auth_provider: string (nullable) — primary auth method used at registration (e.g., 'google', 'linkedin', null for email/password)
referred_by_user_id: fk → users (nullable) — the user who referred this person to the platform
referral_code: string (unique) — auto-generated at registration, used in referral links
referral_earnings_total: decimal(10,2) (default 0.00) — cached lifetime earnings for quick display
two_factor_secret: text (nullable) — Fortify 2FA
two_factor_recovery_codes: text (nullable)
two_factor_confirmed_at: timestamp (nullable)
remember_token: string (nullable)
created_at, updated_at
INDEX: referred_by_user_id
INDEX: referral_code
```

**`social_accounts`** (OAuth provider identities linked to users)
```
id: ulid (pk)
user_id: fk → users
provider: string — e.g. 'google', 'linkedin', 'microsoft', 'github'
provider_user_id: string — the user's ID on the OAuth provider
provider_email: string (nullable) — email from provider (may differ from user.email)
provider_avatar: string (nullable) — avatar URL from provider
access_token: text (encrypted) — for potential API calls to provider
refresh_token: text (nullable, encrypted) — for refreshing access tokens
token_expires_at: timestamp (nullable)
scopes: json (nullable) — OAuth scopes granted
last_used_at: timestamp (nullable) — last time user logged in via this provider
created_at, updated_at
UNIQUE(provider, provider_user_id) — one identity per provider
INDEX: user_id
```

**`workspaces`** (tenant root)
```
id: ulid (pk)
name: string
slug: string (unique, for URL)
plan_id: fk → plans (nullable) — current active plan, null = free tier
logo_path: string (nullable)
settings: json (nullable) — timezone, default pipeline, etc.
subscription_status: enum (trialing/active/past_due/canceled/expired, default trialing)
is_lifetime: boolean (default false) — lifetime deal flag, bypasses subscription checks
stripe_id: string (nullable) — Cashier
pm_type: string (nullable)
pm_last_four: string (nullable)
trial_ends_at: timestamp (nullable)
grace_period_ends_at: timestamp (nullable) — set when subscription lapses, access retained until this date
created_at, updated_at, deleted_at
```

**`workspace_members`**
```
id: ulid (pk)
workspace_id: fk → workspaces
user_id: fk → users
role: enum (owner/admin/member)
joined_at: timestamp
created_at, updated_at
UNIQUE(workspace_id, user_id)
```

**`plans`**
```
id: ulid (pk)
name: string
slug: string (unique)
tier: enum (free/solo/pro/agency/lifetime) — maps to SubscriptionTier enum
is_free: boolean (default false) — true for the free tier (no Stripe subscription required)
stripe_price_id_monthly: string (nullable) — null for free/lifetime plans
stripe_price_id_yearly: string (nullable) — null for free/lifetime plans
max_contacts: integer (nullable = unlimited)
max_users: integer (nullable = unlimited)
max_pipelines: integer (nullable = unlimited)
max_clients: integer (nullable = unlimited)
max_deals: integer (nullable = unlimited)
can_use_campaigns: boolean (default false)
can_use_templates: boolean (default true)
can_use_webhooks: boolean (default false)
can_use_deals: boolean (default true) — deal/opportunity tracking
can_use_lead_scoring: boolean (default false) — custom lead scoring rules
can_use_advanced_reporting: boolean (default false) — conversion funnel, revenue forecast, etc.
can_white_label: boolean (default false)
can_use_chrome_extension: boolean (default false)
can_use_api: boolean (default false) — REST API access (Sanctum)
price_monthly: decimal(8,2)
price_yearly: decimal(8,2)
features: json (nullable) — for marketing display
sort_order: integer (default 0)
is_active: boolean (default true)
created_at, updated_at
```

**`contacts`** (core CRM entity)
```
id: ulid (pk)
workspace_id: fk → workspaces
pipeline_id: fk → pipelines (nullable)
pipeline_stage_id: fk → pipeline_stages (nullable)
client_id: fk → clients (nullable) — for agency multi-client
assigned_to_user_id: fk → users (nullable)
first_name: string
last_name: string
email: string (nullable)
phone: string (nullable)
linkedin_url: string (nullable)
linkedin_username: string (nullable)
company: string (nullable)
job_title: string (nullable)
location: string (nullable)
niche: string (nullable)
avatar_path: string (nullable)
temperature: enum (hot/warm/cold, default cold)
lifecycle_stage: enum (prospect/lead/opportunity/customer/churned/disqualified, default prospect)
qualification: enum (unqualified/mql/sql/pql, default unqualified)
lead_score: integer (default 0) — calculated from engagement, profile fit, and activity
source: enum (linkedin/email/referral/etc.)
custom_fields: json (nullable)
notes: text (nullable)
last_contacted_at: timestamp (nullable)
next_follow_up_at: timestamp (nullable)
converted_to_lead_at: timestamp (nullable)
converted_to_customer_at: timestamp (nullable)
created_at, updated_at, deleted_at
INDEX: workspace_id, lifecycle_stage
INDEX: workspace_id, temperature
INDEX: workspace_id, pipeline_stage_id
INDEX: workspace_id, next_follow_up_at
INDEX: linkedin_url
FULLTEXT: first_name, last_name, company, email
```

**`pipelines`**
```
id: ulid (pk)
workspace_id: fk → workspaces
name: string
description: text (nullable)
is_default: boolean (default false)
sort_order: integer (default 0)
created_at, updated_at
```

**`pipeline_stages`**
```
id: ulid (pk)
pipeline_id: fk → pipelines
name: string
type: enum (new/connected/in_conversation/qualified/call_booked/proposal_sent/negotiation/closed_won/closed_lost/not_a_fit)
color: string (nullable) — hex color
default_probability: integer (0-100) — win probability at this stage (e.g. qualified=20%, proposal_sent=50%, negotiation=75%)
maps_to_lifecycle: enum (nullable) — auto-set contact lifecycle when entering this stage
sort_order: integer
is_terminal: boolean (default false) — closed_won/closed_lost/not_a_fit
created_at, updated_at
```

**`deals`** (opportunity/deal tracking with monetary value)
```
id: ulid (pk)
workspace_id: fk → workspaces
contact_id: fk → contacts
pipeline_id: fk → pipelines
pipeline_stage_id: fk → pipeline_stages
assigned_to_user_id: fk → users (nullable)
client_id: fk → clients (nullable)
name: string — e.g. "Coaching Package — John Smith"
value: decimal(12,2) — deal monetary value
currency: string (default 'usd')
probability: integer (0-100) — weighted by stage, overridable
expected_close_date: date (nullable)
actual_close_date: date (nullable)
status: enum (open/won/lost/abandoned)
lost_reason: string (nullable)
notes: text (nullable)
metadata: json (nullable) — custom deal properties
won_at: timestamp (nullable)
lost_at: timestamp (nullable)
created_at, updated_at, deleted_at
INDEX: workspace_id, status
INDEX: workspace_id, pipeline_stage_id
INDEX: workspace_id, expected_close_date
INDEX: contact_id
```

**`conversion_events`** (lifecycle transition audit log)
```
id: ulid (pk)
workspace_id: fk → workspaces
contact_id: fk → contacts
deal_id: fk → deals (nullable)
user_id: fk → users (who triggered conversion)
type: enum (prospect_to_lead/lead_to_opportunity/opportunity_to_customer/customer_to_churned/reactivation)
from_stage: string — previous lifecycle_stage
to_stage: string — new lifecycle_stage
qualification_at_conversion: enum (nullable) — lead qualification level at time of conversion
deal_value_at_conversion: decimal(12,2) (nullable) — deal value snapshot
source_attribution: string (nullable) — campaign, referral, or channel that drove conversion
notes: text (nullable)
converted_at: timestamp
created_at, updated_at
INDEX: workspace_id, type
INDEX: contact_id, converted_at
INDEX: workspace_id, converted_at
```

**`lead_scoring_rules`** (configurable scoring criteria per workspace)
```
id: ulid (pk)
workspace_id: fk → workspaces
name: string — e.g. "Has LinkedIn URL", "Replied within 7 days"
category: string — profile_fit, engagement, behavior
condition: json — rule definition (field, operator, value)
points: integer — positive or negative score
is_active: boolean (default true)
sort_order: integer (default 0)
created_at, updated_at
```

**`touchpoints`** (conversation/activity timeline per contact)
```
id: ulid (pk)
workspace_id: fk → workspaces
contact_id: fk → contacts
user_id: fk → users (who logged it)
type: enum (connection_request/message_sent/message_received/comment/like/invite/call/proposal/email/note)
subject: string (nullable)
body: text (nullable) — message content or note
metadata: json (nullable) — extra data (call duration, link to post, etc.)
occurred_at: timestamp
created_at, updated_at
INDEX: contact_id, occurred_at
```

**`tasks`** (follow-up system)
```
id: ulid (pk)
workspace_id: fk → workspaces
contact_id: fk → contacts (nullable)
assigned_to_user_id: fk → users (nullable)
created_by_user_id: fk → users
title: string
description: text (nullable)
status: enum (pending/completed/cancelled)
priority: enum (low/medium/high/urgent)
due_at: timestamp (nullable)
completed_at: timestamp (nullable)
created_at, updated_at
INDEX: workspace_id, status, due_at
INDEX: assigned_to_user_id, status, due_at
```

**`templates`** (message scripts/templates)
```
id: ulid (pk)
workspace_id: fk → workspaces
campaign_id: fk → campaigns (nullable)
name: string
category: string (nullable) — connection, follow_up, bump, cta, nurture
subject: string (nullable)
body: text — supports variables {First_Name}, {Niche}, {Offer}, etc.
is_favorite: boolean (default false)
use_count: integer (default 0)
sort_order: integer (default 0)
created_at, updated_at
```

**`campaigns`**
```
id: ulid (pk)
workspace_id: fk → workspaces
name: string
description: text (nullable)
status: enum (draft/active/paused/completed/archived)
target_segment: json (nullable) — filter criteria
settings: json (nullable) — steps, timing, etc.
stats_cache: json (nullable) — cached metrics
started_at: timestamp (nullable)
completed_at: timestamp (nullable)
created_at, updated_at
```

**`campaign_contacts`** (pivot)
```
id: ulid (pk)
campaign_id: fk → campaigns
contact_id: fk → contacts
current_step: integer (default 0)
status: string — active/completed/removed/replied
enrolled_at: timestamp
last_step_at: timestamp (nullable)
created_at, updated_at
UNIQUE(campaign_id, contact_id)
```

**`clients`** (for agency multi-client support)
```
id: ulid (pk)
workspace_id: fk → workspaces
name: string
slug: string
logo_path: string (nullable)
settings: json (nullable)
notes: text (nullable)
is_active: boolean (default true)
created_at, updated_at, deleted_at
```

**`referral_commission_tiers`** (admin-configurable commission structure)
```
id: ulid (pk)
name: string — e.g. "Standard", "VIP Affiliate", "Launch Partner"
is_default: boolean (default false) — assigned to new users automatically
commission_type: enum (percentage/flat) — percentage of subscription or flat dollar amount
commission_rate: decimal(5,2) — percentage (e.g. 20.00 = 20%) or flat amount in dollars
commission_duration_months: integer (nullable) — how many months commission is earned per referral (null = lifetime)
min_referrals_required: integer (default 0) — minimum referrals to qualify for this tier (for volume-based tiers)
applies_to_plans: json (nullable) — array of plan slugs this tier applies to (null = all plans)
bonus_per_referral: decimal(8,2) (nullable) — one-time signup bonus per referred user
is_active: boolean (default true)
created_at, updated_at
```

**`referrals`** (affiliate/referral system)
```
id: ulid (pk)
referrer_user_id: fk → users
referred_user_id: fk → users (nullable — set when they sign up)
commission_tier_id: fk → referral_commission_tiers — which commission structure applies
referral_code: string (unique)
status: enum (pending/active/paying/paid_out/expired)
commission_rate_snapshot: decimal(5,2) — rate at time of referral (frozen so tier changes don't retroactively alter)
commission_earned_total: decimal(10,2) (default 0.00) — running total earned from this referral
commission_paid_total: decimal(10,2) (default 0.00) — running total paid out for this referral
referred_plan: string (nullable) — plan slug the referred user subscribed to
referred_at: timestamp (nullable) — when the referred user actually signed up
activated_at: timestamp (nullable) — when the referred user first subscribed (commission starts)
expires_at: timestamp (nullable) — when commission earning period ends for this referral
paid_at: timestamp (nullable) — final payout date (for fully paid-out referrals)
metadata: json (nullable) — for future Rewardful migration
created_at, updated_at
INDEX: referral_code
INDEX: referrer_user_id, status
INDEX: referred_user_id
```

**`referral_commissions`** (per-payment commission log)
```
id: ulid (pk)
referral_id: fk → referrals
referrer_user_id: fk → users — denormalized for fast earnings queries
subscription_payment_id: string (nullable) — Stripe invoice ID that triggered this commission
amount: decimal(8,2) — commission amount earned for this payment
subscription_amount: decimal(8,2) — the subscription payment amount this was calculated from
commission_rate_applied: decimal(5,2) — rate used for this calculation
status: enum (pending/approved/paid/canceled)
period_start: date — subscription billing period this commission covers
period_end: date
approved_at: timestamp (nullable) — admin approval timestamp
approved_by_user_id: fk → users (nullable) — which admin approved
created_at, updated_at
INDEX: referrer_user_id, status
INDEX: referral_id, period_start
```

**`referral_payouts`** (batch payout records)
```
id: ulid (pk)
referrer_user_id: fk → users — the affiliate receiving the payout
total_amount: decimal(10,2) — sum of all commissions in this payout
currency: string (default 'usd')
payout_method: enum (stripe_connect/manual/paypal/bank_transfer)
stripe_transfer_id: string (nullable)
external_reference: string (nullable) — PayPal transaction ID, bank ref, etc.
status: enum (pending_approval/approved/processing/completed/failed)
commissions_included: json — array of referral_commission IDs included in this payout
period_from: date — earliest commission date included
period_to: date — latest commission date included
approved_by_user_id: fk → users (nullable)
approved_at: timestamp (nullable)
paid_at: timestamp (nullable)
failed_reason: string (nullable)
notes: text (nullable)
created_at, updated_at
INDEX: referrer_user_id, status
INDEX: status, created_at
```

**`webhook_endpoints`** (for Zapier/Make integration)
```
id: ulid (pk)
workspace_id: fk → workspaces
name: string
url: string
secret: string — HMAC signing key
events: json — array of event types to send
is_active: boolean (default true)
last_triggered_at: timestamp (nullable)
created_at, updated_at
```

**`webhook_deliveries`** (delivery log)
```
id: ulid (pk)
webhook_endpoint_id: fk → webhook_endpoints
event: string
payload: json
response_status: integer (nullable)
response_body: text (nullable)
attempts: integer (default 0)
delivered_at: timestamp (nullable)
created_at, updated_at
```

### Phase 1B: Core Filament Resources (Week 3)

**ContactResource**
- Form: 3 sections (Personal Info, LinkedIn Details, Pipeline & Status)
- Table: name, company, pipeline stage badge, temperature badge, last contacted, next follow-up
- Filters: temperature, pipeline stage, source, assigned_to, client, "no response in X days"
- Bulk actions: change temperature, assign to user, move stage, add to campaign
- Global search enabled (name, company, email, LinkedIn)

**PipelineResource**
- Form: name, description, is_default toggle
- Relation manager: PipelineStages (inline, sortable)

**PipelineStageResource**
- Managed inline via PipelineResource relation manager
- Sortable, color picker, terminal toggle

**Pipeline Kanban Page** (custom Filament page)
- Kanban board view of contacts grouped by pipeline stage
- Drag-and-drop to move contacts between stages
- Quick view modal on card click
- Package: `mokhosh/filament-kanban` or custom Livewire component

**DealResource**
- Form: name, contact (searchable), value, currency, probability, expected_close_date, pipeline stage
- Table: name, contact, value (formatted currency), stage badge, probability %, expected close, status
- Filters: status, pipeline stage, assigned_to, value range, expected close range
- Bulk actions: change stage, assign to user, mark won/lost
- Relation manager on ContactResource — see all deals per contact
- "Weighted Value" display: value × probability

**Lifecycle & Conversion Tracking**
- Lifecycle stage badge on ContactResource table + form
- Lead qualification dropdown on contact form
- Lead score display (auto-calculated, read-only on form)
- "Convert" action button on contact record:
  - Prospect → Lead: sets `lifecycle_stage`, `converted_to_lead_at`, logs `ConversionEvent`
  - Lead → Opportunity: creates Deal, updates lifecycle, logs event
  - Opportunity → Customer: marks deal won, updates lifecycle, `converted_to_customer_at`, logs event
- ConversionEvent timeline: visible on contact record alongside touchpoints
- `ConversionService::convert(Contact, targetStage, ?Deal, ?notes)` — handles all state changes, event logging, webhook dispatch

**LeadScoringResource** (Settings area)
- CRUD for workspace-level scoring rules
- Categories: Profile Fit (has email, has LinkedIn, job title match), Engagement (replied, opened, clicked), Behavior (visited pricing, booked call)
- `LeadScoringService::calculate(Contact)` — evaluates all active rules, returns total score
- `LeadScoringService::recalculateAll(Workspace)` — batch job for workspace
- Scheduled command: `RecalculateLeadScores` — nightly recalculation

### Phase 1C: Touchpoint/Activity Tracking (Week 4)

**TouchpointResource**
- Managed via relation manager on ContactResource (not standalone)
- Timeline-style display (newest first)
- Quick-add form: type dropdown, body textarea, occurred_at
- Each touchpoint auto-updates `contact.last_contacted_at`

**Services:**
- `TouchpointService::log(Contact, TouchpointType, ?body, ?metadata)` — creates touchpoint, updates contact timestamps, fires webhook events

### Phase 1D: Templates & Scripts Engine (Week 4)

**TemplateResource**
- Form: name, category, subject, body (rich text with variable buttons)
- Variable insertion: toolbar buttons for `{First_Name}`, `{Last_Name}`, `{Company}`, `{Niche}`, `{Offer}`
- Table: name, category, use_count, is_favorite toggle
- Preview action: renders template with sample data
- "Copy to clipboard" action: renders with contact data

**Services:**
- `TemplateService::render(Template, Contact)` — replaces variables with contact data
- `TemplateService::getAvailableVariables()` — returns list of supported variables

### Phase 1E: Today View / Daily Action Dashboard (Week 5)

**Custom Filament Page: `TodayView`**
- 3-column layout:
  1. **Follow-ups Due** — contacts with `next_follow_up_at <= today`, sorted by priority
  2. **New Connection Requests** — contacts in "New" stage added in last 7 days
  3. **Open Conversations** — contacts in "In Conversation" stage with no touchpoint in 3+ days
- Quick action buttons on each card: log touchpoint, snooze, move stage, mark done
- Stats bar at top: X follow-ups due, Y new to connect, Z conversations waiting

**Widgets:**
- `DailyStatsWidget` — follow-ups due, messages to send, calls this week
- `PipelineOverviewWidget` — contact count per stage (bar chart)
- `DealPipelineWidget` — open deals by stage with weighted value (funnel chart)
- `ConversionMetricsWidget` — this month's conversion rates: prospect→lead, lead→opp, opp→customer
- `ActivityFeedWidget` — last 10 touchpoints across workspace
- `PerformanceWidget` — connections/week, response rate, calls booked, deals won

### Phase 1F: Follow-up & Task System (Week 5)

**TaskResource**
- Form: title, description, contact (searchable select), priority, due_at
- Table: title, contact name, priority badge, due_at (red when overdue), status
- Filters: status, priority, assigned_to, overdue
- "Complete" action: sets status + completed_at
- Calendar integration: optional link field per user

**Services:**
- `TaskService::createFollowUp(Contact, Carbon $dueAt, ?string $title)` — creates task + updates `contact.next_follow_up_at`
- `TaskService::complete(Task)` — marks done, recalculates contact's next follow-up

**Scheduled Command:**
- `SendFollowUpReminders` — daily, sends notification for tasks due today

### Phase 1G: Basic Reporting (Week 6)

**Custom Filament Page: `ReportsPage`**
- **Connections Report** — new contacts per week/month (line chart)
- **Response Rates** — touchpoints sent vs received per campaign/overall
- **Calls Booked** — per week/month (bar chart)
- **Pipeline Velocity** — avg days per stage
- **Team Leaderboard** — contacts added, touchpoints logged, calls booked per user
- **Deal Revenue Report** — total deal value by stage, weighted pipeline value, won/lost ratio
- **Conversion Funnel** — prospect → lead → opportunity → customer conversion rates with drop-off analysis
- **Lead Scoring Distribution** — histogram of lead scores, avg score by source/campaign
- **Lifecycle Stage Breakdown** — contacts per lifecycle stage over time (stacked area chart)
- **Revenue Forecast** — weighted pipeline value by expected close date (forward-looking)
- Date range picker for all reports
- Export to CSV

**Services:**
- `ReportingService::connectionsPerPeriod(Workspace, DateRange)`
- `ReportingService::responseRates(Workspace, ?Campaign)`
- `ReportingService::callsBooked(Workspace, DateRange)`
- `ReportingService::pipelineVelocity(Workspace, Pipeline)`
- `ReportingService::dealRevenue(Workspace, DateRange)` — won deals, pipeline value, avg deal size
- `ReportingService::conversionFunnel(Workspace, DateRange)` — stage-to-stage conversion rates
- `ReportingService::revenueForecast(Workspace)` — weighted pipeline by expected close date
- `ReportingService::leadScoreDistribution(Workspace)` — score histogram + source breakdown

### Phase 1H: Multi-Client Support (Week 6)

**ClientResource**
- Form: name, slug, logo, notes, is_active
- Table: name, contact count, active toggle
- Client filter available on ContactResource, TaskResource, Reports

**`BelongsToWorkspace` Trait Enhancement:**
- Add optional `client_id` scoping for agency-tier workspaces
- Client switcher in sidebar (for agency plan only)

### Phase 1I: Import/Export & Webhooks (Week 7)

**CSV Import:**
- Upload CSV → map columns → preview → import
- Supports: contacts, touchpoints
- Duplicate detection by LinkedIn URL or email
- Package: `maatwebsite/excel` for parsing + Filament import action

**CSV Export:**
- Export contacts (filtered) to CSV/Excel
- Export per-client summary report
- Package: `pxlrbt/filament-excel`

**Webhook System:**
- `WebhookEndpointResource` — CRUD for endpoint URLs
- Events: `contact.created`, `contact.updated`, `contact.stage_changed`, `contact.lifecycle_changed`, `contact.converted`, `deal.created`, `deal.won`, `deal.lost`, `deal.stage_changed`, `touchpoint.created`, `task.completed`, `call.booked`
- `WebhookService::dispatch(event, payload)` — queued delivery with retry (3 attempts)
- HMAC signature on each delivery for verification
- Delivery log viewable per endpoint

### Phase 1J: Billing — Stripe Cashier + Plan Limits (Week 7-8)

**Billing Setup:**
- `Workspace` model gets `Billable` trait (same pattern as MediaVault's Family)
- Cashier migrations for `customers`, `subscriptions`, `subscription_items`

**Plan Tiers:**
| Feature | Free ($0) | Solo ($29-39/mo) | Pro ($79-99/mo) | Agency ($199-299/mo) |
|---|---|---|---|---|
| Users | 1 | 1 | 5 | 15 |
| Contacts | 100 | 5,000 | 25,000 | Unlimited |
| Pipelines | 1 | 2 | Unlimited | Unlimited |
| Deals | 10 | 500 | Unlimited | Unlimited |
| Clients | 0 | 0 | 0 | 10 |
| Campaigns | No | No | Yes | Yes |
| Templates | 5 | Yes | Yes | Yes |
| Lead Scoring | No | No | Yes | Yes |
| Advanced Reporting | No | No | Yes | Yes |
| Webhooks | No | No | Yes | Yes |
| API Access | No | No | Yes | Yes |
| Chrome Extension | No | No | Yes | Yes |
| White-label | No | No | No | Yes |

**Services:**
- `PlanLimitService` (copy pattern from MediaVault)
  - `canAddContact(Workspace)` — checks max_contacts
  - `canAddUser(Workspace)` — checks max_users
  - `canAddPipeline(Workspace)` — checks max_pipelines
  - `canAddDeal(Workspace)` — checks max_deals
  - `canAddClient(Workspace)` — checks max_clients
  - `canUseCampaigns(Workspace)` — checks can_use_campaigns
  - `canUseWebhooks(Workspace)` — checks can_use_webhooks
  - `canUseDeals(Workspace)` — checks can_use_deals
  - `canUseLeadScoring(Workspace)` — checks can_use_lead_scoring
  - `canUseAdvancedReporting(Workspace)` — checks can_use_advanced_reporting
  - `canUseApi(Workspace)` — checks can_use_api
  - `canUseFeature(Workspace, string $feature): bool` — generic check against any `can_use_*` column
  - `canAdd(Workspace, string $resource): bool` — generic check against any `max_*` column
  - `getRemainingCapacity(Workspace, string $resource): int|null` — how many more can be created (null = unlimited)
  - `getUpgradeRequirements(Workspace, string $feature): ?Plan` — lowest plan that unlocks a feature
  - `isSubscriptionActive(Workspace): bool` — active, trialing, lifetime, or within grace period
  - `getUsageSummary(Workspace)` — full breakdown of usage vs limits

**Filament Pages:**
- `BillingPage` — current plan, usage stats, upgrade/downgrade, payment method, invoices
- `PlanSelectionPage` — pricing cards during registration or upgrade
- Stripe Customer Portal link for self-service billing

**Founding Member Lifetime Deal:**
- Special `lifetime` plan with no Stripe subscription
- One-time Stripe Checkout session ($497-$997)
- `is_lifetime` flag on workspace
- PlanLimitService respects lifetime as highest tier

**Free Tier & Trial Handling:**
- `free` plan: default tier assigned at workspace creation, no Stripe subscription required
- Free tier provides limited access (e.g., reduced contacts, no campaigns, no webhooks) — exact limits defined per `plans` table
- 14-day trial: `trial_ends_at` set on workspace creation, grants full Pro-level access during trial period
- `SubscriptionStatus` enum tracks state: `trialing`, `active`, `past_due`, `canceled`, `expired`
- Trial expiration: scheduled command `ExpireTrials` runs daily, transitions expired trials to `free` tier
- Grace period: `past_due` subscriptions retain access for configurable grace window (default 3 days) before downgrading to `free`
- Downgrade behavior: workspace retains data but loses access to gated features, with read-only access to data that exceeds new plan limits (e.g., contacts beyond free tier cap are visible but not editable)

### Phase 1J-b: Subscription Security Model (Week 8)

This phase implements the middleware, gates, and policies that enforce plan-based access control across the entire application.

**Middleware Stack:**

`EnsureSubscribed` — route-level middleware
- Checks that the current workspace has an active subscription (or is in trial, or is lifetime)
- Redirect targets: `/app/billing` for expired/canceled subscriptions with flash message "Your subscription has expired. Please upgrade to continue."
- Bypass list: billing pages, plan selection, logout, webhook receivers — these must remain accessible regardless of subscription state
- Applied globally to the Filament panel via `AppPanelProvider`

`RequiresPlanFeature` — parametric middleware for feature gating
- Usage: `->middleware('requires.feature:campaigns')` or `->middleware('requires.feature:webhooks')`
- Checks the workspace's current plan for the matching `can_use_{feature}` boolean
- Returns 403 with JSON `{ error: "upgrade_required", feature: "campaigns", current_plan: "solo" }` for API routes
- Redirects to upgrade page with feature-specific messaging for web routes
- Supports multiple features: `requires.feature:campaigns,webhooks`

`RequiresPlanLimit` — parametric middleware for quantity gating
- Usage: `->middleware('requires.limit:contacts')` or `->middleware('requires.limit:users')`
- Checks the workspace's current count against the plan's `max_{resource}` limit before allowing create/store actions
- Returns 403 with usage details: `{ error: "limit_reached", resource: "contacts", current: 5000, max: 5000 }`
- Allows read/list/show actions to pass through — only gates create/store/import

**Laravel Gates & Policies:**

Gate definitions registered in `AuthServiceProvider`:
- `Gate::define('use-feature', fn(User $user, string $feature) => PlanLimitService::canUseFeature($user->currentWorkspace, $feature))`
- `Gate::define('create-resource', fn(User $user, string $resource) => PlanLimitService::canAdd($user->currentWorkspace, $resource))`
- `Gate::define('access-admin-feature', fn(User $user, string $feature) => PlanLimitService::canUseFeature($user->currentWorkspace, $feature) && $user->hasWorkspaceRole('admin'))`

Policy integration pattern — each resource policy checks both role AND plan:
```
// ContactPolicy::create()
return $user->hasWorkspacePermission('create-contacts')
    && PlanLimitService::canAddContact($user->currentWorkspace);
```

**PlanLimitService Enhancements:**
- `canUseFeature(Workspace, string $feature): bool` — generic feature check against plan's `can_use_*` columns
- `canAdd(Workspace, string $resource): bool` — generic quantity check against plan's `max_*` columns
- `isSubscriptionActive(Workspace): bool` — checks active, trialing, lifetime, or within grace period
- `getSubscriptionStatus(Workspace): SubscriptionStatus` — returns current subscription state enum
- `getRemainingCapacity(Workspace, string $resource): int|null` — returns how many more of a resource can be created (null = unlimited)
- `getUpgradeRequirements(Workspace, string $feature): ?Plan` — returns the lowest plan that unlocks a given feature (for upgrade prompts)
- `isInGracePeriod(Workspace): bool` — checks if past_due but still within grace window
- `handleExpiredSubscription(Workspace): void` — transitions workspace to free tier, dispatches `subscription.expired` webhook event

**Filament Integration — UI Gating:**

Navigation gating:
- `NavigationItem::make('Campaigns')->visible(fn() => Gate::allows('use-feature', 'campaigns'))` — hides nav items for features the plan doesn't include
- Navigation badge on gated items showing lock icon + "Pro" or "Agency" tier label when feature is unavailable but visible (configurable: hide vs show-locked)

Resource-level gating:
- `ContactResource::canCreate()` — delegates to `PlanLimitService::canAddContact()`, returns false at limit
- `CampaignResource::canAccess()` — delegates to `Gate::allows('use-feature', 'campaigns')`
- All gated resources show contextual upgrade prompt when access is denied (not just a 403)

Action-level gating:
- Bulk actions check plan limits before execution (e.g., "Add to Campaign" only available on Pro+)
- Import actions check remaining capacity and block if import would exceed plan limit
- "Upgrade Required" modal: reusable Filament Action that shows current plan, required plan, feature description, and CTA button to BillingPage

Widget-level gating:
- Dashboard widgets for gated features (e.g., CampaignStatsWidget) show teaser content with upgrade CTA instead of blank/hidden state
- Usage meter widget on dashboard: shows contacts used / max, users used / max, with progress bar turning amber at 80% and red at 95%

**API Route Gating (Sanctum):**
- API routes grouped with middleware: `['auth:sanctum', 'ensure.subscribed']`
- Feature-specific API routes additionally use `requires.feature:{feature}`
- API responses include `X-Plan-Tier` and `X-Plan-Limits-Remaining` headers for client-side awareness
- Rate limiting tiered by plan: Solo (60 req/min), Pro (120 req/min), Agency (300 req/min)
- Chrome Extension reads plan headers to show/hide features in sidebar

**Webhook Events for Subscription Changes:**
- `subscription.created` — new subscription started
- `subscription.upgraded` — plan tier increased
- `subscription.downgraded` — plan tier decreased
- `subscription.canceled` — subscription canceled (enters grace period)
- `subscription.expired` — grace period ended, workspace downgraded to free
- `subscription.renewed` — past_due subscription successfully charged
- `trial.expiring_soon` — 3 days before trial ends (for email notification)
- `trial.expired` — trial ended, workspace transitioned to free tier

**Stripe Webhook Handling:**
- `StripeWebhookController` listens for: `customer.subscription.created`, `customer.subscription.updated`, `customer.subscription.deleted`, `invoice.payment_succeeded`, `invoice.payment_failed`
- Maps Stripe events to internal `SubscriptionStatus` transitions
- On `invoice.payment_failed`: sets status to `past_due`, queues dunning email sequence (immediate, +3 days, +7 days)
- On `customer.subscription.deleted`: starts grace period countdown, queues win-back email

### Phase 1K: Affiliate/Referral System (Week 8)

**Overview:**
Built-in affiliate referral system that lets every user earn commissions by referring new subscribers. Users see a full dashboard of who they've referred and what they've earned. Admins control commission structures, approve payouts, and manage the affiliate program. The system is designed to be self-contained but swappable to Rewardful or another platform later.

**Registration & Tracking:**
- Every new user receives a unique `referral_code` at registration (stored on `users` table)
- `referred_by_user_id` field on `users` table permanently records who originally referred each user
- Referral link format: `ninjacrm.com/?ref=CODE`
- Cookie-based tracking with configurable window (default 30 days)
- `TrackReferralVisit` middleware on public routes: reads `?ref=` param → sets cookie → records visit
- When referred user registers: `referred_by_user_id` set on user record, `referrals` record created with status `pending`
- When referred user subscribes to a paid plan: referral status transitions to `active`, commission earning begins

**Admin Panel — Commission Configuration:**

`ReferralCommissionTierResource` (Super Admin):
- CRUD for commission tier structures
- Fields: name, commission type (percentage or flat), rate, duration (months or lifetime), minimum referrals to qualify, applicable plans, one-time signup bonus
- Default tier: automatically assigned to new users (e.g., "Standard" = 20% recurring for 12 months)
- Volume-based tiers: admins can create tiers that unlock at referral count milestones (e.g., "VIP" = 30% after 10 referrals)
- Plan-specific rates: different commission rates for different subscription tiers (e.g., 20% for Solo, 25% for Pro, 30% for Agency)
- Preview calculator: shows estimated monthly/yearly earnings per referred subscriber at each plan level

`ReferralAdminResource` (Super Admin):
- View all referrals across the platform with filters: status, referrer, date range, plan tier
- Override commission rate for individual referrals
- Manually create referral links for special partnerships
- Bulk approve/reject pending commissions
- Flag/freeze suspicious referral activity

**Admin Panel — Payout Management:**

`ReferralPayoutResource` (Super Admin):
- View all pending commissions aggregated by referrer
- Approval workflow: commissions start as `pending` → admin reviews → marks `approved` → batch processes payout
- Payout methods: Stripe Connect (automated), manual (bank transfer, PayPal, check)
- Batch payout action: select approved commissions → generate payout record → process via configured method
- Payout history with status tracking (pending_approval/approved/processing/completed/failed)
- Minimum payout threshold: configurable minimum balance before payout is processed (e.g., $50)
- Payout schedule: configurable frequency (monthly, bi-weekly) via admin setting
- Export payout reports for accounting (CSV/Excel)

**Admin Panel — Program Settings:**

`ReferralSettingsPage` (Super Admin):
- Cookie tracking window (days)
- Minimum payout threshold
- Payout schedule frequency
- Default commission tier selection
- Self-referral prevention toggle
- Fraud detection sensitivity (flag if same IP, email domain patterns, etc.)
- Program enable/disable toggle (disable hides referral UI from all users)
- Custom referral terms & conditions text

**User-Facing Referral Dashboard:**

`MyReferralsPage` (Custom Filament Page, accessible to all users):
- **Stats Overview Cards:**
  - Total Referrals (all time)
  - Active Referrals (currently subscribed)
  - Pending Earnings (commissions earned but not yet paid)
  - Total Earnings (lifetime paid out)
- **Referral Link Section:**
  - Personal referral link with one-click copy button
  - Social sharing buttons (LinkedIn, Twitter/X, email)
  - QR code generator for referral link
- **Referral List Table:**
  - Shows each referred user: name (or "Pending Signup"), signup date, plan subscribed, status (pending/active/churned), total commission earned from this referral
  - Privacy-conscious: referred users see initials + join date, not full names (configurable)
- **Earnings Breakdown Table:**
  - Per-referral commission history: date, subscription payment amount, commission earned, status (pending/approved/paid)
  - Running total and average commission per referral
- **Payout History Table:**
  - Each payout: date, amount, method, status, reference ID
  - Downloadable payout receipts
- **Commission Tier Display:**
  - Current tier name + commission rate
  - Progress toward next tier (if volume-based tiers exist): "3 more referrals to unlock VIP (30%)"
- **Marketing Assets Section (optional, Phase 2+):**
  - Downloadable banners, email templates, social post templates for affiliates

**Services:**
- `ReferralService::generateCode(User)` — creates unique code at registration
- `ReferralService::trackVisit(string $code)` — sets referral cookie with code + timestamp
- `ReferralService::resolveReferrer(Request)` — reads cookie, returns referrer User or null
- `ReferralService::createReferral(User $referrer, User $referred)` — creates referral record, sets `referred_by_user_id`, assigns commission tier
- `ReferralService::activateReferral(User $subscriber)` — transitions referral to active when subscriber pays
- `ReferralService::calculateCommission(string $stripeInvoiceId)` — queued job: looks up referral, applies commission rate, creates `referral_commissions` record
- `ReferralService::checkTierUpgrade(User $referrer)` — checks if referrer qualifies for higher commission tier based on referral count
- `ReferralService::getEarningsSummary(User)` — returns lifetime, pending, and paid totals
- `ReferralService::getReferralTree(User)` — returns all referred users with status and earnings
- `ReferralService::processPayouts(array $commissionIds, string $method)` — batch payout processing
- `ReferralService::validateReferral(User $referrer, User $referred): bool` — fraud prevention (self-referral, same household, etc.)
- `ReferralService::expireStaleReferrals()` — scheduled: expire pending referrals past cookie window with no signup

**Scheduled Commands:**
- `CalculateReferralCommissions` — runs after each Stripe `invoice.payment_succeeded` webhook (via queued job)
- `ProcessScheduledPayouts` — runs on configured schedule (default: 1st of month), processes approved commissions above minimum threshold
- `ExpireStaleReferrals` — daily: expires pending referrals past tracking window
- `CheckCommissionTierUpgrades` — daily: evaluates all referrers for tier promotion
- `SendReferralEarningsNotification` — weekly: emails referrers a summary of new commissions earned

**Webhook Events:**
- `referral.created` — new referral tracked
- `referral.activated` — referred user subscribed
- `referral.commission_earned` — commission calculated for a payment
- `referral.payout_completed` — payout processed
- `referral.tier_upgraded` — referrer promoted to higher commission tier
- `referral.expired` — referral expired without conversion

**Rewardful Migration Path:**
- `referrals.metadata` JSON column stores external platform IDs
- `ReferralService` is interface-based: swap `BuiltInReferralService` for `RewardfulReferralService` via config
- Webhook endpoint for Rewardful callbacks
- Migration script: export referral data → import to Rewardful → update metadata references

---

## Phase 2: LinkedIn Workspace (Weeks 9-16)

### 2A: Chrome Extension + API (Weeks 9-11)
- RESTful API endpoints (Sanctum-authenticated):
  - `GET /api/contacts/lookup?linkedin_url=...` — find existing contact
  - `POST /api/contacts` — quick-add from LinkedIn profile
  - `POST /api/touchpoints` — log activity
  - `GET /api/contacts/{id}/timeline` — recent touchpoints
  - `PATCH /api/contacts/{id}/stage` — move stage
  - `POST /api/tasks` — create follow-up
- Chrome Extension (Manifest V3, React/Preact sidebar):
  - Detects LinkedIn profile pages → shows NinjaCRM sidebar
  - Shows: contact details, stage, temperature, recent touchpoints, tasks
  - Quick actions: "Add to NinjaCRM", "Log Message", "Follow up in X days", "Tag as Hot"
  - One-click template copy with auto-populated variables

### 2B: Social Touchpoint Tracking (Week 12)
- Enhanced touchpoint types: `post_like`, `post_comment`, `profile_view`, `endorsement`
- "Engagement Score" calculated field per contact
- Engage Queue: contacts whose content you should interact with today (based on temperature + last engagement)

### 2C: Campaign Builder (Weeks 13-14)
- Full campaign CRUD with step builder:
  - Step 1: Send connection request (template)
  - Step 2: Wait 3 days
  - Step 3: Send follow-up message (template)
  - Step 4: Wait 5 days
  - Step 5: Bump message or book call CTA
- Contact enrollment: select by segment/filter → enroll
- Per-contact campaign progress tracking
- Analytics: enrolled, replied, completed, calls booked per campaign
- Pre-built Ninja Prospecting playbooks as starter templates

### 2D: Email Integration (Week 15)
- BCC tracking: unique inbound email per workspace → parsed and attached as touchpoint
- Optional SMTP send from NinjaCRM (for follow-up emails)
- Email templates separate from LinkedIn message templates

### 2E: Content Tracker (Week 16)
- Log which LinkedIn posts a contact engaged with
- "Post that triggered conversation" field on touchpoint
- Content performance: which posts generated the most conversations

---

## Phase 3: Agency & Team Layer (Weeks 17-24)

### 3A: Client Workspaces (Weeks 17-19)
- Full client isolation: each client has own contacts, pipelines, templates, campaigns
- Client dashboard: pipeline overview, activity, calls booked, revenue
- Client login portal (read-only or limited interaction):
  - Custom Filament panel at `/client`
  - See their pipeline, recent activity, reports
  - Cannot edit contacts or templates

### 3B: Team Collaboration (Weeks 20-21)
- Contact assignment to specific reps
- Activity feed per team member
- Internal notes (team-only) vs client-visible notes on contacts
- @mentions in notes → notifications
- Team performance dashboards

### 3C: Advanced Reporting (Weeks 22-23)
- Conversion rate: per rep, per campaign, per pipeline, per lifecycle stage
- Revenue projections from pipeline (weighted by stage probability × deal value)
- Full-funnel analytics: prospect → lead → opportunity → customer with time-in-stage and drop-off rates
- Deal velocity: average time from opportunity creation to close (won vs lost)
- Lead source ROI: which sources produce the highest-value customers
- Customer lifetime value estimates based on deal history
- Time-in-stage analysis
- Client-specific exportable PDF/CSV reports
- Scheduled report emails (weekly/monthly)

### 3D: White-Label Foundation (Week 24)
- Custom domain support per workspace (agency plan)
- Logo, colors, favicon customization
- Custom email sender domain
- Removable "Powered by NinjaCRM" branding

---

## Phase 4: Smart / AI Layer (Weeks 25-32)

### 4A: AI Message Suggestions (Weeks 25-27)
- Based on best-performing templates + contact context
- "Suggest a follow-up" button on contact record
- Uses Claude API or OpenAI to generate personalized messages
- User approves/edits before use

### 4B: Nudge Engine (Weeks 28-30)
- Daily scan: identify stalled conversations (no activity in X days by stage)
- Suggest bump messages for stalled contacts
- "Hottest leads" ranking based on engagement score + recency + stage
- Push notifications for time-sensitive follow-ups

### 4C: Smart Segmentation (Weeks 31-32)
- Auto-segment contacts by engagement patterns
- "Find contacts like this one" similarity search
- Campaign enrollment suggestions based on profile + behavior
- Natural language search: "show me all hot coaches in US with no follow-up this week"

---

## Key Patterns from MediaVault to Reuse

| MediaVault Pattern | NinjaCRM Equivalent | Source File |
|---|---|---|
| `Family` tenant model | `Workspace` tenant model | `app/Models/Family.php` |
| `BelongsToFamily` trait | `BelongsToWorkspace` trait | `app/Models/Concerns/BelongsToFamily.php` |
| `AppPanelProvider` multi-tenant config | Same pattern, new nav groups | `app/Providers/Filament/AppPanelProvider.php` |
| `PlanLimitService` | Same pattern, CRM limits | `app/Services/PlanLimitService.php` |
| `BorrowRequestStatus` enum pattern | All NinjaCRM enums | `app/Enums/BorrowRequestStatus.php` |
| `CheckoutService` (DB transactions + notifications) | `TouchpointService`, `TaskService`, `CampaignService`, `ConversionService`, `LeadScoringService` | `app/Services/CheckoutService.php` |
| `RegisterFamily` page | `RegisterWorkspace` page | `app/Filament/Pages/Auth/RegisterFamily.php` |
| `FamilySettings` tenant profile | `WorkspaceSettings` tenant profile | `app/Filament/Pages/FamilySettings.php` |
| Database seeder strategy | Plan + default pipeline seeder | `database/seeders/` |

---

## Testing Strategy

| Layer | Tool | Coverage |
|---|---|---|
| Unit | Pest | Services, Enums, Models |
| Feature | Pest + Livewire | Filament resources, pages, actions |
| Integration | Pest | Billing flows, webhook delivery |
| Policy | Pest | Authorization per role/plan |
| Browser | Pest + Dusk (optional) | Kanban drag-drop, onboarding flow |

Key test scenarios:
1. Workspace isolation — workspace A cannot see workspace B's contacts
2. Plan limit enforcement — Solo user blocked at 5,001 contacts
3. Pipeline stage transitions — contact moves through full lifecycle
4. Lifecycle conversion flow — prospect → lead → opportunity → customer with correct timestamps, events, and deal creation
5. Deal tracking — create deal, move through stages, probability updates, mark won/lost, revenue reporting reflects correctly
6. Lead scoring — rules evaluate correctly, scores recalculate on contact update, qualification auto-updates
7. Conversion event logging — each lifecycle transition creates audit record with attribution and value snapshot
8. Template variable rendering — all variables resolve correctly
9. Webhook delivery — event fires → payload delivered → signature verified (including deal.won, contact.converted events)
10. Referral tracking — signup with code → `referred_by_user_id` set on user → referral activated on subscription → commission calculated per payment
11. Referral user dashboard — referrer sees referred users list, earnings breakdown, payout history, current commission tier
12. Referral admin — admin creates commission tiers, configures rates per plan, approves commissions, processes batch payouts
13. Referral tier upgrades — referrer hits 10 referrals → auto-promoted to VIP tier → new referrals earn higher rate → old referrals keep original rate
14. Referral fraud prevention — self-referral blocked, same-IP referrals flagged, expired cookies don't create referrals
11. Today View accuracy — correct contacts appear in each column
12. CSV import — handles duplicates, maps columns, respects plan limits
13. Conversion funnel report — rates calculated correctly across date ranges
14. Subscription middleware — expired subscription redirects to billing page, free tier blocks gated features
15. Feature gating — free user cannot access campaigns, Solo user cannot access webhooks/API, Pro user can access all except white-label
16. Quantity gating — free user blocked at 100 contacts, middleware returns correct error with usage details
17. Trial lifecycle — new workspace starts in trialing, trial expiration transitions to free tier, features downgrade correctly
18. Grace period — past_due subscription retains access during grace window, loses access after grace expiration
19. Stripe webhook handling — payment_failed sets past_due, subscription.deleted starts grace period, payment_succeeded restores active
20. Plan upgrade/downgrade — upgrading immediately unlocks features, downgrading at period end retains access until renewal
21. Filament nav gating — gated nav items hidden/locked for lower tiers, upgrade prompts shown contextually
22. API rate limiting — Solo plan throttled at 60 req/min, Pro at 120, Agency at 300
23. Lifetime deal — lifetime workspace bypasses all subscription checks, treated as highest tier
24. OAuth registration — click "Sign in with Google" → authorize → new user created with no password, email auto-verified, workspace created, referral code generated
25. OAuth login — existing OAuth user clicks "Sign in with Google" → logged in directly, `last_used_at` updated on social_accounts
26. OAuth account linking — email/password user links Google from profile → `social_accounts` record created → can now log in either way
27. OAuth email collision — OAuth email matches existing verified account → prompted to log in with existing method first, then link
28. OAuth-only password management — OAuth-only user cannot unlink last social account → can set a password from profile → then can unlink
29. Multiple OAuth providers — user links Google + LinkedIn → can log in with either → unlinking one still allows login with the other

---

## Deployment Architecture

```
[Browser] → [Laravel App (Forge/Vapor)] → [Supabase PostgreSQL]
                                        → [Supabase Storage]
                                        → [Stripe API]
                                        → [Redis (cache/queue)]
                                        → [Mail (Postmark/SES)]
```

- **App hosting:** Laravel Forge (single server to start) or Laravel Vapor (serverless, scales)
- **Database:** Supabase PostgreSQL (managed, backups, connection pooling via PgBouncer)
- **Storage:** Supabase Storage (avatars, logos, exports)
- **Queue worker:** Supervisor on Forge, or Lambda on Vapor
- **SSL:** Forge auto-provisions via Let's Encrypt
- **CI/CD:** GitHub Actions → run tests → deploy to Forge/Vapor

---

## 90-Day Execution Summary

| Week | Phase | Deliverable |
|---|---|---|
| 1 | Phase 0 | Project scaffolded, Supabase connected, Filament panel live, auth working |
| 2 | Phase 1A | All migrations, models, enums (incl. lifecycle, deals, scoring), relationships, seeders |
| 3 | Phase 1B | Contact + Pipeline + Deal resources, Kanban board, lifecycle conversion flow, lead scoring |
| 4 | Phase 1C-D | Touchpoint timeline, Template engine |
| 5 | Phase 1E-F | Today View dashboard, Task/follow-up system |
| 6 | Phase 1G-H | Reporting page, Multi-client support |
| 7 | Phase 1I-J | Import/export, webhooks, Stripe billing |
| 8 | Phase 1J-b + 1K | Subscription security model (middleware, gates, policies, trial/grace handling), referral system, MVP complete |
| 9-12 | Phase 2A-B | Chrome Extension + API, social tracking |
| 13-14 | Phase 2C | Campaign builder with playbooks |
| 15-16 | Phase 2D-E | Email integration, content tracker |
| 17-24 | Phase 3 | Agency workspaces, team features, white-label |
| 25-32 | Phase 4 | AI suggestions, nudge engine, smart segmentation |

---

## Verification Plan

1. **Fresh install test:** Clone → `composer install` → configure `.env` → `php artisan migrate` → `php artisan db:seed` → login works
2. **Tenant isolation:** Create 2 workspaces, verify complete data separation
3. **Contact lifecycle:** Add contact as prospect → qualify as lead → create deal/opportunity → move through pipeline → convert to customer → verify all conversion events logged
4. **Deal tracking:** Create deal with value → move through stages → verify probability auto-updates → mark won → verify revenue reports
5. **Lead scoring:** Configure rules → add contact → verify score calculated → update contact → verify recalculation
6. **Today View:** Verify correct contacts appear based on follow-up dates and conversation state
7. **Template rendering:** Create template with variables → apply to contact → verify output
8. **Subscription security — free tier:** Register new workspace → verify starts on free plan → verify gated features (campaigns, webhooks, API) are blocked → verify free limits enforced (100 contacts, 1 pipeline, 10 deals)
9. **Subscription security — middleware:** Expire a subscription → verify `EnsureSubscribed` redirects to billing page → verify billing/logout routes still accessible
10. **Subscription security — feature gating:** Solo workspace tries to access campaigns → verify `RequiresPlanFeature` blocks with upgrade prompt → upgrade to Pro → verify campaigns now accessible
11. **Subscription security — quantity gating:** Free workspace at 100 contacts → try to add 101st → verify `RequiresPlanLimit` blocks with usage details → upgrade to Solo → verify creation succeeds
12. **Trial lifecycle:** New workspace → verify 14-day trial with Pro-level access → simulate trial expiry → verify downgrade to free tier → verify data preserved but gated features locked
13. **Grace period:** Simulate payment failure → verify `past_due` status → verify access retained during grace window → simulate grace expiry → verify downgrade to free
14. **Stripe webhooks:** Simulate `invoice.payment_failed` → verify status = past_due → simulate `customer.subscription.deleted` → verify grace period starts → simulate `invoice.payment_succeeded` → verify status = active
15. **Plan upgrade/downgrade:** Subscribe Solo → upgrade to Pro → verify immediate feature unlock → downgrade to Solo → verify features lock at period end
16. **Filament UI gating:** Login as free user → verify campaign nav item hidden/locked → verify upgrade modal appears on gated actions → verify usage meter widget shows correct limits
17. **API rate limiting:** Authenticate as Solo → exceed 60 req/min → verify 429 response → authenticate as Pro → verify 120 req/min allowed
18. **Lifetime deal:** Create lifetime workspace → verify bypasses all subscription checks → verify treated as highest tier
19. **OAuth registration (Google):** Click "Sign in with Google" → authorize → verify user created (no password), email auto-verified, workspace + membership created, referral code generated, redirected to dashboard
20. **OAuth registration (LinkedIn):** Same flow with LinkedIn provider → verify profile data populated (name, avatar)
21. **OAuth login (returning user):** OAuth user clicks "Sign in with Google" → verify logged in directly → verify `social_accounts.last_used_at` updated
22. **OAuth account linking:** Register with email/password → go to profile settings → click "Link Google Account" → authorize → verify `social_accounts` record created → logout → log back in with Google → verify same account
23. **OAuth email collision:** Register with email/password → attempt OAuth with same email from different browser → verify prompted to log in first then link
24. **OAuth-only user password:** Register via Google (no password) → go to profile → click "Set Password" → set password → verify can now log in with email/password too → verify can now unlink Google if desired
25. **OAuth provider disable:** Admin disables LinkedIn provider in config → verify LinkedIn login button hidden on login page → existing linked accounts unaffected but cannot re-authenticate via LinkedIn
26. **Plan enforcement:** Solo user hits contact limit → creation blocked with upgrade prompt
27. **Billing:** Subscribe → upgrade → cancel → verify Stripe state matches
28. **Referral registration:** Generate code → visit referral link → verify cookie set → sign up → verify `referred_by_user_id` set on user record → verify `referrals` record created with status `pending`
29. **Referral activation:** Referred user subscribes to paid plan → referral status transitions to `active` → `activated_at` set → commission tier assigned → verify commission rate snapshot frozen
30. **Referral commission calculation:** Referred user's monthly payment succeeds → `referral_commissions` record created with correct amount → referrer's `referral_earnings_total` updated → commission status = `pending`
31. **Referral user dashboard:** Login as referrer → navigate to My Referrals → verify stats cards (totals, pending, paid) → verify referred users list with plan and earnings → verify payout history → verify referral link copy works
32. **Referral admin commission tiers:** Admin creates "VIP" tier (30%, lifetime) → assigns to user → verify new referrals use VIP rate → verify old referrals keep original rate snapshot
33. **Referral admin payouts:** Admin views pending commissions → approves batch → processes payout → verify status transitions (pending → approved → completed) → verify referrer's earnings total updated
34. **Referral tier auto-upgrade:** Referrer reaches 10 referrals → `CheckCommissionTierUpgrades` runs → verify promoted to VIP tier → verify `referral.tier_upgraded` webhook fires
35. **Referral fraud prevention:** Attempt self-referral → verify blocked → attempt same-IP referral → verify flagged for review
36. **CSV import:** Import 100 contacts → verify dedup by LinkedIn URL → verify plan limits
37. **Webhooks:** Configure endpoint → create contact → verify delivery + signature
38. **Conversion funnel report:** Rates calculated correctly across date ranges
39. Run `php artisan test` — all green
40. Run `./vendor/bin/pint --test` and `./vendor/bin/phpstan analyse` — clean

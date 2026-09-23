# UNITY — System Architecture & Design Document
**Connect. Share. Grow.**
Prepared for: Maveric Infotech | Continuation environment: Claude Code

---

## 0. Assumption Called Out (confirm before build)
Activation rule as stated is ambiguous. Interpreted as:

> A registered user's account stays **INACTIVE** until BOTH conditions are met:
> 1. User pays ₹199 activation fee.
> 2. User refers 2 people **who also register AND pay** ₹199.
>
> Until both are true, the user can log in, view a limited dashboard, and share their referral link via WhatsApp — but cannot access full app features.

If this is wrong, correct it before development starts — it drives the entire state machine.

---

## 1. Actors
| Actor | Access |
|---|---|
| Guest | Register, view public CMS pages (blog, about, cause info) |
| User (Pending) | Login, dashboard, referral link/share, payment, profile |
| User (Active) | Full app access |
| Admin | Filament panel — full CMS, user, payment, referral, notification control |
| Sub-admin/Editor (optional) | Filament panel — CMS-only, via Filament Shield roles |

---

## 2. User Activation State Machine

```
REGISTERED (OTP verified)
      │
      ▼
   PENDING ──────────────► ACTIVE
      │  pay ₹199 AND         (both conditions true)
      │  2 referrals paid
      │
      ├─ can view dashboard (limited)
      ├─ can generate/share referral link
      ├─ can make payment
      └─ cannot access core app features until ACTIVE
```

**States:** `registered` → `pending_payment` / `pending_referral` → `active`
Store as a single `status` enum + two boolean flags (`has_paid`, `referral_count`) so activation is computed, not manually set — avoids race conditions.

**Activation trigger logic (server-side, idempotent):**
```
on(user.paid == true AND user.paid_referrals_count >= 2):
    user.status = 'active'
    fire ActivationEvent → push notification + SMS + email
```
This check runs on: payment success webhook, and on every successful referred-user payment (recursively check the referrer).

---

## 3. High-Level Architecture

```
┌─────────────────┐        ┌──────────────────────┐
│   Flutter App    │◄──────►│   Laravel REST API    │
│ (Android/iOS)    │  HTTPS │   (Sanctum/JWT auth)  │
└─────────────────┘        └──────────┬────────────┘
                                       │
        ┌──────────────────────────────┼──────────────────────────┐
        ▼                              ▼                          ▼
┌───────────────┐          ┌────────────────────┐      ┌──────────────────┐
│ Firebase Auth  │          │  MySQL (shared      │      │  Filament Admin   │
│ (Phone OTP)    │          │  hosting DB)         │      │  Panel (web)      │
└───────────────┘          └────────────────────┘      └──────────────────┘
        │                              │
        ▼                              ▼
┌───────────────┐          ┌────────────────────┐
│ Firebase Cloud │          │  Payment Gateway     │
│ Messaging(FCM) │          │  (Razorpay/Cashfree)│
└───────────────┘          └────────────────────┘
        │
        ▼
┌───────────────┐
│  SMS Gateway   │
│ (MSG91/Twilio) │
└───────────────┘
```

**Key decision for shared hosting:** No queue workers/daemons assumed available by default (`php artisan queue:work` needs a persistent process, which most shared hosting lacks). Use **Laravel's database or sync queue driver** triggered via **cron-based scheduler** (`* * * * * php artisan schedule:run`), which shared hosting (cPanel cron) supports. If the host supports Supervisor/SSH persistent processes, switch to `queue:work` + Redis later — flag this as a hosting-tier decision.

---

## 4. Core Modules

### 4.1 Auth & Onboarding
- Registration: name, phone, email (optional), referral code (optional, prefilled from deep link)
- Firebase Phone OTP verification (client-side Firebase SDK → ID token → verified server-side via Firebase Admin SDK)
- Laravel issues its own API token (Sanctum) after Firebase token verification — Firebase is auth **verification only**, not session management
- Referral code auto-generated per user (short alphanumeric, unique)

### 4.2 Referral Engine
- Every user has a unique `referral_code` and `referral_link` (`https://unityapp.in/r/{code}` → deep link into Flutter app via App Links/Universal Links, falls back to Play Store/App Store)
- `referrals` table: `referrer_id`, `referred_id`, `referred_status` (registered/paid), `created_at`
- WhatsApp share uses `url_launcher` package with `wa.me` intent, prefilled message + link
- Admin dashboard: referral tree view (at least 2 levels), leaderboard, fraud flags (same device/IP across multiple accounts)

### 4.3 Payment
- ₹199 activation fee via Razorpay or Cashfree (both support easy Laravel + Flutter SDKs, works on shared hosting)
- Webhook endpoint (`/api/webhooks/payment`) — must be queued/verified via signature, retried safely (idempotent)
- `payments` table: `user_id`, `amount`, `status`, `gateway_txn_id`, `purpose` (self_activation)
- On success: mark `has_paid = true`, re-run activation check for user, AND re-run activation check for their referrer

### 4.4 Notifications
- **FCM (push):** activation success, referral joined, payment success, new blog/cause update, admin broadcast
- **SMS (transactional):** OTP fallback, activation confirmation, payment receipt — via MSG91/Twilio (India-friendly: MSG91 preferred for DLT compliance)
- **In-app notification center:** stored in DB, synced to app, read/unread state

### 4.5 CMS (Filament Admin)
- **Blogs:** title, slug, rich content (Filament rich editor / TipTap), featured image, author, status (draft/published), scheduled publish
- **SEO:** per-page meta title/description/OG image, sitemap.xml auto-generation, canonical URLs — Filament SEO plugin or custom fields
- **Categories:** nested/flat categories for blogs and causes, drag-reorder
- **Image Carousel/Banners:** homepage banner manager (image, link, order, active/inactive, schedule start/end)
- **Cause/Community content:** pages describing the social cause, testimonials, impact stats (editable by admin, rendered in-app)

### 4.6 Admin Panel (Filament) — Core Resources
- Users (view, filter by status, manual activate/deactivate override, view referral chain)
- Payments (list, refund flag, export)
- Referrals (tree/list view, fraud flags)
- Blogs, Categories, Banners, SEO settings
- Notifications (compose + broadcast via FCM/SMS)
- Reports/Dashboard widgets: total users, active vs pending, revenue, top referrers
- Roles/permissions via Filament Shield

---

## 5. Database Schema (Core Tables)

```
users
  id, name, phone (unique), email, firebase_uid, referral_code (unique),
  referred_by (nullable FK → users.id), status (enum: registered/pending/active),
  has_paid (bool), created_at, updated_at

payments
  id, user_id, amount, gateway, gateway_txn_id, status, purpose, created_at

referrals
  id, referrer_id, referred_id, referred_paid (bool), created_at

notifications
  id, user_id (nullable = broadcast), title, body, type, read_at, created_at

blogs
  id, category_id, title, slug, excerpt, content, featured_image, seo_title,
  seo_description, status, published_at

categories
  id, name, slug, parent_id (nullable)

banners
  id, image, title, link, sort_order, active, starts_at, ends_at

settings
  key, value   (site config, activation fee amount, referral count required — kept editable, not hardcoded)
```

---

## 6. REST API — Endpoint Groups

```
POST   /api/auth/verify-otp          # Firebase token → Sanctum token
POST   /api/auth/register            # complete profile after OTP
GET    /api/user/me                  # profile + status + activation progress
GET    /api/user/referral            # code, link, referred list, count
POST   /api/payment/initiate
POST   /api/webhooks/payment         # gateway callback (signature verified)
GET    /api/blogs
GET    /api/blogs/{slug}
GET    /api/categories
GET    /api/banners
GET    /api/notifications
POST   /api/device-token             # register FCM token
GET    /api/settings/public          # activation fee, required referrals (dynamic)
```

All authenticated endpoints via Sanctum bearer token; rate-limited; versioned under `/api/v1/`.

---

## 7. Flutter App — Module Structure
```
lib/
  core/          # api client, constants, theme
  services/      # firebase_auth_service, fcm_service, api_service
  models/        # user, blog, payment, referral
  providers/     # state mgmt (Riverpod/Provider/Bloc — pick one, be consistent)
  screens/
    auth/        # otp, register
    home/        # dashboard (locked/unlocked states)
    referral/    # share screen, progress tracker
    payment/     # checkout
    blog/        # list, detail
    profile/
  widgets/
```
**Activation progress UI is critical** — a clear widget showing: payment status ✓/✗, referral 1 ✓/✗, referral 2 ✓/✗, with a WhatsApp share CTA always visible while pending.

---

## 8. Hosting & Deployment (Shared Hosting Constraints)
- Laravel deployed via standard cPanel/shared hosting practices (public_html → public folder symlink or subdomain pointing to `/public`)
- No SSH daemon assumption — use cron for `schedule:run` (queue processing, activation re-checks, scheduled blog publishing)
- MySQL on shared DB instance — index `users.referral_code`, `referrals.referrer_id`, `payments.user_id` from day one
- File storage: local `storage/app/public` symlinked, or offload to S3-compatible bucket if shared hosting storage/bandwidth is limited (recommended for scale)
- Firebase Admin SDK service account key — store outside web root, reference via `.env`
- SSL mandatory (Firebase Phone Auth + payment gateway both require HTTPS)

---

## 9. Security Notes
- Never trust client-reported activation status — always server-computed
- Payment webhook signature verification mandatory, replay-protected (store processed `gateway_txn_id`)
- Rate-limit OTP requests and referral-code lookups (abuse/fraud prevention)
- Referral fraud checks: block self-referral, flag same-device/IP referral clusters for admin review
- `.env`, Firebase service account JSON, payment gateway keys — never committed, never web-root-accessible

---

## 10. Handover & Documentation Checklist (for Claude Code continuation)
- [ ] `.env.example` with all required keys documented (Firebase, payment gateway, SMS gateway, DB)
- [ ] Firebase project setup guide (Phone Auth enabled, SHA keys for Android, Admin SDK key generation)
- [ ] Payment gateway sandbox → production credential switch documented
- [ ] SMS gateway (MSG91) DLT template registration notes (India requirement)
- [ ] Database ER diagram (exported from actual migrations once built)
- [ ] Postman/Insomnia collection for all API endpoints
- [ ] Filament admin default super-admin seeder
- [ ] Deployment runbook specific to the shared hosting provider (cron setup, symlink steps, storage permissions)
- [ ] Activation logic unit tests (this is the highest-risk piece — test all state transitions)
- [ ] App deep-link / referral-link testing guide (Android App Links + iOS Universal Links config)

---

## 11. Suggested Build Order
1. DB schema + Laravel migrations + Filament base setup
2. Auth (Firebase OTP verify → Sanctum) + registration
3. Referral engine + activation state machine (with tests)
4. Payment integration + webhook
5. Notifications (FCM + SMS)
6. CMS (blogs, categories, banners, SEO)
7. Flutter app wired to API, activation progress UI
8. Admin dashboard widgets/reports
9. Hardening, fraud checks, load test on shared hosting
10. Handover docs finalized

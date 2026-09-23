# UNITY — System Architecture & Design Document
**Connect. Share. Grow.**
Prepared for: Maveric Infotech | Continuation environment: Claude Code
**Last updated:** 2026-07-27 — reflects the system as actually built and deployed, not just planned. See `docs/sprint-log.md` for how it got here.

---

## 0. Decisions on record

These were ambiguous at the start and have since been confirmed — treat them as settled unless someone explicitly revisits them.

> **Activation rule.** A registered user's account stays **INACTIVE** until BOTH: (1) they pay the ₹199 activation fee, and (2) at least 2 people they referred have also registered **and** paid. Until both are true, the user can log in, view a limited dashboard, and share their referral link — but cannot see the community feed (Causes, SOS, Blogs).

> **SOS access.** Raising an SOS alert is restricted to **Approved Author tier** (same gate as Blog posting) — not open to every active member. Chosen over "any member, rate-limited" to avoid prank/abuse alerts on a platform that leans on trust as its core differentiator.

> **80G tax badge.** Unity does **not** display any "80G" or tax-exemption claim anywhere in the product. It is not a registered charitable trust, and showing that badge (as seen in early reference mockups) would be a real compliance risk, not a cosmetic one. Causes only carry a "Verified by admin" badge — an editorial trust signal, not a tax claim.

---

## 1. Actors

| Actor | Access |
|---|---|
| Guest | Register, view public website (home, blog, about, legal pages) |
| User — Pending | Login, limited dashboard, referral link/share, payment — feed locked |
| User — Active | Full feed: Causes, SOS, Blogs |
| **Author tier** (cumulative, stored as `users.author_tier`) | |
|　→ `member` (default) | Standard active-user access only |
|　→ `author` | Can also post Blogs and raise SOS alerts |
|　→ `organizer` | Everything `author` can, plus create Causes (money-moving) |
| Admin | Filament panel — full CMS, user, payment, referral, Cause/SOS moderation, notification control |
| Sub-admin/Editor | Filament panel — CMS-only (Blogs/Categories/Banners), via Filament Shield roles |

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
      └─ cannot see Causes/SOS/Blogs feed until ACTIVE
```

**States:** `registered` → `pending` → `active`, computed from a `status` enum + `has_paid` boolean — never manually set except by an explicit admin override in Filament (logged as a deliberate action, not routine).

**Activation trigger logic** (`App\Services\ActivationService::checkAndActivate()`), idempotent, server-computed only:
```
on(user.has_paid == true AND paidReferralsCount(user) >= requiredReferrals()):
    user.status = 'active'
    fire UserActivated event → in-app notification + push + SMS
```
Runs on: the activation-payment webhook, and — recursively — on the referrer whenever one of their referred users completes their own payment. `activation_fee` and `required_referrals` are stored in the `settings` table, not hardcoded, and are admin-editable.

Fully covered by `tests/Feature/ActivationServiceTest.php` (6 cases: unpaid, under-referred, exact threshold, unpaid referrals don't count, idempotent once active, configurable threshold).

---

## 3. High-Level Architecture

```
┌──────────────────┐        ┌──────────────────────┐
│  Flutter App      │◄──────►│   Laravel REST API    │
│  (Android/iOS)    │  HTTPS │   (Sanctum, /api/v1)  │
└──────────────────┘        └──────────┬────────────┘
                                        │
        ┌───────────────────────────────┼──────────────────────────┐
        ▼                               ▼                          ▼
┌────────────────┐          ┌─────────────────────┐      ┌──────────────────┐
│ Firebase Auth   │          │  MySQL (Hostinger    │      │  Filament Admin   │
│ (Phone OTP)     │          │  shared hosting)      │      │  Panel (web)      │
└────────────────┘          └─────────────────────┘      └──────────────────┘
        │                               │                          │
        ▼                               ▼                          ▼
┌────────────────┐          ┌─────────────────────┐      ┌──────────────────┐
│ Firebase Cloud  │          │  Razorpay             │      │  Public website   │
│ Messaging (FCM) │          │  (activation +        │      │  (Laravel Blade,  │
└────────────────┘          │   cause contributions) │      │  EN/MR, same app) │
        │                    └─────────────────────┘      └──────────────────┘
        ▼
┌────────────────┐
│  MSG91 (SMS)    │
└────────────────┘
```

**Three surfaces, one Laravel app:** the REST API (`/api/v1/*`), the Filament admin panel (`/admin/*`), and the public marketing website (`/`, `/blog`, `/about`, legal pages) all live in this single codebase, deployed as one unit. The Flutter app is the only separate codebase.

**Shared hosting reality (Hostinger, no SSH/queue daemon):**
- No `php artisan queue:work` — everything runs synchronously or via `QUEUE_CONNECTION=database` drained by cron.
- A permanent cron entry runs `php artisan schedule:run` every minute (see §9).
- The subdomain's document root could not be pointed at `/public` directly through the available tooling, so the app root itself is the doc root, protected by a root `.htaccess` that rewrites every request into `public/` and denies direct access to dotfiles/`composer.*`/`artisan`. This is a known, deliberate workaround — see §9 for the exact mechanism.
- One-off ops commands (migrations, seeders, credential rotation) run via short-lived Hostinger cron jobs rather than SSH, since none is available. **Gotcha learned the hard way:** the cron command field does not reliably support shell metacharacters (`&&`, quoted strings with special characters) — inline `tinker --execute="..."` calls silently corrupted. The reliable pattern is a dedicated Artisan command taking plain, unquoted positional arguments (see `App\Console\Commands\RotateAdminPassword` as the template).

---

## 4. Core Modules

### 4.1 Auth & Onboarding
- Registration: name, phone (unique), email (optional), referral code (optional).
- Firebase Phone OTP verified client-side → ID token → **re-verified server-side** on every privileged call (`FirebaseAuthService::verify()`), never trusted from the client. Firebase is verification-only; Laravel Sanctum issues the actual session token.
- Referral code auto-generated per user (`User::generateUniqueReferralCode()`, 8-char, collision-checked).

### 4.2 Referral Engine
- `referrals` table: `referrer_id`, `referred_id`, `referred_paid`.
- Referral link: `https://unityapp.in/r/{code}` — also live as a real page on the website (`referral.landing`) showing the referrer's name and store download links, with a graceful fallback for unknown codes.
- `GET /api/v1/user/referral` returns code, link, progress, and the full list of referred users with live status — this is what the mobile app's Profile screen renders directly.

### 4.3 Payments
Two distinct money flows, sharing one `payments` table and one Razorpay webhook:

| | Activation fee | Cause contribution |
|---|---|---|
| Purpose value | `self_activation` | `cause_contribution` |
| Triggered from | `POST /api/v1/payment/initiate` | `POST /api/v1/causes/{slug}/contribute` |
| On webhook success | `has_paid = true` → re-run activation check (self + referrer) | `causes.raised_amount += amount` only — **never touches the payer's own activation state** |

Webhook (`PaymentWebhookController`) is signature-verified (`PaymentGatewayService::verifyWebhookSignature`) and idempotent — replays of an already-`success` payment are safely no-ops. Covered by `tests/Feature/Api/PaymentWebhookTest.php`.

### 4.4 Causes (fundraisers) — money-moving
- `causes` table: organizer, category, goal/raised amount, deadline, `status` (`draft` → `pending_review` → `published` → `closed`), `verified` (admin editorial badge only — see §0).
- Creation gated to `organizer` tier (`StoreCauseRequest::authorize()`); new causes always start `pending_review` — an admin publishes via Filament.
- `GET /api/v1/causes`, `GET /api/v1/causes/{slug}`, `POST /api/v1/causes` (organizer), `POST /api/v1/causes/{slug}/contribute` (any authenticated user).

### 4.5 SOS (emergency broadcasts) — push-first, not feed-first
- `sos_alerts` table: category (`blood`/`organ`/`medication`/`other`), location, contact info, auto-`expires_at` (+3 days), `status` (`active`/`resolved`/`expired`).
- Creation gated to `author` tier or above (§0 decision). Published **immediately** on creation — moderation is post-hoc (admin can "Mark resolved" in Filament), not a pre-publish review queue, because urgency matters more than pre-screening here.
- On creation, `SosAlertCreated` → `BroadcastSosAlert` listener → `NotificationDispatcher::broadcastSos()`: one `notifications` row with `user_id = null` (the schema's broadcast convention) + an FCM push to the dedicated `sos` topic (separate from the general `broadcast` topic used for admin announcements).
- `GET /api/v1/sos`, `GET /api/v1/sos/{id}`, `POST /api/v1/sos` (author-tier).

### 4.6 Notifications
- **In-app:** `notifications` table, `user_id` nullable = broadcast to everyone.
- **Push (FCM):** activation success, payment receipt, referral joined, SOS broadcast, admin broadcast. All routed through `App\Services\NotificationDispatcher` and `PushNotificationService` — both degrade gracefully (log + continue) if Firebase isn't reachable, by design, so an unconfigured/unreachable push provider never breaks the request that triggered it. Verified by `tests/Feature/NotificationDispatcherTest.php`.
- **SMS (MSG91):** OTP fallback, activation confirmation, payment receipt. Same graceful-degradation posture (`SmsGatewayService` no-ops if `MSG91_AUTH_KEY` is blank).
- Admin composes ad-hoc broadcasts from the Filament **Broadcast notification** page.

### 4.7 CMS (Filament Admin)
- **Blogs:** title, slug, rich content (Filament `RichEditor`), category, author, featured image, SEO fields, draft/published, optional scheduled `published_at`. The `about` page and `about-unity` blog post convention lets admins edit the About page content without a bespoke "pages" table.
- **Categories:** flat/nested, shared between Blogs and Causes.
- **Banners:** homepage carousel, schedule window (`starts_at`/`ends_at`), active toggle.
- **Settings:** key/value store — `activation_fee`, `required_referrals`, both live-editable without a deploy.

### 4.8 Public Website (same Laravel app, Blade views)
- Routes: `/` (landing), `/blog`, `/blog/{slug}`, `/about`, `/r/{code}` (referral landing), `/privacy-policy`, `/terms`, `/data-policy`, `/refund-policy`.
- **Bilingual (English/Marathi):** session-persisted locale switcher (`SetLocale` middleware + `/locale/{locale}` route), full `lang/en/site.php` / `lang/mr/site.php` translation files, Marathi rendered in the **Mukta** Devanagari typeface (scoped via CSS `:lang(mr)`, no per-template classes needed).
- **Brand system:** palette sampled directly from the Unity logo (blue `#0064E6`, teal `#00AA96`, purple `#8C14FA`, magenta `#F00078`, orange `#FA6400`), warm cream background — **light theme only, by deliberate choice** (a broad, mixed-age community audience reads a forced dark mode as "tech-elite" rather than trustworthy; see the mobile UI proposal decision log for the same reasoning applied to the app).
- Homepage sections: hero, "what you can do" (urgent help / fundraisers / awareness), activation steps + "why the ₹199 fee" explainer, a CSS-only network-of-people animation (literal visual metaphor for "Unity"), a "built on trust, not algorithms" section (orbital diagram + trust checklist + a VS-style comparison card against "typical social media"), stories feed, gradient CTA.
- Footer: Maveric Infotech credit, contact number, legal page links. Floating WhatsApp button (`wa.me/919960038829`) on every page.
- Legal pages are template content grounded in what's actually built (Razorpay, Firebase, MSG91, Hostinger) — **not legally reviewed**; flagged on every page and worth a real lawyer's pass before being treated as binding, especially under India's DPDP Act 2023.

### 4.9 Admin Panel (Filament) — Resources
Users · Payments (read-only, webhook-driven, refund-flag only) · Referrals (read-only) · Blogs · Categories · Banners · Settings · **Causes** (status/verified filters, organizer-only assignment) · **SOS Alerts** (category/status filters, "Mark resolved" action) · Roles (Filament Shield: `admin` = full access, `editor` = CMS-only) · Broadcast Notification (page) · Dashboard widgets: `DashboardStats` (users/revenue/top referrer) + `CausesSosStats` (raised total, causes awaiting review, active SOS count).

---

## 5. Database Schema (Current)

```
users
  id, name, phone (unique), email, firebase_uid, referral_code (unique),
  referred_by (FK → users), status (registered/pending/active),
  has_paid, author_tier (member/author/organizer), created_at, updated_at

payments
  id, user_id, cause_id (nullable, FK → causes), amount, gateway,
  gateway_txn_id, status, purpose (self_activation/cause_contribution)

referrals
  id, referrer_id, referred_id, referred_paid, created_at

causes
  id, organizer_id (FK → users), category_id, title, slug, excerpt, content,
  featured_image, goal_amount, raised_amount, deadline,
  status (draft/pending_review/published/closed), verified (bool)

sos_alerts
  id, author_id (FK → users), category (blood/organ/medication/other),
  title, description, location, contact_info,
  status (active/resolved/expired), expires_at

notifications
  id, user_id (nullable = broadcast), title, body, type, read_at

device_tokens
  id, user_id, token (unique), platform

blogs / categories / banners / settings
  — unchanged from the original design (see §4.7)

+ Sanctum personal_access_tokens, Spatie permission tables (roles/permissions)
```

Indexes on `users.referral_code`, `referrals.referrer_id`, `payments.user_id`, `sos_alerts.(status, expires_at)`, `causes.status` from day one.

---

## 6. REST API — `/api/v1` (Sanctum bearer auth where noted)

```
POST   /auth/verify-otp          # Firebase ID token → Sanctum token, or "registration_required"
POST   /auth/register            # completes profile, re-verifies ID token server-side
GET    /user/me                  # profile + activation_progress + author_tier
GET    /user/referral            # code, link, progress, referred users with live status      [auth]
POST   /payment/initiate         # activation fee Razorpay order                                [auth]
POST   /webhooks/payment         # Razorpay callback — signature-verified, idempotent
GET    /blogs, /blogs/{slug}
GET    /categories
GET    /banners
GET    /settings/public          # activation_fee, required_referrals (dynamic)
GET    /causes, /causes/{slug}
POST   /causes                   # organizer-tier only                                          [auth]
POST   /causes/{slug}/contribute # any authenticated user                                       [auth]
GET    /sos, /sos/{id}
POST   /sos                      # author-tier only                                             [auth]
GET    /notifications                                                                            [auth]
POST   /device-token             # FCM token registration                                        [auth]
```

Auth endpoints throttled at 10/min. Full endpoint list is exercised by 49 Pest tests (`tests/Feature/Api/*`).

---

## 7. Flutter App — `unity_app/` (package `com.mavericinfotech.unity`)

```
lib/
  core/          # theme.dart (brand palette, light-only), api_client.dart (Dio + auth
                 # interceptor), router.dart (go_router, auth-gated redirects), constants.dart
  models/        # UnityUser, Blog, Cause, SosAlert, ReferralSummary, etc. — fromJson matching
                 # the API resources exactly
  services/      # FirebaseAuthService (phone OTP), UnityApiService (every endpoint, one class),
                 # PushNotificationService (FCM topic subscription), TokenStorageService
  providers/     # Riverpod: AuthController (AsyncNotifier<UnityUser?>, session restore + 401
                 # handling), data_providers.dart (FutureProviders per resource)
  screens/
    auth/        # phone entry → OTP → register
    home/        # bottom-nav shell + dashboard (locked/unlocked feed states)
    causes/, sos/, blog/, payment/, profile/
  widgets/       # ActivationProgressCard, CauseCard, SosCard, BlogCard, UnityLogo
```
State management: **Riverpod** (no code generation — manual `Provider`/`AsyncNotifierProvider` declarations for velocity). Navigation: **go_router** with a `redirect` callback gating on `authControllerProvider`. `flutter analyze`: 0 errors/warnings as of the last checkpoint.

**Firebase:** real project (`unity-mavericinfotech`), Android + iOS apps registered, `lib/firebase_options.dart` holds real config for both platforms — done via Sprint 7.

**Platform priority (decided Sprint 11): Android is the primary target; iOS support is not yet committed.** First live run on both platforms (Sprint 11) found the actual `applicationId`/bundle ID on both platforms had drifted from what's registered with Firebase since the project was first scaffolded — fixed on both. Live-tested OTP flow: Android handles a phone-auth failure gracefully (error surfaces as on-screen text, no crash); iOS currently **hard-crashes** in `FirebaseAuth.PhoneAuthProvider.verifyPhoneNumber` for a still-unconfirmed reason (likely missing reCAPTCHA Enterprise/App Check config) — deferred rather than blocking, given the Android-first decision. **Still not done:** app icons/store assets, Razorpay checkout untested end-to-end pending real gateway keys, a clean (non-rate-limited) full OTP→register→home pass on Android.

---

## 8. Hosting & Deployment (Hostinger Shared Hosting)

- Live at **https://unity.mavericinfotech.in** — subdomain under the `mavericinfotech.in` Hostinger account, MySQL database `u946560245_unity`.
- No SSH. Deployment is: build locally (`npm run build` for assets) → zip the app (excluding `.git`, `node_modules`, `tests/`) → `hosting_deployStaticWebsite` (Hostinger MCP) extracts it to the subdomain's root. `composer install --no-dev --optimize-autoloader` runs **locally** into the deploy copy before zipping — production never runs Composer itself.
- **Document root workaround:** the subdomain's root directory *is* the Laravel app root (not `public/`). A root `.htaccess` rewrites all non-`/public/` requests into `public/$1` and explicitly denies dotfiles and `composer.*`/`artisan`. This is the standard pattern for this exact shared-hosting constraint — see the design doc's original §8 assumption, now confirmed necessary in practice.
- **One-off ops** (migrations, seeding, credential rotation) run via a temporary Hostinger cron job (`* * * * *`, deleted immediately after one successful tick) rather than SSH. Command field does **not** reliably support `&&`/quoted shell strings — see §3's gotcha. The permanent cron (`schedule:run`, every minute) stays in place for future scheduled tasks.
- `.env` on production: `APP_ENV=production`, `APP_DEBUG=false`, fresh `APP_KEY`, secure session cookies. **Firebase is live** (real project, Admin SDK credentials — see below). Razorpay/MSG91 credentials are still **blank placeholders** — those features degrade gracefully rather than crash, but are functionally inert until real keys are added.
- **Firebase Admin SDK credentials:** the service account JSON is a real secret (private key included) — it is never put directly into `.env` as inline JSON, and never committed to git (`storage/app/firebase/` is git-ignored). It's stored as a file at `storage/app/firebase/unity-mavericinfotech-firebase-adminsdk.json` on both local and production, outside `public/`, with `FIREBASE_CREDENTIALS` pointing at the absolute path. Confirmed not web-accessible on production (direct request → 404, caught by the root `.htaccess` rewrite). Verified end-to-end via a purpose-built `php artisan app:check-firebase-connection` diagnostic command — deliberately argument-free, to sidestep the cron quoting gotcha entirely.

---

## 9. Security Notes

- Never trust client-reported activation status, phone number, or Firebase UID — every privileged action re-verifies the Firebase ID token server-side.
- Payment webhook signature verification mandatory; payments table `status` transitions are idempotent (replay-safe).
- Author-tier gates (`author`/`organizer`) enforced server-side in `FormRequest::authorize()` — the mobile app's UI hides buttons for the wrong tier as a UX nicety, but the server is the actual gate.
- `.env`, Firebase service account JSON, payment gateway keys — never committed, denied at the `.htaccess` level even if somehow requested directly.
- Admin credentials were rotated once already this project (see sprint log) — current login is `sk593@outlook.com`, password known to the client only; not repeated here.

---

## 10. Handover & Documentation Checklist

- [x] `.env.example` with all required keys documented
- [x] Firebase project setup — done (Sprint 7): project `unity-mavericinfotech`, Phone Auth enabled, Admin SDK key generated and wired in, Android/iOS apps registered
- [ ] Payment gateway sandbox → production credential switch — **blocked on real Razorpay keys**
- [ ] SMS gateway (MSG91) DLT template registration — **blocked on a real MSG91 account**
- [x] Database ER diagram — see §5 (kept as schema listing rather than a separate diagram file; regenerate visually if preferred)
- [ ] Postman/Insomnia collection for API endpoints
- [x] Filament admin default super-admin seeder (`AdminUserSeeder` — idempotent, keyed on "does an admin role exist", not a fixed email)
- [x] Deployment runbook — see §8 (this doc now doubles as that runbook given the shared-hosting specifics involved)
- [x] Activation logic unit tests — `tests/Feature/ActivationServiceTest.php`
- [ ] App deep-link / referral-link testing guide (Android App Links + iOS Universal Links) — not started; referral links currently only work via the website landing page, not a native deep link into the app

---

## 11. Known Gaps / Next Decisions

These are the real blockers between "code complete" and "actually live for users":

1. ~~**Firebase project.**~~ ✅ Done (Sprint 7) — project `unity-mavericinfotech`, Phone Auth enabled, real credentials wired into both the backend and the app.
2. **Razorpay production keys.** Currently blank; payment initiation will fail at the gateway call until set.
3. **MSG91 account + DLT-approved templates.** India requires pre-registered SMS templates; `services.msg91.templates.*` config keys are placeholders.
4. **Mobile app never run on a device.** Only statically analyzed (`flutter analyze`, 0 errors). Now unblocked on Firebase — first real run will surface UI issues invisible to static analysis.
5. **App store assets.** No icons, splash screens, screenshots, or store listings prepared yet.
6. **Legal pages need real legal review** before being relied on as binding (see §4.8).

**Lesson from closing #1:** the moment real Firebase credentials made `PushNotificationService`'s topic-broadcast code path actually executable, it immediately surfaced a genuine bug (`CloudMessage::withTarget()` doesn't exist on the installed SDK — fixed to `CloudMessage::new()->withTopic()`) that had been silently masked by graceful-degradation error handling since Sprint 1. Worth remembering for MSG91/Razorpay too: "degrades gracefully when unconfigured" is not the same claim as "works when configured" — re-test the real path the moment real credentials exist for those as well.

---

## 12. Suggested Build Order (Actual, Retrospective)

1. ~~DB schema + Laravel migrations + Filament base setup~~ ✅
2. ~~Auth (Firebase OTP verify → Sanctum) + registration~~ ✅
3. ~~Referral engine + activation state machine (with tests)~~ ✅
4. ~~Payment integration + webhook~~ ✅
5. ~~Notifications (FCM + SMS)~~ ✅ (scaffolded, inert without real credentials)
6. ~~CMS (blogs, categories, banners, SEO)~~ ✅
7. ~~Public website (bilingual, legal pages)~~ ✅ — added mid-stream, not in the original plan
8. ~~Causes & SOS backend + admin resources~~ ✅ — scope expansion once the product vision clarified beyond pure referral-membership
9. ~~Flutter app scaffold + core screens~~ ✅ — architecture complete, untested on-device
10. Hardening, fraud checks, load test on shared hosting — **not started**
11. Handover docs finalized — **in progress** (this document + `docs/sprint-log.md`)

See `docs/sprint-log.md` for how this actually unfolded sprint by sprint, including scope changes along the way.

# UNITY — Sprint Log

Agile tracking for the Unity build. Sprints here are logical work increments (each delivered and verified before moving on), not fixed two-week calendar boxes — the whole log below happened across 2026-07-26 to 2026-07-27. Going forward, add a new sprint section per work session; keep the **Backlog** at the bottom current.

**Status legend:** ✅ Done &nbsp;·&nbsp; 🟡 In progress &nbsp;·&nbsp; ⛔ Blocked &nbsp;·&nbsp; ⬜ Not started

---

## Sprint 1 — Backend Foundation
**Dates:** 2026-07-26
**Goal:** Stand up the Laravel/Filament backend for the referral-activation membership model as originally specced.

**Delivered**
- ✅ Laravel 13 + Filament 3 scaffold, MySQL, Herd local dev environment
- ✅ Core schema: `users` (referral code, activation status), `payments`, `referrals`, `notifications`, `blogs`, `categories`, `banners`, `settings`, `device_tokens`
- ✅ `ActivationService` — server-computed, idempotent activation state machine + 6 unit tests
- ✅ Filament resources: Users (status filters, manual override, referral chain view), Payments (read-only), Referrals (read-only), Blogs/Categories/Banners (full CMS), Settings
- ✅ Filament Shield roles (`admin` full access, `editor` CMS-only)
- ✅ REST API v1: auth (Firebase OTP verify/register), referral, payment initiate, CMS endpoints (blogs/categories/banners), notifications, device-token
- ✅ Razorpay webhook — signature-verified, idempotent
- ✅ Notification scaffolding — FCM push + MSG91 SMS + in-app, graceful degradation when unconfigured
- ✅ Dashboard stats widget

**Decisions**
- Activation rule confirmed: ₹199 fee **AND** 2 paid referrals, both required.
- `WithoutModelEvents` on `DatabaseSeeder` was silently breaking referral-code auto-generation — removed.
- Shield permissions (`shield:generate`) write straight to the DB, not via migrations — added a dedicated `ShieldPermissionsSeeder` so `migrate:fresh` doesn't strand the admin role without permissions.

**Tests at end of sprint:** 11 passing.

---

## Sprint 2 — Production Deployment & Public Website
**Dates:** 2026-07-26
**Goal:** Get the backend live on Hostinger shared hosting, and build the public marketing site.

**Delivered**
- ✅ Deployed to `unity.mavericinfotech.in` (Hostinger shared hosting, no SSH)
- ✅ Root `.htaccess` workaround for the doc-root-can't-point-at-/public constraint
- ✅ Admin password rotated (twice, per explicit request) — surfaced the Hostinger cron quoting gotcha (inline `tinker --execute` corrupts on shell metacharacters), resolved by shipping a dedicated `RotateAdminPassword` Artisan command taking plain positional args
- ✅ Public website: homepage, blog list/detail, about, referral landing — Laravel Blade, same app
- ✅ Full visual redesign: extracted the real brand palette from the Unity logo, Ketto-inspired layout, light/warm theme
- ✅ Homepage sections: hero, "what you can do", activation steps, ₹199 fee explainer, trust/orbital-diagram section, "typical social media vs Unity" comparison, stories feed, CTA
- ✅ "Unity" brand visual — a CSS-only network-of-people animation as a literal metaphor for the product name

**Decisions**
- Site commits to **light theme only**, no `prefers-color-scheme` dark mode — deliberate call for a broad, mixed-age audience where dark mode reads as "tech-elite" rather than trustworthy. Applied consistently to every visual surface built after this point (mobile UI proposal included).

**Bugs found & fixed**
- Orbital CSS animation had zero visible motion — a custom unlayered CSS rule's `animation` shorthand (no duration) was silently overriding a Tailwind arbitrary-value utility that set the real duration, because unlayered CSS always beats `@layer utilities` regardless of source order. Fixed by baking duration directly into each named class.

---

## Sprint 3 — Localization, Trust & Legal
**Dates:** 2026-07-26
**Goal:** Marathi language support and the legal/compliance pages a real product needs before launch.

**Delivered**
- ✅ Full EN/MR bilingual site — session-persisted switcher, `lang/en/site.php` + `lang/mr/site.php`, every page translated
- ✅ Marathi rendered in **Mukta** (proper Devanagari typeface, not a generic system fallback), scoped via CSS `:lang(mr)`
- ✅ Legal pages ×4, both languages: Privacy Policy, Terms & Conditions, Data Policy, Refund & Cancellation Policy — grounded in the real stack (Razorpay/Firebase/MSG91/Hostinger), explicitly flagged as template content needing real legal review
- ✅ Footer: "Developed & managed by Maveric Infotech" credit + contact number
- ✅ Floating WhatsApp button (all pages)

**Notes**
- Legal content is mine (AI-generated), not a lawyer's — this is stated on every legal page and repeated in the system design doc. Do not treat as binding without review, especially for DPDP Act 2023 compliance.

---

## Sprint 4 — Product Scope Expansion: Causes & SOS
**Dates:** 2026-07-26 – 2026-07-27
**Goal:** The client's actual vision (surfaced via a reference UI screenshot) was broader than pure referral-membership — a hyperlocal social-good platform with fundraisers and emergency broadcasts. Design it, get the ambiguous parts decided, then build the backend.

**Delivered**
- ✅ Mobile app UI proposal — 10 phone-frame mockups as a published artifact, using the real brand system
- ✅ Information architecture proposal — content-type segregation (money vs. push vs. editorial), authoring tiers, two flagged open decisions
- ✅ **Decision:** SOS raising restricted to Approved Author tier (not open to all members)
- ✅ **Decision:** No 80G tax badge anywhere in the product — compliance risk, not cosmetic
- ✅ Backend: `author_tier` on users (member/author/organizer, cumulative), `causes` + `sos_alerts` tables, `payments.cause_id`
- ✅ Cause contribution flow reusing the existing Razorpay webhook (`purpose = cause_contribution`, increments `raised_amount`, never touches the payer's own activation state)
- ✅ SOS push-first delivery — dedicated FCM `sos` topic, published immediately, moderated after the fact via Filament
- ✅ Filament: Cause resource, SOS Alert resource ("Mark resolved" action), `author_tier` field on Users, `CausesSosStats` dashboard widget
- ✅ 10 new tests (tier gating, cause contribution webhook, SOS creation/broadcast)

**Bugs found & fixed**
- `AdminUserSeeder` looked up the admin by a hardcoded email; once that email was rotated (Sprint 2), re-running the seeder tried to create a duplicate user and hit a unique-constraint error on phone number. Fixed to key off "does any user hold the admin role" instead of a fixed identifier — idempotent regardless of credential rotation.
- `UserResource` (API) didn't expose `author_tier`, so the mobile app couldn't correctly gate its own "create Cause/SOS" UI (server still enforced the real enforcement either way). Added the field.

**Tests at end of sprint:** 49 passing.

---

## Sprint 5 — Mobile App Foundation
**Dates:** 2026-07-27
**Goal:** Scaffold the Flutter app and build every screen from the UI proposal against the now-complete backend.

**Delivered**
- ✅ Flutter project (`com.mavericinfotech.unity`), Riverpod state management, go_router navigation
- ✅ Brand theme ported exactly from the website (same palette, Instrument Sans, light-only)
- ✅ Dio API client with auth interceptor + typed models matching every API resource
- ✅ Auth flow: phone entry → Firebase OTP → verify/register → Sanctum token
- ✅ Home dashboard (locked/unlocked feed states), Causes (list/detail/contribute/organizer-create), SOS (list/detail/author-create), Blogs (list/detail with HTML rendering), Profile (referral sharing, WhatsApp), Razorpay activation payment screen
- ✅ FCM device-token registration + topic subscription (`broadcast`, `sos`)
- ✅ `flutter analyze`: 0 errors/warnings

**Known incomplete at sprint close**
- ⛔ No real Firebase project — `firebase_options.dart` is a structured placeholder
- ⬜ Never run on a physical device or simulator
- ⬜ No app icons, splash assets, or store listings

---

## Sprint 6 — Documentation
**Dates:** 2026-07-27
**Goal:** Bring `docs/system-design.md` up to date with everything actually built, and start this sprint log.

**Delivered**
- ✅ `docs/system-design.md` rewritten — architecture, schema, API, Filament resources, mobile app structure, deployment mechanics, and known gaps all reflect current reality
- ✅ `docs/sprint-log.md` created (this file)

---

## Sprint 7 — Real Firebase Project
**Dates:** 2026-07-27
**Goal:** Close the biggest blocker in the backlog — stand up an actual Firebase project and wire real credentials into both the backend and the Flutter app.

**Delivered**
- ✅ Firebase project created: `unity-mavericinfotech`
- ✅ Android app (`com.mavericinfotech.unity`) and iOS app (same bundle ID) registered on the project via `flutterfire configure`
- ✅ `lib/firebase_options.dart` regenerated with real API keys/app IDs for both platforms (was a structured placeholder before)
- ✅ Phone sign-in provider enabled (manual step, Firebase Console)
- ✅ Admin SDK service account key generated (manual step, Firebase Console) and wired into both local and production `.env` as `FIREBASE_CREDENTIALS` — stored as a file **outside the web root** (`storage/app/firebase/`, git-ignored, confirmed 404 on direct HTTP request against production), not inlined into `.env` as raw JSON
- ✅ `FCM_PROJECT_ID` set to the real project ID in both environments
- ✅ Verified end-to-end on production via a purpose-built `app:check-firebase-connection` diagnostic command (resolves the Admin SDK, no arguments — sidesteps the cron quoting issue entirely)

**Tooling notes**
- Firebase CLI + FlutterFire CLI were already installed and authenticated on this machine; no fresh install needed.
- `flutterfire configure` initially failed twice: once on a stale cached Dart snapshot (`Invalid SDK hash` — fixed by re-running `dart pub global activate flutterfire_cli`), once on the iOS Xcode-project integration step needing the Ruby `xcodeproj` gem, which wasn't installed (`gem install xcodeproj --user-install` fixed it). Both Android and iOS apps had already registered on Firebase by that point, so the retry was idempotent and just picked up where it left off.
- No `gcloud` CLI on this machine, and `firebase-tools` has no command for enabling a specific Auth sign-in provider or generating an Admin SDK service account key — both are Console-only one-click actions, done manually rather than scripted.

**Bug found & fixed**
- Running the diagnostic command against *real* credentials for the first time immediately surfaced a genuine bug in `PushNotificationService::sendToTopic()`: it called `CloudMessage::withTarget('topic', $topic)`, which doesn't exist on the installed `kreait/firebase-php` SDK — the correct method is `CloudMessage::new()->withTopic($topic)`. This had been silently masked since Sprint 1 because the service's graceful-degradation `try/catch` around Firebase resolution meant the broken call was never actually reached while credentials were blank. `tests/Feature/NotificationDispatcherTest.php` caught it the moment real credentials made the code path executable. **Takeaway:** graceful-degradation code needs at least one test run against a real backing service before being trusted — "it fails safely" and "it works" are different claims.

**Tests at end of sprint:** 49 passing (regression caught and fixed within the same sprint).

**Still blank:** Razorpay production keys, MSG91 account/templates — see Backlog.

---

## Sprint 8 — Documentation Sync
**Dates:** 2026-07-27
**Goal:** Keep `system-design.md` and this log honest after Sprint 7 closed a major backlog item.

**Delivered**
- ✅ `docs/system-design.md` — Firebase moved from "Known Gaps" to done; env var / credential-handling notes updated
- ✅ `docs/sprint-log.md` — this sprint + Sprint 7 added, Backlog table updated

---

## Sprint 9 — First Real Device/Simulator Run

**Dates:** 2026-07-27
**Goal:** Actually run the Flutter app (iOS Simulator) for the first time against the real Firebase project, and fix whatever surfaces.

**Delivered**
- ✅ App launched and ran on iOS Simulator (`iPhone 17 Pro`, iOS 26) via `flutter run`
- ✅ Confirmed clean boot: no crashes, no red error screens, Firebase initializes without throwing
- ✅ Verified visually (simulator screenshots) that the app reaches the phone-entry screen ("Welcome to Unity") with the light brand theme rendering correctly

**Bugs found & fixed**
- `ios/Podfile` and `ios/Runner.xcodeproj/project.pbxproj` still targeted iOS 13.0, but `firebase_auth` (Firebase SDK 12.x) requires iOS 15.0+ as its minimum deployment target — `pod install` failed outright. Fixed by setting `platform :ios, '15.0'` in the Podfile and all 3 `IPHONEOS_DEPLOYMENT_TARGET` entries in the Xcode project.
- Local CocoaPods trunk spec repo was stale and didn't have the Firebase pod version now pinned by `firebase_core` — fixed with `pod repo update`.
- **Real logic bug in `lib/core/router.dart`:** the go_router `redirect` callback included `/splash` in its `loggingIn` set. Once `authControllerProvider` resolved to "signed out," the rule `!isSignedIn && !loggingIn` never fired, because `loggingIn` was `true` while sitting on `/splash` — so the router had no code path that ever left the splash screen. The app silently hung on the splash spinner forever, with no exception thrown anywhere (this is why `flutter analyze` and the graceful-degradation design didn't catch it — it only manifests once you actually watch the screen). Found by adding temporary debug prints to `AuthController.build()`, confirming the auth check itself resolved in ~1 second, then tracing the redirect logic by hand. Fixed by handling `/splash` as its own explicit case: redirect to `/home` if signed in, `/phone` otherwise, independent of the `loggingIn` set.

**Takeaway:** this is the second bug this project has found that was invisible to static analysis and only surfaced by actually running the app end-to-end (the first was the FCM `withTarget` bug in Sprint 7). Both were silent — no crash, no log — reinforcing that "compiles clean" and "works" are different claims for this codebase specifically.

**Tests at end of sprint:** 49 passing (unaffected — this was a Flutter-side bug, no backend change).

---

## Sprint 10 — Post-Router-Fix QA Pass

**Dates:** 2026-07-27
**Goal:** Continue QA past the phone-entry screen now that the app boots correctly.

**What happened**
- Interactive tapping through the OTP flow turned out not to be viable in this environment: no Firebase test phone number is configured, so the simulator can't receive a real SMS, and there's no `idb`/`cliclick`/UI-automation tooling installed to reliably drive taps by screen coordinate (attempted via AppleScript/System Events; unreliable enough to abandon rather than risk false QA signal).
- Pivoted to a targeted manual code review of the screens most likely to hide the same class of bug the router fix caught (silent dead-ends / unreachable states in navigation and async flows): `phone_screen.dart`, `otp_screen.dart`, `register_screen.dart`, `firebase_auth_service.dart`, `activation_payment_screen.dart`, `dashboard_screen.dart`, `home_shell.dart`, `cause_detail_screen.dart`, `sos_create_screen.dart`.
- No further logic bugs found. All screens follow a consistent, correct pattern (loading/error/success state handled per action, `mounted` checks before `setState`/navigation after an `await`).
- Confirmed `cause_detail_screen.dart`'s "Contribute" flow is a known, intentional stub (shows a snackbar, doesn't open Razorpay checkout) — cause-contribution payment wiring is pending real Razorpay keys, same blocker as activation.

**Still open**
- The OTP → register → home path, and everything behind it (Causes contribute-to-payment, SOS create, profile), has only been verified by code review, not by actually running it — needs either a Firebase test phone number (Console: Authentication → Sign-in method → Phone → Phone numbers for testing) or a physical device with a real SIM to close out.

---

## Sprint 11 — First Live OTP Attempt: Found a Real iOS Crash, Two Real Config Bugs, Android Looks Fine

**Dates:** 2026-07-27 – 2026-07-28
**Goal:** With a Firebase test phone number now configured (`9552302834` / code `123456`), actually drive the OTP flow instead of just code-reviewing it.

**What happened — iOS Simulator**
- Simulated taps via AppleScript/System Events, calibrated against the Simulator window's true on-screen content rect (window bounds include title-bar chrome and phone-bezel padding that aren't part of the actual screen — pixel-scanned the real content rectangle to get accurate tap coordinates).
- Typed the test number, tapped **Send OTP** — the app hard-crashed. Crash report: `EXC_BREAKPOINT`/`SIGTRAP`, `_assertionFailure` inside `FirebaseAuth.PhoneAuthProvider.verifyPhoneNumber`.
- Root-caused to **two real, previously-undiscovered config bugs**, present since the project was first scaffolded:
  1. **Bundle ID / applicationId drift on both platforms.** `flutter create` had generated `com.mavericinfotech.unityApp` (iOS `PRODUCT_BUNDLE_IDENTIFIER`) and `com.mavericinfotech.unity_app` (Android `applicationId`/`namespace`), while the actual Firebase apps registered in Sprint 7 (and the documented/intended package name everywhere else) are `com.mavericinfotech.unity`. The running app was never actually the app Firebase had on file, on **either** platform.
  2. **iOS `Info.plist` had no `CFBundleURLTypes`.** Firebase Phone Auth's reCAPTCHA fallback verification (used whenever silent-push app-attestation isn't available — always true on Simulator, often true on fresh real-device installs) needs a URL scheme registered to complete its redirect flow. It was entirely absent.
- Fixed both: corrected `PRODUCT_BUNDLE_IDENTIFIER` (`ios/Runner.xcodeproj/project.pbxproj`, all 6 occurrences) and Android `applicationId`/`namespace` (`android/app/build.gradle.kts`), moved `MainActivity.kt` to match the corrected package directory, added `CFBundleURLTypes` (scheme = the corrected bundle ID) to `ios/Runner/Info.plist`.
- Retested on iOS after the fix: **still crashes**, same signature, now under the correct bundle ID. So the bundle ID / URL scheme fix was necessary (real bugs, worth having fixed) but not sufficient — the iOS crash has a deeper cause, not yet identified. Likely candidate given the Android-side log evidence below: the FirebaseAuth iOS SDK (12.15.0) appears to hard-crash on a condition that the Android SDK handles as a normal recoverable error.

**What happened — physical Android device**
- User connected a real device (Xiaomi M2101K6I, Android 13/API 33) via USB; `adb` wasn't on PATH but the SDK was present at `~/Library/Android/sdk/platform-tools/adb`.
- First `flutter run -d <device>` built and installed fine but `adb` reported exit code 1 on install (needed the on-device "Install via USB debugging" confirmation, which hadn't been accepted yet); retried and it installed and launched cleanly.
- Drove the OTP flow: entering the test number and tapping Send OTP did **not** crash. `adb logcat` showed FirebaseAuth returning the failure through its normal callback (`Invoking original failure callbacks after phone verification failure`), which `firebase_auth_service.dart`'s existing try/catch surfaces as on-screen error text exactly as designed — no native crash, no app-breaking behavior.
- The actual verification failed with Firebase error **17010 / `ERROR_TOO_MANY_REQUESTS`** ("We have blocked all requests from this device due to unusual activity") — this is Firebase's own abuse throttle, tripped by the volume of repeated `verifyPhoneNumber` calls against the same test number during the iOS crash investigation minutes earlier. Not an app bug; needs a cooldown (or a different test number) before a clean successful run can be confirmed.
- Also visible in logcat: `Failed to initialize reCAPTCHA config: No Recaptcha Enterprise siteKey configured for tenant/project` — logged as a recoverable error on Android, versus the same underlying gap apparently crashing iOS outright.

**Decision**
- User confirmed **Android is the primary target platform; iOS support is not yet committed.** Given Android shows no crash and degrades exactly as the code intends, the iOS `PhoneAuthProvider.verifyPhoneNumber` crash is real but de-prioritized — worth a future fix, not a current blocker.

**Still open**
- Retry the Android OTP flow once the Firebase rate-limit cools down (or with a fresh test number) to get one clean, unthrottled pass through OTP → register → home.
- iOS `verifyPhoneNumber` crash root cause is still unconfirmed beyond "probably related to missing reCAPTCHA Enterprise / App Check configuration" — deferred, not urgent, given the Android-first decision.

---

## Sprint 12 — Demo Content, Branding, Overflow Fixes, Release Build

**Dates:** 2026-07-28
**Goal:** Fill the app with real-looking demo content for review, wire in the real logo/icon/splash assets, and produce a signed Play Store build with listing screenshots.

**Delivered**
- ✅ Demo content seeded on production via a new idempotent `app:seed-demo-content` Artisan command (3 published Causes, 3 published Blogs, 2 active SOS alerts) — deployed and run the same way as prior ops tasks (dedicated command + one-off Hostinger cron, cleaned up after). Also found and deleted a stray leftover cron (`AdminUserSeeder --force`, running every minute since an earlier sprint — idempotent so harmless, but wasteful).
- ✅ **Real bug found and fixed:** `CauseResource`/`BlogResource` unconditionally prepended `asset('storage/...')` to `featured_image`, which mangles any full external URL into a broken path. Not just a demo-data problem — this would break for any admin pasting an external image URL via Filament too. Fixed to pass absolute URLs through unchanged.
- ✅ **Real bug found and fixed:** `CauseCard`'s "Verified" badge lived in its own row, adding unaccounted-for height. Fine with placeholder/empty data (which is why it was never caught), but real content (badge + 2-line title) overflowed the dashboard's fixed-height horizontal scroller by up to 58px. Fixed by overlaying the badge on the image via `Stack` instead of stacking it in the layout flow, plus a small height bump for margin. Confirmed via the Flutter log: zero overflow errors across every screen exercised afterward (Home, Causes grid, Cause/SOS/Blog detail, Profile).
- ✅ Real brand assets wired in (previously the app used a coded gradient "U" approximation): `flutter_launcher_icons` for the app icon (opaque iOS + Android adaptive icon with foreground/background/monochrome layers) and `flutter_native_splash` for the native splash screen, both generated from provided source files (`assets/branding/`). Replaced the coded logo mark in `UnityLogo` and `SplashScreen` with the real transparent-background asset.
- ✅ **Android release signing set up for the first time** — no keystore existed before. Generated `~/.android-keystores/unity-release-key.jks` (alias `unity-upload`, self-signed, 10000-day validity), wired via `android/key.properties` (git-ignored) and `android/app/build.gradle.kts`. **The passphrase was generated and given to the user once at creation time — it is not stored anywhere else in this log and must be kept safe (e.g. a password manager), since every future Play Store update needs the same key.**
- ✅ Built and verified the first signed release AAB: `build/app/outputs/bundle/release/app-release.aab` (50.1MB), confirmed signed with the correct release cert via `jarsigner -verify`.
- ✅ Captured 7 Play Store listing screenshots on a Pixel 9 Pro emulator (`store-assets/screenshots-android/`): Home, Causes grid, Cause detail, SOS list, SOS detail, Blogs list, Blog detail. Captured via `adb shell input tap` + `adb exec-out screencap` — far more reliable than the AppleScript/window-coordinate approach needed for the iOS Simulator earlier. Profile screenshot excluded from the store-ready set — it shows a visible "Unauthenticated." error text, a side effect of the temporary demo-login bypass having no real backend token; needs a real signed-in account to capture cleanly.
- ✅ **New, more specific OTP diagnostic finding**, superseding some of Sprint 11's uncertainty: driving the real (non-bypassed) phone-entry screen against the configured Firebase test number now surfaces `This operation is not allowed. This may be because the given sign-in provider is disabled for this Firebase project... [ SMS unable to be sent until this region enabled by the app developer. ]`. This points at Firebase's **SMS region policy** (Authentication → Settings → SMS region policy, a fraud-prevention feature that blocks sending to regions not explicitly allowlisted) as a likely real blocker for India (+91), separate from the earlier rate-limit finding — needs checking directly in Firebase Console, which requires console access this session doesn't have.

**Process note**
- The temporary demo-login bypass in `AuthController.build()` (returns a fake fully-active `UnityUser`, skips the real token/API check) was toggled on twice this session for screenshot/demo purposes and reverted both times immediately after. It is **not** present in the current codebase. Useful pattern for future demo/screenshot needs, but always revert before considering work "done."

**Still open**
- Confirm/fix the Firebase SMS region policy for India — needs Firebase Console access.
- Capture a clean Profile screenshot once real login works (or accept a version without the error banner via a differently-configured demo state).
- Play Store submission itself (feature graphic, descriptions, content rating questionnaire, etc.) not started — only the AAB and in-app screenshots are ready.

---

## Backlog (not yet started)

| Item | Blocked on |
|---|---|
| Razorpay production keys | Client action — Razorpay account/keys |
| MSG91 account + DLT-approved SMS templates | Client action — India SMS compliance registration |
| Retry Android OTP flow for one clean pass (Firebase test number is currently rate-limited from repeated test attempts) | Cooldown, or a different Firebase test number |
| Check/fix Firebase SMS region policy for India (+91) — likely real OTP blocker per Sprint 12 | Needs Firebase Console access |
| Investigate iOS `PhoneAuthProvider.verifyPhoneNumber` crash (likely missing reCAPTCHA Enterprise/App Check config) | De-prioritized — Android is the primary platform, iOS not yet committed |
| Continue manual QA on-device past the phone-entry screen (Causes, SOS, payment screen) | Needs a clean OTP pass first |
| Complete Play Store listing (feature graphic already provided, needs descriptions + content rating + submission) | Client action — Play Console access |
| Postman/Insomnia API collection | — |
| Legal pages — real lawyer review | Client action |
| Fraud checks (same device/IP referral clustering) | No IP/device tracking field exists yet — schema change needed |
| Load testing on shared hosting | — |
| Android App Links / iOS Universal Links for referral deep-linking | — |
| Send an actual test SOS/broadcast push end-to-end (device token → real device) | Needs the app running on a real device first |
| Test on a physical device / Android emulator (only iOS Simulator covered so far) | — |
| Add a real widget test for `test/widget_test.dart` (currently a placeholder) | Needs Firebase/network mocking |

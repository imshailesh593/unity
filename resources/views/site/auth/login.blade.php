@extends('layouts.site')

@section('title', __('site.auth.login_meta_title'))
@section('content')

    <section class="relative overflow-hidden">
        <div class="absolute inset-0 -z-10 bg-linear-to-br from-brand-blue/10 via-warm-50 to-brand-teal/10"></div>
        <div class="mx-auto max-w-md px-6 py-20">
            <div class="rounded-2xl border border-neutral-200 bg-white p-8 shadow-sm">

                {{-- Step 1: phone --}}
                <div id="step-phone">
                    <h1 class="text-2xl font-bold text-neutral-900">{{ __('site.auth.login_heading') }}</h1>
                    <p class="mt-2 text-sm text-neutral-600">{{ __('site.auth.login_subheading') }}</p>

                    <label for="phone" class="mt-6 block text-sm font-medium text-neutral-700">{{ __('site.auth.phone_label') }}</label>
                    <div class="mt-1 flex items-center rounded-lg border border-neutral-300 focus-within:border-brand-blue">
                        <span class="pl-3 text-sm text-neutral-500">+91</span>
                        <input type="tel" id="phone" inputmode="numeric" maxlength="10" autocomplete="tel"
                               placeholder="{{ __('site.auth.phone_placeholder') }}"
                               class="w-full rounded-lg border-0 bg-transparent px-3 py-2.5 text-sm focus:outline-none focus:ring-0">
                    </div>

                    <button id="send-otp-btn" type="button"
                            class="mt-5 w-full rounded-full bg-brand-blue px-6 py-3 text-sm font-semibold text-white shadow-sm shadow-brand-blue/30 hover:bg-brand-blue-dark disabled:opacity-60">
                        {{ __('site.auth.send_otp') }}
                    </button>

                    <p class="mt-4 text-xs text-neutral-400">{{ __('site.auth.recaptcha_notice') }}</p>
                </div>

                {{-- Step 2: OTP --}}
                <div id="step-otp" class="hidden">
                    <h1 class="text-2xl font-bold text-neutral-900">{{ __('site.auth.otp_heading') }}</h1>
                    <p class="mt-2 text-sm text-neutral-600" id="otp-subheading"></p>

                    <label for="otp" class="mt-6 block text-sm font-medium text-neutral-700">{{ __('site.auth.otp_label') }}</label>
                    <input type="text" id="otp" inputmode="numeric" maxlength="6" autocomplete="one-time-code"
                           class="mt-1 w-full rounded-lg border border-neutral-300 px-3 py-2.5 text-center text-lg tracking-[0.5em] focus:border-brand-blue focus:outline-none focus:ring-0">

                    <button id="verify-otp-btn" type="button"
                            class="mt-5 w-full rounded-full bg-brand-blue px-6 py-3 text-sm font-semibold text-white shadow-sm shadow-brand-blue/30 hover:bg-brand-blue-dark disabled:opacity-60">
                        {{ __('site.auth.verify_otp') }}
                    </button>

                    <div class="mt-4 flex justify-between text-xs">
                        <button id="change-number-btn" type="button" class="text-neutral-500 hover:text-brand-blue">{{ __('site.auth.change_number') }}</button>
                        <button id="resend-otp-btn" type="button" class="text-neutral-500 hover:text-brand-blue">{{ __('site.auth.resend_otp') }}</button>
                    </div>
                </div>

                {{-- Step 3: registration (new users only) --}}
                <div id="step-register" class="hidden">
                    <h1 class="text-2xl font-bold text-neutral-900">{{ __('site.auth.register_heading') }}</h1>
                    <p class="mt-2 text-sm text-neutral-600">{{ __('site.auth.register_subheading') }}</p>

                    <label for="name" class="mt-6 block text-sm font-medium text-neutral-700">{{ __('site.auth.name_label') }}</label>
                    <input type="text" id="name" autocomplete="name"
                           class="mt-1 w-full rounded-lg border border-neutral-300 px-3 py-2.5 text-sm focus:border-brand-blue focus:outline-none focus:ring-0">

                    <label for="email" class="mt-4 block text-sm font-medium text-neutral-700">{{ __('site.auth.email_label') }}</label>
                    <input type="email" id="email" autocomplete="email"
                           class="mt-1 w-full rounded-lg border border-neutral-300 px-3 py-2.5 text-sm focus:border-brand-blue focus:outline-none focus:ring-0">

                    <label for="referral_code" class="mt-4 block text-sm font-medium text-neutral-700">{{ __('site.auth.referral_code_label') }}</label>
                    <input type="text" id="referral_code" value="{{ $referralCode }}"
                           class="mt-1 w-full rounded-lg border border-neutral-300 px-3 py-2.5 text-sm uppercase focus:border-brand-blue focus:outline-none focus:ring-0">

                    <button id="create-account-btn" type="button"
                            class="mt-5 w-full rounded-full bg-brand-blue px-6 py-3 text-sm font-semibold text-white shadow-sm shadow-brand-blue/30 hover:bg-brand-blue-dark disabled:opacity-60">
                        {{ __('site.auth.create_account') }}
                    </button>
                </div>

                <p id="auth-error" class="mt-4 hidden rounded-lg bg-red-50 px-4 py-2.5 text-sm text-red-600"></p>
                <div id="recaptcha-container"></div>
            </div>
        </div>
    </section>

    <script src="https://www.gstatic.com/firebasejs/10.13.0/firebase-app-compat.js"
            integrity="sha384-1hBNr0agKHNFwbheHcAJzeLri4PPLTLbaXUH5d9CauwS3A/PrkIt2tzxBJK3bHjY"
            crossorigin="anonymous"></script>
    <script src="https://www.gstatic.com/firebasejs/10.13.0/firebase-auth-compat.js"
            integrity="sha384-QCZO5nLB2oUUv0yv01wZHylPt2CPzKdo0YnmSYCTIOo8XPPIlsIP3f1cikv2oiJ1"
            crossorigin="anonymous"></script>
    <script>
        firebase.initializeApp({
            apiKey: @json(config('unity.firebase_web.api_key')),
            authDomain: @json(config('unity.firebase_web.auth_domain')),
            projectId: @json(config('unity.firebase_web.project_id')),
            storageBucket: @json(config('unity.firebase_web.storage_bucket')),
            messagingSenderId: @json(config('unity.firebase_web.messaging_sender_id')),
            appId: @json(config('unity.firebase_web.app_id')),
        });

        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const stepPhone = document.getElementById('step-phone');
        const stepOtp = document.getElementById('step-otp');
        const stepRegister = document.getElementById('step-register');
        const errorEl = document.getElementById('auth-error');

        let confirmationResult = null;
        let recaptchaVerifier = null;
        let pendingIdToken = null;
        let pendingPhone = null;

        function showError(message) {
            errorEl.textContent = message || @json(__('site.auth.generic_error'));
            errorEl.classList.remove('hidden');
        }

        function clearError() {
            errorEl.classList.add('hidden');
        }

        function setBusy(button, busy) {
            button.disabled = busy;
        }

        function getRecaptcha() {
            if (!recaptchaVerifier) {
                recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', { size: 'invisible' });
            }
            return recaptchaVerifier;
        }

        async function postJson(url, body) {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(body),
            });
            const data = await response.json().catch(() => ({}));
            return { ok: response.ok, status: response.status, data };
        }

        document.getElementById('send-otp-btn').addEventListener('click', async (event) => {
            clearError();
            const digits = document.getElementById('phone').value.replace(/\D/g, '');
            if (digits.length !== 10) {
                showError(@json(__('site.auth.invalid_phone')));
                return;
            }
            const phone = '+91' + digits;
            setBusy(event.currentTarget, true);
            try {
                confirmationResult = await firebase.auth().signInWithPhoneNumber(phone, getRecaptcha());
                pendingPhone = phone;
                document.getElementById('otp-subheading').textContent =
                    @json(__('site.auth.otp_subheading', ['phone' => '__PHONE__'])).replace('__PHONE__', phone);
                stepPhone.classList.add('hidden');
                stepOtp.classList.remove('hidden');
            } catch (error) {
                showError(error.message);
            } finally {
                setBusy(event.currentTarget, false);
            }
        });

        document.getElementById('verify-otp-btn').addEventListener('click', async (event) => {
            clearError();
            const code = document.getElementById('otp').value.trim();
            if (!confirmationResult || code.length < 6) {
                return;
            }
            setBusy(event.currentTarget, true);
            try {
                const result = await confirmationResult.confirm(code);
                const idToken = await result.user.getIdToken();

                const { ok, data } = await postJson(@json(route('login.verify')), { id_token: idToken });

                if (ok && data.status === 'authenticated') {
                    window.location.href = data.redirect;
                } else if (ok && data.status === 'registration_required') {
                    pendingIdToken = idToken;
                    stepOtp.classList.add('hidden');
                    stepRegister.classList.remove('hidden');
                } else {
                    showError(data.message);
                }
            } catch (error) {
                showError(error.message);
            } finally {
                setBusy(event.currentTarget, false);
            }
        });

        document.getElementById('change-number-btn').addEventListener('click', () => {
            clearError();
            stepOtp.classList.add('hidden');
            stepPhone.classList.remove('hidden');
        });

        document.getElementById('resend-otp-btn').addEventListener('click', async (event) => {
            clearError();
            if (!pendingPhone) return;
            setBusy(event.currentTarget, true);
            try {
                confirmationResult = await firebase.auth().signInWithPhoneNumber(pendingPhone, getRecaptcha());
            } catch (error) {
                showError(error.message);
            } finally {
                setBusy(event.currentTarget, false);
            }
        });

        document.getElementById('create-account-btn').addEventListener('click', async (event) => {
            clearError();
            if (!pendingIdToken) return;
            setBusy(event.currentTarget, true);
            try {
                const { ok, data } = await postJson(@json(route('register')), {
                    id_token: pendingIdToken,
                    name: document.getElementById('name').value,
                    email: document.getElementById('email').value,
                    referral_code: document.getElementById('referral_code').value,
                });

                if (ok && data.redirect) {
                    window.location.href = data.redirect;
                } else if (data.errors) {
                    showError(Object.values(data.errors)[0][0]);
                } else {
                    showError(data.message);
                }
            } catch (error) {
                showError(error.message);
            } finally {
                setBusy(event.currentTarget, false);
            }
        });
    </script>

@endsection

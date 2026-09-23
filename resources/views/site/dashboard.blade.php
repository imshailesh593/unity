@extends('layouts.site')

@section('title', __('site.dashboard.meta_title'))
@section('content')

    <section class="relative overflow-hidden">
        <div class="absolute inset-0 -z-10 bg-linear-to-br from-brand-blue/10 via-warm-50 to-brand-teal/10"></div>
        <div class="mx-auto max-w-2xl px-6 py-16">

            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-neutral-900">{{ __('site.dashboard.welcome', ['name' => $user->name]) }}</h1>
                    <span class="mt-2 inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold
                        {{ $user->isActive() ? 'bg-brand-teal/10 text-brand-teal' : 'bg-brand-orange/10 text-brand-orange' }}">
                        {{ $user->isActive() ? __('site.dashboard.status_active') : __('site.dashboard.status_pending') }}
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-full border border-neutral-300 px-4 py-2 text-sm font-semibold text-neutral-700 hover:border-brand-blue hover:text-brand-blue">
                        {{ __('site.dashboard.logout') }}
                    </button>
                </form>
            </div>

            <p class="mt-4 text-sm text-neutral-600">
                {{ $user->isActive() ? __('site.dashboard.status_active_desc') : __('site.dashboard.status_pending_desc') }}
            </p>

            @unless($user->isActive())
                <div class="mt-8 space-y-4">

                    {{-- Step: activation fee --}}
                    <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="font-semibold text-neutral-900">{{ __('site.dashboard.step_fee_title') }}</h2>
                                <p class="mt-1 text-sm text-neutral-600">{{ __('site.dashboard.step_fee_desc', ['amount' => '₹'.$activationFee]) }}</p>
                            </div>
                            @if($user->has_paid)
                                <span class="shrink-0 rounded-full bg-brand-teal/10 px-3 py-1 text-xs font-semibold text-brand-teal">
                                    {{ __('site.dashboard.step_fee_done') }}
                                </span>
                            @endif
                        </div>

                        @unless($user->has_paid)
                            <button id="pay-now-btn" type="button"
                                    class="mt-4 w-full rounded-full bg-brand-blue px-6 py-3 text-sm font-semibold text-white shadow-sm shadow-brand-blue/30 hover:bg-brand-blue-dark disabled:opacity-60">
                                {{ __('site.dashboard.pay_now', ['amount' => '₹'.$activationFee]) }}
                            </button>
                            <p id="payment-status" class="mt-3 hidden text-sm"></p>
                        @endunless
                    </div>

                    {{-- Step: referrals --}}
                    <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm">
                        <h2 class="font-semibold text-neutral-900">{{ __('site.dashboard.step_referrals_title', ['count' => $requiredReferrals]) }}</h2>
                        <p class="mt-1 text-sm text-neutral-600">{{ __('site.dashboard.step_referrals_desc', ['count' => $requiredReferrals]) }}</p>

                        <div class="mt-4">
                            <div class="h-2 w-full overflow-hidden rounded-full bg-neutral-100">
                                <div class="h-full rounded-full bg-brand-teal" style="width: {{ $requiredReferrals > 0 ? min(100, round($paidReferrals / $requiredReferrals * 100)) : 0 }}%"></div>
                            </div>
                            <p class="mt-2 text-xs font-medium text-neutral-500">
                                {{ __('site.dashboard.referrals_progress', ['paid' => $paidReferrals, 'required' => $requiredReferrals]) }}
                            </p>
                        </div>

                        <label class="mt-5 block text-xs font-medium text-neutral-500">{{ __('site.dashboard.referral_link_label') }}</label>
                        <div class="mt-1 flex items-center gap-2">
                            <input id="referral-link" type="text" readonly value="{{ $referralLink }}"
                                   class="w-full truncate rounded-lg border border-neutral-300 bg-neutral-50 px-3 py-2 text-xs text-neutral-600">
                            <button id="copy-link-btn" type="button"
                                    class="shrink-0 rounded-lg border border-neutral-300 px-3 py-2 text-xs font-semibold text-neutral-700 hover:border-brand-blue hover:text-brand-blue">
                                {{ __('site.dashboard.copy_link') }}
                            </button>
                        </div>
                        <a href="https://wa.me/?text={{ urlencode($referralLink) }}" target="_blank" rel="noopener noreferrer"
                           class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-brand-teal hover:underline">
                            {{ __('site.dashboard.share_whatsapp') }}
                        </a>
                    </div>
                </div>
            @endunless
        </div>
    </section>

    @unless($user->isActive() || $user->has_paid)
        {{-- Razorpay's checkout.js is an unversioned, frequently-updated endpoint by
             design (fraud-detection rules ship through it); Razorpay's own docs do
             not support pinning it with Subresource Integrity. --}}
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>
            document.getElementById('pay-now-btn').addEventListener('click', async (event) => {
                const button = event.currentTarget;
                const statusEl = document.getElementById('payment-status');
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                button.disabled = true;

                try {
                    const response = await fetch(@json(route('activation.initiate')), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });
                    const order = await response.json();

                    if (!response.ok) {
                        statusEl.textContent = order.message || @json(__('site.dashboard.payment_failed'));
                        statusEl.classList.remove('hidden');
                        button.disabled = false;
                        return;
                    }

                    const razorpay = new Razorpay({
                        key: order.key,
                        amount: order.amount * 100,
                        currency: order.currency,
                        order_id: order.order_id,
                        name: 'Unity',
                        description: @json(__('site.dashboard.step_fee_title')),
                        prefill: { name: @json($user->name), contact: @json($user->phone) },
                        handler: function () {
                            statusEl.textContent = @json(__('site.dashboard.payment_success'));
                            statusEl.classList.remove('hidden');
                            setTimeout(() => window.location.reload(), 4000);
                        },
                        modal: {
                            ondismiss: function () {
                                button.disabled = false;
                            },
                        },
                    });

                    razorpay.on('payment.failed', function () {
                        statusEl.textContent = @json(__('site.dashboard.payment_failed'));
                        statusEl.classList.remove('hidden');
                        button.disabled = false;
                    });

                    razorpay.open();
                } catch (error) {
                    statusEl.textContent = @json(__('site.dashboard.payment_failed'));
                    statusEl.classList.remove('hidden');
                    button.disabled = false;
                }
            });
        </script>
    @endunless

    <script>
        document.getElementById('copy-link-btn')?.addEventListener('click', async (event) => {
            const input = document.getElementById('referral-link');
            try {
                await navigator.clipboard.writeText(input.value);
                const button = event.currentTarget;
                const original = button.textContent;
                button.textContent = @json(__('site.dashboard.copied'));
                setTimeout(() => { button.textContent = original; }, 2000);
            } catch (error) {
                input.select();
            }
        });
    </script>

@endsection

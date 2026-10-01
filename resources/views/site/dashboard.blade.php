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
                            <p id="payment-status" class="mt-3 text-sm {{ session('payment_status') ? '' : 'hidden' }}">
                                @if(session('payment_status') === 'success')
                                    {{ __('site.dashboard.payment_success') }}
                                @elseif(session('payment_status') === 'failed')
                                    {{ __('site.dashboard.payment_failed') }}
                                @elseif(session('payment_status') === 'pending')
                                    {{ __('site.dashboard.processing_payment') }}
                                @endif
                            </p>
                        @endunless
                    </div>
                </div>
            @endunless
        </div>
    </section>

    @unless($user->isActive() || $user->has_paid)
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

                    if (!response.ok || !order.redirect_url) {
                        statusEl.textContent = order.message || @json(__('site.dashboard.payment_failed'));
                        statusEl.classList.remove('hidden');
                        button.disabled = false;
                        return;
                    }

                    // PhonePe's Standard Checkout is redirect-based — the browser
                    // navigates to PhonePe's hosted page and comes back to our
                    // return route, which redirects here with a status flash.
                    window.location.href = order.redirect_url;
                } catch (error) {
                    statusEl.textContent = @json(__('site.dashboard.payment_failed'));
                    statusEl.classList.remove('hidden');
                    button.disabled = false;
                }
            });
        </script>
    @endunless

@endsection

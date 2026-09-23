@extends('layouts.site')

@section('title', __('site.referral.meta_title'))
@section('content')

    <section class="relative overflow-hidden">
        <div class="absolute inset-0 -z-10 bg-linear-to-br from-brand-blue/10 via-warm-50 to-brand-teal/10"></div>
        <div class="mx-auto max-w-xl px-6 py-24 text-center">
            @if($referrer)
                <span class="inline-flex items-center gap-2 rounded-full border border-brand-teal/30 bg-brand-teal/10 px-4 py-1.5 text-xs font-semibold text-brand-teal">
                    {{ __('site.referral.invited_badge') }}
                </span>
                <h1 class="mt-6 text-3xl font-bold text-neutral-900 sm:text-4xl">{{ __('site.referral.invited_heading', ['name' => $referrer->name]) }}</h1>
                <p class="mt-4 text-neutral-600">
                    {!! __('site.referral.invited_desc', ['code' => '<span class="rounded-full bg-white px-3 py-1 font-mono text-sm text-brand-blue shadow-sm">'.e($code).'</span>']) !!}
                </p>
            @else
                <span class="inline-flex items-center gap-2 rounded-full border border-brand-orange/30 bg-brand-orange/10 px-4 py-1.5 text-xs font-semibold text-brand-orange">
                    {{ __('site.referral.expired_badge') }}
                </span>
                <h1 class="mt-6 text-3xl font-bold text-neutral-900 sm:text-4xl">{{ __('site.referral.expired_heading') }}</h1>
                <p class="mt-4 text-neutral-600">
                    {!! __('site.referral.expired_desc', ['code' => '<span class="rounded-full bg-white px-3 py-1 font-mono text-sm text-neutral-500 shadow-sm">'.e($code).'</span>']) !!}
                </p>
            @endif

            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                @if(config('unity.play_store_url'))
                    <a href="{{ config('unity.play_store_url') }}" class="rounded-full bg-brand-blue px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-brand-blue/30 hover:bg-brand-blue-dark">
                        {{ __('site.home.google_play') }}
                    </a>
                @endif
                @if(config('unity.app_store_url'))
                    <a href="{{ config('unity.app_store_url') }}" class="rounded-full border border-neutral-300 bg-white px-6 py-3 text-sm font-semibold text-neutral-800 hover:border-brand-blue hover:text-brand-blue">
                        {{ __('site.home.app_store') }}
                    </a>
                @endif
            </div>
        </div>
    </section>

@endsection

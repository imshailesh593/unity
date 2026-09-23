@extends('layouts.site')

@section('content')

    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 -z-10 bg-linear-to-br from-brand-blue/10 via-warm-50 to-brand-teal/10"></div>
        <div class="mx-auto max-w-6xl px-6 pb-20 pt-16 text-center sm:pt-24">
            <span class="inline-flex items-center gap-2 rounded-full border border-brand-teal/30 bg-brand-teal/10 px-4 py-1.5 text-xs font-semibold text-brand-teal">
                {{ __('site.home.hero_badge') }}
            </span>
            <h1 class="mx-auto mt-6 max-w-3xl text-4xl font-bold tracking-tight text-neutral-900 sm:text-6xl">
                {{ __('site.home.hero_heading_1') }}
                <span class="bg-linear-to-r from-brand-blue via-brand-purple to-brand-magenta bg-clip-text text-transparent">
                    {{ __('site.home.hero_heading_2') }}
                </span>
            </h1>
            <p class="mx-auto mt-6 max-w-xl text-lg text-neutral-600">
                {{ __('site.home.hero_subheading') }}
            </p>
            <div id="download" class="mt-10 flex flex-wrap items-center justify-center gap-4">
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

            <x-unity-network />
        </div>
    </section>

    {{-- What Unity is for --}}
    <section class="border-y border-neutral-200 bg-white">
        <div class="mx-auto max-w-6xl px-6 py-16">
            <div class="text-center">
                <h2 class="text-2xl font-bold text-neutral-900 sm:text-3xl">{{ __('site.home.features_heading') }}</h2>
                <p class="mx-auto mt-3 max-w-xl text-neutral-500">{{ __('site.home.features_subheading') }}</p>
            </div>
            <div class="mt-12 grid gap-6 sm:grid-cols-3">
                <div class="rounded-2xl border border-neutral-200 p-6">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-orange/10 text-brand-orange">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.97-3.5-9-7.24-9-11.24C3 6.8 5.24 4.5 8.1 4.5c1.68 0 3.09.86 3.9 2.16.81-1.3 2.22-2.16 3.9-2.16 2.86 0 5.1 2.3 5.1 5.26 0 4-4.03 7.74-9 11.24Z"/></svg>
                    </div>
                    <h3 class="mt-4 font-semibold text-neutral-900">{{ __('site.home.feature_urgent_title') }}</h3>
                    <p class="mt-2 text-sm text-neutral-500">{{ __('site.home.feature_urgent_desc') }}</p>
                </div>
                <div class="rounded-2xl border border-neutral-200 p-6">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-teal/10 text-brand-teal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-6-6h12"/><circle cx="12" cy="12" r="9"/></svg>
                    </div>
                    <h3 class="mt-4 font-semibold text-neutral-900">{{ __('site.home.feature_fundraiser_title') }}</h3>
                    <p class="mt-2 text-sm text-neutral-500">{{ __('site.home.feature_fundraiser_desc') }}</p>
                </div>
                <div class="rounded-2xl border border-neutral-200 p-6">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-purple/10 text-brand-purple">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.97-4.03 9-9 9-1.5 0-2.91-.37-4.15-1.02L3 21l1.05-3.68A8.96 8.96 0 0 1 3 12c0-4.97 4.03-9 9-9s9 4.03 9 9Z"/></svg>
                    </div>
                    <h3 class="mt-4 font-semibold text-neutral-900">{{ __('site.home.feature_awareness_title') }}</h3>
                    <p class="mt-2 text-sm text-neutral-500">{{ __('site.home.feature_awareness_desc') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- How activation works --}}
    <section id="how-it-works" class="mx-auto max-w-6xl px-6 py-20">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-neutral-900 sm:text-3xl">{{ __('site.home.activation_heading') }}</h2>
            <p class="mx-auto mt-3 max-w-xl text-neutral-500">{{ __('site.home.activation_subheading') }}</p>
        </div>
        <div class="mt-12 grid gap-8 sm:grid-cols-3">
            <div>
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-blue text-sm font-bold text-white">1</div>
                <h3 class="mt-4 font-semibold text-neutral-900">{{ __('site.home.step1_title') }}</h3>
                <p class="mt-2 text-sm text-neutral-500">{{ __('site.home.step1_desc') }}</p>
            </div>
            <div>
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-blue text-sm font-bold text-white">2</div>
                <h3 class="mt-4 font-semibold text-neutral-900">{{ __('site.home.step2_title', ['fee' => $activationFee]) }}</h3>
                <p class="mt-2 text-sm text-neutral-500">{{ __('site.home.step2_desc') }}</p>
            </div>
            <div>
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-blue text-sm font-bold text-white">3</div>
                <h3 class="mt-4 font-semibold text-neutral-900">{{ __('site.home.step3_title', ['count' => $requiredReferrals]) }}</h3>
                <p class="mt-2 text-sm text-neutral-500">{{ __('site.home.step3_desc') }}</p>
            </div>
        </div>

        {{-- Why the activation fee --}}
        <div class="mt-16 rounded-2xl border border-neutral-200 bg-neutral-50 p-8">
            <h3 class="text-lg font-semibold text-neutral-900">{{ __('site.home.fee_heading', ['fee' => $activationFee]) }}</h3>
            <p class="mt-2 max-w-2xl text-sm text-neutral-500">
                {{ __('site.home.fee_intro') }}
            </p>
            <div class="mt-8 grid gap-6 sm:grid-cols-3">
                <div class="flex gap-3">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-orange/10 text-brand-orange">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9Z"/></svg>
                    </span>
                    <div>
                        <p class="font-medium text-neutral-900">{{ __('site.home.fee_reason1_title') }}</p>
                        <p class="mt-1 text-sm text-neutral-500">{{ __('site.home.fee_reason1_desc') }}</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-teal/10 text-brand-teal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437 5.6 5.6"/></svg>
                    </span>
                    <div>
                        <p class="font-medium text-neutral-900">{{ __('site.home.fee_reason2_title') }}</p>
                        <p class="mt-1 text-sm text-neutral-500">{{ __('site.home.fee_reason2_desc') }}</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-purple/10 text-brand-purple">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                    </span>
                    <div>
                        <p class="font-medium text-neutral-900">{{ __('site.home.fee_reason3_title') }}</p>
                        <p class="mt-1 text-sm text-neutral-500">{{ __('site.home.fee_reason3_desc', ['count' => $requiredReferrals]) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Why Unity — trust & legitimacy --}}
    <section class="border-y border-neutral-200 bg-white">
        <div class="mx-auto max-w-6xl px-6 py-20">
            <div class="text-center">
                <h2 class="text-2xl font-bold text-neutral-900 sm:text-3xl">{{ __('site.home.trust_heading') }}</h2>
                <p class="mx-auto mt-3 max-w-xl text-neutral-500">{{ __('site.home.trust_subheading') }}</p>
            </div>

            <div class="mt-16 grid items-center gap-16 lg:grid-cols-2">
                {{-- Orbital diagram --}}
                <div class="mx-auto flex h-80 w-80 items-center justify-center sm:h-96 sm:w-96" role="img" aria-label="{{ __('site.home.orbit_aria_label') }}">
                    <div class="relative h-full w-full">
                        <div class="absolute inset-0 rounded-full border border-dashed border-neutral-200"></div>
                        <div class="absolute inset-10 rounded-full border border-dashed border-neutral-200"></div>
                        <div class="absolute inset-20 rounded-full border border-dashed border-neutral-200"></div>

                        {{-- Centre: Unity --}}
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-white shadow-lg ring-4 ring-brand-blue/10 sm:h-20 sm:w-20">
                                <img src="{{ asset('images/brand/logo-icon.png') }}" alt="" class="h-9 w-9 sm:h-11 sm:w-11">
                            </div>
                        </div>

                        {{-- Ring 1 — Verified people --}}
                        <div class="orbit-ring-outer absolute inset-0">
                            <div class="absolute left-1/2 top-0 -translate-x-1/2 -translate-y-1/2">
                                <div class="orbit-label-outer flex items-center gap-1.5 whitespace-nowrap rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-neutral-700 shadow-md ring-1 ring-neutral-100">
                                    <span class="h-2 w-2 rounded-full bg-brand-orange"></span> {{ __('site.home.orbit_verified') }}
                                </div>
                            </div>
                        </div>

                        {{-- Ring 2 — Real & local (reverse direction) --}}
                        <div class="orbit-ring-middle absolute inset-10">
                            <div class="absolute left-1/2 top-0 -translate-x-1/2 -translate-y-1/2">
                                <div class="orbit-label-middle flex items-center gap-1.5 whitespace-nowrap rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-neutral-700 shadow-md ring-1 ring-neutral-100">
                                    <span class="h-2 w-2 rounded-full bg-brand-teal"></span> {{ __('site.home.orbit_local') }}
                                </div>
                            </div>
                        </div>

                        {{-- Ring 3 — Community powered --}}
                        <div class="orbit-ring-inner absolute inset-20">
                            <div class="absolute left-1/2 top-0 -translate-x-1/2 -translate-y-1/2">
                                <div class="orbit-label-inner flex items-center gap-1.5 whitespace-nowrap rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-neutral-700 shadow-md ring-1 ring-neutral-100">
                                    <span class="h-2 w-2 rounded-full bg-brand-purple"></span> {{ __('site.home.orbit_community') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Trust points --}}
                <div>
                    <h3 class="text-lg font-semibold text-neutral-900">{{ __('site.home.trust_points_heading') }}</h3>
                    <ul class="mt-6 space-y-5">
                        <li class="flex gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-teal/10 text-brand-teal">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            </span>
                            <div>
                                <p class="font-medium text-neutral-900">{{ __('site.home.trust1_title') }}</p>
                                <p class="text-sm text-neutral-500">{{ __('site.home.trust1_desc') }}</p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-teal/10 text-brand-teal">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            </span>
                            <div>
                                <p class="font-medium text-neutral-900">{{ __('site.home.trust2_title') }}</p>
                                <p class="text-sm text-neutral-500">{{ __('site.home.trust2_desc') }}</p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-teal/10 text-brand-teal">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            </span>
                            <div>
                                <p class="font-medium text-neutral-900">{{ __('site.home.trust3_title') }}</p>
                                <p class="text-sm text-neutral-500">{{ __('site.home.trust3_desc') }}</p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-teal/10 text-brand-teal">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            </span>
                            <div>
                                <p class="font-medium text-neutral-900">{{ __('site.home.trust4_title') }}</p>
                                <p class="text-sm text-neutral-500">{{ __('site.home.trust4_desc') }}</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Comparison --}}
            @php
                $comparisonRows = [
                    ['bad' => __('site.home.compare_bad_1'), 'good' => __('site.home.compare_good_1')],
                    ['bad' => __('site.home.compare_bad_2'), 'good' => __('site.home.compare_good_2')],
                    ['bad' => __('site.home.compare_bad_3'), 'good' => __('site.home.compare_good_3')],
                    ['bad' => __('site.home.compare_bad_4'), 'good' => __('site.home.compare_good_4')],
                ];
            @endphp
            <div class="mt-20">
                <div class="relative mx-auto grid max-w-4xl gap-4 sm:grid-cols-2 sm:gap-0">
                    {{-- Typical social media --}}
                    <div class="rounded-2xl bg-neutral-50 p-8 sm:rounded-r-none">
                        <h3 class="text-sm font-semibold text-neutral-400">{{ __('site.home.compare_bad_heading') }}</h3>
                        <ul class="mt-6 space-y-6">
                            @foreach($comparisonRows as $row)
                                <li class="flex items-start gap-3">
                                    <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-neutral-200 text-neutral-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                    </span>
                                    <span class="text-sm text-neutral-500">{{ $row['bad'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Unity --}}
                    <div class="rounded-2xl bg-linear-to-br from-brand-blue to-brand-blue-dark p-8 shadow-xl shadow-brand-blue/20 sm:rounded-l-none">
                        <h3 class="flex items-center gap-2 text-sm font-semibold text-white">
                            <img src="{{ asset('images/brand/logo-icon.png') }}" alt="" class="h-5 w-5">
                            {{ __('site.home.compare_good_heading') }}
                        </h3>
                        <ul class="mt-6 space-y-6">
                            @foreach($comparisonRows as $row)
                                <li class="flex items-start gap-3">
                                    <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/15 text-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    </span>
                                    <span class="text-sm font-medium text-white">{{ $row['good'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- VS badge --}}
                    <div class="absolute left-1/2 top-1/2 hidden h-12 w-12 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full border-4 border-warm-50 bg-white text-xs font-bold text-neutral-400 shadow-md sm:flex">
                        {{ __('site.home.compare_vs') }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Stories --}}
    @if($latestBlogs->isNotEmpty())
        <section class="border-t border-neutral-200 bg-white">
            <div class="mx-auto max-w-6xl px-6 py-20">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-neutral-900 sm:text-3xl">{{ __('site.home.stories_heading') }}</h2>
                    <a href="{{ route('blog.index') }}" class="text-sm font-semibold text-brand-blue hover:text-brand-blue-dark">{{ __('site.home.view_all') }}</a>
                </div>
                <div class="mt-10 grid gap-8 sm:grid-cols-3">
                    @foreach($latestBlogs as $blog)
                        <a href="{{ route('blog.show', $blog->slug) }}" class="group block">
                            @if($blog->featured_image)
                                <img src="{{ asset('storage/'.$blog->featured_image) }}" alt="" class="aspect-video w-full rounded-xl object-cover">
                            @else
                                <div class="aspect-video w-full rounded-xl bg-linear-to-br from-brand-blue/15 to-brand-teal/15"></div>
                            @endif
                            <h3 class="mt-4 font-semibold text-neutral-900 group-hover:text-brand-blue">{{ $blog->title }}</h3>
                            @if($blog->excerpt)
                                <p class="mt-2 line-clamp-2 text-sm text-neutral-500">{{ $blog->excerpt }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Final CTA --}}
    <section class="mx-auto max-w-6xl px-6 py-20">
        <div class="rounded-3xl bg-linear-to-br from-brand-blue via-brand-purple to-brand-magenta px-8 py-14 text-center text-white">
            <h2 class="text-2xl font-bold sm:text-3xl">{{ __('site.home.cta_heading') }}</h2>
            <p class="mx-auto mt-3 max-w-lg text-white/90">{{ __('site.home.cta_subheading') }}</p>
            <a href="#download" class="mt-8 inline-block rounded-full bg-white px-6 py-3 text-sm font-semibold text-brand-blue hover:bg-warm-50">
                {{ __('site.home.cta_button') }}
            </a>
        </div>
    </section>

@endsection

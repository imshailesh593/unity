@extends('layouts.site')

@section('title', __('site.about.meta_title'))
@section('content')

    <section class="border-b border-neutral-200 bg-white">
        <div class="mx-auto max-w-3xl px-6 py-16 text-center">
            <span class="inline-flex items-center gap-2 rounded-full border border-brand-teal/30 bg-brand-teal/10 px-4 py-1.5 text-xs font-semibold text-brand-teal">
                {{ __('site.about.badge') }}
            </span>
            <h1 class="mt-6 text-3xl font-bold text-neutral-900 sm:text-4xl">{{ __('site.about.heading') }}</h1>
        </div>
    </section>

    <article class="mx-auto max-w-3xl px-6 py-16">
        @if($page)
            <div class="prose max-w-none prose-headings:text-neutral-900 prose-a:text-brand-blue">
                {!! $page->content !!}
            </div>
        @else
            <p class="text-neutral-600">
                {{ __('site.about.placeholder_text') }}
            </p>
            <p class="mt-10 text-sm text-neutral-400">
                {!! __('site.about.placeholder_admin_note', ['slug' => '<code class="rounded bg-neutral-100 px-1.5 py-0.5">about-unity</code>']) !!}
            </p>
        @endif
    </article>

@endsection

@extends('layouts.site')

@section('title', $title.' — Unity')
@section('content')

    <article class="mx-auto max-w-3xl px-6 py-16">
        <h1 class="text-3xl font-bold text-neutral-900 sm:text-4xl">{{ $title }}</h1>
        <p class="mt-2 text-sm text-neutral-400">{{ __('site.legal.last_updated', ['date' => \Illuminate\Support\Facades\Config::get('unity.legal_updated_at')]) }}</p>

        <div class="prose mt-10 max-w-none prose-headings:text-neutral-900 prose-a:text-brand-blue">
            @include($bodyView)
        </div>
    </article>

@endsection

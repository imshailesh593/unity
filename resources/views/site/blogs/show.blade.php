@extends('layouts.site')

@section('title', $blog->seo_title ?: $blog->title)
@section('content')

    <article class="mx-auto max-w-3xl px-6 py-16">
        <a href="{{ route('blog.index') }}" class="text-sm font-semibold text-brand-blue hover:text-brand-blue-dark">{{ __('site.blog.back_to_stories') }}</a>

        @if($blog->category)
            <div class="mt-6 text-xs font-semibold uppercase tracking-wide text-brand-blue">{{ $blog->category->name }}</div>
        @endif
        <h1 class="mt-2 text-3xl font-bold text-neutral-900 sm:text-4xl">{{ $blog->title }}</h1>
        <div class="mt-3 text-sm text-neutral-400">
            @if($blog->author)
                {{ $blog->author->name }} &middot;
            @endif
            {{ $blog->published_at?->format('M j, Y') }}
        </div>

        @if($blog->featured_image)
            <img src="{{ asset('storage/'.$blog->featured_image) }}" alt="" class="mt-8 w-full rounded-2xl object-cover">
        @endif

        <div class="prose mt-10 max-w-none prose-headings:text-neutral-900 prose-a:text-brand-blue">
            {!! $blog->content !!}
        </div>
    </article>

@endsection

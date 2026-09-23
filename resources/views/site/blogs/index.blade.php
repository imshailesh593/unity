@extends('layouts.site')

@section('title', __('site.blog.meta_title'))
@section('content')

    <section class="mx-auto max-w-6xl px-6 py-16">
        <h1 class="text-3xl font-bold text-neutral-900 sm:text-4xl">{{ __('site.blog.index_heading') }}</h1>
        <p class="mt-3 text-neutral-500">{{ __('site.blog.index_subheading') }}</p>

        @if($blogs->isEmpty())
            <p class="mt-16 text-neutral-400">{{ __('site.blog.empty') }}</p>
        @else
            <div class="mt-12 grid gap-10 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($blogs as $blog)
                    <a href="{{ route('blog.show', $blog->slug) }}" class="group block">
                        @if($blog->featured_image)
                            <img src="{{ asset('storage/'.$blog->featured_image) }}" alt="" class="aspect-video w-full rounded-xl object-cover">
                        @else
                            <div class="aspect-video w-full rounded-xl bg-linear-to-br from-brand-blue/15 to-brand-teal/15"></div>
                        @endif
                        @if($blog->category)
                            <div class="mt-4 text-xs font-semibold uppercase tracking-wide text-brand-blue">{{ $blog->category->name }}</div>
                        @endif
                        <h2 class="mt-2 font-semibold text-neutral-900 group-hover:text-brand-blue">{{ $blog->title }}</h2>
                        @if($blog->excerpt)
                            <p class="mt-2 line-clamp-2 text-sm text-neutral-500">{{ $blog->excerpt }}</p>
                        @endif
                    </a>
                @endforeach
            </div>

            <div class="mt-16">
                {{ $blogs->links() }}
            </div>
        @endif
    </section>

@endsection

<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Blog;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::query()
            ->with('category')
            ->where('status', 'published')
            ->latest('published_at')
            ->paginate(9);

        return view('site.blogs.index', compact('blogs'));
    }

    public function show(string $slug)
    {
        $blog = Blog::query()
            ->with(['category', 'author'])
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        return view('site.blogs.show', compact('blog'));
    }
}

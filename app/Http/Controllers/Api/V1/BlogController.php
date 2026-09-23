<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $blogs = Blog::query()
            ->with(['category', 'author'])
            ->where('status', 'published')
            ->when($request->filled('category'), fn ($query) => $query->whereHas(
                'category',
                fn ($q) => $q->where('slug', $request->string('category'))
            ))
            ->latest('published_at')
            ->paginate(15);

        return BlogResource::collection($blogs);
    }

    public function show(string $slug)
    {
        $blog = Blog::query()
            ->with(['category', 'author'])
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        return new BlogResource($blog);
    }
}

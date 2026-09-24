<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Services\ActivationService;

class HomeController extends Controller
{
    public function index(ActivationService $activation)
    {
        $latestBlogs = Blog::query()
            ->where('status', 'published')
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('site.home', [
            'activationFee' => $activation->activationFee(),
            'latestBlogs' => $latestBlogs,
        ]);
    }
}

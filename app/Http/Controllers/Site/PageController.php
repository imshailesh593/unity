<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Blog;

class PageController extends Controller
{
    /**
     * The About/cause page is admin-editable through the existing Blog CMS —
     * it's just a published post with a fixed, well-known slug. This avoids
     * a bespoke "static pages" table for a single page.
     */
    public function about()
    {
        $page = Blog::query()
            ->where('status', 'published')
            ->where('slug', 'about-unity')
            ->first();

        return view('site.about', compact('page'));
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Support\Carbon;

class BannerController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        return BannerResource::collection(
            Banner::query()
                ->where('active', true)
                ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
                ->orderBy('sort_order')
                ->get()
        );
    }
}

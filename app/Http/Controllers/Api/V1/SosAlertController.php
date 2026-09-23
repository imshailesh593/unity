<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\SosAlertCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSosAlertRequest;
use App\Http\Resources\SosAlertResource;
use App\Models\SosAlert;

class SosAlertController extends Controller
{
    public function index()
    {
        $alerts = SosAlert::query()
            ->with('author')
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->latest()
            ->paginate(20);

        return SosAlertResource::collection($alerts);
    }

    public function show(SosAlert $sosAlert)
    {
        return new SosAlertResource($sosAlert->load('author'));
    }

    /**
     * Author-tier only. Published immediately (urgency), then broadcast via
     * push — moderation happens after the fact, not before, by design.
     */
    public function store(StoreSosAlertRequest $request)
    {
        $alert = SosAlert::create([
            ...$request->validated(),
            'author_id' => $request->user()->id,
            'status' => 'active',
            'expires_at' => now()->addDays(3),
        ]);

        event(new SosAlertCreated($alert));

        return (new SosAlertResource($alert))->response()->setStatusCode(201);
    }
}

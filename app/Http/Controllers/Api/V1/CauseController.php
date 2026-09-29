<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCauseRequest;
use App\Http\Resources\CauseResource;
use App\Models\Cause;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CauseController extends Controller
{
    public function index(Request $request)
    {
        $causes = Cause::query()
            ->with(['category', 'organizer'])
            ->where('status', 'published')
            ->when($request->filled('category'), fn ($query) => $query->whereHas(
                'category',
                fn ($q) => $q->where('slug', $request->string('category'))
            ))
            ->latest()
            ->paginate(15);

        return CauseResource::collection($causes);
    }

    public function show(string $slug)
    {
        $cause = Cause::query()
            ->with(['category', 'organizer'])
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        return new CauseResource($cause);
    }

    /**
     * Organizer-tier only. New causes start in pending_review — an admin
     * publishes (and optionally verifies) them via Filament.
     */
    public function store(StoreCauseRequest $request)
    {
        $cause = Cause::create([
            ...$request->validated(),
            'goal_amount' => $request->integer('goal_amount', 0),
            'organizer_id' => $request->user()->id,
            'slug' => Str::slug($request->string('title')).'-'.now()->timestamp,
            'status' => 'pending_review',
        ]);

        return (new CauseResource($cause))->response()->setStatusCode(201);
    }
}

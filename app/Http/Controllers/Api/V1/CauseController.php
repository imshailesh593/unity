<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ContributeToCauseRequest;
use App\Http\Requests\Api\V1\StoreCauseRequest;
use App\Http\Resources\CauseResource;
use App\Models\Cause;
use App\Models\Payment;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CauseController extends Controller
{
    public function __construct(private readonly PaymentGatewayService $gateway) {}

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
            'organizer_id' => $request->user()->id,
            'slug' => Str::slug($request->string('title')).'-'.now()->timestamp,
            'status' => 'pending_review',
        ]);

        return (new CauseResource($cause))->response()->setStatusCode(201);
    }

    /**
     * Creates a pending Payment row against this cause and a matching
     * gateway order. The webhook — not this endpoint — marks it successful
     * and increments the cause's raised_amount.
     */
    public function contribute(ContributeToCauseRequest $request, string $slug)
    {
        $cause = Cause::query()->where('status', 'published')->where('slug', $slug)->firstOrFail();

        $amount = (int) $request->integer('amount');
        $order = $this->gateway->createOrder($amount, receipt: "cause-{$cause->id}-".now()->timestamp);

        $payment = Payment::create([
            'user_id' => $request->user()->id,
            'cause_id' => $cause->id,
            'amount' => $amount,
            'gateway' => 'razorpay',
            'gateway_txn_id' => $order['id'],
            'status' => 'pending',
            'purpose' => 'cause_contribution',
        ]);

        return response()->json([
            'payment_id' => $payment->id,
            'gateway' => 'razorpay',
            'order_id' => $order['id'],
            'amount' => $order['amount'],
            'currency' => $order['currency'],
            'key' => config('services.razorpay.key'),
        ], 201);
    }
}

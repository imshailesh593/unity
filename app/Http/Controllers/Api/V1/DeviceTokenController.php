<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreDeviceTokenRequest;
use App\Models\DeviceToken;

class DeviceTokenController extends Controller
{
    public function store(StoreDeviceTokenRequest $request)
    {
        DeviceToken::updateOrCreate(
            ['token' => $request->string('token')],
            [
                'user_id' => $request->user()->id,
                'platform' => $request->input('platform'),
            ]
        );

        return response()->json(['message' => 'Device token registered.'], 201);
    }
}

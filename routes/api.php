<?php

use App\Http\Controllers\Api\V1\Auth\OtpController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\BlogController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CauseController;
use App\Http\Controllers\Api\V1\DeviceTokenController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PaymentWebhookController;
use App\Http\Controllers\Api\V1\ReferralController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\SosAlertController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {

    // Auth — tightly throttled to slow down OTP/registration abuse.
    Route::middleware('throttle:10,1')->prefix('auth')->group(function () {
        Route::post('verify-otp', [OtpController::class, 'verify']);
        Route::post('register', [RegisterController::class, 'register']);
    });

    // Public CMS/content endpoints.
    Route::get('blogs', [BlogController::class, 'index']);
    Route::get('blogs/{slug}', [BlogController::class, 'show']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('banners', [BannerController::class, 'index']);
    Route::get('settings/public', [SettingController::class, 'public']);
    Route::get('causes', [CauseController::class, 'index']);
    Route::get('causes/{slug}', [CauseController::class, 'show']);
    Route::get('sos', [SosAlertController::class, 'index']);
    Route::get('sos/{sosAlert}', [SosAlertController::class, 'show']);

    // Gateway webhook — authenticated via signature, not Sanctum.
    Route::post('webhooks/payment', [PaymentWebhookController::class, 'handle']);

    // Authenticated app endpoints.
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('user/me', [UserController::class, 'me']);
        Route::get('user/referral', [ReferralController::class, 'index']);
        Route::post('payment/initiate', [PaymentController::class, 'initiate']);
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('device-token', [DeviceTokenController::class, 'store']);

        // Organizer-tier only (enforced in StoreCauseRequest::authorize()).
        Route::post('causes', [CauseController::class, 'store']);
        Route::post('causes/{slug}/contribute', [CauseController::class, 'contribute']);

        // Author-tier only (enforced in StoreSosAlertRequest::authorize()).
        Route::post('sos', [SosAlertController::class, 'store']);
    });
});

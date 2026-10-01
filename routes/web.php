<?php

use App\Http\Controllers\Site\ActivationController;
use App\Http\Controllers\Site\Auth\LoginController;
use App\Http\Controllers\Site\Auth\RegisterController;
use App\Http\Controllers\Site\BlogController;
use App\Http\Controllers\Site\DashboardController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\LegalController;
use App\Http\Controllers\Site\LocaleController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\PaymentReturnController;
use App\Http\Controllers\Site\ReferralLandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/r/{code}', [ReferralLandingController::class, 'show'])->name('referral.landing');
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::get('/privacy-policy', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/terms', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/data-policy', [LegalController::class, 'data'])->name('legal.data');
Route::get('/refund-policy', [LegalController::class, 'refund'])->name('legal.refund');
Route::get('/child-safety-standards', [LegalController::class, 'childSafety'])->name('legal.child-safety');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login/verify', [LoginController::class, 'verify'])->name('login.verify');
    Route::post('/login/password', [LoginController::class, 'password'])->middleware('throttle:6,1')->name('login.password');
    Route::post('/register', [RegisterController::class, 'register'])->name('register');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/activate', [ActivationController::class, 'initiate'])->name('activation.initiate');
    Route::get('/payment/phonepe/return', [PaymentReturnController::class, 'show'])->name('payment.phonepe.return');
});

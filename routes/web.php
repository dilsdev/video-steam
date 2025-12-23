<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\PayoutManagementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', [VideoController::class, 'index'])->name('home');
Route::get('/v/{video:slug}', [VideoController::class, 'show'])->name('videos.show');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Streaming routes (rate limited)
Route::middleware(['throttle:100,1'])->group(function () {
    Route::get('/stream/{token}', [StreamController::class, 'stream'])->name('stream.video');
    Route::post('/stream/{token}/ad-watched', [StreamController::class, 'confirmAdWatched'])->name('stream.ad-watched');
});

// Public video token (untuk guest & authenticated users)
Route::post('/videos/{video}/token', [VideoController::class, 'generateToken'])->name('videos.token');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Membership
    Route::get('/memberships', [MembershipController::class, 'index'])->name('memberships.index');
    Route::post('/memberships', [MembershipController::class, 'store'])->name('memberships.store');
    Route::post('/memberships/validate-voucher', [MembershipController::class, 'validateVoucher'])->name('memberships.validate-voucher');
});

// Uploader routes
Route::middleware(['auth', \App\Http\Middleware\CheckUploader::class])
    ->prefix('uploader')
    ->name('uploader.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Video management
        Route::get('/videos/create', [VideoController::class, 'create'])->name('videos.create');
        Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');
        Route::get('/videos/{video}/edit', [VideoController::class, 'edit'])->name('videos.edit');
        Route::put('/videos/{video}', [VideoController::class, 'update'])->name('videos.update');
        Route::delete('/videos/{video}', [VideoController::class, 'destroy'])->name('videos.destroy');

        // Payouts
        Route::get('/payouts', [PayoutController::class, 'index'])->name('payouts.index');
        Route::get('/payouts/create', [PayoutController::class, 'create'])->name('payouts.create');
        Route::post('/payouts', [PayoutController::class, 'store'])->name('payouts.store');
        Route::post('/payouts/{payout}/cancel', [PayoutController::class, 'cancel'])->name('payouts.cancel');
    });

// Admin routes
Route::middleware(['auth', \App\Http\Middleware\CheckAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Payout management
        Route::get('/payouts', [PayoutManagementController::class, 'index'])->name('payouts.index');
        Route::get('/payouts/{payout}', [PayoutManagementController::class, 'show'])->name('payouts.show');
        Route::post('/payouts/{payout}/process', [PayoutManagementController::class, 'process'])->name('payouts.process');
    });

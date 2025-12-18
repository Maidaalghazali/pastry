<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // REGISTER ROUTES (Aktifkan!)
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    // LOGIN ROUTES
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // PASSWORD RESET ROUTES
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

Route::get('/', function () {
    return redirect()->route('items.index');
});

// TAMBAHKAN INI - Route Dashboard
Route::get('/dashboard', function () {
    return redirect()->route('items.index');
})->middleware(['auth'])->name('dashboard');

// Routes tanpa auth - hanya lihat
Route::get('/items', [ItemController::class, 'index'])->name('items.index');

// Routes butuh login - create, store, dan delete
Route::middleware(['auth'])->group(function () {
    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
});


// Routes khusus leader - edit dan update
Route::middleware(['auth', 'role:leader'])->group(function () {
    Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
});

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Routes untuk semua user (leader & staff)
Route::resource('items', ItemController::class)->only(['index', 'create', 'store', 'destroy']);

Route::middleware(['auth'])->group(function () {

    // Item Routes (yang sudah ada)
    Route::resource('items', ItemController::class);

    // Report Routes (UPDATE INI)
    Route::prefix('reports')->name('reports.')->group(function () {
        // Laporan Bulanan (halaman utama)
        Route::get('/', [ReportController::class, 'monthly'])->name('monthly');

        // Laporan Mingguan dengan detail transaksi
        Route::get('/weekly/{item}', [ReportController::class, 'weekly'])->name('weekly');

        // Regenerate laporan
        Route::post('/regenerate', [ReportController::class, 'regenerate'])->name('regenerate');

        // Legacy route (untuk backward compatibility jika ada link lama)
        Route::get('/index', [ReportController::class, 'index'])->name('index');
    });
});

require __DIR__ . '/auth.php';

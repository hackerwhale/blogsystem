<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\RegisteredUserController;

// General Routes
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Testing Routes
Route::view('/example-page', 'example-page');
Route::view('/example-auth', 'example-auth');

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Auth Routes for Unauthenticated Users
    Route::middleware(['guest'])->group(function(){
        Route::controller(AuthController::class)->group(function () {
            Route::get('/login', 'loginform')->name('login');
            Route::get('/forgot-password', 'forgotform')->name('forgot');
            Route::post('/login-handler', 'loginHandler')->name('login_handler');
            Route::post('/send-password-reset-link','sendPasswordResetLink')->name('send_password_reset_link');
            Route::get('/password/reset/{token}','resetForm')->name('reset_password_form');
            Route::post('/reset-password-handler','resetPasswordHandler')->name('reset_password_handler');


        });
    });

    // Admin Dashboard Routes (Requires Authentication)
    Route::middleware(['auth'])->group(function () {
        Route::controller(AdminController::class)->group(function () {
            Route::get('/dashboard', 'adminDashboard')->name('dashboard');
            Route::post('/logout', 'logoutHandler')->name('logout');
        });
    });
});

// Include auth routes (this is for default Laravel authentication routes)
require __DIR__.'/auth.php';

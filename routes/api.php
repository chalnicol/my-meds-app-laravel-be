<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;

use App\Http\Controllers\MedicationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RoleController;

use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;


use Illuminate\Support\Facades\Broadcast;

// use Illuminate\Foundation\Auth\EmailVerificationRequest;

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/social-login', [SocialLoginController::class, 'socialLogin']);


Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::post('/email/verify', [AuthController::class, 'verifyEmail']);

Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail'])->middleware('throttle:6,1');

//Route::post('/leave-message', [PageController::class, 'leave_message']);
//Route::get('/user/profile', [PageController::class, 'get_user']);


// Protected routes (require authentication with Sanctum)
Route::middleware(['auth:sanctum', 'user.blocked', 'verified'])->group(function () {

    // Broadcast::routes();

    //medications routes
    // Route::apiResource('medications', MedicationController::class);
    Route::post('/medications', [MedicationController::class, 'store']);
    Route::post('/medications/{medication}/stocks', [MedicationController::class, 'addStock']);
    
    Route::get('/medications', [MedicationController::class, 'index']);
    Route::get('/medications/today', [MedicationController::class, 'getTodaysMeds']);

    Route::get('/medications/{medication}/stocks', [MedicationController::class, 'getStocks']);
    Route::get('/medications/{medication}', [MedicationController::class, 'show']);
    Route::get('/medications/{medication}/edit', [MedicationController::class, 'edit']);
    Route::post('/medications/take', [MedicationController::class, 'takeMedication']);
    
    Route::put('/medications/{medication}', [MedicationController::class, 'update']);
    Route::put('/medications/{medication}/stocks/{stock}', [MedicationController::class, 'updateStock']);
    Route::patch('/medications/{medication}/toggleStatus', [MedicationController::class, 'toggleStatus']);
    Route::delete('/medications/{medication}', [MedicationController::class, 'destroy']);

    //auth protected routes..

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', [AuthController::class, 'user']); // Get authenticated user's details

    Route::put('/user/profile', [AuthController::class, 'update_profile']);

    Route::put('/user/password', [AuthController::class, 'update_password']);

    Route::put('/user/settings', [AuthController::class, 'update_settings']);

    Route::delete('/user', [AuthController::class, 'delete_account']);

    
    Route::group(['middleware' => ['role:admin']], function () {
        //..
        Route::get('/admin', [AdminController::class, 'getCounts']);
        
        Route::get('/admin/users', [UserController::class, 'index']);
        Route::get('/admin/users/{user}', [UserController::class, 'show']);
        Route::patch('/admin/users/{user}/toggleBlock', [UserController::class, 'toggleBlockUser']);
        Route::patch('/admin/users/{user}/updateRoles', [UserController::class, 'updateUserRoles']);
        
        Route::get('/admin/roles', [RoleController::class, 'index']);

        
    });
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Events (public access for viewing)
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event}', [EventController::class, 'show']);
    
    // Events management (organizer & admin)
    Route::middleware(['role:admin,organizer'])->group(function () {
        Route::post('/events', [EventController::class, 'store']);
        Route::put('/events/{event}', [EventController::class, 'update']);
        Route::delete('/events/{event}', [EventController::class, 'destroy']);
        
        // Ticket management
        Route::post('/events/{event}/tickets', [TicketController::class, 'store']);
        Route::put('/tickets/{ticket}', [TicketController::class, 'update']);
        Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy']);
    });
    
    // Bookings (customers)
    Route::post('/tickets/{ticket}/bookings', [BookingController::class, 'store'])
        ->middleware('prevent.double.booking');
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::put('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    
    // Payments
    Route::post('/bookings/{booking}/payment', [PaymentController::class, 'process']);
    Route::get('/payments/{payment}', [PaymentController::class, 'show']);
});
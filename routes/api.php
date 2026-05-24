<?php

use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ScheduleController;
use App\Http\Middleware\CheckAdminRole;
use Illuminate\Support\Facades\Route;


// =====================================================================
// PUBLIC ROUTES (guests & Admins can access)
// =====================================================================
Route::post('/light/toggle', [DeviceController::class, 'toggle']);
Route::get('/light/status', [DeviceController::class, 'status']);
Route::get('/schedules', [ScheduleController::class, 'index']);

// ======================================================================
// PROTECTED RIUTES (Admins ONLY)
// ======================================================================
Route::middleware([CheckAdminRole::class])->group(function () {
   Route::get('/budget/current', [DeviceController::class, 'getBudget']);
   Route::post('/schedules', [ScheduleController::class, 'store']);
   Route::delete('/schedules/{id}', [ScheduleController::class, 'destroy']);
});

// The Secret Cron Webhook (Npo auth needed, triggered by the machine)
Route::get('/schedules/trigger', [ScheduleController::class, 'trigger']);

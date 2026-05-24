<?php

use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ScheduleController;
use App\Http\Middleware\CheckAdminRole;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

// Save the color sent from the phoneto the larvarel's memory
Route::post('/light/color', function (Request $request) {
    Cache::put('smart_bulb_color', $request->input('color', '#facc15'), 86400);
    return response()->json(['status' => 'success', 'color' => $request->input('color')]);
});

// Let the laptop projector read the current color
Route::get('/light/color', function () {
    return response()->json(['color' => Cache::get('smart_bulb_color', '#facc15')]);
});

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

<?php

use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::post('/light/toggle', [DeviceController::class, 'toggle']);
Route::get('/light/status', [DeviceController::class, 'status']);
Route::get('/budget/current', [DeviceController::class, 'getBudget']);
Route::get('/schedules', [ScheduleController::class, 'index']);
Route::post('/schedules', [ScheduleController::class, 'store']);
Route::delete('/schedules/{id}', [ScheduleController::class, 'destroy']);
Route::get('/schedules/trigger', [ScheduleController::class, 'trigger']);

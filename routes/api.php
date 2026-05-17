<?php

use App\Http\Controllers\DeviceController;
use Illuminate\Support\Facades\Route;

Route::post('/light/toggle', [DeviceController::class, 'toggle']);
Route::get('/light/status', [DeviceController::class, 'status']);
<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function toggle()
    {
        // Find the light or create it if it doesn't exist
        $device = Device::firstOrCreate(
            ['id' => 1],
            ['name' => 'Main Room Light', 'is_on' => false]
        );

        // Flip the boolean state
        $device->is_on = !$device->is_on;
        $device->save();

        return response()->json(['is_on' => $device->is_on, 'last_active' => $device->updated_at->diffForHumans()]);
    }

    public function status()
    {
        $device = Device::find(1);
        return response()->json([
            'is_on' => $device ? $device->is_on : false, 'last_active' => $device ?
            $device->updated_at->diffForHumans() : 'Never'
            ]);
    }
}
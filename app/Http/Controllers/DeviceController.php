<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\EnergyLog;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function toggle(Request $request)
    {
        // Find the light or create it if it doesn't exist
        $device = Device::firstOrCreate(
            ['id' => 1],
            ['name' => 'Main Room Light', 'is_on' => false]
        );

        // Flip the boolean state
        $device->is_on = !$device->is_on;
        $device->save();

        // --- NEW: SMART METER LOGIC ---
        if ($device->is_on) {
            // The light turned ON: Start the meter
            EnergyLog::create([
                'appliance_name' => $device->name,
                'wattage' => 15, // Assuming a 15W LED bulb
                'turned_on_at' => now(),
            ]);
        } else {
            // The light turned OFF: Stop the meter and calculate
            $log = EnergyLog::where('appliance_name', $device->name)
                            ->whereNull('turned_off_at')
                            ->latest()
                            ->first();
                            
            if ($log) {
                $log->turned_off_at = now();
                
                // Calculate time difference in hours
                $hours = $log->turned_on_at->diffInMinutes(now()) / 60;
                
                // Formula: (Watts * Hours) / 1000 = kWh
                $log->total_kwh = (15 * $hours) / 1000;
                $log->save();
            }
        }
        // ------------------------------

        return response()->json([
            'is_on' => $device->is_on, 
            'last_active' => $device->updated_at->diffForHumans()
        ]);
    }

    public function status()
    {
        $device = Device::find(1);
        return response()->json([
            'is_on' => $device ? $device->is_on : false, 
            'last_active' => $device ? $device->updated_at->diffForHumans() : 'Never'
        ]);
    }

    // --- NEW: BUDGET CALCULATOR ---
    public function getBudget()
    {
        // Get all completed logs for the current month
        $currentMonthLogs = EnergyLog::whereNotNull('total_kwh')
                                     ->whereMonth('created_at', now()->month)
                                     ->get();
        
        // Sum the total kWh used
        $totalKwh = $currentMonthLogs->sum('total_kwh');
        
        // Assume electricity costs ₦200 per kWh
        $ratePerKwh = 200; 
        $currentSpend = $totalKwh * $ratePerKwh;

        return response()->json([
            'current_spend' => round($currentSpend, 2),
            'total_kwh' => round($totalKwh, 4)
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    // Fetch all active schedules to show on the React dashboard
    public function index()
    {
        return response()->json(Schedule::where('is_active', true)->get());
    }

    // Save a new timer from React
    public function store(Request $request)
    {
        $request->validate([
            'action' => 'required|in:on,off',
            'scheduled_time' => 'required|date_format:H:i',
        ]);

        $schedule = Schedule::create([
            'device_name' => 'Main Room Light',
            'action' => $request->action,
            'scheduled_time' => $request->scheduled_time,
            'is_active' => true,
        ]);

        return response()->json(['message' => 'Schedule set successfully!', 'schedule' => $schedule]);
    }

    // Delete a timer
    public function destroy($id)
    {
        Schedule::destroy($id);
        return response()->json(['message' => 'Schedule removed']);
    }

    // --- NEW: The Automated Webhook Trigger ---
    public function trigger()
    {
        // Get the current time (e.g., "14:30")
        $now = now()->format('H:i');
        
        // Find any active schedules matching this exact minute
        $schedules = Schedule::where('is_active', true)
                             ->where('scheduled_time', 'like', $now . '%')
                             ->get();

        $executedCount = 0;

        foreach ($schedules as $schedule) {
            // Grab our main device
            $device = \App\Models\Device::find(1);
            if (!$device) continue;

            $targetState = ($schedule->action === 'on');

            // Only flip the switch if it actually needs to change
            if ($device->is_on !== $targetState) {
                $device->is_on = $targetState;
                $device->save();

                // Run the Smart Meter Logging
                if ($device->is_on) {
                    \App\Models\EnergyLog::create([
                        'appliance_name' => $device->name,
                        'wattage' => 15,
                        'turned_on_at' => now(),
                    ]);
                } else {
                    $log = \App\Models\EnergyLog::where('appliance_name', $device->name)
                                    ->whereNull('turned_off_at')
                                    ->latest()
                                    ->first();
                    if ($log) {
                        $log->turned_off_at = now();
                        $hours = $log->turned_on_at->diffInMinutes(now()) / 60;
                        $log->total_kwh = (15 * $hours) / 1000;
                        $log->save();
                    }
                }
                $executedCount++;
            }
        }

        return response()->json([
            'status' => 'success',
            'time_checked' => $now,
            'actions_executed' => $executedCount
        ]);
    }
}    
            
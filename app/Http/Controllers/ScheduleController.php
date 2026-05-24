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

    // --- UPGRADED: Automated Trigger with X-Ray Diagnostics ---
    public function trigger()
    {
        $now = now()->format('H:i');
        
        // Grab ALL active schedules so we can see what the server sees
        $schedules = Schedule::where('is_active', true)->get();

        $executedCount = 0;
        $debugLogs = [];

        foreach ($schedules as $schedule) {
            $device = \App\Models\Device::find(1);
            if (!$device) continue;

            // Bulletproof time matching (forces "01:42:00" to become "01:42")
            $dbTime = substr($schedule->scheduled_time, 0, 5);
            $timeMatches = ($dbTime === $now);
            
            $targetState = ($schedule->action === 'on');
            
            // Build the X-Ray log
            $log = [
                'saved_alarm_time' => $dbTime,
                'server_clock_now' => $now,
                'time_matches' => $timeMatches,
                'light_already_in_state' => ($device->is_on === $targetState),
                'action_taken' => 'none'
            ];

            if ($timeMatches) {
                // Only flip if the light isn't ALREADY in the target state
                if ($device->is_on !== $targetState) {
                    
                    $device->is_on = $targetState;
                    $device->save();

                    // --- Smart Meter Math ---
                    if ($device->is_on) {
                        \App\Models\EnergyLog::create([
                            'appliance_name' => $device->name,
                            'wattage' => 15,
                            'turned_on_at' => now(),
                        ]);
                    } else {
                        $energy = \App\Models\EnergyLog::where('appliance_name', $device->name)
                                        ->whereNull('turned_off_at')
                                        ->latest()->first();
                        if ($energy) {
                            $energy->turned_off_at = now();
                            $hours = $energy->turned_on_at->diffInMinutes(now()) / 60;
                            $energy->total_kwh = (15 * $hours) / 1000;
                            $energy->save();
                        }
                    }
                    // ------------------------

                    $log['action_taken'] = "SUCCESS: Switched light " . strtoupper($schedule->action);
                    $executedCount++;
                } else {
                    $log['action_taken'] = "SKIPPED: Light was already " . strtoupper($schedule->action);
                }
            } else {
                $log['action_taken'] = "IGNORED: Not the right time yet";
            }
            
            $debugLogs[] = $log;
        }

        return response()->json([
            'status' => 'success',
            'actions_executed' => $executedCount,
            'x_ray_vision' => $debugLogs
        ]);
    }
}
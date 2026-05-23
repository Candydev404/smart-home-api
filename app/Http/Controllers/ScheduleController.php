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
}
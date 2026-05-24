<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the request has the Admin role header
        if ($request->header('X-User-Role') !== 'Admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Access Denied: You do not have Admin privileges to perform this action.'
            ], 403);
        }

        // If they are an Admin, let them through
        return $next($request);
    }
}
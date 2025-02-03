<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (Auth::check()) {
            $user = Auth::user();
            $activity = $this->getUserActivity($request);

            // Save the activity log
            UserActivityLog::create([
                'user_id' => $user->id,
                'activity' => $activity['activity'],
                'ip_address' => $request->ip(),
                'details' => json_encode($activity['details']),
            ]);
        }

        return $response;
    }

    private function getUserActivity(Request $request)
    {
        // Capture details about the request
        return [
            'activity' => $request->method() . ' ' . $request->path(),
            'details' => [
                'headers' => $request->headers->all(),
                'query' => $request->query(),
                'body' => $request->all(),
            ],
        ];
    }
}

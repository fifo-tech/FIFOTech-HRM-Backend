<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIpAddress
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Function to check if an IP is within a given CIDR range
        function ipInRange($ip, $range) {
            list($subnet, $mask) = explode('/', $range);
            $subnet = ip2long($subnet); // Convert subnet to long integer
            $ip = ip2long($ip); // Convert the request IP to long integer
            $mask = ~((1 << (32 - $mask)) - 1); // Apply subnet mask to get the range

            // Check if the IP is within the range
            return ($ip & $mask) === ($subnet & $mask);
        }

        // List of allowed IP ranges in CIDR format (e.g., '192.168.105.0/24')
        $allowedIps = ['192.168.105.0/24']; // You can add other IP ranges here

        // Check if the request's IP is in the allowed list or matches any CIDR range
        $isAllowed = false;
        foreach ($allowedIps as $allowedIp) {
            // If the allowed IP is a valid IP address
            if (filter_var($allowedIp, FILTER_VALIDATE_IP)) {
                if ($request->ip() == $allowedIp) {
                    $isAllowed = true;
                    break;
                }
            } else {
                // If it's a CIDR range, check if the request IP falls within that range
                if (ipInRange($request->ip(), $allowedIp)) {
                    $isAllowed = true;
                    break;
                }
            }
        }

        // If the IP is not allowed, return 403 response
        if (!$isAllowed) {
            return response()->json(['success' => false, 'message' => 'Unauthorized IP address'], 403);
        }

        // If allowed, proceed with the next request
        return $next($request);
    }
}

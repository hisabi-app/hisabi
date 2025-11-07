<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockDemoActions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the authenticated user is a demo user
        if ($request->user() && $this->isDemoUser($request->user())) {
            // Block DELETE, POST, PUT, PATCH requests for demo users
            if (in_array($request->method(), ['DELETE', 'POST', 'PUT', 'PATCH'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This action is not allowed in demo mode. Data is read-only and will be refreshed every hour.',
                ], 403);
            }
        }

        return $next($request);
    }

    /**
     * Check if the user is a demo user
     */
    private function isDemoUser($user): bool
    {
        return $user->email === config('hisabi.demo.email');
    }
}

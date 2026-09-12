<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentBusiness
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $businessId = $request->header('X-Business-ID');

        if (! $businessId) {
            return response()->json([
                'message' => 'Business context is required.',
            ], 400);
        }

        $business = $user->businesses()
            ->where('businesses.id', $businessId)
            ->wherePivot('status', 'active')
            ->first();

        if (! $business) {
            return response()->json([
                'message' => 'You do not have access to this business.',
            ], 403);
        }

        app()->instance('currentBusiness', $business);

        return $next($request);
    }
}
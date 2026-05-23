<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSellerIsApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->seller_status !== 'approved') {
            return response()->json([
                'message' => 'Your seller account has not been approved yet. Current status: ' . $user->seller_status,
                'rejection_reason' => $user->rejection_reason
            ], 403);
        }

        return $next($request);
    }
}

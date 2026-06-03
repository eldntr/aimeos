<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureIsAdmin
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

        // Get default site ID dynamically
        try {
            $context = app('aimeos.context')->get();
            $siteManager = \Aimeos\MShop::create($context, 'locale/site');
            $defaultSiteId = $siteManager->find('default')->getSiteId();
        } catch (\Exception $e) {
            $defaultSiteId = '1.';
        }

        // Asumsi admin adalah user di default site atau siteid empty, atau superuser = 1
        if (!$user || ($user->superuser !== 1 && $user->siteid !== $defaultSiteId && $user->siteid !== '' && $user->siteid !== null)) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        return $next($request);
    }
}

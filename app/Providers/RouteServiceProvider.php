<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/profile';


    /**
     * Returns the relative URL to the home of the user
     *
     * @return string Relative URL
     */

    public static function home()
    {
        if( config( 'app.shop_registration' ) ) {
            return route('aimeos_shop_admin');
        }

        return airoute( 'aimeos_shop_account' );
    }


    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        Route::pattern('site', '^(?!profile|login|register|logout|dashboard|forgot-password|reset-password|verify-email|confirm-password|ready)[A-Za-z0-9\.\-]+');

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });

        $this->app->booted(function () {
            foreach (Route::getRoutes() as $route) {
                if (in_array('site', $route->parameterNames())) {
                    $route->where('site', '^(?!profile|login|register|logout|dashboard|forgot-password|reset-password|verify-email|confirm-password|ready)[A-Za-z0-9\.\-]+');
                }
            }
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('checkout_process', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}

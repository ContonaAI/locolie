<?php

namespace App\Providers;

use App\Models\Business;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Default "api" throttle limiter (referenced by routes/api.php via throttle:api).
        RateLimiter::for('api', fn (Request $r) => Limit::perMinute(60)->by($r->ip()));

        // Launch-market values (config/locolie.php) available to every view, so
        // marketing copy/meta/schema scale to a new city by changing config only.
        $launch = config('locolie.launch');
        View::share('ll', $launch);
        View::share('llPlace', $launch['place']);       // "Newcastle NE1"
        View::share('llCity', $launch['city']);         // "Newcastle"
        View::share('llFeaturedPrice', Business::PLANS['featured']['price'] ?? 19);
    }
}

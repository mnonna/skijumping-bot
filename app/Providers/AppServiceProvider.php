<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
class AppServiceProvider extends ServiceProvider {
    public function register(): void {}
    public function boot(): void {
        RateLimiter::for('api', fn(Request $request) => Limit::perMinute(120)->by((string)($request->user()?->id ?? $request->ip())));
    }
}

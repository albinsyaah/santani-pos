<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // After signing out, go back to the login page.
        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse
        {
            public function toResponse($request)
            {
                return redirect()->route('login');
            }
        });
    }

    public function boot(): void
    {
        Fortify::loginView(fn () => view('auth.login'));

        // Only active accounts may sign in. A wrong password, an unknown email and
        // a disabled account all fail the same way, so nothing leaks about which
        // emails exist.
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', Str::lower((string) $request->input(Fortify::username())))->first();

            if ($user && $user->status === 'active' && Hash::check((string) $request->input('password'), $user->password)) {
                return $user;
            }

            return null;
        });

        // 5 attempts per minute for each email + IP address.
        RateLimiter::for('login', function (Request $request) {
            $key = Str::transliterate(Str::lower((string) $request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($key);
        });
    }
}

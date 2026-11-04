<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    public const LOGIN_ATTEMPTS_PER_MINUTE = 3;

    public const LOGIN_ATTEMPTS_PER_IP_PER_MINUTE = 3;

    public const REGISTER_ATTEMPTS_PER_MINUTE = 3;

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
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
            $email = Str::lower((string) $request->input('email'));
            $response = fn (Request $request, array $headers) => $this->tooManyAttemptsResponse($request, $headers, 'email');

            return [
                Limit::perMinute(self::LOGIN_ATTEMPTS_PER_MINUTE)->by('login:'.$email.'|'.$request->ip())->response($response),
                Limit::perMinute(self::LOGIN_ATTEMPTS_PER_IP_PER_MINUTE)->by('login-ip:'.$request->ip())->response($response),
            ];
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(self::REGISTER_ATTEMPTS_PER_MINUTE)
                ->by($request->ip())
                ->response(fn (Request $request, array $headers) => $this->tooManyAttemptsResponse($request, $headers, 'email'));
        });
    }

    /**
     * @param array<string, int|string> $headers
     */
    private function tooManyAttemptsResponse(Request $request, array $headers, string $field): RedirectResponse
    {
        $seconds = (int) ($headers['Retry-After'] ?? 60);

        return back()
            ->withInput($request->only($field))
            ->withErrors([$field => "Demasiados intentos. Inténtalo de nuevo en {$seconds} segundos."]);
    }
}

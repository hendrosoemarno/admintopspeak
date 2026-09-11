<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Konsistenkan scheme (http/https) di semua URL dari APP_URL.
        $appUrl = rtrim((string) config('app.url'), '/');
        if ($scheme = parse_url($appUrl, PHP_URL_SCHEME)) {
            URL::forceScheme($scheme);
        }

        // Deploy subdirektori (mis. https://topexam.id/admintopspeak):
        // paksa semua URL (route incl. Livewire JS, asset) menyertakan prefix folder.
        $subdir = trim((string) parse_url($appUrl, PHP_URL_PATH), '/');
        if ($subdir) {
            URL::forceRootUrl($appUrl);

            // Route POST /livewire/update tak bisa dipakai di subfolder karena
            // Livewire memangkas baseUrl sehingga jatuh ke web root (404).
            // Daftarkan route serupa yang menyertakan prefix subfolder.
            Livewire::setUpdateRoute(function ($handle) use ($subdir) {
                return Route::post($subdir.'/livewire/update', $handle)
                    ->middleware('web');
            });
        }

        // Anti brute-force login: maksimal 5 percobaan per menit per email + IP.
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(
                Str::lower((string) $request->input('email')).'|'.$request->ip(),
            );
        });
    }
}
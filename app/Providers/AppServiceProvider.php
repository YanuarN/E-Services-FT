<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
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
        $this->configureRequestUrls();
        Vite::prefetch(concurrency: 3);
    }

    protected function configureRequestUrls(): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $rootUrl = rtrim(request()->root(), '/');

        if ($rootUrl === '') {
            $rootUrl = rtrim((string) config('app.url', ''), '/');
        }

        if ($rootUrl === '') {
            return;
        }

        $isHttps = str_starts_with($rootUrl, 'https://');

        config([
            'app.url' => $rootUrl,
            'filesystems.disks.public.url' => "{$rootUrl}/storage",
            'session.secure' => $isHttps,
        ]);

        URL::forceRootUrl($rootUrl);

        if ($isHttps) {
            URL::forceScheme('https');
        }
    }
}

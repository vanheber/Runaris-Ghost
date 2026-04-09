<?php

namespace App\Providers;

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
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('system_settings')) {
                $locale = \App\Models\SystemSetting::getSetting('system_locale', 'pt_BR');
                app()->setLocale($locale);
            }
        } catch (\Exception $e) {
            // Silently ignore if DB is not ready
        }
    }
}

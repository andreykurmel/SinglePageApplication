<?php

namespace Vanguard\Providers;

use Illuminate\Support\ServiceProvider;
use Vanguard\Modules\Geolocation\GeoLocationInterface;
use Vanguard\Modules\Geolocation\IpApiComLocator;
use Vanguard\Singletones\AuthUserModule;
use Vanguard\Singletones\AuthUserSingleton;

class StandAloneModulesProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(AuthUserSingleton::class, AuthUserModule::class);

        $this->app->bind(GeoLocationInterface::class, IpApiComLocator::class);
    }
}

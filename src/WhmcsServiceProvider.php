<?php

namespace DarthSoup\Whmcs;

use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use DarthSoup\Whmcs\Adapter\GuzzleHttpAdapter;

/**
 * Whmcs ServiceProvider.
 */
class WhmcsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->isLaravel()) {
            $source = dirname(__DIR__).'/config/whmcs.php';
            $this->publishes([$source => config_path('whmcs.php')]);
            $this->mergeConfigFrom($source, 'whmcs');
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerClient();

        $this->registerWhmcs();
    }

    /**
     * register Client.
     */
    public function registerClient()
    {
        $this->app->singleton('whmcs.client', function () {
            return new GuzzleHttpAdapter();
        });
        $this->app->alias('whmcs.client', GuzzleHttpAdapter::class);
    }

    /**
     * register Whmcs.
     */
    public function registerWhmcs()
    {
        $this->app->singleton('whmcs', function (Container $app) {
            $config = $app['config'];
            $client = $app['whmcs.client'];

            return new WhmcsManager($config, $client);
        });
        $this->app->alias('whmcs', Whmcs::class);
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            'whmcs.client',
            'whmcs',
        ];
    }

    /**
     * @return bool
     */
    protected function isLaravel()
    {
        return ! preg_match('/lumen/i', $this->app->version());
    }
}

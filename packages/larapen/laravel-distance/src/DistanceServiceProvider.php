<?php

namespace Larapen\LaravelDistance;

use Illuminate\Support\ServiceProvider;

class DistanceServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('distance', function ($app) {
			return new Distance($app);
		});
	}
	
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
		// Merge plugin config
		$this->mergeConfigFrom(realpath(__DIR__ . '/config/distance.php'), 'distance');
    }
    
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['distance'];
    }
}

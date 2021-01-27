<?php

namespace Larapen\Admin;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class AdminServiceProvider extends ServiceProvider
{
	/**
	 * Register any package services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('admin', function ($app) {
			return new Admin($app);
		});
		
		// Register its dependencies
		$this->app->register(\Jenssegers\Date\DateServiceProvider::class);
		$this->app->register(\Prologue\Alerts\AlertsServiceProvider::class);
		$this->app->register(\Collective\Html\HtmlServiceProvider::class);
		$this->app->register(\Intervention\Image\ImageServiceProvider::class);
		$this->app->register(\Spatie\Permission\PermissionServiceProvider::class);
		
		// Register their aliases
		$loader = \Illuminate\Foundation\AliasLoader::getInstance();
		$loader->alias('Alert', \Prologue\Alerts\Facades\Alert::class);
		$loader->alias('Date', \Jenssegers\Date\Date::class);
		$loader->alias('CRUD', RoutesCrud::class);
		$loader->alias('Form', \Collective\Html\FormFacade::class);
		$loader->alias('Html', \Collective\Html\HtmlFacade::class);
		$loader->alias('Image', \Intervention\Image\Facades\Image::class);
	}
	
	/**
	 * Perform post-registration booting of services.
	 *
	 * @return void
	 */
	public function boot()
	{
		// LOAD THE VIEWS
		// First the published/overwritten views (in case they have any changes)
		$this->loadViewsFrom(resource_path('views/vendor/admin'), 'admin');
		// ... Then the stock views that come with the package, in case a published view might be missing
		$this->loadViewsFrom(realpath(__DIR__ . '/resources/views'), 'admin');
		
		// LOAD THE LANGUAGES FILES
		$this->loadTranslationsFrom(realpath(__DIR__ . '/resources/lang'), 'admin');
		
		// Use the vendor configuration file as fallback
		$this->mergeConfigFrom(__DIR__ . '/config/admin.php', 'admin');
		
		$this->registerAdminMiddleware($this->app->router);
		$this->setupRoutes($this->app->router);
		$this->publishFiles();
	}
	
	public function registerAdminMiddleware(Router $router)
	{
		Route::aliasMiddleware('admin', \App\Http\Middleware\Admin::class);
		Route::aliasMiddleware('clearance', \App\Http\Middleware\Clearance::class);
		Route::aliasMiddleware('banned.user', \App\Http\Middleware\BannedUser::class);
		Route::aliasMiddleware('install.checker', \App\Http\Middleware\InstallationChecker::class);
	}
	
	public function publishFiles()
	{
		// Publish lang files
		$this->publishes([__DIR__ . '/resources/lang' => resource_path('lang/vendor/admin')], 'lang');
		
		// Publish views
		$this->publishes([__DIR__ . '/resources/views' => resource_path('views/vendor/admin')], 'views');
		
		// Publish error views
		$this->publishes([__DIR__ . '/resources/error_views' => resource_path('views/errors')], 'errors');
		
		// Publish config file
		$this->publishes([__DIR__ . '/config' => config_path()], 'config');
		
		// Publish public AdminLTE assets
		$this->publishes([base_path('vendor/almasaeed2010/adminlte') => public_path('vendor/adminlte')], 'adminlte');
		
		// Publish public CRUD assets
		$this->publishes([__DIR__ . '/public' => public_path('vendor/admin')], 'public');
	}
	
	/**
	 * Define the routes for the application.
	 *
	 * @param Router $router
	 */
	public function setupRoutes(Router $router)
	{
		/*
		// Admin Interface Routes
		$router->group(['namespace' => 'App\Http\Controllers\Admin'], function ($router) {
			Route::group([
				'middleware' => ['admin', 'banned.user', 'install.checker'],
				'prefix'     => config('larapen.admin.route_prefix', 'admin'),
			], function () {
				// Language
				// Route::get('languages/texts/{lang?}/{file?}', 'LanguageController@showTexts');
				// Route::post('languages/texts/{lang}/{file}', 'LanguageController@updateTexts');
			});
		});
		*/
	}
}

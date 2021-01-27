<?php

namespace Larapen\Feed;

use Illuminate\Support\Facades\View;
use Larapen\Feed\Http\FeedController;
use Spatie\Feed\Helpers\Path;

class FeedServiceProvider extends \Spatie\Feed\FeedServiceProvider
{
	public function register()
	{
		$this->mergeConfigFrom(__DIR__ . '/../../../../vendor/spatie/laravel-feed/config/feed.php', 'feed');
		
		$this->registerRouteMacro();
	}
	
	public function boot()
	{
		/*
		 * Cmd:
		 * php artisan vendor:publish --provider="Larapen\Feed\FeedServiceProvider" --tag="config"
		 */
		$this->publishes([
			__DIR__ . '/../../../../vendor/spatie/laravel-feed/config/feed.php' => config_path('feed.php'),
		], 'config');
		
		$this->loadViewsFrom(__DIR__ . '/../../../../vendor/spatie/laravel-feed/resources/views', 'feed');
		
		/*
		 * Cmd:
		 * php artisan vendor:publish --provider="Larapen\Feed\FeedServiceProvider" --tag="views"
		 */
		$this->publishes([
			__DIR__ . '/../../../../vendor/spatie/laravel-feed/resources/views' => resource_path('views/vendor/feed'),
		], 'views');
		
		$this->registerLinksComposer();
	}
	
	protected function registerRouteMacro()
	{
		$router = $this->app['router'];
		
		$router->macro('feeds', function ($baseUrl = '') use ($router) {
			foreach (config('feed.feeds') as $name => $configuration) {
				$url = Path::merge($baseUrl, $configuration['url']);
				
				$router->get($url, '\\' . FeedController::class)->name("feeds.{$name}");
			}
		});
	}
	
	public function registerLinksComposer()
	{
		View::composer('feed::links', function ($view) {
			$view->with('feeds', $this->feeds());
		});
	}
	
	protected function feeds()
	{
		return collect(config('feed.feeds'));
	}
}

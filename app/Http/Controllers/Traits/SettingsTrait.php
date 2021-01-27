<?php
/**
 * JobClass - Job Board Web Application
 * Copyright (c) BedigitCom. All Rights Reserved
 *
 * Website: http://www.bedigit.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from Codecanyon,
 * Please read the full License from here - http://codecanyon.net/licenses/standard
 */

namespace App\Http\Controllers\Traits;

use App\Models\Page;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Cache;
use ChrisKonnertz\OpenGraph\OpenGraph;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use App\Helpers\Localization\Country as CountryLocalization;
use App\Helpers\Localization\Helpers\Country as CountryLocalizationHelper;
use Jenssegers\Date\Date;
use Torann\LaravelMetaTags\Facades\MetaTag;

trait SettingsTrait
{
	public $cookieExpiration;
	public $cacheExpiration = 3600; // In minutes (e.g. 60 * 60 for 1h)
	
	public $countries = null;
	
	public $paymentMethods;
	public $countPaymentMethods = 0;
	
	public $og;
	
	/**
	 * Set all the front-end settings
	 */
	public function applyFrontSettings()
	{
		// Cache Expiration Time
		$this->cacheExpiration = (int)config('settings.optimization.cache_expiration');
		view()->share('cacheExpiration', $this->cacheExpiration);
		
		// Ads photos number
		$picturesLimit = (int)config('settings.single.pictures_limit', 5);
		if ($picturesLimit <= 0) {
			$picturesLimit = 1;
		}
		view()->share('picturesLimit', $picturesLimit);
		
		/*
		// Default language for Bots
		$crawler = new CrawlerDetect();
		if ($crawler->isCrawler()) {
			$lang = collect(config('country.lang'));
			if ($lang->has('abbr')) {
				config()->set('lang.abbr', $lang->get('abbr'));
				config()->set('lang.locale', $lang->get('locale'));
			}
			app()->setLocale(config('lang.abbr'));
		}
		*/
		
		// Set Date Locale
		appSetLocale(config('lang.locale', 'en_US'));
		
		// DNS Prefetch meta tags
		$dnsPrefetch = [
			str_replace(['http://', 'https://'], '', config('app.url')),
			'fonts.googleapis.com',
			'graph.facebook.com',
			'google.com',
			'apis.google.com',
			'ajax.googleapis.com',
			'www.google-analytics.com',
			'pagead2.googlesyndication.com',
			'gstatic.com',
			'cdn.api.twitter.com',
			'oss.maxcdn.com',
		];
		view()->share('dnsPrefetch', $dnsPrefetch);
		
		// SEO
		$title       = getMetaTag('title', 'home');
		$description = getMetaTag('description', 'home');
		$keywords    = getMetaTag('keywords', 'home');
		
		// Meta Tags
		MetaTag::set('title', $title);
		MetaTag::set('description', strip_tags($description));
		MetaTag::set('keywords', $keywords);
		
		// Open Graph
		$this->og = new OpenGraph();
		$locale   = !empty(config('lang.locale')) ? config('lang.locale') : 'en_US';
		try {
			$this->og->siteName(config('settings.app.app_name'))->locale($locale)->type('website')->url(rawurldecode(url()->current()));
		} catch (\Exception $e) {};
		view()->share('og', $this->og);
		
		// CSRF Control
		// CSRF - Some JavaScript frameworks, like Angular, do this automatically for you.
		// It is unlikely that you will need to use this value manually.
		$domain = (getSubDomainName() != '') ? getSubDomainName() . '.' . getDomain() : getDomain();
		setcookie('X-XSRF-TOKEN', csrf_token(), $this->cookieExpiration, '/', $domain);
		
		// Skin selection
		config(['app.skin' => getFrontSkin(request()->input('skin'))]);
		
		// Reset session Post view counter
		if (!Str::contains(Route::currentRouteAction(), 'Post\DetailsController')) {
			if (session()->has('postIsVisited')) {
				session()->forget('postIsVisited');
			}
		}
		
		// Pages Menu
		$pages = Cache::remember('pages.' . config('app.locale') . '.menu', $this->cacheExpiration, function () {
			return Page::trans()->where('excluded_from_footer', '!=', 1)->orderBy('lft', 'ASC')->get();
		});
		view()->share('pages', $pages);
		
		// Get all Countries
		$countries = CountryLocalizationHelper::transAll(CountryLocalization::getCountries());
		// $maxRowsPerCol = round($countries->count() / 4, 0, PHP_ROUND_HALF_EVEN);
		$maxRowsPerCol = ceil($countries->count() / 4);
		$maxRowsPerCol = ($maxRowsPerCol > 0) ? $maxRowsPerCol : 1; // Fix array_chunk with 0
		view()->share('countryCols', $countries->chunk($maxRowsPerCol)->all());
		
		// Get Payment Methods
		$this->paymentMethods      = Cache::remember(config('country.code') . '.paymentMethods.all', $this->cacheExpiration, function () {
			return PaymentMethod::whereIn('name', array_keys((array)config('plugins.installed')))
				->where(function ($query) {
					$query->whereRaw('FIND_IN_SET("' . config('country.icode') . '", LOWER(countries)) > 0')
						->orWhereNull('countries');
				})->orderBy('lft')->get();
		});
		$this->countPaymentMethods = $this->paymentMethods->count();
		view()->share('paymentMethods', $this->paymentMethods);
		view()->share('countPaymentMethods', $this->countPaymentMethods);
	}
	
	/**
	 * Check & Add the missing entries in the /.env file
	 */
	public function checkDotEnvEntries()
	{
		$isChanged = false;
		
		// Check the App Config Locale
		if (!DotenvEditor::keyExists('APP_LOCALE')) {
			DotenvEditor::addEmpty();
			DotenvEditor::setKey('APP_LOCALE', config('appLang.abbr'));
			$isChanged = true;
		}
		
		// Check Purchase Code
		if (!DotenvEditor::keyExists('PURCHASE_CODE')) {
			if (!empty(config('settings.app.purchase_code'))) {
				DotenvEditor::addEmpty();
				DotenvEditor::setKey('PURCHASE_CODE', config('settings.app.purchase_code'));
				$isChanged = true;
			}
		}
		
		// MySQL Dump Binary Path
		if (!DotenvEditor::keyExists('DB_DUMP_BINARY_PATH')) {
			DotenvEditor::addEmpty();
			if (DotenvEditor::keyExists('DB_DUMP_COMMAND_PATH')) {
				DotenvEditor::setKey('DB_DUMP_BINARY_PATH', env('DB_DUMP_COMMAND_PATH', ''));
				DotenvEditor::deleteKey('DB_DUMP_COMMAND_PATH');
			} else {
				DotenvEditor::setKey('DB_DUMP_BINARY_PATH', '');
			}
			$isChanged = true;
		}
		
		if ($isChanged) {
			DotenvEditor::save();
		}
	}
}

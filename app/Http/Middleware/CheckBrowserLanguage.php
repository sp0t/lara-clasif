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

namespace App\Http\Middleware;

use App\Helpers\Localization\Language;
use App\Models\Language as LanguageModel;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

class CheckBrowserLanguage
{
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		// Bots and Other Exceptions
		$crawler = new CrawlerDetect();
		if (
			$crawler->isCrawler()
			|| isFromAdminPanel()
			|| Str::contains(Route::currentRouteAction(), 'InstallController')
			|| Str::contains(Route::currentRouteAction(), 'UpgradeController')
		) {
			return $next($request);
		}
		
		// Detect the user's browser language (If the option is activated in the system)
		if (config('settings.app.auto_detect_language') == '1') {
			$cacheExpiration = config('settings.optimization.cache_expiration');
			
			if (!session()->has('browserLangCode')) {
				
				// Get the user's browser language
				$acceptLanguage    = request()->server('HTTP_ACCEPT_LANGUAGE');
				$acceptLanguageTab = explode(',', $acceptLanguage);
				$langTab           = [];
				if (!empty($acceptLanguageTab)) {
					foreach ($acceptLanguageTab as $key => $value) {
						$tmp = explode(';', $value);
						if (empty($tmp)) continue;
						
						if (isset($tmp[0]) && isset($tmp[1])) {
							$q               = str_replace('q=', '', $tmp[1]);
							$langTab[$value] = ['code' => $tmp[0], 'q' => (double)$q];
						} else {
							$langTab[$value] = ['code' => $tmp[0], 'q' => 1];
						}
					}
				}
				
				// Get the user's country info (by the user's IP address) \w the country's language
				try {
					$country = Language::getCountryFromIP();
				} catch (\Exception $e) {
					$country = collect([]);
				}
				
				// Search the default language (Intersection Browser & User's Country language OR First Browser language)
				// Prevent to always select "en" or "en-US" as First Browser Language Code
				$browserLangCode = '';
				if (!empty($langTab)) {
					foreach ($langTab as $key => $value) {
						if (!$country->isEmpty() && $country->has('lang')) {
							if (!$country->get('lang')->isEmpty() && $country->get('lang')->has('abbr')) {
								if ($value['code'] == $country->get('lang')->get('abbr')) {
									$browserLangCode = $value['code'];
									break;
								}
								if (Str::contains($value['code'], $country->get('lang')->get('abbr'))) {
									$browserLangCode = substr($value['code'], 0, 2);
									break;
								}
							}
						} else {
							if ($browserLangCode == '') {
								$browserLangCode = substr($value['code'], 0, 2);
							}
						}
					}
				}
				
				// Check if the language is available in the system
				if ($browserLangCode != '') {
					// Get the Language details
					if (
						!$country->isEmpty()
						&& $country->has('lang')
						&& $country->get('lang')->has('abbr')
						&& $country->get('lang')->get('abbr') == $browserLangCode
					) {
						$isAvailableLang = $country->get('lang');
					} else {
						try {
							$isAvailableLang = Cache::remember('language.' . $browserLangCode, $cacheExpiration, function () use ($browserLangCode) {
								return LanguageModel::where('abbr', $browserLangCode)->first();
							});
						} catch (\Exception $e) {
							$isAvailableLang = [];
						}
						$isAvailableLang = collect($isAvailableLang);
					}
					
					if (!$isAvailableLang->isEmpty()) {
						// If language found and is available in the system,
						// And if the browser language redirection has not been done yet,
						// Save it in session or in cookie and select it by making a redirect to the homepage (with the language code)
						if ($isAvailableLang->has('abbr')) {
							if (!request()->filled('bl')) {
								session()->put('browserLangCode', $isAvailableLang->get('abbr'));
								
								// If the browser language is different to the system language,
								// Make a redirection for language auto-selection
								if ($browserLangCode != config('app.locale')) {
									$currentUrl  = getCurrentUrlByLanguage($isAvailableLang->get('abbr'));
									$queryString = (request()->getQueryString()) ? '&bl=1' : '?bl=1';
									headerLocation($currentUrl . $queryString, 302);
									exit();
								}
							}
						}
					}
				}
				
			}
		}
		
		return $next($request);
	}
}

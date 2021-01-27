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

use Closure;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

class CheckCountryLanguage
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
		// Send the HTTP request and Get the response
		$response = $next($request);
		
		// Bots and Other Exceptions
		$crawler = new CrawlerDetect();
		if (
			$crawler->isCrawler()
			|| isFromAdminPanel()
			|| Str::contains(Route::currentRouteAction(), 'InstallController')
			|| Str::contains(Route::currentRouteAction(), 'UpgradeController')
		) {
			return $response;
		}
		
		// Get the user's country info (by the user's IP address) \w the country's language
		$country = config('country');
		if (empty($country)) {
			return $response;
		}
		
		// Detect the user's browser language (If the option is activated in the system)
		if (config('settings.app.auto_detect_language') == '2') {
			// Check if the language is available in the system
			if (is_array($country) && isset($country['lang']) && isset($country['code'])) {
				// Get session name
				$sessionName = strtolower($country['code']) . 'CountryLangCode';
				
				// If language found and is available in the system,
				// And if the country language redirection has not been done yet,
				// Save it in session or in cookie and select it by making a redirect to the homepage (with the language code)
				if (is_array($country['lang']) && isset($country['lang']['abbr']) && !empty($country['lang']['abbr'])) {
					$countryLangCode = $country['lang']['abbr'];
					if (!session()->has($sessionName)) {
						if (!request()->filled('cl')) {
							session()->put($sessionName, $countryLangCode);
							
							// If the country language is different to the system language,
							// Make a redirection for language auto-selection
							if ($countryLangCode != config('app.locale')) {
								$currentUrl  = getCurrentUrlByLanguage($countryLangCode);
								$queryString = (request()->getQueryString()) ? '&cl=1' : '?cl=1';
								headerLocation($currentUrl . $queryString, 302);
								exit();
							}
						}
					}
				}
			}
		}
		
		return $response;
	}
}

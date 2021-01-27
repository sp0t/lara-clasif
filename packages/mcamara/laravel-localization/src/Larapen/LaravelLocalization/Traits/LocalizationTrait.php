<?php
/**
 * LaraClassified - Classified Ads Web Application
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

namespace Larapen\LaravelLocalization;

use App\Models\Category;
use App\Models\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

trait LocalizationTrait
{
	/**
	 * Get URL through the current Controller
	 *
	 * @param null $locale
	 * @param array $attributes
	 * @return null|string
	 */
	public function getUrlThroughCurrentController($locale = null, $attributes = [])
	{
		$url = null;
		
		if (empty($locale)) {
			$locale = $this->getCurrentLocale();
		}
		
		// Get the Query String
		$queryString = (request()->all() ? ('?' . httpBuildQuery(request()->all())) : '');
		
		// Get the Country Code
		$countryCode = $this->getCountryCode($attributes);
		
		// Get the Locale Path
		$localePath = $this->getLocalePath($locale);
		
		// Search: Category
		if (Str::contains(Route::currentRouteAction(), 'Search\CategoryController@index')) {
			// Get category or sub-category translation
			if (isset($attributes['catSlug'])) {
				// Get Category
				$cat = self::getCategoryBySlug($attributes['catSlug'], $locale);
				if (!empty($cat)) {
					$routePath = '';
					if (isset($attributes['subCatSlug']) && !empty($attributes['subCatSlug'])) {
						$subCat = self::getSubCategoryBySlug($cat->tid, $attributes['subCatSlug'], $locale);
						if (!empty($subCat)) {
							// Get the Route Path
							$routePath = trans('routes.v-search-subCat', [
								'countryCode' => $countryCode,
								'catSlug'     => $cat->slug,
								'subCatSlug'  => $subCat->slug,
							], $locale);
						}
					} else {
						// Get the Route Path
						$routePath = trans('routes.v-search-cat', [
							'countryCode' => $countryCode,
							'catSlug'     => $cat->slug,
						], $locale);
					}
					
					$url = app('url')->to($localePath . $routePath) . $queryString;
				}
			}
		} // Search: Location - Laravel Routing doesn't support PHP rawurlencode() function
		else if (Str::contains(Route::currentRouteAction(), 'Search\CityController@index')) {
			// Get the Route Path
			if (isset($attributes['city'])) {
				$routePath = trans('routes.v-search-city', [
					'countryCode' => $countryCode,
					'city'        => $attributes['city'],
					'id'          => $attributes['id'],
				], $locale);
				
				$url = app('url')->to($localePath . $routePath) . $queryString;
			}
		} // Search: User
		else if (Str::contains(Route::currentRouteAction(), 'Search\UserController@index')) {
			// Get the Route Path
			if (isset($attributes['id'])) {
				$routePath = trans('routes.v-search-user', [
					'countryCode' => $countryCode,
					'id'          => $attributes['id'],
				], $locale);
				
				$url = app('url')->to($localePath . $routePath) . $queryString;
			}
			if (isset($attributes['username'])) {
				$routePath = trans('routes.v-search-username', [
					'countryCode' => $countryCode,
					'username'    => $attributes['username'],
				], $locale);
				
				$url = app('url')->to($localePath . $routePath) . $queryString;
			}
		} // Search: Tag
		else if (Str::contains(Route::currentRouteAction(), 'Search\TagController@index')) {
			// Get the Route Path
			if (isset($attributes['tag'])) {
				$routePath = trans('routes.v-search-tag', [
					'countryCode' => $countryCode,
					'tag'          => $attributes['tag'],
				], $locale);
				
				$url = app('url')->to($localePath . $routePath) . $queryString;
			}
		} // Search: Company
		else if (Str::contains(Route::currentRouteAction(), 'Search\CompanyController@profile')) {
			// Get the Route Path
			if (isset($attributes['id'])) {
				$routePath = trans('routes.v-search-company', [
					'countryCode' => $countryCode,
					'id'          => $attributes['id'],
				], $locale);
				
				$url = app('url')->to($localePath . $routePath) . $queryString;
			}
		} // Search: Company (Static)
		else if (Str::contains(Route::currentRouteAction(), 'Search\CompanyController@index')) {
			// Get the Route Path
			$routePath = trans('routes.v-companies-list', [
				'countryCode' => $countryCode,
			], $locale);
			
			$url = app('url')->to($localePath . $routePath) . $queryString;
		} // Pages
		else if (Str::contains(Route::currentRouteAction(), 'PageController@index')) {
			if (isset($attributes['slug'])) {
				$page = self::getPageBySlug($attributes['slug'], $locale);
				if (!empty($page)) {
					// Get the Route Path
					$routePath = trans('routes.v-page', ['slug' => $page->slug], $locale);
					$url = app('url')->to($localePath . $routePath) . $queryString;
				}
			}
		} // Search: Index
		else if (Str::contains(Route::currentRouteAction(), 'Search\SearchController@index')) {
			// Get the Route Path
			$routePath = trans('routes.v-search', ['countryCode' => $countryCode], $locale);
			
			$url = app('url')->to($localePath . $routePath) . $queryString;
		} else {
			$url = null;
			
			if (!currentLocaleShouldBeHiddenInUrl($locale)) {
				// request()->route() return null on 404 page
				$requestRoute = request()->route();
				if (!is_null($requestRoute)) {
					$pattern = '#/' . $requestRoute->getPrefix() . '#ui';
					// $routePath = preg_replace($pattern, '', request()->getRequestUri(), 1);
					$routePath = preg_replace($pattern, '', request()->getPathInfo(), 1);
					$routePath = ltrim($routePath, '/');
					$url = app('url')->to($localePath . $routePath) . $queryString;
				}
			}
		}
		
		return $url;
	}
	
	
	/**
	 * Get URL through entered Route (Or through entered URL)
	 *
	 * @param null $locale
	 * @param null $url
	 * @param array $attributes
	 * @return mixed|null|string
	 */
	public function getUrlThroughEnteredRoute($locale = null, $url = null, $attributes = [])
	{
		if (empty($locale)) {
			$locale = $this->getCurrentLocale();
		}
		
		// Don't capture RAW urls
		if (Str::contains($url, '{')) {
			return $url;
		}
		
		// Get the Query String
		$queryString = '';
		$parts = mb_parse_url($url);
		if (isset($parts['query'])) {
			$queryString = '?' . (is_array($parts['query']) || is_object($parts['query'])) ? httpBuildQuery($parts['query']) : $parts['query'];
		}
		
		// Get the Country Code
		$countryCode = $this->getCountryCode($attributes);
		
		// Get the Locale Path
		$localePath = $this->getLocalePath($locale);
		
		// Work with URL Path (without URL Protocol & Host)
		$url = $this->getUrlPath($url, $locale);
		
		// Search: Category
		if (
			Str::contains($url, trans('routes.t-search-cat', [], $locale))
			&& isset($attributes['catSlug'])
		) {
			$cat = self::getCategoryBySlug($attributes['catSlug'], $locale);
			if (!empty($cat)) {
				$routePath = '';
				if (isset($attributes['subCatSlug']) && !empty($attributes['subCatSlug'])) {
					$subCat = self::getSubCategoryBySlug($cat->tid, $attributes['subCatSlug'], $locale);
					if (!empty($subCat)) {
						$routePath = trans('routes.v-search-subCat', [
								'countryCode' => $countryCode,
								'catSlug'     => $cat->slug,
								'subCatSlug'  => $subCat->slug,
							], $locale);
					}
				} else {
					$routePath = trans('routes.v-search-cat', [
						'countryCode' => $countryCode,
						'catSlug'     => $cat->slug,
					], $locale);
				}
				
				$url = app('url')->to($localePath . $routePath) . $queryString;
			}
		} // Search: Location - Laravel Routing don't support PHP rawurlencode() function
		else if (
			Str::contains($url, trans('routes.t-search-city', [], $locale))
			&& isset($attributes['city'])
			&& isset($attributes['id'])
		) {
			$routePath = trans('routes.v-search-city', [
				'countryCode' => $countryCode,
				'city'        => $attributes['city'],
				'id'          => $attributes['id'],
			], $locale);
			
			$url = app('url')->to($localePath . $routePath) . $queryString;
		} // Search: User (by ID)
		else if (
			Str::contains($url, trans('routes.t-search-user', [], $locale))
			&& isset($attributes['id'])
			&& isset($attributes['username'])
		) {
			$routePath = trans('routes.v-search-user', [
				'countryCode' => $countryCode,
				'id'          => $attributes['id'],
			], $locale);
			
			$url = app('url')->to($localePath . $routePath) . $queryString;
		} // Search: User (by Username)
		else if (
			Str::contains($url, trans('routes.t-search-username', [], $locale))
			&& isset($attributes['id'])
			&& isset($attributes['username'])
		) {
			$routePath = trans('routes.v-search-username', [
				'countryCode' => $countryCode,
				'username'    => $attributes['username'],
			], $locale);
			
			$url = app('url')->to($localePath . $routePath) . $queryString;
		} // Search: Company
		else if (
			Str::contains($url, trans('routes.t-search-company', [], $locale))
			&& isset($attributes['id'])
		) {
			$routePath = trans('routes.v-search-company', [
				'countryCode' => $countryCode,
				'id'          => $attributes['id'],
			], $locale);
			
			$url = app('url')->to($localePath . $routePath) . $queryString;
		} // Pages
		else if (
			Str::contains($url, trans('routes.page', [], $locale))
			&& isset($attributes['slug'])
		) {
			$page = self::getPageBySlug($attributes['slug'], $locale);
			if (!empty($page)) {
				$routePath = trans('routes.v-page', ['slug' => $page->slug], $locale);
				
				$url = app('url')->to($localePath . $routePath) . $queryString;
			}
			
		} // Search: Index
		else if (
			Str::contains($url, trans('routes.t-search', [], $locale))
			&& !Str::contains($url, trans('routes.t-search-cat', [], $locale))
			&& !preg_match('/.*' . trans('routes.t-search', [], $locale) . '.+/ui', $url)
			&& !preg_match('/.+' . trans('routes.t-search', [], $locale) . '.*/ui', $url)
		) {
			$routePath = trans('routes.v-search', ['countryCode' => $countryCode], $locale);
			
			$url = app('url')->to($localePath . $routePath) . $queryString;
		} else {
			$url = '###' . $url . '###';
		}
		
		return $url;
	}
	
	/**
	 * Get the Locale Path (i.e. Language Path)
	 *
	 * @param null $locale
	 * @return string
	 */
	public function getLocalePath($locale = null)
	{
		if (empty($locale)) {
			$locale = $this->getCurrentLocale();
		}
		
		$path = '';
		if (!currentLocaleShouldBeHiddenInUrl($locale)) {
			$path = $locale . '/';
		}
		
		return $path;
	}
	
	/**
	 * Get the URL Path (without URL Protocol & Host)
	 *
	 * @param $url
	 * @param null $locale
	 * @return mixed
	 */
	public function getUrlPath($url, $locale = null)
	{
		// Get Locale path
		$localePath = $this->getLocalePath($locale);
		
		if (Str::contains($url, 'http://') || Str::contains($url, 'https://')) {
			$basePath = '/' . $localePath;
			$baseUrl = url('/') . preg_replace('#/+#ui', '/', $basePath);
			$url = str_replace($baseUrl, '', $url);
		}
		
		return $url;
	}
	
	/**
	 * Get the Country Code
	 *
	 * @param array $attributes
	 * @return mixed|null|string
	 */
	public function getCountryCode($attributes = [])
	{
		$countryCode = null;
		
		// Get the default Country
		// NOTE: The current method is generally called from views links, so all the settings are already set.
		$countryCode = strtolower(config('country.code'));
		
		// Get the Country
		if (empty($countryCode)) {
			if (isset($attributes['countryCode']) && !empty($attributes['countryCode'])) {
				$countryCode = $attributes['countryCode'];
			}
		}
		if (empty($countryCode)) {
			if (request()->filled('d')) {
				$countryCode = strtolower(request()->input('d'));
			}
		}
		
		return $countryCode;
	}
	
	/**
	 * Get Category by Slug
	 *
	 * @param $slug
	 * @param $locale
	 * @return null
	 */
	public static function getCategoryBySlug($slug, $locale)
	{
		$cat = null;
		
		if ($slug == '' || $locale == '') {
			return $cat;
		}
		
		$cacheId = 'getCategoryBySlug.' . config('app.locale') . '.parent.' . $slug;
		$tmpCat = Cache::remember($cacheId, self::$cacheExpiration, function () use ($slug) {
			return Category::transIn(config('app.locale'))->where('parent_id', 0)->where('slug', '=', $slug)->first();
		});
		if (!empty($tmpCat)) {
			$cacheId = 'getCategoryBySlug.' . $locale . '.' . $tmpCat->tid;
			$cat = Cache::remember($cacheId, self::$cacheExpiration, function () use ($tmpCat, $locale) {
				return Category::findTrans($tmpCat->tid, $locale);
			});
		}
		
		return $cat;
	}
	
	/**
	 * Get Sub-category by the Category translated's ID and by Sub-category's Slug
	 *
	 * @param $parentTid
	 * @param $slug
	 * @param $locale
	 * @return null
	 */
	public static function getSubCategoryBySlug($parentTid, $slug, $locale)
	{
		$subCat = null;
		
		if ($slug == '' || $locale == '') {
			return $subCat;
		}
		
		$cacheId = 'getSubCategoryBySlug.' . config('app.locale') . '.parent.' . $parentTid . '.' . $slug;
		$tmpSubCat = Cache::remember($cacheId, self::$cacheExpiration, function () use ($parentTid, $slug) {
			return Category::transIn(config('app.locale'))->where('parent_id', $parentTid)->where('slug', '=', $slug)->first();
		});
		if (!empty($tmpSubCat)) {
			$cacheId = 'getSubCategoryBySlug.' . $locale . '.' . $tmpSubCat->tid;
			$subCat = Cache::remember($cacheId, self::$cacheExpiration, function () use ($tmpSubCat, $locale) {
				return Category::findTrans($tmpSubCat->tid, $locale);
			});
		}
		
		return $subCat;
	}
	
	/**
	 * Get Page by Slug
	 *
	 * @param $slug
	 * @param $locale
	 * @return null
	 */
	public static function getPageBySlug($slug, $locale)
	{
		$page = null;
		
		if ($slug == '' || $locale == '') {
			return $page;
		}
		
		$cacheId = 'getPageBySlug.' . config('app.locale') . '.' . $slug;
		$tmpPage = Cache::remember($cacheId, self::$cacheExpiration, function () use ($slug) {
			return Page::transIn(config('app.locale'))->where('slug', '=', $slug)->first();
		});
		if (!empty($tmpPage)) {
			$cacheId = 'getPageBySlug.' . $locale . '.' . $tmpPage->tid;
			$page = Cache::remember($cacheId, self::$cacheExpiration, function () use ($tmpPage, $locale) {
				return Page::findTrans($tmpPage->tid, $locale);
			});
		}
		
		return $page;
	}
	
	/**
	 * Don't translate these path or folders
	 *
	 * @return bool
	 */
	public function exceptRedirectionPath()
	{
		// Use url() for this paths
		if (in_array(request()->segment(1), [
			'_debugbar',
			'assets',
			'css',
			'js',
			'pic',
			'ajax',
			'api',
			'script',
			'tools',
			'images',
			admin_uri(),
			'api',
		])) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Sub-folder support
	 *
	 * @param $parsedUrl
	 * @param $url
	 * @return \Illuminate\Contracts\Routing\UrlGenerator|mixed|null|string|string[]
	 */
	public function extendedUnparseUrl($parsedUrl, $url)
	{
		if (isset($parsedUrl['path'])) {
			$homeUrlParsed = parse_url(url('/'));
			if (isset($homeUrlParsed['path'])) {
				$homeUrlParsed['path'] = ltrim($homeUrlParsed['path'], '/');
				if (!isset($parsedUrl['scheme'])) {
					if ($homeUrlParsed['path'] != $parsedUrl['path']) {
						$url = str_replace($homeUrlParsed['path'], '', $url);
						$url = preg_replace('#/+#', '/', $url);
						$url = url($url);
					}
				}
			}
		}
		
		return $url;
	}
}

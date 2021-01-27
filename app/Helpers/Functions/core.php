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

/**
 * Check if a Model has translation fields
 *
 * @param $model
 * @return bool
 */
function isTranlatableModel($model)
{
	$isTranslatable = false;
	
	try {
		if (!($model instanceof \Illuminate\Database\Eloquent\Model)) {
			return $isTranslatable;
		}
		
		if (
			property_exists($model, 'translatable')
			&& (
				isset($model->translatable)
				&& is_array($model->translatable)
				&& !empty($model->translatable)
			)
		) {
			$isTranslatable = true;
		}
	} catch (\Exception $e) {
		return false;
	}
	
	return $isTranslatable;
}

/**
 * Set Locale Information (Override the PHP setlocale() function)
 *
 * IMPORTANT: Prevent issue in Laravel Blade
 * The Blade @if(...) statement doesn't convert to <?php if(...): ?> in Turkish language (for example).
 *
 * RESOURCES
 * - https://www.php.net/manual/fr/function.setlocale.php
 * - https://stackoverflow.com/questions/43589501/if-statements-not-working-correctly-on-laravel-blade
 * - https://docs.moodle.org/dev/Table_of_locales
 * - https://stackoverflow.com/questions/3191664/list-of-all-locales-and-their-short-codes
 * - https://github.com/umpirsky/locale-list
 * - Get locales list in Terminal on MAC/Linux: locale -a
 *
 * @param $locale
 * @return string
 */
function appSetLocale($locale)
{
	$localeFound = false;
	
	// Get available locales from the server
	try {
		exec('locale -a', $locales);
	} catch (\Exception $e) {}
	if (!isset($locales) || empty($locales)) {
		$locales = array_keys((array)config('locales'));
	}
	
	if (is_array($locales) && in_array($locale, $locales)) {
		if (setlocale(LC_ALL, $locale)) {
			\Jenssegers\Date\Date::setLocale($locale);
			setlocale(LC_ALL, $locale);
			$localeFound = true;
		}
	}
	
	if (!$localeFound) {
		$locale = 'en_US';
		\Jenssegers\Date\Date::setLocale($locale);
		setlocale(LC_ALL, $locale);
	}
	
	return $locale;
}

/**
 * Hide part of email addresses
 *
 * @param $value
 * @param int $escapedChars
 * @return string
 */
function hidePartOfEmail($value, $escapedChars = 1)
{
	$atPos = mb_stripos($value, '@');
	if ($atPos === false) {
		return $value;
	}
	
	$emailUsername = mb_substr($value, 0, $atPos);
	$emailDomain = mb_substr($value, ($atPos + 1));
	
	if (!empty($emailUsername) && !empty($emailDomain)) {
		$value = hidePartOfString($emailUsername, $escapedChars) . '@' . $emailDomain;
	}
	
	return $value;
}

/**
 * Hide a part of a string
 *
 * @param $value
 * @param int $escapedChars
 * @param string $replacement
 * @return string
 */
function hidePartOfString($value, $escapedChars = 1, $replacement = 'x')
{
	$escapedChars = (int)$escapedChars;
	
	$valueParts = explode(' ', $value);
	if (!empty($valueParts)) {
		$value = '';
		foreach ($valueParts as $valuePart) {
			if ($escapedChars <= 0) {
				$valuePart = str_pad('', mb_strlen($valuePart), $replacement);
			} else {
				$hiddenSubString = str_pad('', mb_strlen($valuePart) - ($escapedChars * 2), $replacement);
				$valuePart = mb_substr($valuePart, 0, $escapedChars) . $hiddenSubString . mb_substr($valuePart, -$escapedChars);
			}
			$value .= (empty($value)) ? $valuePart : ' ' . $valuePart;
		}
	}
	
	return $value;
}

/**
 * Default translator (e.g. en/global.php)
 *
 * @param $string
 * @param array $replace
 * @param string $file
 * @param null $locale
 * @return array|\Illuminate\Contracts\Translation\Translator|string|null
 */
function t($string, $replace = [], $file = 'global', $locale = null)
{
	if (is_null($locale)) {
		$locale = config('app.locale');
	}
	
	return trans($file . '.' . $string, $replace, $locale);
}

/**
 * Get default max file upload size (from PHP.ini)
 *
 * @return mixed
 */
function maxUploadSize()
{
	$maxUpload = (int)(ini_get('upload_max_filesize'));
	$maxPost = (int)(ini_get('post_max_size'));
	
	return min($maxUpload, $maxPost);
}

/**
 * Get max file upload size
 *
 * @return int|mixed
 */
function maxApplyFileUploadSize()
{
	$size = maxUploadSize();
	if ($size >= 5) {
		return 5;
	}
	
	return $size;
}

/**
 * Escape JSON string
 *
 * Escape this:
 * \b  Backspace (ascii code 08)
 * \f  Form feed (ascii code 0C)
 * \n  New line
 * \r  Carriage return
 * \t  Tab
 * \"  Double quote
 * \\  Backslash caracter
 *
 * @param $value
 * @return mixed
 */
function escapeJsonString($value)
{
	// list from www.json.org: (\b backspace, \f formfeed)
	$escapers = ["\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c"];
	$replacements = ["\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b"];
	$value = str_replace($escapers, $replacements, trim($value));
	
	return $value;
}

/**
 * @param bool $canGetLocalIp
 * @param string $defaultIp
 * @return string
 */
function getIp($canGetLocalIp = true, $defaultIp = '')
{
	return \App\Helpers\Ip::get($canGetLocalIp, $defaultIp);
}

/**
 * @return string
 */
function getScheme()
{
	if (isset($_SERVER['HTTPS']) and in_array($_SERVER['HTTPS'], ['on', 1])) {
		$protocol = 'https://';
	} else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) and $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
		$protocol = 'https://';
	} else if (stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true) {
		$protocol = 'https://';
	} else {
		$protocol = 'http://';
	}
	
	return $protocol;
}


/**
 * Get host (domain with sub-domain)
 *
 * @param null $url
 * @return array|mixed|string
 */
function getHost($url = null)
{
	if (!empty($url)) {
		$host = parse_url($url, PHP_URL_HOST);
	} else {
		$host = (trim(request()->server('HTTP_HOST')) != '') ? request()->server('HTTP_HOST') : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
	}
	
	if ($host == '') {
		$host = parse_url(url()->current(), PHP_URL_HOST);
	}
	
	return $host;
}

/**
 * Get domain (host without sub-domain)
 *
 * @param null $url
 * @return string
 */
function getDomain($url = null)
{
	if (!empty($url)) {
		$host = parse_url($url, PHP_URL_HOST);
	} else {
		$host = getHost();
	}
	
	$tmp = explode('.', $host);
	if (count($tmp) > 2) {
		$itemsToKeep = count($tmp) - 2;
		$tlds = config('tlds');
		if (isset($tmp[$itemsToKeep]) && isset($tlds[$tmp[$itemsToKeep]])) {
			$itemsToKeep = $itemsToKeep - 1;
		}
		for ($i = 0; $i < $itemsToKeep; $i++) {
			\Illuminate\Support\Arr::forget($tmp, $i);
		}
		$domain = implode('.', $tmp);
	} else {
		$domain = @implode('.', $tmp);
	}
	
	return $domain;
}

/**
 * Get sub-domain name
 *
 * @return string
 */
function getSubDomainName()
{
	$host = getHost();
	
	return (substr_count($host, '.') > 1) ? trim(current(explode('.', $host))) : '';
}

/**
 * Generate a querystring url for the application.
 *
 * Assumes that you want a URL with a querystring rather than route params
 * (which is what the default url() helper does)
 *
 * @param null $path
 * @param array $inputArray
 * @param null $secure
 * @param bool $localized
 * @return mixed|string
 */
function qsurl($path = null, $inputArray = [], $secure = null, $localized = true)
{
	if ($localized) {
		if (preg_match('#^http(s)?://#', $path)) {
			$path = mb_parse_url($path, PHP_URL_PATH);
		}
		$url = lurl($path);
	} else {
		if (currentLocaleShouldBeHiddenInUrl(config('app.locale'))) {
			if (!preg_match('#^http(s)?://#', $path)) {
				$localePath = config('app.locale') . '/';
				if (\Illuminate\Support\Str::startsWith($path, $localePath)) {
					$path = mb_substr($path, strlen($localePath));
				}
			}
		}
		
		$url = app('url')->to($path, $secure);
	}
	
	if (config('plugins.domainmapping.installed')) {
		if (isset($inputArray['d'])) {
			unset($inputArray['d']);
		}
		$inputArray = array_filter($inputArray, function($v, $k) {
			if (in_array($k, ['distance'])) {
				return !empty($v) || $v == 0;
			} else {
				return !empty($v);
			}
		}, ARRAY_FILTER_USE_BOTH);
	}
	
	if (!empty($inputArray)) {
		$url = $url . '?' . httpBuildQuery($inputArray);
	}
	
	return $url;
}

/**
 * @param $array
 * @return mixed|string
 */
function httpBuildQuery($array)
{
	if (!is_array($array) && !is_object($array)) {
		return $array;
	}
	
	$queryString = http_build_query($array);
	$queryString = str_replace(['%5B', '%5D'], ['[', ']'], $queryString);
	
	return $queryString;
}

/**
 * Localized URL
 *
 * @param null $path
 * @param array $attributes
 * @param null $locale
 * @return false|\Illuminate\Contracts\Routing\UrlGenerator|null|string
 */
function lurl($path = null, $attributes = [], $locale = null)
{
	if (empty($locale)) {
		$locale = config('app.locale');
	}
	
	if (request()->segment(1) == admin_uri()) {
		return url($locale . '/' . $path);
	}
	
	return \Larapen\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL($locale, $path, $attributes);
}

/**
 * Get URL related to the given country (or country code)
 *
 * @param $country
 * @param string $path
 * @param bool $forceCountry
 * @param bool $forceLocale
 * @return \Illuminate\Contracts\Routing\UrlGenerator|string
 */
function localUrl($country, $path = '/', $forceCountry = false, $forceLocale = true)
{
	if (empty($path)) {
		$path = '/';
	}
	
	// If given country value is a string & having 2 characters (like country code),
	// Get the country collection by the country code.
	if (is_string($country)) {
		if (strlen($country) == 2) {
			$country = \App\Helpers\Localization\Country::getCountryInfo($country);
			if ($country->isEmpty() || !$country->has('code')) {
				return url($path);
			}
		} else {
			return url($path);
		}
	}
	
	// Country collection is required to continue
	if (!($country instanceof \Illuminate\Support\Collection)) {
		return url($path);
	}
	
	// Country collection code is required to continue
	if (!$country->has('code')) {
		return url($path);
	}
	
	// Clear the path
	$path = ltrim($path, '/');
	
	// Get the country main language path
	$langPath = '';
	if ($forceLocale) {
		if ($country->has('lang') && $country->get('lang')->has('abbr')) {
			$langPath = '/' . $country->get('lang')->get('abbr');
		} else {
			if ($country->has('languages')) {
				$countryLang = \App\Helpers\Localization\Country::getLangFromCountry($country->get('languages'));
				if ($countryLang->has('abbr')) {
					$langPath = '/' . $countryLang->get('abbr');
				}
			} else {
				// From XML Sitemaps
				if ($country->has('locale')) {
					$langPath = '/' . $country->get('locale');
				}
			}
		}
	}
	if (!isFromUrlAlwaysContainingCountryCode($path)) {
		if (currentLocaleShouldBeHiddenInUrl(trim($langPath, '/'))) {
			$langPath = '';
		}
	}
	
	// Get the country domain data from the Domain Mapping plugin,
	// And get a new URL related to domain, country language & given path
	$domain = collect((array)config('domains'))->firstWhere('country_code', $country->get('code'));
	if (isset($domain['url']) && !empty($domain['url'])) {
		$path = preg_replace('#' . $country->get('code') . '/#ui', '', $path, 1);
		
		$url = rtrim($domain['url'], '/') . $langPath;
		$url = $url . ((!empty($path)) ? '/' . $path : '');
	} else {
		$url = rtrim(env('APP_URL', ''), '/') . $langPath;
		$url = $url . ((!empty($path)) ? '/' . $path : '');
		if ($forceCountry) {
			$url = $url . ('?d=' . $country->get('code'));
		}
	}
	
	return $url;
}

/**
 * If the Domain Mapping plugin is installed, apply its configs.
 * NOTE: Don't apply them if the session is shared.
 *
 * @param $countryCode
 */
function applyDomainMappingConfig($countryCode)
{
	if (empty($countryCode)) {
		return;
	}
	
	if (config('plugins.domainmapping.installed')) {
		/*
		 * When the session is shared, the domains name and logo columns are disabled.
		 * The dashboard per country feature is also disabled.
		 * So, it is recommended to access to the Admin panel through the main URL from the /.env file (i.e. APP_URL/admin)
		 */
		if (!config('settings.domain_mapping.share_session')) {
			$domain = collect((array)config('domains'))->firstWhere('country_code', $countryCode);
			if (!empty($domain)) {
				if (isset($domain['url']) && !empty($domain['url'])) {
					//\URL::forceRootUrl($domain['url']);
				}
			}
		}
	}
}

/**
 * Check if user is using the API
 *
 * @return bool
 */
function isFromApi()
{
	$isFromApi = false;
	if (
		request()->segment(1) == 'api'
		&& \Illuminate\Support\Str::contains(\Route::currentRouteAction(), plugin_namespace('apijc'))
	) {
		// Check the API Plugin
		$isFromApi = config('plugins.apijc.installed');
	}
	
	return $isFromApi;
}

/**
 * Check if user is located in the Admin panel
 * NOTE: Please see the provider of the package: lab404/laravel-impersonate
 *
 * @param null $url
 * @return bool
 */
function isFromAdminPanel($url = null)
{
	if (empty($url)) {
		$isValid = (
			request()->segment(1) == admin_uri()
			|| request()->segment(1) == 'impersonate'
			|| \Illuminate\Support\Str::contains(\Route::currentRouteAction(), '\Admin\\')
		);
	} else {
		try {
			$urlPath = '/' . ltrim(parse_url($url, PHP_URL_PATH), '/');
			$adminUri = '/' . ltrim(admin_uri(), '/');
			
			$isValid = (
				\Illuminate\Support\Str::startsWith($urlPath, $adminUri)
				|| \Illuminate\Support\Str::startsWith($urlPath, '/impersonate')
			);
		} catch (\Exception $e) {
			$isValid = false;
		}
	}
	
	return $isValid;
}

/**
 * Check the demo website domain
 *
 * @param null $url
 * @return bool
 */
function isDemoDomain($url = null)
{
	return getDomain($url) == config('larapen.core.demo.domain') || in_array(getHost($url), (array)config('larapen.core.demo.hosts'));
}

/**
 * @param null $url
 * @return bool
 */
function isDemo($url = null)
{
	if (isDemoDomain()) {
		if (
			\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Post\DetailsController@sendMessage')
			|| \Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Post\ReportController@sendReport')
			|| \Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'PageController@contactPost')
		) {
			return true;
		}
		if (auth()->check()) {
			if (isFromAdminPanel($url)) {
				if (auth()->user()->can(\App\Models\Permission::getStaffPermissions()) && auth()->user()->id == 1) {
					return false;
				}
				
				return true;
			} else {
				if (!in_array(auth()->user()->id, [2, 3, 4])) {
					return false;
				}
				if (
					!\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Controllers\HomeController')
					&& !\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Post\CreateOrEdit\MultiSteps\CreateController')
					&& !\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Post\CreateOrEdit\MultiSteps\EditController')
					&& !\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Post\CreateOrEdit\MultiSteps\PhotoController')
					&& !\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Post\CreateOrEdit\MultiSteps\PaymentController')
					&& !\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Post\CreateOrEdit\SingleStep\CreateController')
					&& !\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Post\CreateOrEdit\SingleStep\EditController')
					&& !\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Post\DetailsController')
					&& !\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Post\ReportController')
					&& !\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Ajax\PostController')
					&& !\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Account\CompanyController')
					&& !\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Account\ConversationsController')
					&& !\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Account\ResumeController')
					&& !\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Auth\LoginController')
				) {
					return true;
				}
			}
		}
	}
	
	return false;
}

/**
 * File Size Format
 *
 * @param $bytes
 * @return string
 */
function fileSizeFormat($bytes)
{
	if ($bytes >= 1073741824) {
		$bytes = number_format($bytes / 1073741824, 2) . ' GB';
	} elseif ($bytes >= 1048576) {
		$bytes = number_format($bytes / 1048576, 2) . ' MB';
	} elseif ($bytes >= 1024) {
		$bytes = number_format($bytes / 1024, 2) . ' KB';
	} elseif ($bytes > 1) {
		$bytes = $bytes . ' bytes';
	} elseif ($bytes == 1) {
		$bytes = $bytes . ' byte';
	} else {
		$bytes = '0 bytes';
	}
	
	return $bytes;
}

/**
 * Check if the current Locale should be hidden in the URL
 *
 * @param null $locale
 * @return bool
 */
function currentLocaleShouldBeHiddenInUrl($locale = null)
{
	if (empty($locale)) {
		$locale = config('app.locale');
	}
	
	if (config('laravellocalization.hideDefaultLocaleInURL') == true && $locale == config('appLang.abbr')) {
		return true;
	}
	
	return false;
}

/**
 * @param $index
 * @param null $default
 * @return mixed
 */
function getSegment($index, $default = null)
{
	$index = (int)$index;
	
	// Default checking
	$segment = request()->segment($index, $default);
	
	// Checking with Default Language parameters
	if (!isFromUrlAlwaysContainingCountryCode()) {
		if (!currentLocaleShouldBeHiddenInUrl()) {
			$segment = request()->segment(($index + 1), $default);
		}
	}
	
	return $segment;
}

/**
 * Get the Country Code from URI Path
 *
 * @return bool
 */
function getCountryCodeFromPath()
{
	$countryCode = null;
	
	// With these URLs, the language code and the country code can be available in the segments
	// (If the "Multi-countries URLs Optimization" is enabled)
	if (isFromUrlThatCanContainCountryCode()) {
		$countryCode = getSegment(1);
	}
	
	// With these URLs, the language code and the country code are available in the segments
	if (isFromUrlAlwaysContainingCountryCode()) {
		$countryCode = getSegment(2);
	}
	
	return $countryCode;
}

/**
 * Check if user is coming from a URL that can contain the country code
 * With these URLs, the language code and the country code can be available in the segments
 * (If the "Multi-countries URLs Optimization" is enabled)
 *
 * @return bool
 */
function isFromUrlThatCanContainCountryCode()
{
	if (config('settings.seo.multi_countries_urls')) {
		if (
			\Illuminate\Support\Str::contains(\Illuminate\Support\Facades\Route::currentRouteAction(), 'SearchController')
			|| \Illuminate\Support\Str::contains(\Illuminate\Support\Facades\Route::currentRouteAction(), 'CategoryController')
			|| \Illuminate\Support\Str::contains(\Illuminate\Support\Facades\Route::currentRouteAction(), 'CityController')
			|| \Illuminate\Support\Str::contains(\Illuminate\Support\Facades\Route::currentRouteAction(), 'UserController')
			|| \Illuminate\Support\Str::contains(\Illuminate\Support\Facades\Route::currentRouteAction(), 'TagController')
			|| \Illuminate\Support\Str::contains(\Illuminate\Support\Facades\Route::currentRouteAction(), 'CompanyController')
			|| \Illuminate\Support\Str::contains(\Illuminate\Support\Facades\Route::currentRouteAction(), 'SitemapController')
		) {
			return true;
		}
	}
	
	return false;
}

/**
 * Check if called page can always have the country code
 * With these URLs, the language code and the country code are available in the segments
 *
 * @param null $url
 * @return bool
 */
function isFromUrlAlwaysContainingCountryCode($url = null)
{
	if (empty($url)) {
		$isValid = (
			\Illuminate\Support\Str::endsWith(request()->url(), '.xml')
			|| \Illuminate\Support\Str::contains(\Illuminate\Support\Facades\Route::currentRouteAction(), 'SitemapsController')
		);
	} else {
		$isValid = (\Illuminate\Support\Str::endsWith($url, '.xml'));
	}
	
	return $isValid;
}

/**
 * Get the current URL by language code
 *
 * @param $countryLangCode
 * @return \Illuminate\Contracts\Routing\UrlGenerator|string
 */
function getCurrentUrlByLanguage($countryLangCode)
{
	$currentUrl = url($countryLangCode);
	
	foreach(\Larapen\LaravelLocalization\Facades\LaravelLocalization::getSupportedLocales() as $localeCode => $properties) {
		if (strtolower($localeCode) == strtolower($countryLangCode)) {
			$currentUrl = \Larapen\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL($localeCode);
			break;
		}
	}
	
	return $currentUrl;
}

/**
 * Check if file is uploaded
 *
 * @param $value
 * @return bool
 */
function fileIsUploaded($value)
{
	$isUploaded = false;
	
	if (
		(is_string($value) && \Illuminate\Support\Str::startsWith($value, 'data:image'))
		|| ($value instanceof \Illuminate\Http\UploadedFile)
	) {
		$isUploaded = true;
	}
	
	return $isUploaded;
}

/**
 * Get the uploaded file extension
 *
 * @param $value
 * @return mixed|null|string
 */
function getUploadedFileExtension($value)
{
	$extension = null;
	
	if (!is_string($value)) {
		if ($value instanceof \Illuminate\Http\UploadedFile) {
			$extension = $value->getClientOriginalExtension();
		}
	} else {
		if (\Illuminate\Support\Str::startsWith($value, 'data:image')) {
			$matches = [];
			preg_match('#data:image/([^;]+);base64#', $value, $matches);
			$extension = (isset($matches[1]) && !empty($matches[1])) ? $matches[1] : 'png';
		} else {
			$extension = getExtension($value);
		}
	}
	
	return strtolower($extension);
}

/**
 * Get file extension
 *
 * @param $filename
 * @return mixed
 */
function getExtension($filename)
{
	$tmp = explode('?', $filename);
	$tmp = explode('.', current($tmp));
	$ext = end($tmp);
	
	return $ext;
}

/**
 * Transform Description column before displaying it
 *
 * @param $string
 * @return mixed|string
 */
function transformDescription($string)
{
	if (config('settings.single.simditor_wysiwyg')) {
		
		try {
			$string = \Mews\Purifier\Facades\Purifier::clean($string);
		} catch (\Exception $e) {
			// Nothing.
		}
		$string = createAutoLink($string);
		
	} else {
		$string = nl2br(createAutoLink(strCleaner($string)));
	}
	
	return $string;
}

/**
 * String strip
 *
 * @param $string
 * @return string
 */
function str_strip($string)
{
	$string = trim(preg_replace('/\s\s+/u', ' ', $string));
	
	return $string;
}

/**
 * String cleaner
 *
 * @param $string
 * @return mixed|string
 */
function strCleaner($string)
{
	$string = strip_tags($string, '<br><br/>');
	$string = str_replace(['<br>', '<br/>', '<br />'], "\n", $string);
	$string = preg_replace("/[\r\n]+/", "\n", $string);
	/*
	Remove 4(+)-byte characters from a UTF-8 string
	It seems like MySQL does not support characters with more than 3 bytes in its default UTF-8 charset.
	NOTE: you should not just strip, but replace with replacement character U+FFFD to avoid unicode attacks, mostly XSS:
	http://unicode.org/reports/tr36/#Deletion_of_Noncharacters
	*/
	$string = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $string);
	$string = mb_ucfirst(trim($string));
	
	return $string;
}

/**
 * String cleaner (Lite version)
 *
 * @param $string
 * @return mixed|string
 */
function strCleanerLite($string)
{
	$string = strip_tags($string);
	$string = html_entity_decode($string);
	$string = strip_tags($string);
	$string = preg_replace('/[\'"]*(<|>)[\'"]*/us', '', $string);
	$string = trim($string);
	
	/*
	Remove non-breaking spaces
	In HTML, the common non-breaking space, which is the same width as the ordinary space character, is encoded as &nbsp; or &#160;.
	In Unicode, it is encoded as U+00A0.
	https://en.wikipedia.org/wiki/Non-breaking_space
	https://graphemica.com/00A0
	*/
	$string = preg_replace('~\x{00a0}~siu', '', $string);
	
	return $string;
}

/**
 * Title cleaner
 *
 * @param $string
 * @return mixed|string|string[]|null
 * @todo: Code not tested. Test it!
 */
function titleCleaner($string)
{
	$string = strip_tags($string);
	$string = html_entity_decode($string);
	$string = str_replace('º', '', $string);
	$string = str_replace('ª', '', $string);
	
	/*
	Match a single character not present in the list below
	[^\p{L}\p{M}\p{Z}\p{N}\p{Sc}\%\'\"!?¿¡-]
	\p{L} matches any kind of letter from any language
	\p{M} matches a character intended to be combined with another character (e.g. accents, umlauts, enclosing boxes, etc.)
	\p{Z} matches any kind of whitespace or invisible separator
	\p{N} matches any kind of numeric character in any script
	\p{Sc} matches any currency sign
	\% matches the character % literally (case sensitive)
	\' matches the character ' literally (case sensitive)
	\" matches the character " literally (case sensitive)
	!?¿¡- matches a single character in the list !?¿¡- (case sensitive)
	
	Global pattern flags
	g modifier: global. All matches (don't return after first match)
	m modifier: multi line. Causes ^ and $ to match the begin/end of each line (not only begin/end of string)
	*/
	$string = preg_replace('/[^\p{L}\p{M}\p{Z}\p{N}\p{Sc}\%\'\"\!\?¿¡\-]/u', ' ', $string);
	
	$string = preg_replace('/[\'"]*(<|>)[\'"]*/us', '', $string);
	$string = str_replace(' ', ' ', $string); // do NOT remove, first is NOT blank space.
	$string = str_replace('️', ' ', $string); // do NOT remove, there is a ghost.
	$string = preg_replace('/-{2,}/', '-', $string);
	$string = preg_replace('/"{2,}/', '"', $string);
	$string = preg_replace("/'{2,}/", "'", $string);
	$string = preg_replace('/!{2,}/', '!', $string);
	$string = preg_replace("/[\?]+/", "?", $string);
	$string = preg_replace("/[%]+/", "%", $string);
	$string = str_replace('- -', ' - ', $string);
	$string = str_replace('! !', ' ! ', $string);
	$string = str_replace('? ?', ' ? ', $string);
	$string = rtrim($string, '-');
	$string = ltrim($string, '-');
	$string = trim(preg_replace('/\s+/', ' ', $string)); // strip blank spaces, tabs
	$string = trim($string);
	
	/*
	Remove non-breaking spaces
	In HTML, the common non-breaking space, which is the same width as the ordinary space character, is encoded as &nbsp; or &#160;.
	In Unicode, it is encoded as U+00A0.
	https://en.wikipedia.org/wiki/Non-breaking_space
	https://graphemica.com/00A0
	*/
	$string = preg_replace('~\x{00a0}~siu', '', $string);
	
	return $string;
}

/**
 * Prevent problem with the #hastags when they are only numeric
 *
 * @param $string
 * @return string|null
 */
function tagCleaner($string)
{
	$tags = [];
	
	$limit = (int)config('settings.single.tags_limit', 15);
	
	$i = 0;
	$tmpTab = preg_split('#[:,;\s]+#ui', $string);
	foreach ($tmpTab as $tag) {
		// Remove all tags (simultaneously) staring and ending by a number
		$tag = preg_replace('/\b\d+\b/ui', '', $tag);
		$tag = mb_strtolower(trim($tag));
		if ($tag != '') {
			if (mb_strlen($tag) > 1) {
				if ($i <= $limit) {
					$tags[] = $tag;
				}
				$i++;
			}
		}
	}
	
	return !empty($tags) ? substr(implode(',', $tags), 0, 255) : null;
}

/**
 * Only numeric string cleaner
 *
 * @param $string
 * @return null
 */
function onlyNumCleaner($string)
{
	$tmpString = preg_replace('/\d/u', '', strip_tags($string));
	if ($tmpString == '') {
		$string = null;
	}
	
	return $string;
}

/**
 * Extract emails from string, and get the first
 *
 * @param $string
 * @return string
 */
function extractEmailAddress($string)
{
	$tmp = [];
	preg_match_all('/([a-z0-9\-\._%\+]+@[a-z0-9\-\.]+\.[a-z]{2,4}\b)/i', $string, $tmp);
	$emails = (isset($tmp[1])) ? $tmp[1] : [];
	$email = head($emails);
	if ($email == '') {
		$tmp = [];
		preg_match('/[a-z0-9\-_]+(\.[a-z0-9\-_]+)*@[a-z0-9\-]+(\.[a-z0-9\-]+)*(\.[a-z]{2,3})/i', $string, $tmp);
		$email = (isset($tmp[0])) ? trim($tmp[0]) : '';
		if ($email == '') {
			$tmp = [];
			preg_match('/[a-z0-9\-\._%\+]+@[a-z0-9\-\.]+\.[a-z]{2,4}\b/i', $string, $tmp);
			$email = (isset($tmp[0])) ? trim($tmp[0]) : '';
		}
	}
	
	return strtolower($email);
}

/**
 * Check if language code is available
 *
 * @param $abbr
 * @return bool
 */
function isAvailableLang($abbr)
{
	$cacheExpiration = (int)config('settings.optimization.cache_expiration', 86400);
	$lang = \Cache::remember('language.' . $abbr, $cacheExpiration, function () use ($abbr) {
		$lang = \App\Models\Language::where('abbr', $abbr)->first();
		
		return $lang;
	});
	
	if (!empty($lang)) {
		return true;
	} else {
		return false;
	}
}

function getHostByUrl($url)
{
	// in case scheme relative URI is passed, e.g., //www.google.com/
	$url = trim($url, '/');
	
	// If scheme not included, prepend it
	if (!preg_match('#^http(s)?://#', $url)) {
		$url = 'http://' . $url;
	}
	
	$urlParts = parse_url($url);
	
	// remove www
	$domain = preg_replace('/^www\./', '', $urlParts['host']);
	
	return $domain;
}

/**
 * Add rel=”nofollow” to links
 *
 * @param $html
 * @param null $skip
 * @return mixed
 */
function noFollow($html, $skip = null)
{
	return preg_replace_callback(
		"#(<a[^>]+?)>#is", function ($mach) use ($skip) {
		return (
			!($skip && strpos($mach[1], $skip) !== false) &&
			strpos($mach[1], 'rel=') === false
		) ? $mach[1] . ' rel="nofollow">' : $mach[0];
	},
		$html
	);
}

/**
 * Create auto-links for URLs in string
 *
 * @param $str
 * @param array $attributes
 * @return mixed|string
 */
function createAutoLink($str, $attributes = [])
{
	// Transform URL to HTML link
	$attrs = '';
	foreach ($attributes as $attribute => $value) {
		$attrs .= " {$attribute}=\"{$value}\"";
	}
	
	$str = ' ' . $str;
	$str = preg_replace('`([^"=\'>])((http|https|ftp)://[^\s<]+[^\s<\.)])`i', '$1<a rel="nofollow" href="$2"' . $attrs . ' target="_blank">$2</a>', $str);
	$str = substr($str, 1);
	
	// Add rel=”nofollow” to links
	$parse = parse_url('http://' . $_SERVER['HTTP_HOST']);
	$str = noFollow($str, $parse['host']);
	
	// Find and attach target="_blank" to all href links from text
	$str = openLinksInNewWindow($str);
	
	return $str;
}

/**
 * Find and attach target="_blank" to all href links from text
 *
 * @param $content
 * @return mixed
 */
function openLinksInNewWindow($content)
{
	// Find all links
	preg_match_all('/<a ((?!target)[^>])+?>/ui', $content, $hrefMatches);
	
	// Loop only first array to modify links
	if (is_array($hrefMatches) && isset($hrefMatches[0])) {
		foreach ($hrefMatches[0] as $key => $value) {
			// Take orig link
			$origLink = $value;
			
			// Does it have target="_blank"
			if (!preg_match('/target="_blank"/ui', $origLink)) {
				// Add target = "_blank"
				$newLink = preg_replace("/<a(.*?)>/ui", "<a$1 target=\"_blank\">", $origLink);
				
				// Replace old link in content with new link
				$content = str_replace($origLink, $newLink, $content);
			}
		}
	}
	
	return $content;
}

/**
 * Add target=_blank to outside links
 *
 * @param $content
 * @return null|string|string[]
 */
function openOutsideLinksInNewWindow($content)
{
	// Remove existing "target" attribute
	$content = preg_replace('# target=[\'"]?[^\'"]*[\'"]?#ui', '', $content);
	
	// Add target=_blank to outside links
	$pattern = '#(<a\\b[^<>]*href=[\'"]?http[^<>]+)>#ui';
	$replace = '$1 target="_blank">';
	$content = preg_replace($pattern, $replace, $content);
	
	return $content;
}

/**
 * Check tld is a valid tld
 *
 * @param $url
 * @return bool|int
 */
function checkTld($url)
{
	$parsed_url = parse_url($url);
	if ($parsed_url === false) {
		return false;
	}
	
	$tlds = config('tlds');
	$patten = implode('|', array_keys($tlds));
	
	return preg_match('/\.(' . $patten . ')$/i', $parsed_url['host']);
}

/**
 * Function to convert hex value to rgb array
 *
 * @param $colour
 * @return array|bool
 *
 * @todo: improve this function
 */
function hex2rgb($colour)
{
	if ($colour[0] == '#') {
		$colour = substr($colour, 1);
	}
	if (strlen($colour) == 6) {
		[$r, $g, $b] = [$colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5]];
	} elseif (strlen($colour) == 3) {
		[$r, $g, $b] = [$colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2]];
	} else {
		return false;
	}
	$r = hexdec($r);
	$g = hexdec($g);
	$b = hexdec($b);
	
	return ['r' => $r, 'g' => $g, 'b' => $b];
}

/**
 * Convert hexdec color string to rgb(a) string
 *
 * @param $color
 * @param bool $opacity
 * @return string
 *
 * @todo: improve this function
 */
function hex2rgba($color, $opacity = false)
{
	$default = 'rgb(0,0,0)';
	
	// Return default if no color provided
	if (empty($color)) {
		return $default;
	}
	
	// Sanitize $color if "#" is provided
	if ($color[0] == '#') {
		$color = substr($color, 1);
	}
	
	// Check if color has 6 or 3 characters and get values
	if (strlen($color) == 6) {
		$hex = [$color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]];
	} elseif (strlen($color) == 3) {
		$hex = [$color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]];
	} else {
		return $default;
	}
	
	// Convert hexadec to rgb
	$rgb = array_map('hexdec', $hex);
	
	// Check if opacity is set(rgba or rgb)
	if ($opacity) {
		if (abs($opacity) > 1) {
			$opacity = 1.0;
		}
		$output = 'rgba(' . implode(",", $rgb) . ',' . $opacity . ')';
	} else {
		$output = 'rgb(' . implode(",", $rgb) . ')';
	}
	
	// Return rgb(a) color string
	return $output;
}

/**
 * ucfirst() function for multibyte character encodings
 *
 * @param $string
 * @param string $encoding
 * @return string
 */
function mb_ucfirst($string, $encoding = 'utf-8')
{
	if (empty($string) || !is_string($string)) {
		return null;
	}
	
	$strLen = mb_strlen($string, $encoding);
	$firstChar = mb_substr($string, 0, 1, $encoding);
	$then = mb_substr($string, 1, $strLen - 1, $encoding);
	
	return mb_strtoupper($firstChar, $encoding) . $then;
}

/**
 * ucwords() function for multibyte character encodings
 *
 * @param $string
 * @param string $encoding
 * @return null|string
 */
function mb_ucwords($string, $encoding = 'utf-8')
{
	if (empty($string) || !is_string($string)) {
		return null;
	}
	
	$tab = [];
	
	// Split the phrase by any number of space characters, which include " ", \r, \t, \n and \f
	$words = preg_split('/[\s]+/ui', $string);
	if (!empty($words)) {
		foreach ($words as $key => $word) {
			$tab[$key] = mb_ucfirst($word, $encoding);
		}
	}
	
	$string = (!empty($tab)) ? implode(' ', $tab) : null;
	
	return $string;
}

/**
 * parse_url() function for multibyte character encodings
 *
 * @param $url
 * @param int $component
 * @return mixed
 */
function mb_parse_url($url, $component = -1)
{
	$encodedUrl = preg_replace_callback('%[^:/@?&=#]+%usD', function ($matches) {
		return urlencode($matches[0]);
	}, $url);
	
	$parts = parse_url($encodedUrl, $component);
	
	if ($parts === false) {
		throw new \InvalidArgumentException('Malformed URL: ' . $url);
	}
	
	if (is_array($parts) && count($parts) > 0) {
		foreach ($parts as $name => $value) {
			$parts[$name] = urldecode($value);
		}
	}
	
	return $parts;
}

/**
 * Friendly UTF-8 URL for all languages
 *
 * @param $string
 * @param string $separator
 * @return mixed|string
 */
function slugify($string, $separator = '-')
{
	// Remove accents using WordPress API method.
	$string = remove_accents($string);
	
	// Slug
	$string = mb_strtolower($string);
	$string = @trim($string);
	$replace = "/(\\s|\\" . $separator . ")+/mu";
	$subst = $separator;
	$string = preg_replace($replace, $subst, $string);
	
	// Remove unwanted punctuation, convert some to '-'
	$puncTable = [
		// remove
		"'"  => '',
		'"'  => '',
		'`'  => '',
		'='  => '',
		'+'  => '',
		'*'  => '',
		'&'  => '',
		'^'  => '',
		''   => '',
		'%'  => '',
		'$'  => '',
		'#'  => '',
		'@'  => '',
		'!'  => '',
		'<'  => '',
		'>'  => '',
		'?'  => '',
		// convert to minus
		'['  => '-',
		']'  => '-',
		'{'  => '-',
		'}'  => '-',
		'('  => '-',
		')'  => '-',
		' '  => '-',
		','  => '-',
		';'  => '-',
		':'  => '-',
		'/'  => '-',
		'|'  => '-',
		'\\' => '-',
	];
	$string = str_replace(array_keys($puncTable), array_values($puncTable), $string);
	
	// Clean up multiple '-' characters
	$string = preg_replace('/-{2,}/', '-', $string);
	
	// Remove trailing '-' character if string not just '-'
	if ($string != '-') {
		$string = rtrim($string, '-');
	}
	
	return $string;
}

/**
 * @return mixed|string
 */
function detectLocale()
{
	$lang = detectLanguage();
	
	return (isset($lang) and !$lang->isEmpty()) ? $lang->get('locale') : 'en_US';
}

/**
 * @return \Illuminate\Support\Collection|static
 */
function detectLanguage()
{
	$obj = new App\Helpers\Localization\Language();
	
	return $obj->find();
}

/**
 * Get file/folder permissions.
 *
 * @param $path
 * @return string
 */
function getPerms($path)
{
	return substr(sprintf('%o', fileperms($path)), -4);
}

/**
 * Get all countries from PHP array (umpirsky)
 *
 * @return array|null
 */
function getCountriesFromArray()
{
	$countries = new App\Helpers\Localization\Helpers\Country();
	$countries = $countries->all();
	
	if (empty($countries)) return null;
	
	$arr = [];
	foreach ($countries as $code => $value) {
		if (!file_exists(storage_path('database/geonames/countries/' . strtolower($code) . '.sql'))) {
			continue;
		}
		$row = ['value' => $code, 'text' => $value];
		$arr[] = $row;
	}
	
	return $arr;
}

/**
 * Get all countries from DB (Geonames) & Translate them
 *
 * @return array
 */
function getCountries()
{
	$arr = [];
	
	// Get installed countries list
	$countries = \App\Helpers\Localization\Country::getCountries();
	
	// Translate the countries list
	$countries = \App\Helpers\Localization\Helpers\Country::transAll($countries);
	
	// The countries list must be a Laravel Collection object
	if (!$countries instanceof \Illuminate\Support\Collection) {
		$countries = collect($countries);
	}
	
	if ($countries->count() > 0) {
		foreach ($countries as $code => $country) {
			// The country entry must be a Laravel Collection object
			if (!$country instanceof \Illuminate\Support\Collection) {
				$country = collect($country);
			}
			
			// Get the country data
			$code = ($country->has('code')) ? $country->get('code') : $code;
			$name = ($country->has('name')) ? $country->get('name') : '';
			$arr[$code] = $name;
		}
	}
	
	return $arr;
}

/**
 * Pluralization
 *
 * @param $number
 * @return int
 */
function getPlural($number)
{
	if (config('lang.russian_pluralization')) {
		// Russian pluralization rules
		$typeOfPlural = (($number % 10 == 1) && ($number % 100 != 11))
			? 0
			: ((($number % 10 >= 2)
				&& ($number % 10 <= 4)
				&& (($number % 100 < 10)
					|| ($number % 100 >= 20)))
				? 1
				: 2
			);
	} else {
		// No rule for other languages
		$typeOfPlural = $number;
	}
	
	return $typeOfPlural;
}

/**
 * Get URL of Page by page's type
 *
 * @param $type
 * @param null $locale
 * @return mixed|string
 */
function getUrlPageByType($type, $locale = null)
{
	if (is_null($locale)) {
		$locale = config('app.locale');
	}
	
	$cacheExpiration = (int)config('settings.optimization.cache_expiration', 86400);
	$cacheId = 'page.' . $locale . '.type.' . $type;
	$page = \Cache::remember($cacheId, $cacheExpiration, function () use ($type, $locale) {
		return \App\Models\Page::type($type)->transIn($locale)->first();
	});
	
	$linkTarget = '';
	$linkRel = '';
	if (!empty($page)) {
		if ($page->target_blank == 1) {
			$linkTarget = ' target="_blank"';
		}
		if (!empty($page->external_link)) {
			$linkRel = ' rel="nofollow"';
			$url = $page->external_link;
		} else {
			$url = \App\Helpers\UrlGen::page($page);
		}
	} else {
		$url = '#';
	}
	
	// Get attributes
	return 'href="' . $url . '"' . $linkRel . $linkTarget;
}

/**
 * @param string $uploadType
 * @param bool $jsFormat
 * @return array|mixed|string
 */
function getUploadFileTypes($uploadType = 'file', $jsFormat = false)
{
	if ($uploadType == 'image') {
		$types = config('settings.upload.image_types', 'jpg,jpeg,gif,png');
	} else {
		$types = config('settings.upload.file_types', 'pdf,doc,docx,word,rtf,rtx,ppt,pptx,odt,odp,wps,jpeg,jpg,bmp,png');
	}
	
	$separators = ['|', '-', ';', '.', '/', '_', ' '];
	$types = str_replace($separators, ',', $types);
	
	if ($jsFormat) {
		$types = explode(',', $types);
		$types = array_filter($types, function ($value) {
			return $value !== '';
		});
		$types = json_encode($types);
	}
	
	return $types;
}

/**
 * @param string $uploadType
 * @return array|mixed|string
 */
function showValidFileTypes($uploadType = 'file')
{
	$formats = getUploadFileTypes($uploadType);
	$formats = str_replace(',', ', ', $formats);
	
	return $formats;
}

/**
 * Json To Array
 * NOTE: Used for MySQL Json and Laravel array (casts) columns
 *
 * @param $string
 * @return array|mixed
 */
function jsonToArray($string)
{
	if (is_array($string)) {
		return $string;
	}
	
	if (is_object($string)) {
		return \App\Helpers\ArrayHelper::fromObject($string);
	}
	
	if (isValidJson($string)) {
		$array = json_decode($string, true);
	} else {
		$array = [];
	}
	
	return $array;
}

/**
 * Check if json string is valid
 *
 * @param $string
 * @return bool
 */
function isValidJson($string)
{
	try {
		json_decode($string);
	} catch (\Exception $e) {
		return false;
	}
	
	return (json_last_error() == JSON_ERROR_NONE);
}

/**
 * Check if exec() function is available
 *
 * @return boolean
 */
function exec_enabled()
{
	try {
		// make a small test
		exec("ls");
		
		return function_exists('exec') && !in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))));
	} catch (\Exception $ex) {
		return false;
	}
}

/**
 * Check if function is enabled
 *
 * @param $name
 * @return bool
 */
function func_enabled($name)
{
	try {
		$disabled = array_map('trim', explode(',', ini_get('disable_functions')));
		
		return !in_array($name, $disabled);
	} catch (\Exception $ex) {
		return false;
	}
}

/**
 * Run artisan config cache
 *
 * @return mixed
 */
function artisanConfigCache()
{
	// Artisan config:cache generate the following two files
	// Since config:cache runs in the background
	// to determine if it is done, we just check if the files modified time have been changed
	$files = ['bootstrap/cache/config.php', 'bootstrap/cache/services.php'];
	
	// get the last modified time of the files
	$last = 0;
	foreach ($files as $file) {
		$path = base_path($file);
		if (file_exists($path)) {
			if (filemtime($path) > $last) {
				$last = filemtime($path);
			}
		}
	}
	
	// Prepare to run (5 seconds for $timeout)
	$timeout = 5;
	$start = time();
	
	// Actually call the Artisan command
	$exitCode = \Artisan::call('config:cache');
	
	// Check if Artisan call is done
	while (true) {
		// Just finish if timeout
		if (time() - $start >= $timeout) {
			echo "Timeout\n";
			break;
		}
		
		// If any file is still missing, keep waiting
		// If any file is not updated, keep waiting
		// @todo: services.php file keeps unchanged after artisan config:cache
		foreach ($files as $file) {
			$path = base_path($file);
			if (!file_exists($path)) {
				sleep(1);
				continue;
			} else {
				if (filemtime($path) == $last) {
					sleep(1);
					continue;
				}
			}
		}
		
		// Just wait another extra 3 seconds before finishing
		sleep(3);
		break;
	}
	
	return $exitCode;
}

/**
 * Run artisan migrate
 *
 * @return mixed
 */
function artisanMigrate()
{
	return \Artisan::call('migrate', ["--force" => true]);
}

/**
 * Check if the PHP Exif component is enabled
 *
 * @return bool
 */
function exifExtIsEnabled()
{
	try {
		if (extension_loaded('exif') && function_exists('exif_read_data')) {
			return true;
		}
		
		return false;
	} catch (\Exception $e) {
		return false;
	}
}

/**
 * @param $filePath
 * @return \Illuminate\Contracts\Routing\UrlGenerator|string
 */
function fileUrl($filePath)
{
	// Storage Disk Init.
	$disk = \App\Helpers\Files\Storage\StorageDisk::getDisk();
	
	try {
		return $disk->url($filePath);
	} catch (\Exception $e) {
		return url('file?path=' . $filePath);
	}
}

/**
 * @param $filePath
 * @param string $type
 * @return mixed|string
 */
function imgUrl($filePath, $type = 'big')
{
	// Check if this is the default picture
	if (
		\Illuminate\Support\Str::contains($filePath, config('larapen.core.logo'))
		|| \Illuminate\Support\Str::contains($filePath, config('larapen.core.favicon'))
		|| \Illuminate\Support\Str::contains($filePath, config('larapen.core.picture.default'))
	) {
		return \Storage::disk(config('filesystems.default'))->url($filePath) . getPictureVersion();
	}
	
	// Storage Disk Init.
	$disk = \App\Helpers\Files\Storage\StorageDisk::getDisk();
	
	// Get pre-resized picture URL
	if (in_array($type, ['logo', 'favicon', 'cat', 'page', 'css'])) {
		try {
			return $disk->url($filePath) . getPictureVersion();
		} catch (\Exception $e) {
			return url('file?path=' . $filePath) . getPictureVersion(true);
		}
	}
	
	// Check, Create thumbnail and Get its URL
	return resize($disk, $filePath, $type);
}

/**
 * @param $disk
 * @param $filePath
 * @param string $type
 * @return string
 */
function resize($disk, $filePath, $type = 'big')
{
	// Image Quality
	$imageQuality = config('settings.upload.image_quality', 90);
	
	// Get Dimensions
	$width = (int)config('settings.upload.img_resize_' . $type . '_width', 816);
	$height = (int)config('settings.upload.img_resize_' . $type . '_height', 460);
	
	$filename = last(explode(DIRECTORY_SEPARATOR, $filePath));
	$fileDir = str_replace($filename, '', $filePath);
	$fileDir = rtrim($fileDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	
	// Thumb file name
	$sizeLabel = $width . 'x' . $height;
	$thumbFilename = 'thumb-' . $sizeLabel . '-' . $filename;
	$thumbFilePath = $fileDir . $thumbFilename;
	
	// Check if thumb image exists
	if ($disk->exists($thumbFilePath)) {
		// Get the image URL
		try {
			return $disk->url($thumbFilePath) . getPictureVersion();
		} catch (\Exception $e) {
			return url('file?path=' . $thumbFilePath) . getPictureVersion();
		}
	} else {
		// Create thumb image if it not exists
		try {
			// Get file extension
			$extension = (is_png($disk->get($filePath))) ? 'png' : 'jpg';
			
			// Make the image
			if (in_array($type, ['logo'])) {
				// Get image manipulation settings
				$ratio = config('settings.upload.img_resize_logo_ratio', '1');
				$upSize = config('settings.upload.img_resize_logo_upsize', '0');
				
				// Resize logo pictures
				$image = \Image::make($disk->get($filePath))->resize($width, $height, function ($constraint) use ($ratio, $upSize) {
					if ($ratio == '1') {
						$constraint->aspectRatio();
					}
					if ($upSize == '1') {
						$constraint->upsize();
					}
				})->encode($extension, $imageQuality);
			} else if (in_array($type, ['large', 'big', 'medium', 'small'])) {
				// Get image manipulation settings
				$resizeType = config('settings.upload.img_resize_' . $type . '_resize_type', '0');
				$ratio = config('settings.upload.img_resize_' . $type . '_ratio', '1');
				$upSize = config('settings.upload.img_resize_' . $type . '_upsize', '0');
				$position = config('settings.upload.img_resize_' . $type . '_position', 'center');
				$relative = config('settings.upload.img_resize_' . $type . '_relative', false);
				$bgColor = config('settings.upload.img_resize_' . $type . '_bg_color', 'ffffff');
				
				if ($resizeType == '0') {
					// Resize
					$image = \Image::make($disk->get($filePath))->resize($width, $height, function ($constraint) use ($ratio, $upSize) {
						if ($ratio == '1') {
							$constraint->aspectRatio();
						}
						if ($upSize == '1') {
							$constraint->upsize();
						}
					})->encode($extension, $imageQuality);
				} else if ($resizeType == '1') {
					// Fit
					$image = \Image::make($disk->get($filePath))->fit($width, $height, function ($constraint) use ($ratio, $upSize) {
						if ($ratio == '1') {
							$constraint->aspectRatio();
						}
						if ($upSize == '1') {
							$constraint->upsize();
						}
					})->encode($extension, $imageQuality);
				} else if ($resizeType == '2') {
					// ResizeCanvas
					$image = \Image::make($disk->get($filePath))->resize($width, $height, function ($constraint) use ($ratio, $upSize) {
						if ($ratio == '1') {
							$constraint->aspectRatio();
						}
						if ($upSize == '1') {
							$constraint->upsize();
						}
					})->resizeCanvas($width, $height, $position, $relative, $bgColor)
						->resize($width, $height)
						->encode($extension, $imageQuality);
				} else {
					// Resize (with hard parameters)
					$image = \Image::make($disk->get($filePath))->resize($width, $height, function ($constraint) {
						$constraint->aspectRatio();
					})->encode($extension, $imageQuality);
				}
			} else {
				// Resize (with hard parameters)
				$image = \Image::make($disk->get($filePath))->resize($width, $height, function ($constraint) {
					$constraint->aspectRatio();
				})->encode($extension, $imageQuality);
			}
		} catch (\Exception $e) {
			return \Storage::disk(config('filesystems.default'))->url($filePath) . getPictureVersion();
		}
		
		// Store the image on disk.
		$disk->put($thumbFilePath, $image->stream()->__toString());
		
		// Now delete temporary intervention image as we have moved it to Storage folder with Laravel filesystem.
		$image->destroy();
		
		// Get the image URL
		try {
			return $disk->url($thumbFilePath) . getPictureVersion();
		} catch (\Exception $e) {
			return url('file?path=' . $thumbFilePath) . getPictureVersion();
		}
	}
}

/**
 * Get pictures version
 *
 * @param bool $queryStringExists
 * @return string
 */
function getPictureVersion($queryStringExists = false)
{
	$pictureVersion = '';
	if (config('larapen.core.picture.versioned') && !empty(config('larapen.core.picture.version'))) {
		$pictureVersion .= ($queryStringExists) ? '&' : '?';
		$pictureVersion .= 'v=' . config('larapen.core.picture.version');
	}
	
	return $pictureVersion;
}

/**
 * @return int|string
 */
function vTime()
{
	$timeStamp = '?v=' . time();
	if (\App::environment(['staging', 'production'])) {
		$timeStamp = '';
	}
	
	return $timeStamp;
}

/**
 * Get image extension from base64 string
 *
 * @param $bufferImg
 * @param bool $recursive
 * @return bool
 */
function is_png($bufferImg, $recursive = true)
{
	$f = finfo_open();
	$result = finfo_buffer($f, $bufferImg, FILEINFO_MIME_TYPE);
	
	if (!\Illuminate\Support\Str::contains($result, 'image') && $recursive) {
		// Plain Text
		return \Illuminate\Support\Str::contains($bufferImg, 'image/png');
	}
	
	return $result == 'image/png';
}

/**
 * Get the login field
 *
 * @param $value
 * @return string
 */
function getLoginField($value)
{
	$field = 'username';
	if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
		$field = 'email';
	} else if (preg_match('/^((\+|00)\d{1,3})?[\s\d]+$/', $value)) {
		$field = 'phone';
	}
	
	return $field;
}

/**
 * Get Phone's National Format
 *
 * @param $phone
 * @param null $countryCode
 * @param int $format
 * @return \libphonenumber\PhoneNumberUtil|mixed|string
 */
function phoneFormat($phone, $countryCode = null, $format = \libphonenumber\PhoneNumberFormat::NATIONAL)
{
	// Country Exception
	if ($countryCode == 'UK') {
		$countryCode = 'GB';
	}
	
	// Set the phone format
	try {
		$phone = phone($phone, $countryCode, $format);
	} catch (\Exception $e) {
		// Keep the default value
	}
	
	// Keep only numeric characters
	$phone = preg_replace('/[^0-9\+]/', '', $phone);
	
	return $phone;
}

/**
 * Get Phone's International Format
 *
 * @param $phone
 * @param null $countryCode
 * @param int $format
 * @return \libphonenumber\PhoneNumberUtil|mixed|string
 */
function phoneFormatInt($phone, $countryCode = null, $format = \libphonenumber\PhoneNumberFormat::INTERNATIONAL)
{
	return phoneFormat($phone, $countryCode, $format);
}

/**
 * @param $phone
 * @param null $provider
 * @return mixed|string
 */
function setPhoneSign($phone, $provider = null)
{
	if ($provider == 'nexmo') {
		// Nexmo doesn't support the sign '+'
		if (\Illuminate\Support\Str::startsWith($phone, '+')) {
			$phone = str_replace('+', '', $phone);
		}
	}
	
	if ($provider == 'twilio') {
		// Twilio requires the sign '+'
		if (!\Illuminate\Support\Str::startsWith($phone, '+')) {
			$phone = '+' . $phone;
		}
	}
	
	return $phone;
}

/**
 * @param $countryCode
 * @return string
 */
function getPhoneIcon($countryCode)
{
	if (file_exists(public_path() . '/images/flags/16/' . strtolower($countryCode) . '.png')) {
		$phoneIcon = '<img src="' . url('images/flags/16/' . strtolower($countryCode) . '.png') . getPictureVersion() . '" style="padding: 2px;">';
	} else {
		$phoneIcon = '<i class="icon-phone-1"></i>';
	}
	
	return $phoneIcon;
}

/**
 * @param $field
 * @return bool
 */
function isEnabledField($field)
{
	// Front Register Form
	if ($field == 'phone') {
		return !config('larapen.core.disable.phone');
	} else if ($field == 'email') {
		return !config('larapen.core.disable.email') or
			(config('larapen.core.disable.email') and config('larapen.core.disable.phone'));
	} else if ($field == 'username') {
		return !config('larapen.core.disable.username');
	} else {
		return true;
	}
}

function getLoginLabel()
{
	if (isEnabledField('email') && isEnabledField('phone')) {
		$loginLabel = t('Email or Phone');
	} else {
		if (isEnabledField('phone')) {
			$loginLabel = t('Phone');
		} else {
			$loginLabel = t('Email address');
		}
	}
	
	return $loginLabel;
}

function getTokenLabel()
{
	if (isEnabledField('email') && isEnabledField('phone')) {
		$loginLabel = t('Code received by SMS or Email');
	} else {
		if (isEnabledField('phone')) {
			$loginLabel = t('Code received by SMS');
		} else {
			$loginLabel = t('Code received by Email');
		}
	}
	
	return $loginLabel;
}

function getTokenMessage()
{
	if (isEnabledField('email') && isEnabledField('phone')) {
		$loginLabel = t('Enter the code you received by SMS or Email in the field below');
	} else {
		if (isEnabledField('phone')) {
			$loginLabel = t('Enter the code you received by SMS in the field below');
		} else {
			$loginLabel = t('Enter the code you received by Email in the field below');
		}
	}
	
	return $loginLabel;
}

/**
 * Get meta tag from settings
 *
 * @param $tag
 * @param $page
 * @return null|string
 */
function getMetaTag($tag, $page)
{
	$out = null;
	
	// Check if the Domain Mapping plugin is available
	if (config('plugins.domainmapping.installed')) {
		$out = \App\Plugins\domainmapping\Domainmapping::getMetaTag($tag, $page);
		if (!empty($out)) {
			return $out;
		}
	}
	
	// Get the current Language
	$languageCode = config('lang.abbr');
	
	// Get the Page's MetaTag
	$metaTag = null;
	try {
		$cacheExpiration = (int)config('settings.optimization.cache_expiration', 86400);
		$cacheId = 'metaTag.' . $languageCode . '.' . $page;
		$metaTag = \Cache::remember($cacheId, $cacheExpiration, function () use ($languageCode, $page) {
			return \App\Models\MetaTag::transIn($languageCode)->where('page', $page)->first();
		});
	} catch (\Exception $e) {
	}
	
	if (!empty($metaTag)) {
		if (isset($metaTag->$tag) && !empty($metaTag->$tag)) {
			$out = $metaTag->$tag;
			$out = str_replace(['{app_name}', '{country}'], [config('app.name'), config('country.name')], $out);
			
			return $out;
		}
	}
	
	if (config('app.name') || config('settings.app.slogan')) {
		if (in_array($tag, ['title', 'description'])) {
			if (config('settings.app.slogan')) {
				$out = config('app.name') . ' - ' . config('settings.app.slogan');
			} else {
				$out = config('app.name') . ' - ' . config('country.name');
			}
		}
	}
	
	return $out;
}

/**
 * Redirect (Prevent Browser cache)
 *
 * @param $url
 * @param int $code (301 => Moved Permanently | 302 => Moved Temporarily)
 */
function headerLocation($url, $code = 301)
{
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header("Location: " . $url, true, $code);
	
	exit();
}

/**
 * Split a name into the first name and last name
 *
 * @param $input
 * @return array
 */
function splitName($input)
{
	$output = ['firstName' => '', 'lastName' => ''];
	$space = mb_strpos($input, ' ');
	if ($space !== false) {
		$output['firstName'] = mb_substr($input, 0, $space);
		$output['lastName'] = mb_substr($input, $space, strlen($input));
	} else {
		$output['lastName'] = $input;
	}
	
	return $output;
}

/**
 * Zero leading for numeric values
 *
 * @param $number
 * @param int $padLength
 * @return string
 */
function zeroLead($number, $padLength = 2)
{
	if (is_numeric($number)) {
		$number = str_pad($number, $padLength, '0', STR_PAD_LEFT);
	}
	
	return $number;
}

/**
 * Get the Distance Calculation Unit
 *
 * @param null $countryCode
 * @return string
 */
function getDistanceUnit($countryCode = null)
{
	if (empty($countryCode)) {
		$countryCode = config('country.code');
	}
	$unit = \Larapen\LaravelDistance\Helper::getDistanceUnit($countryCode);
	
	return t($unit);
}

/**
 * Check if the app's installation files exist
 *
 * @return bool
 */
function appInstallFilesExist()
{
	// Check if the '.env' and 'storage/installed' files exist
	if (file_exists(base_path('.env')) && file_exists(storage_path('installed'))) {
		return true;
	}
	
	return false;
}

/**
 * Check if the app is installed
 *
 * @return bool
 */
function appIsInstalled()
{
	// Check if the app's installation files exist
	if (!appInstallFilesExist()) {
		return false;
	}
	
	// Check Installation Setup
	$properly = true;
	try {
		// Check if all database tables exists
		$namespace = 'App\\Models\\';
		$modelsPath = app_path('Models');
		$modelFiles = array_filter(glob($modelsPath . DIRECTORY_SEPARATOR . '*.php'), 'is_file');
		
		if (count($modelFiles) > 0) {
			foreach ($modelFiles as $filePath) {
				$filename = last(explode(DIRECTORY_SEPARATOR, $filePath));
				$modelname = head(explode('.', $filename));
				
				if (
					!\Illuminate\Support\Str::contains(strtolower($filename), '.php')
					|| \Illuminate\Support\Str::contains(strtolower($modelname), 'base')
				) {
					continue;
				}
				
				eval('$model = new ' . $namespace . $modelname . '();');
				if (!\Illuminate\Support\Facades\Schema::hasTable($model->getTable())) {
					$properly = false;
				}
			}
		}
		
		// Check Settings table
		if (\App\Models\Setting::count() <= 0) {
			$properly = false;
		}
		// Check TimeZone table
		if (\App\Models\TimeZone::count() <= 0) {
			$properly = false;
		}
	} catch (\PDOException $e) {
		if (preg_match('/.*(max_user_connections|max_connections|Too many connections).*/ui', $e->getMessage())) {
			dd($e->getMessage());
		}
		$properly = false;
	} catch (\Exception $e) {
		$properly = false;
	}
	
	return $properly;
}

/**
 * Check if an update is available
 *
 * @return bool
 */
function updateIsAvailable()
{
	// Check if the '.env' file exists
	if (!file_exists(base_path('.env'))) {
		return false;
	}
	
	$updateIsAvailable = false;
	
	// Get eventual new version value & the current (installed) version value
	$lastVersion = getLatestVersion();
	$currentVersion = getCurrentVersion();
	
	// Check the update
	if (version_compare($lastVersion, $currentVersion, '>')) {
		$updateIsAvailable = true;
	}
	
	return $updateIsAvailable;
}

/**
 * Get the script possible URL base
 *
 * @return mixed
 */
function getRawBaseUrl()
{
	// Get the Laravel's App public path name
	$laravelPublicPath = trim(public_path(), '/');
	$laravelPublicPathLabel = last(explode('/', $laravelPublicPath));
	
	// Get Server Variables
	$httpHost = (trim(request()->server('HTTP_HOST')) != '') ? request()->server('HTTP_HOST') : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
	$requestUri = (trim(request()->server('REQUEST_URI')) != '') ? request()->server('REQUEST_URI') : (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
	
	// Clear the Server Variables
	$httpHost = trim($httpHost, '/');
	$requestUri = trim($requestUri, '/');
	$requestUri = (mb_substr($requestUri, 0, strlen($laravelPublicPathLabel)) === $laravelPublicPathLabel) ? '/' . $laravelPublicPathLabel : '';
	
	// Get the Current URL
	$currentUrl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://') . $httpHost . strtok($requestUri, '?');
	$currentUrl = head(explode('/' . admin_uri(), $currentUrl));
	
	// Get the Base URL
	$baseUrl = head(explode('/install', $currentUrl));
	$baseUrl = rtrim($baseUrl, '/');
	
	return $baseUrl;
}

/**
 * Get the installed version value
 *
 * @return null|string
 */
function getCurrentVersion()
{
	// Get the Current Version
	$version = null;
	if (\Jackiedo\DotenvEditor\Facades\DotenvEditor::keyExists('APP_VERSION')) {
		try {
			$version = \Jackiedo\DotenvEditor\Facades\DotenvEditor::getValue('APP_VERSION');
		} catch (\Exception $e) {
		}
	}
	$version = checkAndUseSemVer($version);
	
	return $version;
}

/**
 * Get the app latest version
 *
 * @return \Illuminate\Config\Repository|mixed|string
 */
function getLatestVersion()
{
	return checkAndUseSemVer(config('app.version'));
}

/**
 * Check and use semver version num format
 *
 * @param $version
 * @return string
 */
function checkAndUseSemVer($version)
{
	$semver = '0.0.0';
	if (!empty($version)) {
		$numPattern = '([0-9]+)';
		if (preg_match('#^' . $numPattern . '\.' . $numPattern . '\.' . $numPattern . '$#', $version)) {
			$semver = $version;
		} else {
			if (preg_match('#^' . $numPattern . '\.' . $numPattern . '$#', $version)) {
				$semver = $version . '.0';
			} else {
				if (preg_match('#^' . $numPattern . '$#', $version)) {
					$semver = $version . '.0.0';
				} else {
					$semver = '0.0.0';
				}
			}
		}
	}
	
	return $semver;
}

/**
 * Extract only digit characters and Convert the result in integer.
 *
 * @param $value
 * @return mixed
 */
function strToInt($value)
{
	$value = preg_replace('/[^0-9]/', '', $value);
	$value = (int)$value;
	
	return $value;
}

/**
 * Change whitespace (\n and \r) to simple space in string
 * PHP_EOL catches newlines that \n, \r\n, \r miss.
 *
 * @param $string
 * @return mixed
 */
function changeWhiteSpace($string)
{
	$string = str_replace(PHP_EOL, ' ', $string);
	
	return $string;
}

/**
 * PHP round() function that always return a float value in any language
 *
 * @param $val
 * @param int $precision
 * @param int $mode
 * @return string
 */
function round_val($val, $precision = 0, $mode = PHP_ROUND_HALF_UP)
{
	return number_format((float)round($val, $precision, $mode), $precision, '.', '');
}

/**
 * Print JavaScript code in HTML
 *
 * @param $code
 * @return mixed
 */
function printJs($code)
{
	// Get the External JS, and make for them a pattern
	$exRegex = '/<script([a-z0-9\-_ ]+)src=([^>]+)>(.*?)<\/script>/ius';
	$replace = '<#EXTERNALJS#$1src=$2>$3</#EXTERNALJS#>';
	$code = preg_replace($exRegex, $replace, $code);
	
	// Get the Inline JS, and make for them a pattern
	$inRegex = '/<script([^>]*)>(.*?)<\/script>/ius';
	$replace = '<#INLINEJS#$1>$2</#INLINEJS#>';
	while (preg_match($inRegex, $code)) {
		$code = preg_replace($inRegex, $replace, $code);
	}
	
	// Replace the patterns
	$code = str_replace(['#EXTERNALJS#', '#INLINEJS#'], 'script', $code);
	
	// The code doesn't contain a <script> tag
	if (!preg_match($inRegex, $code)) {
		$code = '<script type="text/javascript">' . "\n" . $code . "\n" . '</script>';
	}
	
	return $code;
}

/**
 * Print CSS code in HTML
 *
 * @param $code
 * @return mixed
 */
function printCss($code)
{
	$code = preg_replace('/<[^>]+>/i', '', $code);
	$code = '<style type="text/css">' . "\n" . $code . "\n" . '</style>';
	
	return $code;
}

/**
 * Get Front Skin
 *
 * @param null $skin
 * @return \Illuminate\Config\Repository|mixed|null|string
 */
function getFrontSkin($skin = null)
{
	if (!empty($skin)) {
		if (!file_exists(public_path() . '/assets/css/skins/' . $skin . '.css')) {
			$skin = 'skin-default';
		}
	} else {
		$skin = config('settings.style.app_skin', 'skin-default');
	}
	
	return $skin;
}

/**
 * Count the total number of line of a given file without loading the entire file.
 * This is effective for large file
 *
 * @param string file path
 * @return int line count
 */
function lineCount($path)
{
	$file = new \SplFileObject($path, 'r');
	$file->seek(PHP_INT_MAX);
	
	return $file->key() + 1;
}

/**
 * Escape characters with slashes like in C & Remove the double white spaces
 *
 * @param $string
 * @param string $quote
 * @return null|string|string[]
 */
function cleanAddSlashes($string, $quote = '"')
{
	$string = preg_replace("/\s+/ui", " ", addcslashes($string, $quote));
	
	return $string;
}

/**
 * Get the current request path by pattern
 *
 * @param null $pattern
 * @return string
 */
function getRequestPath($pattern = null)
{
	if (empty($pattern)) {
		return request()->path();
	}
	
	$pattern = '#(' . $pattern . ')#ui';
	
	$tmp = '';
	preg_match($pattern, request()->path(), $tmp);
	
	return (isset($tmp[1]) && !empty($tmp[1])) ? $tmp[1] : request()->path();
}

/**
 * Unset cookies
 * Unset all of the cookies for current domain
 */
function unsetCookies()
{
	if (isset($_SERVER['HTTP_COOKIE'])) {
		$cookies = explode(';', $_SERVER['HTTP_COOKIE']);
		if (!empty($cookies)) {
			foreach ($cookies as $cookie) {
				$parts = explode('=', $cookie);
				$name = trim($parts[0]);
				setcookie($name, '', time() - 1000);
				setcookie($name, '', time() - 1000, '/');
			}
		}
	}
}

/**
 * Get random password
 *
 * @param $length
 * @return bool|string
 */
function getRandomPassword($length)
{
	$allowedCharacters = 'abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ234567890!$%^&#!$%^&#';
	$random = str_shuffle($allowedCharacters);
	$password = substr($random, 0, $length);
	
	if (is_bool($password) || empty($password)) {
		$password = \Illuminate\Support\Str::random($length);
	}
	
	return $password;
}

if (! function_exists('ietfLangTag')) {
	/**
	 * IETF language tag(s)
	 * Example: en-US, pt-BR, fr-CA, ... (Usage of "-" instead of "_")
	 *
	 * @param null $locale
	 * @return mixed
	 */
	function ietfLangTag($locale = null)
	{
		if (empty($locale)) {
			$locale = config('app.locale');
		}
		
		return str_replace('_', '-', $locale);
	}
}

if (! function_exists('head')) {
	/**
	 * Get the first element of an array. Useful for method chaining.
	 *
	 * @param  array  $array
	 * @return mixed
	 */
	function head($array)
	{
		return reset($array);
	}
}

if (! function_exists('last')) {
	/**
	 * Get the last element from an array.
	 *
	 * @param  array  $array
	 * @return mixed
	 */
	function last($array)
	{
		return end($array);
	}
}

if (! function_exists('class_basename')) {
	/**
	 * Get the class "basename" of the given object / class.
	 *
	 * @param  string|object  $class
	 * @return string
	 */
	function class_basename($class)
	{
		$class = is_object($class) ? get_class($class) : $class;
		
		return basename(str_replace('\\', '/', $class));
	}
}

if (! function_exists('userBrowser')) {
	/**
	 * Check if the user browser is the given value.
	 * The given value can be:
	 * 'Firefox', 'Chrome', 'Safari', 'Opera', 'MSIE', 'Trident', 'Edge'
	 *
	 * Usage: userBrowser('Chrome') or userBrowser() == 'Chrome'
	 *
	 * @param null $browser
	 * @return bool|mixed|null
	 */
	function userBrowser($browser = null)
	{
		if (!empty($browser)) {
			return (strpos(request()->server('HTTP_USER_AGENT'), $browser) !== false);
		} else {
			$browsers = ['Firefox', 'Chrome', 'Safari', 'Opera', 'MSIE', 'Trident', 'Edge'];
			$agent = request()->server('HTTP_USER_AGENT');
			
			$userBrowser = null;
			foreach ($browsers as $browser) {
				if (strpos($agent, $browser) !== false) {
					$userBrowser = $browser;
					break;
				}
			}
			
			return $userBrowser;
		}
	}
}

/**
 * Get sitemaps indexes
 *
 * @param bool $htmlFormat
 * @return string
 */
function getSitemapsIndexes($htmlFormat = false)
{
	$out = '';
	
	$countries = \App\Helpers\Localization\Helpers\Country::transAll(\App\Helpers\Localization\Country::getCountries());
	if (!$countries->isEmpty()) {
		if ($htmlFormat) {
			$cmFieldStyle = ($countries->count() > 10) ? ' style="height: 205px; overflow-y: scroll;"' : '';
			$out .= '<ul' . $cmFieldStyle . '>';
		}
		foreach ($countries as $country) {
			$country = \App\Helpers\Localization\Country::getCountryInfo($country->get('code'));
			
			if ($country->isEmpty()) {
				continue;
			}
			
			// Get the Country's Language Code
			$countryLanguageCode = ($country->has('lang') && $country->get('lang')->has('abbr'))
				? $country->get('lang')->get('abbr')
				: config('app.locale');
			
			// Add the Sitemap Index
			if ($htmlFormat) {
				$out .= '<li>' . localUrl($country, $country->get('icode') . '/sitemaps.xml') . '</li>';
			} else {
				$out .= 'Sitemap: ' . localUrl($country, $country->get('icode') . '/sitemaps.xml') . "\n";
			}
		}
		if ($htmlFormat) {
			$out .= '</ul>';
		}
	}
	
	return $out;
}

/**
 * Default robots.txt content
 *
 * @return string
 */
function getDefaultRobotsTxtContent()
{
	$out = '';
	$out .= 'User-agent: *' . "\n";
	$out .= 'Disallow:' . "\n";
	$out .= "\n";
	$out .= 'Allow: /' . "\n";
	$out .= "\n";
	$out .= 'User-agent: *' . "\n";
	$out .= 'Disallow: /' . admin_uri() . '/' . "\n";
	$out .= 'Disallow: /ajax/' . "\n";
	$out .= 'Disallow: /assets/' . "\n";
	$out .= 'Disallow: /css/' . "\n";
	$out .= 'Disallow: /js/' . "\n";
	$out .= 'Disallow: /vendor/' . "\n";
	$out .= 'Disallow: /main.php' . "\n";
	$out .= 'Disallow: /mix-manifest.json' . "\n";
	
	return $out;
}

/**
 * Get a masked phone number
 *
 * Replace some characters with Asterisks (or with the given char) using str_repeat()
 * Skip X last chars OR X first chars
 *
 * @param $number
 * @param int $skip
 * @param bool $lastChars
 * @param string $mask
 * @return string
 */
function maskPhoneNumber($number, $skip = 3, $lastChars = true, $mask = 'X')
{
	$skip = (int)$skip;
	
	// Multiplier must be greater than or equal to 0.
	// And if the multiplier is set to 0 the str_repeat() function will return an empty string.
	$multiplier = strlen($number) - $skip;
	if ($multiplier <= 0) {
		return $number;
	}
	
	if ($skip <= 0) {
		$number = str_repeat($mask, $multiplier);
	} else {
		if ($lastChars) {
			$number = str_repeat($mask, $multiplier) . substr($number, -$skip);
		} else {
			$number = substr($number, 0, $skip) . str_repeat($mask, $multiplier);
		}
	}
	
	return $number;
}

/**
 * Generate the Email Form button
 *
 * @param null $post
 * @param bool $btnBlock
 * @return string
 */
function genEmailContactBtn($post = null, $btnBlock = false)
{
	$out = '';
	
	if (!isset($post->email) || empty($post->email)) {
		return $out;
	}
	
	$btnLink = '#applyJob';
	$btnAttr = 'data-toggle="modal"';
	$btnClass = '';
	if (isset($post->application_url) && !empty($post->application_url)) {
		$btnLink = $post->application_url;
		$btnAttr = '';
	}
	if (!auth()->check()) {
		if (config('settings.single.guests_can_contact_ads_authors') != '1') {
			$btnLink = '#quickLogin';
			$btnAttr = 'data-toggle="modal"';
		}
	}
	
	if ($btnBlock) {
		$btnClass = $btnClass . ' btn-block';
	}
	
	$out .= '<a href="' . $btnLink . '" class="btn btn-default' . $btnClass . '" ' . $btnAttr . '>';
	$out .= '<i class="icon-mail-2"></i> ';
	$out .= t('Apply Online');
	$out .= '</a>';
	
	return $out;
}

/**
 * Generate the Phone Number button
 *
 * @param $post
 * @param bool $btnBlock
 * @return string
 */
function genPhoneNumberBtn($post, $btnBlock = false)
{
	$out = '';
	
	if (!isset($post->phone) || empty($post->phone) || $post->phone_hidden == 1) {
		return $out;
	}
	
	$btnLink = 'tel:' . $post->phone;
	$btnAttr = '';
	$btnClass = ' phoneBlock'; // for the JS showPhone() function
	$btnHint = t('Click to see');
	$phone = $post->phone;
	if (config('settings.single.hide_phone_number')) {
		if (config('settings.single.hide_phone_number') == '1') {
			$phone = maskPhoneNumber($phone, 3, true);
		}
		if (config('settings.single.hide_phone_number') == '2') {
			$phone = maskPhoneNumber($phone, 3, false);
		}
		if (config('settings.single.hide_phone_number') == '3') {
			$phone = maskPhoneNumber($phone, 0, true);
		}
		$btnLink = '';
		$btnAttr = 'data-toggle="tooltip" data-original-title="' . $btnHint . '"';
		$btnClass = $btnClass . ' tooltipHere';
	} else {
		if (config('settings.single.convert_phone_number_to_img')) {
			try {
				$phone = \Larapen\TextToImage\Facades\TextToImage::make($phone, config('larapen.core.textToImage'));
			} catch (\Exception $e) {
				$phone = $post->phone;
			}
			$btnClass = '';
		}
	}
	if (!auth()->check()) {
		if (config('settings.single.guests_can_contact_ads_authors') != '1') {
			$phone = $btnHint;
			$btnLink = '#quickLogin';
			$btnAttr = 'data-toggle="modal"';
			$btnClass = '';
		}
	}
	
	if ($btnBlock) {
		$btnClass = $btnClass . ' btn-block';
	}
	
	// Generate the Phone Number button
	$out .= '<a href="' . $btnLink . '" ' . $btnAttr . ' class="btn btn-success' . $btnClass . '">';
	$out .= '<i class="icon-phone-1"></i> ';
	$out .= $phone;
	$out .= '</a>';
	
	return $out;
}

/**
 * Set the Backup config vars
 *
 * @param null $typeOfBackup
 */
function setBackupConfig($typeOfBackup = null)
{
	// Backup Disks Setup
	\App\Helpers\Files\Storage\StorageDisk::setBackupDisks();
	
	// Get the current version value
	$version = preg_replace('/[^0-9\+]/', '', config('app.version'));
	
	// All backup filename prefix
	config()->set('backup.backup.destination.filename_prefix', 'all-site-v' . $version . '-');
	
	// Database backup
	if ($typeOfBackup == 'database') {
		config()->set('backup.backup.admin_flags', [
			'--disable-notifications' => true,
			'--only-db'               => true,
		]);
		config()->set('backup.backup.destination.filename_prefix', 'database-v' . $version . '-');
	}
	
	// Languages files backup
	if ($typeOfBackup == 'languages') {
		$include = [
			resource_path('lang'),
		];
		$pluginsDirs = glob(config('larapen.core.plugin.path') . '*', GLOB_ONLYDIR);
		if (!empty($pluginsDirs)) {
			foreach ($pluginsDirs as $pluginDir) {
				$pluginLangFolder = $pluginDir . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang';
				if (file_exists($pluginLangFolder)) {
					$include[] = $pluginLangFolder;
				}
			}
		}
		
		config()->set('backup.backup.admin_flags', [
			'--disable-notifications' => true,
			'--only-files'            => true,
		]);
		config()->set('backup.backup.source.files.include', $include);
		config()->set('backup.backup.source.files.exclude', [
			//...
		]);
		config()->set('backup.backup.destination.filename_prefix', 'languages-');
	}
	
	// Generated files backup
	if ($typeOfBackup == 'files') {
		config()->set('backup.backup.admin_flags', [
			'--disable-notifications' => true,
			'--only-files'            => true,
		]);
		config()->set('backup.backup.source.files.include', [
			base_path('.env'),
			storage_path('app/public'),
			storage_path('installed'),
		]);
		config()->set('backup.backup.source.files.exclude', [
			//...
		]);
		config()->set('backup.backup.destination.filename_prefix', 'files-');
	}
	
	// App files backup
	if ($typeOfBackup == 'app') {
		config()->set('backup.backup.admin_flags', [
			'--disable-notifications' => true,
			'--only-files'            => true,
		]);
		config()->set('backup.backup.source.files.include', [
			base_path(),
			// base_path('.gitattributes'),
			base_path('.gitignore'),
		]);
		config()->set('backup.backup.source.files.exclude', [
			base_path('node_modules'),
			base_path('.git'),
			base_path('.idea'),
			base_path('.env'),
			base_path('bootstrap/cache') . DIRECTORY_SEPARATOR . '*',
			public_path('robots.txt'),
			storage_path('app/backup-temp'),
			storage_path('app/database'),
			storage_path('app/public/app/categories/custom') . DIRECTORY_SEPARATOR . '*',
			storage_path('app/public/app/ico') . DIRECTORY_SEPARATOR . '*',
			storage_path('app/public/app/logo') . DIRECTORY_SEPARATOR . '*',
			storage_path('app/public/app/page') . DIRECTORY_SEPARATOR . '*',
			storage_path('app/public/files') . DIRECTORY_SEPARATOR . '*',
			storage_path('app/purifier') . DIRECTORY_SEPARATOR . '*',
			storage_path('database/demo'),
			storage_path('backups'),
			storage_path('dotenv-editor') . DIRECTORY_SEPARATOR . '*',
			storage_path('framework/cache') . DIRECTORY_SEPARATOR . '*',
			storage_path('framework/sessions') . DIRECTORY_SEPARATOR . '*',
			storage_path('framework/testing') . DIRECTORY_SEPARATOR . '*',
			storage_path('framework/views') . DIRECTORY_SEPARATOR . '*',
			storage_path('installed'),
			storage_path('laravel-backups'),
			storage_path('logs') . DIRECTORY_SEPARATOR . '*',
		]);
		config()->set('backup.backup.destination.filename_prefix', 'app-v' . $version . '-');
	}
	
	// Backup Cleanup Settings
	$keepAllBackupsForDays = (int)config('settings.backup.backup_cleanup_keep_days');
	if ($keepAllBackupsForDays > 0) {
		config()->set('backup.cleanup.defaultStrategy.keepAllBackupsForDays', $keepAllBackupsForDays);
	}
	$deleteOldestBackupsWhenUsingMoreMegabytesThan = (int)config('settings.backup.backup_cleanup_dobwummt');
	if ($deleteOldestBackupsWhenUsingMoreMegabytesThan > 0) {
		config()->set('backup.cleanup.defaultStrategy.deleteOldestBackupsWhenUsingMoreMegabytesThan', $deleteOldestBackupsWhenUsingMoreMegabytesThan);
	}
}

/**
 * Add http:// if it doesn't exists in the URL
 * Recognizes ftp://, ftps://, http:// and https:// in a case insensitive way.
 *
 * @param $url
 * @return string
 */
function addHttp($url)
{
	if (!empty($url)) {
		if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
			$url = 'http://' . $url;
		}
	}
	
	return $url;
}

/**
 * Determine if php is running at the command line
 *
 * @return bool
 */
function isCli()
{
	if (defined('STDIN')) {
		return true;
	}
	
	if (php_sapi_name() === 'cli') {
		return true;
	}
	
	if (array_key_exists('SHELL', $_ENV)) {
		return true;
	}
	
	if (empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0) {
		return true;
	}
	
	if (!array_key_exists('REQUEST_METHOD', $_SERVER)) {
		return true;
	}
	
	return false;
}

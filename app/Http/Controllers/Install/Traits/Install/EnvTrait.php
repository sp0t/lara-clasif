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

namespace App\Http\Controllers\Install\Traits\Install;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait EnvTrait
{
	/**
	 * Write configuration values to file
	 *
	 * @param \Illuminate\Http\Request $request
	 */
	private function writeEnv(Request $request)
	{
		// Get .env file path
		$filePath = base_path('.env');
		
		// Remove the old .env file (If exists)
		if (File::exists($filePath)) {
			File::delete($filePath);
		}
		
		// Set app key
		$key = 'base64:' . base64_encode($this->randomString(32));
		$key = config('app.key', $key);
		
		// Get app host
		$appHost = getHostByUrl($this->baseUrl);
		
		// Get app version
		$version = getLatestVersion();
		
		// Get database & site info
		$database = $request->session()->get('database');
		$siteInfo = $request->session()->get('site_info');
		
		// Generate .env file content
		$content = '';
		$content .= 'APP_ENV=production' . "\n";
		$content .= 'APP_KEY=' . $key . "\n";
		$content .= 'APP_DEBUG=false' . "\n";
		$content .= 'APP_URL=' . $this->baseUrl . "\n";
		$content .= 'APP_LOCALE=en' . "\n";
		$content .= 'APP_VERSION=' . $version . "\n";
		$content .= "\n";
		$content .= 'PURCHASE_CODE=' . (isset($siteInfo['purchase_code']) ? $siteInfo['purchase_code'] : '') . "\n";
		$content .= 'FORCE_HTTPS=' . (Str::startsWith($this->baseUrl, 'https://') ? 'true' : 'false') . "\n";
		$content .= "\n";
		$content .= 'DB_HOST=' . (isset($database['host']) ? $database['host'] : '') . "\n";
		$content .= 'DB_PORT=' . (isset($database['port']) ? $database['port'] : '') . "\n";
		$content .= 'DB_DATABASE=' . (isset($database['database']) ? $database['database'] : '') . "\n";
		$content .= 'DB_USERNAME=' . (isset($database['username']) ? $database['username'] : '') . "\n";
		$content .= 'DB_PASSWORD="' . (isset($database['password']) ? addcslashes($database['password'], '"') : '') . '"' . "\n";
		$content .= 'DB_SOCKET=' . (isset($database['socket']) ? $database['socket'] : '') . "\n";
		$content .= 'DB_TABLES_PREFIX=' . (isset($database['prefix']) ? $database['prefix'] : '') . "\n";
		$content .= 'DB_CHARSET=utf8' . "\n";
		$content .= 'DB_COLLATION=utf8_unicode_ci' . "\n";
		$content .= 'DB_DUMP_BINARY_PATH=' . "\n";
		$content .= "\n";
		$content .= 'IMAGE_DRIVER=gd' . "\n";
		$content .= "\n";
		$content .= 'CACHE_DRIVER=file' . "\n";
		$content .= 'CACHE_PREFIX=lc_' . "\n";
		$content .= 'QUEUE_CONNECTION=sync' . "\n";
		$content .= 'SESSION_DRIVER=file' . "\n";
		$content .= 'SESSION_LIFETIME=10080' . "\n";
		$content .= "\n";
		$content .= 'LOG_CHANNEL=daily' . "\n";
		$content .= 'LOG_LEVEL=debug' . "\n";
		$content .= 'LOG_DAYS=2' . "\n";
		$content .= "\n";
		$content .= 'DISABLE_PHONE=false' . "\n";
		$content .= 'DISABLE_EMAIL=false' . "\n";
		$content .= 'DISABLE_USERNAME=true' . "\n";
		
		// Save the new .env file
		File::put($filePath, $content);
		
		// Reload .env (related to the config values)
		$exitCode = Artisan::call('config:clear');
	}
	
	/**
	 * Create random string
	 *
	 * @param int $length
	 * @return string
	 */
	private function randomString(int $length = 6): string
	{
		$str   = "";
		$chars = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
		$max   = count($chars) - 1;
		for ($i = 0; $i < $length; $i++) {
			$rand = mt_rand(0, $max);
			$str  .= $chars[$rand];
		}
		
		return $str;
	}
}

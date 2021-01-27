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


use App\Helpers\Number;
use Illuminate\Http\Request;

trait CheckerTrait
{
	/**
	 * Get the system compatibilities data
	 */
	private function getSystemCompatibilitiesData(): array
	{
		$requiredPhpVersion = $this->getComposerRequiredPhpVersion();
		
		return [
			[
				'type'  => 'requirement',
				'name'  => 'PHP version',
				'check' => version_compare(PHP_VERSION, $requiredPhpVersion, '>='),
				'note'  => 'PHP ' . $requiredPhpVersion . ' or higher is required.',
			],
			[
				'type'  => 'requirement',
				'name'  => 'OpenSSL Extension',
				'check' => extension_loaded('openssl'),
				'note'  => 'OpenSSL PHP Extension is required.',
			],
			[
				'type'  => 'requirement',
				'name'  => 'Mbstring PHP Extension',
				'check' => extension_loaded('mbstring'),
				'note'  => 'Mbstring PHP Extension is required.',
			],
			[
				'type'  => 'requirement',
				'name'  => 'PDO PHP Extension',
				'check' => extension_loaded('pdo'),
				'note'  => 'PDO PHP Extension is required.',
			],
			[
				'type'  => 'requirement',
				'name'  => 'Tokenizer PHP Extension',
				'check' => extension_loaded('tokenizer'),
				'note'  => 'Tokenizer PHP Extension is required.',
			],
			[
				'type'  => 'requirement',
				'name'  => 'XML PHP Extension',
				'check' => extension_loaded('xml'),
				'note'  => 'XML PHP Extension is required.',
			],
			[
				'type'  => 'requirement',
				'name'  => 'PHP Fileinfo Extension',
				'check' => extension_loaded('fileinfo'),
				'note'  => 'PHP Fileinfo Extension is required.',
			],
			[
				'type'  => 'requirement',
				'name'  => 'PHP GD Library',
				'check' => (extension_loaded('gd') && function_exists('gd_info')),
				'note'  => 'PHP GD Library is required.',
			],
			[
				'type'  => 'requirement',
				'name'  => 'escapeshellarg()',
				'check' => func_enabled('escapeshellarg'),
				'note'  => 'escapeshellarg() must be enabled.',
			],
			[
				'type'  => 'permission',
				'name'  => 'bootstrap/cache/',
				'check' => file_exists(base_path('bootstrap/cache')) &&
					is_dir(base_path('bootstrap/cache')) &&
					(is_writable(base_path('bootstrap/cache'))) &&
					getPerms(base_path('bootstrap/cache')) >= 755,
				'note'  => 'The directory must be writable by the web server (0755).',
			],
			[
				'type'  => 'permission',
				'name'  => 'storage/',
				'check' => (file_exists(storage_path('/')) &&
					is_dir(storage_path('/')) &&
					(is_writable(storage_path('/'))) &&
					getPerms(storage_path('/')) >= 755),
				'note'  => 'The directory must be writable (recursively) by the web server (0755).',
			],
		];
	}
	
	/**
	 * Check for requirement when install app (Automatic)
	 *
	 * @param Request $request
	 * @param $compatibilities
	 * @return bool
	 */
	private function checkSystemCompatibility(Request $request, $compatibilities): bool
	{
		if ($request->has('mode') && $request->input('mode') == 'manual') {
			return false;
		}
		
		// Check Default Compatibilities
		$defaultCompatibilityTest = true;
		foreach ($compatibilities as $compatibility) {
			if (!$compatibility['check']) {
				$defaultCompatibilityTest = false;
			}
		}
		
		// Check Additional Directories Permissions
		$additionalPermissionsAreOk = false;
		if (
			(file_exists(storage_path('app/public/app')) &&
				is_dir(storage_path('app/public/app')) &&
				(is_writable(storage_path('app/public/app'))) &&
				getPerms(storage_path('app/public/app')) >= 755)
			&&
			(file_exists(storage_path('app/public/app/categories/custom')) &&
				is_dir(storage_path('app/public/app/categories/custom')) &&
				(is_writable(storage_path('app/public/app/categories/custom'))) &&
				getPerms(storage_path('app/public/app/categories/custom')) >= 755)
			&&
			(file_exists(storage_path('app/public/app/logo')) &&
				is_dir(storage_path('app/public/app/logo')) &&
				(is_writable(storage_path('app/public/app/logo'))) &&
				getPerms(storage_path('app/public/app/logo')) >= 755)
			&&
			(file_exists(storage_path('app/public/app/page')) &&
				is_dir(storage_path('app/public/app/page')) &&
				(is_writable(storage_path('app/public/app/page'))) &&
				getPerms(storage_path('app/public/app/page')) >= 755)
			&&
			(file_exists(storage_path('app/public/files')) &&
				is_dir(storage_path('app/public/files')) &&
				(is_writable(storage_path('app/public/files'))) &&
				getPerms(storage_path('app/public/files')) >= 755)
		) {
			$additionalPermissionsAreOk = true;
		}
		
		return ($defaultCompatibilityTest && $additionalPermissionsAreOk);
	}
	
	/**
	 * Get the composer.json required PHP version
	 *
	 * @return mixed|string
	 */
	private function getComposerRequiredPhpVersion()
	{
		$filePath = base_path('composer.json');
		
		$content = file_get_contents($filePath);
		$array = json_decode($content,true);
		
		if (!isset($array['require']) || !isset($array['require']['php'])) {
			echo "<pre><strong>ERROR:</strong> Impossible to get the composer.json's required PHP version value.</pre>";
			exit();
		}
		
		return Number::getFloatRawFormat($array['require']['php']);
	}
	
	/**
	 * @return string
	 */
	private function checkServerVar(): string
	{
		$vars    = ['HTTP_HOST', 'SERVER_NAME', 'SERVER_PORT', 'SCRIPT_NAME', 'SCRIPT_FILENAME', 'PHP_SELF', 'HTTP_ACCEPT', 'HTTP_USER_AGENT'];
		$missing = [];
		foreach ($vars as $var) {
			if (!isset($_SERVER[$var])) {
				$missing[] = $var;
			}
		}
		
		if (!empty($missing)) {
			return '$_SERVER does not have: ' . implode(', ', $missing);
		}
		
		if (!isset($_SERVER['REQUEST_URI']) && isset($_SERVER['QUERY_STRING'])) {
			return 'Either $_SERVER["REQUEST_URI"] or $_SERVER["QUERY_STRING"] must exist.';
		}
		
		if (!isset($_SERVER['PATH_INFO']) && strpos($_SERVER['PHP_SELF'], $_SERVER['SCRIPT_NAME']) !== 0) {
			return 'Unable to determine URL path info. Please make sure $_SERVER["PATH_INFO"] (or $_SERVER["PHP_SELF"] and $_SERVER["SCRIPT_NAME"]) contains proper value.';
		}
		
		return '';
	}
}

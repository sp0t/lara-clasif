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

$valid = true;
$error = '';

// Check the server components to prevent error during the installation process.
$requiredPhpVersion = _getComposerRequiredPhpVersion();
if (!version_compare(PHP_VERSION, $requiredPhpVersion, '>=')) {
	$error .= "<strong>ERROR:</strong> PHP " . $requiredPhpVersion . " or higher is required.<br />";
	$valid = false;
}
if (!extension_loaded('mbstring')) {
	$error .= "<strong>ERROR:</strong> The requested PHP extension mbstring is missing from your system.<br />";
	$valid = false;
}

if (!empty(ini_get('open_basedir'))) {
	$error .= "<strong>ERROR:</strong> Please disable the <strong>open_basedir</strong> setting to continue.<br />";
	$valid = false;
}

if (!$valid) {
	echo '<pre>'; echo $error; echo '</pre>';
	exit();
}

// Remove the bootstrap/cache files before making upgrade
if (_updateIsAvailable()) {
	$cachedFiles = [
		realpath(__DIR__ . '/../bootstrap/cache/packages.php'),
		realpath(__DIR__ . '/../bootstrap/cache/services.php')
	];
	foreach ($cachedFiles as $file) {
		if (file_exists($file)) {
			unlink($file);
		}
	}
}

// Remove unsupported bootstrap/cache files
$unsupportedCachedFiles = [
	realpath(__DIR__ . '/../bootstrap/cache/config.php'),
	realpath(__DIR__ . '/../bootstrap/cache/routes.php')
];
foreach ($unsupportedCachedFiles as $file) {
	if (file_exists($file)) {
		unlink($file);
	}
}

// Load Laravel Framework
require 'main.php';





// ==========================================================================================
// THESE FUNCTIONS WILL RUN BEFORE LARAVEL LIBRARIES
// ==========================================================================================

// Get the composer.json required PHP version
function _getComposerRequiredPhpVersion()
{
	$filePath = realpath(__DIR__ . '/../composer.json');
	
	$content = file_get_contents($filePath);
	$array = json_decode($content,true);
	
	if (!isset($array['require']) || !isset($array['require']['php'])) {
		echo "<pre><strong>ERROR:</strong> Impossible to get the composer.json's required PHP version value.</pre>";
		exit();
	}
	
	$value = $array['require']['php'];
	
	// String to Float
	$value = trim($value);
	$value = strtr($value, [' ' => '']);
	$value = preg_replace('/ +/', '', $value);
	$value = str_replace(',', '.', $value);
	$value = preg_replace('/[^0-9\.]/', '', $value);
	
	return $value;
}

// Check if a new version is available
function _updateIsAvailable()
{
	$lastVersion = _getLatestVersion();
	$currentVersion = _getCurrentVersion();
	
	if (!empty($lastVersion) && !empty($currentVersion)) {
		if (version_compare($lastVersion, $currentVersion, '>')) {
			return true;
		}
	}
	
	return false;
}

// Get the current version value
function _getCurrentVersion()
{
	// Get the Current Version
	$version = _getDotEnvValue('APP_VERSION');
	$version = _checkAndUseSemVer($version);
	
	return $version;
}

// Get the latest version value
function _getLatestVersion()
{
	$configFilePath = realpath(__DIR__ . '/../config/app.php');
	
	$lastVersion = null;
	if (file_exists($configFilePath)) {
		$array = include($configFilePath);
		if (isset($array['version'])) {
			$lastVersion = $array['version'];
		}
	}
	
	return $lastVersion;
}

// Check and use semver version num format
function _checkAndUseSemVer($version)
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

// Get a /.env file key's value
function _getDotEnvValue($key)
{
	$value = null;
	
	if (empty($key)) {
		return $value;
	}
	
	$filePath = realpath(__DIR__ . '/../.env');
	if (file_exists($filePath)) {
		$content = file_get_contents($filePath);
		$tmp = [];
		preg_match('/' . $key . '=(.*)[^\n]*/', $content, $tmp);
		if (isset($tmp[1]) && trim($tmp[1]) != '') {
			$value = trim($tmp[1]);
		}
	}
	
	return $value;
}

// ==========================================================================================

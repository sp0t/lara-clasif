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

namespace App\Observer;

use App\Helpers\Files\Storage\StorageDisk;
use App\Helpers\Lang\LangManager;
use App\Models\Category;
use App\Models\Country;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use Larapen\LaravelDistance\Libraries\mysql\DistanceHelper;
use Prologue\Alerts\Facades\Alert;

class SettingObserver
{
	/**
	 * Listen to the Entry updating event.
	 *
	 * @param Setting $setting
	 * @return void
	 */
	public function updating(Setting $setting)
	{
		if (isset($setting->key) && isset($setting->value)) {
			// Get the original object values
			$original = $setting->getOriginal();
			
			// Storage Disk Init.
			$disk = StorageDisk::getDisk();
			
			if (is_array($original) && array_key_exists('value', $original)) {
				$original['value'] = jsonToArray($original['value']);
				
				// Remove old logo from disk (Don't remove the default logo)
				if (array_key_exists('logo', $setting->value)) {
					if (
						is_array($original['value'])
						&& isset($original['value']['logo'])
						&& $setting->value['logo'] != $original['value']['logo']
					) {
						if (
							!Str::contains($original['value']['logo'], config('larapen.core.logo'))
							&& $disk->exists($original['value']['logo'])
						) {
							$disk->delete($original['value']['logo']);
						}
					}
				}
				
				// Remove old favicon from disk (Don't remove the default favicon)
				if (array_key_exists('favicon', $setting->value)) {
					if (
						is_array($original['value'])
						&& isset($original['value']['favicon'])
						&& $setting->value['favicon'] != $original['value']['favicon']
					) {
						if (
							!Str::contains($original['value']['favicon'], config('larapen.core.favicon'))
							&& $disk->exists($original['value']['favicon'])
						) {
							$disk->delete($original['value']['favicon']);
						}
					}
				}
				
				// Remove the language detection created sessions
				if (array_key_exists('auto_detect_language', $setting->value)) {
					if (
						empty($original['value'])
						|| (
							is_array($original['value'])
							&& !isset($original['value']['auto_detect_language'])
						)
						|| (
							is_array($original['value'])
							&& isset($original['value']['auto_detect_language'])
							&& $setting->value['auto_detect_language'] != $original['value']['auto_detect_language']
						)
					) {
						if (session()->has('browserLangCode')) {
							session()->forget('browserLangCode');
						}
						if (session()->has('countryLangCode')) {
							session()->forget('countryLangCode');
						}
						$countries = Country::all();
						if ($countries->count() > 0) {
							foreach ($countries as $country) {
								$sessionName = strtolower($country->code) . 'CountryLangCode';
								if (session()->has($sessionName)) {
									session()->forget($sessionName);
								}
							}
						}
					}
				}
				
				// Remove old body_background_image from disk
				if (array_key_exists('body_background_image', $setting->value)) {
					if (
						is_array($original['value'])
						&& isset($original['value']['body_background_image'])
						&& $setting->value['body_background_image'] != $original['value']['body_background_image']
						&& $disk->exists($original['value']['body_background_image'])
					) {
						$disk->delete($original['value']['body_background_image']);
					}
				}
				
				// Enable Posts Approbation by User Admin (Post Review)
				if (array_key_exists('posts_review_activation', $setting->value)) {
					// If Post Approbation is enabled, then update all the existing Posts
					if ((int)$setting->value['posts_review_activation'] == 1) {
						Post::where('reviewed', '!=', 1)->update(['reviewed' => 1]);
					}
				}
				
				if ($setting->key == 'seo') {
					// Remove the "public/robots.txt" file (It will be re-generated automatically)
					if (
						array_key_exists('robots_txt', $setting->value)
						|| array_key_exists('robots_txt_sm_indexes', $setting->value)
					) {
						if (
							empty($original['value'])
							|| (
								is_array($original['value'])
								&& !isset($original['value']['robots_txt'])
							)
							|| (
								is_array($original['value'])
								&& isset($original['value']['robots_txt'])
								&& md5($setting->value['robots_txt']) != md5($original['value']['robots_txt'])
							)
							|| (
								is_array($original['value'])
								&& !isset($original['value']['robots_txt_sm_indexes'])
							)
							|| (
								is_array($original['value'])
								&& isset($original['value']['robots_txt_sm_indexes'])
								&& $setting->value['robots_txt_sm_indexes'] != $original['value']['robots_txt_sm_indexes']
							)
						) {
							$this->removeRobotsTxtFile();
						}
					}
					
					// Regenerate the "resources/lang/[langCode]/routes.php" file
					if (
						array_key_exists('posts_permalink', $setting->value)
						|| array_key_exists('posts_permalink_ext', $setting->value)
						|| array_key_exists('multi_countries_urls', $setting->value)
					) {
						if (
							empty($original['value'])
							|| (
								is_array($original['value'])
								&& !isset($original['value']['posts_permalink'])
							)
							|| (
								is_array($original['value'])
								&& isset($original['value']['posts_permalink'])
								&& $setting->value['posts_permalink'] != $original['value']['posts_permalink']
							)
							|| (
								is_array($original['value'])
								&& !isset($original['value']['posts_permalink_ext'])
							)
							|| (
								is_array($original['value'])
								&& isset($original['value']['posts_permalink_ext'])
								&& $setting->value['posts_permalink_ext'] != $original['value']['posts_permalink_ext']
							)
							|| (
								is_array($original['value'])
								&& !isset($original['value']['multi_countries_urls'])
							)
							|| (
								is_array($original['value'])
								&& isset($original['value']['multi_countries_urls'])
								&& $setting->value['multi_countries_urls'] != $original['value']['multi_countries_urls']
							)
						) {
							$this->regenerateLangRoutes($setting);
						}
					}
				}
				
				// Check if the session sharing field has changed & Update the /.env file
				if (array_key_exists('share_session', $setting->value)) {
					if (
						empty($original['value'])
						|| (
							is_array($original['value'])
							&& !isset($original['value']['share_session'])
						)
						|| (
							is_array($original['value'])
							&& isset($original['value']['share_session'])
							&& $setting->value['share_session'] != $original['value']['share_session']
						)
					) {
						$this->updateEnvFileForSessionSharing($setting);
					}
				}
				
				// Update app caching system parameters in the /.env file
				if ($setting->key == 'optimization' && is_array($setting->value)) {
					$this->updateEnvFileForCacheParameters($setting);
				}
			}
		}
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param Setting $setting
	 * @return void
	 */
	public function saved(Setting $setting)
	{
		// If the Default Country is changed, then clear the 'country_code' from the sessions
		if (isset($setting->value['default_country_code'])) {
			session()->forget('country_code');
			session(['country_code' => $setting->value['default_country_code']]);
		}
		
		// If the Default Front Skin is changed, then update its assets paths (like categories pictures, etc.)
		if (isset($setting->value['app_skin']) && !empty($setting->value['app_skin'])) {
			$categories = Category::where('parent_id', 0)->get();
			if ($categories->count() > 0) {
				foreach ($categories as $category) {
					$canSave = false;
					
					// If the Category contains a skinnable icon,
					// Change it by the selected skin icon.
					if (Str::contains($category->picture, 'app/categories/skin-')) {
						$pattern     = '/app\/categories\/skin-[^\/]+\//ui';
						$replacement = 'app/categories/' . $setting->value['app_skin'] . '/';
						$picture     = preg_replace($pattern, $replacement, $category->picture);
						if (!empty($picture)) {
							$category->picture = $picture;
							$canSave           = true;
						}
					}
					
					// (Optional)
					// If the Category contains a skinnable default icon,
					// Change it by the selected skin default icon.
					if (Str::contains($category->picture, 'app/default/categories/fa-folder-')) {
						$pattern     = '/app\/default\/categories\/fa-folder-[^\.]+\./ui';
						$replacement = 'app/default/categories/fa-folder-' . $setting->value['app_skin'] . '.';
						$picture     = preg_replace($pattern, $replacement, $category->picture);
						if (!empty($picture)) {
							$category->picture = $picture;
							$canSave           = true;
						}
					}
					
					if ($canSave) {
						$category->save();
					}
				}
			}
		}
		
		// If the 'distance_calculation_formula' has been changed
		if (array_key_exists('distance_calculation_formula', $setting->value)) {
			try {
				$customFunctions = ['haversine', 'orthodromy'];
				foreach ($customFunctions as $function) {
					// Drop the function, If exists
					$sql = 'DROP FUNCTION IF EXISTS ' . $function . ';';
					DB::statement($sql);
					
					// Remove the corresponding cache (@todo: remove it)
					$cacheId = 'checkIfMySQLFunctionExists.' . $function;
					if (Cache::has($cacheId)) {
						Cache::forget($cacheId);
					}
				}
			} catch (\Exception $e) {
			}
			
			// Create the MySQL Distance Calculation function, If doesn't exist.
			if (!DistanceHelper::checkIfDistanceCalculationFunctionExists($setting->value['distance_calculation_formula'])) {
				$res = DistanceHelper::createDistanceCalculationFunction($setting->value['distance_calculation_formula']);
			}
		}
		
		// Removing Entries from the Cache
		$this->clearCache($setting);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param Setting $setting
	 * @return void
	 */
	public function deleted(Setting $setting)
	{
		// Removing Entries from the Cache
		$this->clearCache($setting);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $setting
	 */
	private function clearCache($setting)
	{
		Cache::flush();
	}
	
	/**
	 * Regenerate the "resources/lang/[langCode]/routes.php" file
	 *
	 * @param null $setting
	 * @return bool
	 */
	private function regenerateLangRoutes($setting = null)
	{
		$doneSuccessfully = true;
		
		try {
			// Update in live the config vars related the Settings below before saving them.
			if (isset($setting->value)) {
				if (array_key_exists('posts_permalink', $setting->value)) {
					config()->set('settings.seo.posts_permalink', $setting->value['posts_permalink']);
				}
				if (array_key_exists('posts_permalink_ext', $setting->value)) {
					config()->set('settings.seo.posts_permalink_ext', $setting->value['posts_permalink_ext']);
				}
				if (array_key_exists('multi_countries_urls', $setting->value)) {
					// Check the Domain Mapping plugin
					if (config('plugins.domainmapping.installed')) {
						config()->set('settings.seo.multi_countries_urls', false);
					} else {
						config()->set('settings.seo.multi_countries_urls', $setting->value['multi_countries_urls']);
					}
				}
			}
			
			// Init. the language manager
			$manager = new LangManager();
			
			// Get current values of "resources/lang/[langCode]/routes.php" (Original version)
			$routes = $manager->getFileContent(config_path('larapen/routes.php'));
			
			// Get all the others languages
			$locales = $manager->getLocales();
			if (!empty($locales)) {
				foreach ($locales as $locale) {
					$filePath = resource_path('lang/' . $locale . '/routes.php');
					$manager->writeFile($filePath, $routes);
				}
			}
		} catch (\Exception $e) {
			Alert::error($e->getMessage())->flash();
			$doneSuccessfully = false;
		}
		
		return $doneSuccessfully;
	}
	
	/**
	 * Update the /.env file to apply the session sharing rules
	 * The admin user will be log out automatically.
	 *
	 * @param $setting
	 */
	private function updateEnvFileForSessionSharing($setting)
	{
		// Check the Domain Mapping plugin
		if (config('plugins.domainmapping.installed')) {
			if (isset($setting->value['share_session'])) {
				config()->set('settings.domain_mapping.share_session', $setting->value['share_session']);
				
				// Log out the admin user
				\App\Plugins\domainmapping\Domainmapping::logout();
				
				// Update the /.env file to meet the plugin installation requirements
				\App\Plugins\domainmapping\Domainmapping::updateEnvFile(true);
			}
		}
	}
	
	/**
	 * Update app caching system parameters in the /.env file
	 *
	 * @param $setting
	 */
	private function updateEnvFileForCacheParameters($setting)
	{
		// Remove Existing Variables
		if (DotenvEditor::keyExists('CACHE_DRIVER')) {
			DotenvEditor::deleteKey('CACHE_DRIVER');
		}
		if (DotenvEditor::keyExists('CACHE_PREFIX')) {
			DotenvEditor::deleteKey('CACHE_PREFIX');
		}
		if (DotenvEditor::keyExists('MEMCACHED_PERSISTENT_ID')) {
			DotenvEditor::deleteKey('MEMCACHED_PERSISTENT_ID');
		}
		if (DotenvEditor::keyExists('MEMCACHED_USERNAME')) {
			DotenvEditor::deleteKey('MEMCACHED_USERNAME');
		}
		if (DotenvEditor::keyExists('MEMCACHED_PASSWORD')) {
			DotenvEditor::deleteKey('MEMCACHED_PASSWORD');
		}
		$i = 1;
		while (DotenvEditor::keyExists('MEMCACHED_SERVER_' . $i . '_HOST')) {
			DotenvEditor::deleteKey('MEMCACHED_SERVER_' . $i . '_HOST');
			$i++;
		}
		$i = 1;
		while (DotenvEditor::keyExists('MEMCACHED_SERVER_' . $i . '_PORT')) {
			DotenvEditor::deleteKey('MEMCACHED_SERVER_' . $i . '_PORT');
			$i++;
		}
		if (DotenvEditor::keyExists('REDIS_CLIENT')) {
			DotenvEditor::deleteKey('REDIS_CLIENT');
		}
		if (DotenvEditor::keyExists('REDIS_CLUSTER')) {
			DotenvEditor::deleteKey('REDIS_CLUSTER');
		}
		if (DotenvEditor::keyExists('REDIS_HOST')) {
			DotenvEditor::deleteKey('REDIS_HOST');
		}
		if (DotenvEditor::keyExists('REDIS_PASSWORD')) {
			DotenvEditor::deleteKey('REDIS_PASSWORD');
		}
		if (DotenvEditor::keyExists('REDIS_PORT')) {
			DotenvEditor::deleteKey('REDIS_PORT');
		}
		if (DotenvEditor::keyExists('REDIS_DB')) {
			DotenvEditor::deleteKey('REDIS_DB');
		}
		$i = 1;
		while (DotenvEditor::keyExists('REDIS_CLUSTER_' . $i . '_HOST')) {
			DotenvEditor::deleteKey('REDIS_CLUSTER_' . $i . '_HOST');
			$i++;
		}
		$i = 1;
		while (DotenvEditor::keyExists('REDIS_CLUSTER_' . $i . '_PASSWORD')) {
			DotenvEditor::deleteKey('REDIS_CLUSTER_' . $i . '_PASSWORD');
			$i++;
		}
		$i = 1;
		while (DotenvEditor::keyExists('REDIS_CLUSTER_' . $i . '_PORT')) {
			DotenvEditor::deleteKey('REDIS_CLUSTER_' . $i . '_PORT');
			$i++;
		}
		$i = 1;
		while (DotenvEditor::keyExists('REDIS_CLUSTER_' . $i . '_DB')) {
			DotenvEditor::deleteKey('REDIS_CLUSTER_' . $i . '_DB');
			$i++;
		}
		DotenvEditor::save();
		
		// Create Variables
		$envFileIsChanged = false;
		if (array_key_exists('cache_driver', $setting->value)) {
			DotenvEditor::setKey('CACHE_DRIVER', $setting->value['cache_driver']);
			DotenvEditor::setKey('CACHE_PREFIX', 'jc_');
			$envFileIsChanged = true;
		}
		if (array_key_exists('memcached_persistent_id', $setting->value)) {
			DotenvEditor::setKey('MEMCACHED_PERSISTENT_ID', $setting->value['memcached_persistent_id']);
			$envFileIsChanged = true;
		}
		if (array_key_exists('memcached_sasl_username', $setting->value)) {
			DotenvEditor::setKey('MEMCACHED_USERNAME', $setting->value['memcached_sasl_username']);
			$envFileIsChanged = true;
		}
		if (array_key_exists('memcached_sasl_password', $setting->value)) {
			DotenvEditor::setKey('MEMCACHED_PASSWORD', $setting->value['memcached_sasl_password']);
			$envFileIsChanged = true;
		}
		$i = 1;
		while (
			array_key_exists('memcached_servers_' . $i . '_host', $setting->value)
			&& array_key_exists('memcached_servers_' . $i . '_port', $setting->value)
		) {
			if (DotenvEditor::keyExists('MEMCACHED_SERVER_' . $i . '_HOST')) {
				DotenvEditor::deleteKey('MEMCACHED_SERVER_' . $i . '_HOST');
			}
			if (DotenvEditor::keyExists('MEMCACHED_SERVER_' . $i . '_PORT')) {
				DotenvEditor::deleteKey('MEMCACHED_SERVER_' . $i . '_PORT');
			}
			DotenvEditor::setKey('MEMCACHED_SERVER_' . $i . '_HOST', $setting->value['memcached_servers_' . $i . '_host']);
			DotenvEditor::setKey('MEMCACHED_SERVER_' . $i . '_PORT', $setting->value['memcached_servers_' . $i . '_port']);
			$i++;
			if (!$envFileIsChanged) {
				$envFileIsChanged = true;
			}
		}
		if (array_key_exists('redis_client', $setting->value)) {
			DotenvEditor::setKey('REDIS_CLIENT', $setting->value['redis_client']);
			$envFileIsChanged = true;
		}
		if (array_key_exists('redis_cluster', $setting->value)) {
			DotenvEditor::setKey('REDIS_CLUSTER', $setting->value['redis_cluster']);
			$envFileIsChanged = true;
		}
		if (array_key_exists('redis_host', $setting->value)) {
			DotenvEditor::setKey('REDIS_HOST', $setting->value['redis_host']);
			$envFileIsChanged = true;
		}
		if (array_key_exists('redis_password', $setting->value)) {
			DotenvEditor::setKey('REDIS_PASSWORD', $setting->value['redis_password']);
			$envFileIsChanged = true;
		}
		if (array_key_exists('redis_port', $setting->value)) {
			DotenvEditor::setKey('REDIS_PORT', $setting->value['redis_port']);
			$envFileIsChanged = true;
		}
		if (array_key_exists('redis_database', $setting->value)) {
			DotenvEditor::setKey('REDIS_DB', $setting->value['redis_database']);
			$envFileIsChanged = true;
		}
		if (array_key_exists('redis_cluster_activation', $setting->value) && $setting->value['redis_cluster_activation'] == '1') {
			$i = 1;
			while (
				array_key_exists('redis_cluster_' . $i . '_host', $setting->value)
				&& array_key_exists('redis_cluster_' . $i . '_password', $setting->value)
				&& array_key_exists('redis_cluster_' . $i . '_port', $setting->value)
				&& array_key_exists('redis_cluster_' . $i . '_database', $setting->value)
			) {
				DotenvEditor::setKey('REDIS_CLUSTER_' . $i . '_HOST', $setting->value['redis_cluster_' . $i . '_host']);
				DotenvEditor::setKey('REDIS_CLUSTER_' . $i . '_PASSWORD', $setting->value['redis_cluster_' . $i . '_password']);
				DotenvEditor::setKey('REDIS_CLUSTER_' . $i . '_PORT', $setting->value['redis_cluster_' . $i . '_port']);
				DotenvEditor::setKey('REDIS_CLUSTER_' . $i . '_DB', $setting->value['redis_cluster_' . $i . '_database']);
				$i++;
				if (!$envFileIsChanged) {
					$envFileIsChanged = true;
				}
			}
		}
		
		// Save the /.env file
		if ($envFileIsChanged) {
			DotenvEditor::save();
			
			// Some time of pause
			sleep(2);
		}
	}
	
	/**
	 * Remove the robots.txt file (It will be re-generated automatically)
	 */
	private function removeRobotsTxtFile()
	{
		$robotsFile = public_path('robots.txt');
		if (File::exists($robotsFile)) {
			File::delete($robotsFile);
		}
	}
}

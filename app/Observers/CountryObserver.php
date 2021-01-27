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
use App\Models\City;
use App\Models\Country;
use App\Models\Post;
use App\Models\SubAdmin1;
use App\Models\SubAdmin2;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Prologue\Alerts\Facades\Alert;

class CountryObserver
{
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param Country $country
	 * @return bool
	 */
	public function deleting(Country $country)
	{
		// Storage Disk Init.
		$disk = StorageDisk::getDisk();
		
		// Cannot delete the current country when the Domain Mapping plugin is installed
		if (config('plugins.domainmapping.installed')) {
			if (strtolower($country->code) == strtolower(config('settings.geo_location.default_country_code'))) {
				$msg = trans('admin::messages.Cannot delete the current country when the Domain Mapping plugin is installed.');
				Alert::error($msg)->flash();
				
				return false;
			}
		}
		
		// Remove background_image files (if exists)
		if (!empty($country->background_image)) {
			if (
				!Str::contains($country->background_image, config('larapen.core.picture.default'))
				&& $disk->exists($country->background_image)
			) {
				$disk->delete($country->background_image);
			}
		}
		
		// Delete all Geonames entries
		$deletedRows = SubAdmin1::countryOf($country->code)->delete();
		$deletedRows = SubAdmin2::countryOf($country->code)->delete();
		$deletedRows = City::countryOf($country->code)->delete();
		
		// Delete all Posts entries
		$posts = Post::countryOf($country->code)->get();
		if ($posts->count() > 0) {
			foreach ($posts as $post) {
				$post->delete();
			}
		}
		
		if (config('plugins.domainmapping.installed')) {
			try {
				$domain = \App\Plugins\domainmapping\app\Models\Domain::where('country_code', $country->code)->first();
				if (!empty($domain)) {
					$domain->delete();
				}
			} catch (\Exception $e) {
			}
		}
		
		return true;
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param Country $country
	 * @return void
	 */
	public function saved(Country $country)
	{
		// Remove the robots.txt file
		$this->removeRobotsTxtFile();
		
		// Removing Entries from the Cache
		$this->clearCache($country);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param Country $country
	 * @return void
	 */
	public function deleted(Country $country)
	{
		// Remove the robots.txt file
		$this->removeRobotsTxtFile();
		
		// Removing Entries from the Cache
		$this->clearCache($country);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $country
	 */
	private function clearCache($country)
	{
		Cache::flush();
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

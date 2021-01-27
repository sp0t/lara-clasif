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
use App\Models\HomeSection;
use Illuminate\Support\Facades\Cache;

class HomeSectionObserver
{
	/**
	 * Listen to the Entry updating event.
	 *
	 * @param HomeSection $homeSection
	 * @return void
	 */
	public function updating(HomeSection $homeSection)
	{
		if (isset($homeSection->method) && isset($homeSection->value)) {
			// Get the original object values
			$original = $homeSection->getOriginal();
			
			// Storage Disk Init.
			$disk = StorageDisk::getDisk();
			
			if (is_array($original) && array_key_exists('value', $original)) {
				$original['value'] = jsonToArray($original['value']);
				
				// Remove old background_image from disk
				if (array_key_exists('background_image', $homeSection->value)) {
					if (
						is_array($original['value'])
						&& isset($original['value']['background_image'])
						&& $homeSection->value['background_image'] != $original['value']['background_image']
						&& $disk->exists($original['value']['background_image'])
					) {
						$disk->delete($original['value']['background_image']);
					}
				}
			}
		}
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param HomeSection $homeSection
	 * @return void
	 */
	public function updated(HomeSection $homeSection)
	{
		//...
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param HomeSection $homeSection
	 * @return void
	 */
	public function saved(HomeSection $homeSection)
	{
		// Removing Entries from the Cache
		$this->clearCache($homeSection);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param HomeSection $homeSection
	 * @return void
	 */
	public function deleted(HomeSection $homeSection)
	{
		// Removing Entries from the Cache
		$this->clearCache($homeSection);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $homeSection
	 */
	private function clearCache($homeSection)
	{
		Cache::flush();
	}
}

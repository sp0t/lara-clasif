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

use App\Models\MetaTag;
use Illuminate\Support\Facades\Cache;

class MetaTagObserver extends TranslatedModelObserver
{
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param MetaTag $metaTag
	 * @return void
	 */
	public function saved(MetaTag $metaTag)
	{
		// Removing Entries from the Cache
		$this->clearCache($metaTag);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param MetaTag $metaTag
	 * @return void
	 */
	public function deleted(MetaTag $metaTag)
	{
		// Removing Entries from the Cache
		$this->clearCache($metaTag);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $metaTag
	 */
	private function clearCache($metaTag)
	{
		Cache::flush();
	}
}

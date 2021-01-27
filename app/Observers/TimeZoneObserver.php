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

use App\Models\TimeZone;
use Illuminate\Support\Facades\Cache;

class TimeZoneObserver
{
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param TimeZone $timeZone
	 * @return void
	 */
	public function saved(TimeZone $timeZone)
	{
		// Removing Entries from the Cache
		$this->clearCache($timeZone);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param TimeZone $timeZone
	 * @return void
	 */
	public function deleted(TimeZone $timeZone)
	{
		// Removing Entries from the Cache
		$this->clearCache($timeZone);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $timeZone
	 */
	private function clearCache($timeZone)
	{
		Cache::flush();
	}
}

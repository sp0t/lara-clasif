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

use App\Models\Continent;
use App\Models\Country;
use Illuminate\Support\Facades\Cache;

class ContinentObserver
{
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param Continent $continent
	 * @return void
	 */
	public function deleting(Continent $continent)
	{
		// Delete all Continent's Countries
		$countries = Country::where('continent_code', $continent->code)->get();
		if ($countries->count() > 0) {
			foreach ($countries as $country) {
				$country->delete();
			}
		}
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param Continent $continent
	 * @return void
	 */
	public function saved(Continent $continent)
	{
		// Removing Entries from the Cache
		$this->clearCache($continent);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param Continent $continent
	 * @return void
	 */
	public function deleted(Continent $continent)
	{
		// Removing Entries from the Cache
		$this->clearCache($continent);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $continent
	 */
	private function clearCache($continent)
	{
		Cache::flush();
	}
}

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

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;

class CurrencyObserver
{
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param Currency $currency
	 * @return void
	 */
	public function saved(Currency $currency)
	{
		// Removing Entries from the Cache
		$this->clearCache($currency);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param Currency $currency
	 * @return void
	 */
	public function deleted(Currency $currency)
	{
		// Removing Entries from the Cache
		$this->clearCache($currency);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $currency
	 */
	private function clearCache($currency)
	{
		Cache::flush();
	}
}

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

use App\Models\SalaryType;
use Illuminate\Support\Facades\Cache;

class SalaryTypeObserver extends TranslatedModelObserver
{
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param SalaryType $salaryType
	 * @return void
	 */
	public function saved(SalaryType $salaryType)
	{
		// Removing Entries from the Cache
		$this->clearCache($salaryType);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param SalaryType $salaryType
	 * @return void
	 */
	public function deleted(SalaryType $salaryType)
	{
		// Removing Entries from the Cache
		$this->clearCache($salaryType);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $salaryType
	 */
	private function clearCache($salaryType)
	{
		Cache::flush();
	}
}

<?php
/**
 * LaraClassified - Classified Ads Web Application
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

namespace App\Http\Controllers\Install\Traits\Update;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

trait CleanUpTrait
{
	/**
	 * Clear all the cache
	 */
	private function clearCache()
	{
		$this->removeRobotsTxtFile();
		
		$exitCode = Artisan::call('cache:clear');
		sleep(2);
		
		$exitCode = Artisan::call('view:clear');
		sleep(1);
		
		File::delete(File::glob(storage_path('logs') . DIRECTORY_SEPARATOR . 'laravel*.log'));
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

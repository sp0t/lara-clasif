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

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\File;

trait RobotsTxtTrait
{
	/**
	 * Check & Create the robots.txt file if it doesn't exist
	 */
	public function checkRobotsTxtFile()
	{
		// Get the robots.txt file path
		$robotsFile = public_path('robots.txt');
		
		// Generate the robots.txt (If it does not exist)
		if (!File::exists($robotsFile)) {
			$robotsTxt = '';
			
			// Custom robots.txt content
			$robotsTxtArr = preg_split('/\r\n|\r|\n/', config('settings.seo.robots_txt', ''));
			if (!empty($robotsTxtArr)) {
				foreach ($robotsTxtArr as $key => $value) {
					$robotsTxt .= trim($value) . "\n";
				}
			}
			
			if (config('settings.seo.robots_txt_sm_indexes')) {
				$robotsTxt .= "\n";
				$robotsTxt .= getSitemapsIndexes();
			}
			
			// Create the robots.txt file
			if (File::isWritable(dirname($robotsFile))) {
				File::put($robotsFile, $robotsTxt);
			}
		}
	}
}

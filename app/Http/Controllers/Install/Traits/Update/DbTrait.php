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

use App\Helpers\DBTool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

trait DbTrait
{
	/**
	 * Upgrade the Database & Apply actions
	 *
	 * @param $lastVersion
	 * @param $currentVersion
	 * @return bool
	 */
	private function upgradeDatabase($lastVersion, $currentVersion)
	{
		$migrationFilesPath = base_path('database/upgrade/');
		$migrationFilesPath = rtrim($migrationFilesPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$versionsDirsPaths  = array_filter(glob($migrationFilesPath . '*'), 'is_dir');
		
		$errorDetect = false;
		$errors      = '';
		
		try {
			// Upgrade the website database version by version
			foreach ($versionsDirsPaths as $versionPath) {
				// Get the iterated version
				$version = last(explode(DIRECTORY_SEPARATOR, $versionPath));
				
				// Check the semver format
				if (!preg_match('#^([0-9]+)\.([0-9]+)\.([0-9]+)$#', $version)) {
					continue;
				}
				
				// Load and Apply migration & SQL (queries) files of the "iterated versions",
				// that are greater than the "website current version" && are lower than or equal to the "app's latest version"
				if (version_compare($version, $currentVersion, '>') && version_compare($version, $lastVersion, '<=')) {
					
					$updateFile = $migrationFilesPath . $version . '/update.php';
					if (File::exists($updateFile)) {
						require_once($updateFile);
					}
					
					$updateSqlFile = $migrationFilesPath . $version . '/update.sql';
					if (File::exists($updateSqlFile)) {
						// Import the SQL file
						$res = DBTool::importSqlFile(DB::connection()->getPdo(), $updateSqlFile, DB::getTablePrefix());
						if ($res === false) {
							$errorDetect = true;
							$errors      .= '<br>Error occurred in the file: ' . $updateSqlFile;
						}
					}
					
				}
			}
			
			// Check if error is detected
			if ($errorDetect) {
				echo '<pre>' . $errors . '</pre>';
				
				return false;
			}
		} catch (\Exception $e) {
			$errors .= '<br>Exception => ' . $e->getMessage();
			$errors .= '<br>[ FAILED ]';
			$errors .= '<br>Error occurred during the database upgrade.';
			echo '<pre>' . $errors . '</pre>';
			
			return false;
		}
		
		return true;
	}
}

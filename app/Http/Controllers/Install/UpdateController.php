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

namespace App\Http\Controllers\Install;

/*
 * Increase PHP page execution time for this controller.
 * NOTE: This function has no effect when PHP is running in safe mode (http://php.net/manual/en/ini.sect.safe-mode.php#ini.safe-mode).
 * There is no workaround other than turning off safe mode or changing the time limit (max_execution_time) in the php.ini.
 */
set_time_limit(0);

use App\Http\Controllers\Controller;
use App\Http\Controllers\Install\Traits\Update\ApiTrait;
use App\Http\Controllers\Install\Traits\Update\CleanUpTrait;
use App\Http\Controllers\Install\Traits\Update\DbTrait;
use App\Http\Controllers\Install\Traits\Update\EnvTrait;
use App\Http\Controllers\Install\Traits\Update\LanguageTrait;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class UpdateController extends Controller
{
	use CleanUpTrait, DbTrait, EnvTrait, LanguageTrait, ApiTrait;
	
	/**
	 * URL: /upgrade
	 *
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function index()
	{
		// Lunch the installation if the /.env file doesn't exists
		if (!File::exists(base_path('.env'))) {
			return redirect('/install');
		}
		
		// Get eventual new version value & the current (installed) version value
		$lastVersion = getLatestVersion();
		$currentVersion = getCurrentVersion();
		
		// All is Up to Date
		if (version_compare($lastVersion, $currentVersion, '<=')) {
			abort(401);
		}
		
		// Installed version number is NOT found
		if (version_compare('1.0.0', $currentVersion, '>')) {
			$message = "<strong style='color:red;'>ERROR:</strong> Cannot find your current version from the '/.env' file.<br><br>";
			$message .= "<br><strong style='color:green;'>SOLUTION:</strong>";
			$message .= "<br>1. You have to add in the '/.env' file a line like: <strong>APP_VERSION=X.X</strong> (Don't forget to replace <strong>X.X</strong> by your current version)";
			$message .= "<br>2. (Optional) If you are forgot your current version, you have to see it from your backup 'config/app.php' file (it's the last element of the array).";
			$message .= "<br>3. And <strong>refresh this page</strong> to finish upgrading";
			echo '<pre>' . $message . '</pre>';
			exit();
		}
		
		// Go to maintenance with DOWN status
		$exitCode = Artisan::call('down');
		
		// Clear all the cache
		$this->clearCache();
		
		// Upgrade the Database
		$res = $this->upgradeDatabase($lastVersion, $currentVersion);
		if ($res === false) {
			dd('ERROR');
		}
		
		// Update the current version to last version
		$this->setCurrentVersion($lastVersion);
		
		// (Try to) Fill the missing lines in all languages files
		$this->syncLanguageFilesLines();
		
		// Check the Purchase Code
		$this->checkPurchaseCode();
		
		// Clear all the cache
		$this->clearCache();
		
		// Restore system UP status
		$exitCode = Artisan::call('up');
		
		// Success message
		flash("Congratulations! Your website has been upgraded to v" . $lastVersion)->success();
		
		// Redirection
		return redirect('/');
	}
}

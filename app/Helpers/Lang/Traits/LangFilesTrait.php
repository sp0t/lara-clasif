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

namespace App\Helpers\Lang\Traits;

use Illuminate\Support\Facades\File;

trait LangFilesTrait
{
	/**
	 * Copy the master language folder to the new language folder
	 *
	 * @param $defaultLangCode
	 * @param $langCodeTo
	 */
	public function copyFiles($defaultLangCode, $langCodeTo)
	{
		if ($this->masterLangExists()) {
			$defaultLangCode = $this->masterLangCode;
		}
		
		// Copy the language files (If the destination files don't exist)
		if (!File::exists($this->path . $langCodeTo)) {
			File::copyDirectory($this->path . $defaultLangCode, $this->path . $langCodeTo);
		}
		if (!File::exists($this->path . 'vendor/admin/' . $langCodeTo)) {
			File::copyDirectory($this->path . 'vendor/admin/' . $defaultLangCode, $this->path . 'vendor/admin/' . $langCodeTo);
		}
	}
	
	/**
	 * Remove the Language files
	 *
	 * @param $langCode
	 * @return bool
	 */
	public function removeFiles($langCode)
	{
		// Don't remove the master Language files
		if ($langCode == $this->masterLangCode) {
			return false;
		}
		
		// Don't remove the included languages files
		if (in_array($langCode, $this->includedLanguagesFiles)) {
			return false;
		}
		
		// Remove the Language files
		File::deleteDirectory($this->path . $langCode);
		File::deleteDirectory($this->path . 'vendor/admin/' . $langCode);
	}
	
	/**
	 * Check if the master language exists
	 *
	 * @return bool
	 */
	protected function masterLangExists()
	{
		$masterFrontLangPath = $this->path . $this->masterLangCode;
		$masterBackendLangPath = $this->path . 'vendor/admin/' . $this->masterLangCode;
		if (File::exists($masterFrontLangPath) && File::exists($masterBackendLangPath)) {
			return true;
		}
		
		return false;
	}
}
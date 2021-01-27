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

namespace App\Helpers\Lang;

use App\Helpers\Lang\Traits\LangFilesTrait;
use App\Helpers\Lang\Traits\LangLinesTrait;
use App\Helpers\Lang\Traits\LangTablesTrait;

class LangManager
{
	use LangFilesTrait, LangLinesTrait, LangTablesTrait;
	
	/**
	 * The path to the language files.
	 *
	 * @var string
	 */
	protected $path;
	
	/**
	 * The master language code
	 *
	 * @var string
	 */
	protected $masterLangCode = 'en';
	
	/**
	 * Included languages files
	 *
	 * @var array
	 */
	protected $includedLanguagesFiles = [
		'en', // English
		'fr', // French - Français
		'es', // Spanish - Español
		'ar', // Arabic - ‫العربية
	];
	
	/**
	 * LangManager constructor.
	 */
	public function __construct()
	{
		$this->path = resource_path('lang/');
	}
	
	/**
	 * Get all codes of the included languages
	 *
	 * @return array
	 */
	public function getIncludedLanguages()
	{
		return $this->includedLanguagesFiles;
	}
	
	/**
	 * Get all the codes of included and existing languages
	 *
	 * @return array
	 */
	public function getTranslatedLanguages()
	{
		$languages = [];
		
		if (!empty($this->includedLanguagesFiles)) {
			foreach($this->includedLanguagesFiles as $code) {
				$path = $this->path . $code;
				if (file_exists($path) && is_dir($path)) {
					$languages[] = $code;
				}
			}
		}
		
		return $languages;
	}
}

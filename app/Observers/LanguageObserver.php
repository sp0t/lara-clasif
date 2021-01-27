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

use App\Helpers\Lang\LangManager;
use App\Models\Language;
use Illuminate\Support\Facades\Cache;
use Prologue\Alerts\Facades\Alert;

class LanguageObserver
{
	/**
	 * Listen to the Entry creating event.
	 *
	 * @param Language $language
	 * @return void
	 */
	public function creating(Language $language)
	{
		// Check Demo Website
		$this->isDemo();
		
		// Get the current Default Language
		$defaultLang = Language::where('default', 1)->first();
		
		if (!empty($defaultLang)) {
			$manager = new LangManager();
			$manager->copyFiles($defaultLang->abbr, $language->abbr);             // Copy the default language files
			$manager->copyTranslatedEntries($defaultLang->abbr, $language->abbr); // Copy the default language translated DB entries
		}
	}
	
	/**
	 * Listen to the Entry updating event.
	 *
	 * @param Language $language
	 * @return bool
	 */
	public function updating(Language $language)
	{
		// Check Demo Website
		$this->isDemo();
		
		// Get the original object values
		$original = $language->getOriginal();
		
		// Set default language
		if ($language->default != $original['default']) {
			if ($language->default == 1 || $language->default == 'on') {
				// The current language is updated as default language
				
				$manager = new LangManager();
				$manager->updateTranslatedEntries($language->abbr); // Update translated entries
				$manager->setDefaultLanguage($language->abbr);      // Set default language
				
			} else {
				// The current language is updated as non-default language
				
				// Make sure a default language is set,
				// If not don't perform the update and go back.
				$existingDefaultLang = Language::where('default', 1)->where('abbr', '!=', $language->abbr);
				if ($existingDefaultLang->count() <= 0) {
					Alert::warning(trans('admin::messages.The app requires a default language.'))->flash();
					
					return false;
				}
				
			}
		} else {
			if ($language->default == 1 && $language->active != 1) {
				Alert::warning(trans('admin::messages.You cannot disable the default language.'))->flash();
				
				return false;
			}
		}
	}
	
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param Language $language
	 * @return bool
	 */
	public function deleting(Language $language)
	{
		// Check Demo Website
		$this->isDemo();
		
		// Don't delete the default language
		if ($language->abbr == config('appLang.abbr')) {
			Alert::warning(trans('admin::messages.You cannot delete the default language.'))->flash();
			
			return false;
		}
		
		$manager = new LangManager();
		$manager->destroyTranslatedEntries($language->abbr); // Delete all translated entries
		$manager->removeFiles($language->abbr);              // Remove all language files
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param Language $language
	 * @return void
	 */
	public function saved(Language $language)
	{
		// Removing Entries from the Cache
		$this->clearCache($language);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param Language $language
	 * @return void
	 */
	public function deleted(Language $language)
	{
		// Removing Entries from the Cache
		$this->clearCache($language);
	}
	
	
	// PRIVATE METHODS
	
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $language
	 */
	private function clearCache($language)
	{
		Cache::flush();
	}
	
	/**
	 * Check Demo Website
	 *
	 * @return bool|\Illuminate\Http\RedirectResponse
	 */
	private function isDemo()
	{
		if (isDemo()) {
			$message = t('demo_mode_message');
			Alert::error($message)->flash();
			
			return back();
		}
		
		return false;
	}
}

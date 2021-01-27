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

use App\Models\Language;
use Illuminate\Support\Str;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;

trait LangTablesTrait
{
	/**
	 * Translated models with their relations
	 *
	 * @var array
	 */
	private $translatedModels = [
		'PostType'   => [
			['name' => 'Post', 'key' => 'post_type_id'],
		],
		'Category' => [
			['name' => 'Category', 'key' => 'parent_id'],
			['name' => 'Post', 'key' => 'category_id'],
		],
		'Gender'   => [
			['name' => 'User', 'key' => 'gender_id'],
		],
		'Package'  => [
			['name' => 'Payment', 'key' => 'package_id'],
		],
		'ReportType',
		'Page',
		'SalaryType' => [
			['name' => 'Post', 'key' => 'salary_type_id'],
		],
		'MetaTag',
	];
	
	/**
	 * Get models namespace
	 *
	 * @var string
	 */
	private $namespace = '\\App\Models\\';
	
	/**
	 * @return array
	 */
	public function getTranslatedModels()
	{
		// Core translated models
		$models = $this->translatedModels;
		
		// Domain Mapping plugin translated models
		if (config('plugins.domainmapping.installed')) {
			$models[] = '\\App\Plugins\domainmapping\app\Models\DomainMetaTag';
		}
		
		return $models;
	}
	
	/**
	 * CREATING - Copy translated entries
	 *
	 * @param $defaultLangAbbr
	 * @param $abbr
	 */
	public function copyTranslatedEntries($defaultLangAbbr, $abbr)
	{
		// (If exist...)
		// Delete all the translations related to the new language
		$this->destroyTranslatedEntries($abbr);
		
		// Create Translated Models entries
		foreach($this->getTranslatedModels() as $name => $relations) {
			// Fix models without relations
			if (is_numeric($name) && is_string($relations)) {
				$name = $relations;
			}
			
			// Get model full name (with the namespace)
			if (Str::contains($name, '\\')) {
				$model = $name;
			} else {
				$model = $this->namespace . $name;
			}
			
			// Get the model's main entries
			$mainEntries = $model::where('translation_lang', strtolower($defaultLangAbbr))->get();
			if ($mainEntries->count() > 0) {
				foreach($mainEntries as $entry) {
					$newEntryInfo = $entry->toArray();
					$newEntryInfo['translation_lang'] = strtolower($abbr);
					
					// If the current Model is 'Category', Then ...
					// Make the 'slug' column unique using the new language code (abbr)
					if ($name == 'Category') {
						$newEntryInfo['slug'] = $newEntryInfo['slug'] . '-' . strtolower($abbr);
					}
					
					// Save newEntry to database
					$newEntry = new $model($newEntryInfo);
					$newEntry->save();
				}
			}
		}
	}
	
	/**
	 * UPDATING - Update translated entries
	 *
	 * @param $abbr
	 */
	public function updateTranslatedEntries($abbr)
	{
		// Update Translated Models entries
		foreach($this->getTranslatedModels() as $name => $relations) {
			// Fix models without relations
			if (is_numeric($name) && is_string($relations)) {
				$name = $relations;
			}
			
			// Get model full name (with the namespace)
			if (Str::contains($name, '\\')) {
				$model = $name;
			} else {
				$model = $this->namespace . $name;
			}
			
			// Get new "translation_of" value with old entries
			$tmpEntries = $model::where('translation_lang', strtolower($abbr))->get();
			$newTid = [];
			if ($tmpEntries->count() > 0) {
				foreach($tmpEntries as $tmp) {
					$newTid[$tmp->translation_of] = $tmp->id;
				}
			}
			
			// Change "translation_of" value with new Default Language
			$entries = $model::all();
			if ($entries->count() > 0) {
				foreach($entries as $entry) {
					if (isset($newTid[$entry->translation_of])) {
						$entry->translation_of = $newTid[$entry->translation_of];
						$entry->save();
					}
				}
			}
			
			// If relation exists, change its foreign key value
			if (isset($relations) && is_array($relations) && !empty($relations)) {
				foreach($relations as $relation) {
					if (!isset($relation) || !isset($relation['key']) || !isset($relation['name'])) {
						continue;
					}
					$relModel = $this->namespace . $relation['name'];
					$relEntries = $relModel::all();
					if ($relEntries->count() > 0) {
						foreach($relEntries as $relEntry) {
							if (isset($newTid[$relEntry->{$relation['key']}])) {
								// Update the relation entry
								$relEntry->{$relation['key']} = $newTid[$relEntry->{$relation['key']}];
								$relEntry->save();
							}
						}
					}
				}
			}
		}
	}
	
	/**
	 * DELETING - Delete translated entries
	 *
	 * @param $abbr
	 */
	public function destroyTranslatedEntries($abbr)
	{
		// Remove Translated Models entries
		foreach($this->getTranslatedModels() as $name => $relations) {
			// Fix models without relations
			if (is_numeric($name) && is_string($relations)) {
				$name = $relations;
			}
			
			// Get model full name (with the namespace)
			if (Str::contains($name, '\\')) {
				$model = $name;
			} else {
				$model = $this->namespace . $name;
			}
			
			// Get the model's main entries
			$translatedEntries = $model::where('translation_lang', strtolower($abbr))->get();
			if ($translatedEntries->count() > 0) {
				foreach($translatedEntries as $entry) {
					// Delete
					$entry->delete();
				}
			}
		}
	}
	
	/**
	 * UPDATING - Set default language (Call this method at last)
	 *
	 * @param $abbr
	 */
	public function setDefaultLanguage($abbr)
	{
		// Unset the old default language
		Language::whereIn('active', [0, 1])->update(['default' => 0]);
		
		// Set the new default language
		Language::where('abbr', $abbr)->update(['default' => 1]);
		
		// Update the Default App Locale
		$this->updateDefaultAppLocale($abbr);
	}
	
	// PRIVATE METHODS
	
	/**
	 * Update the Default App Locale
	 *
	 * @param $locale
	 */
	private function updateDefaultAppLocale($locale)
	{
		if (!DotenvEditor::keyExists('APP_LOCALE')) {
			DotenvEditor::addEmpty();
		}
		DotenvEditor::setKey('APP_LOCALE', $locale);
		DotenvEditor::save();
	}
}
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

namespace App\Models\Traits;


use App\Models\Language;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

trait TranslatedTrait
{
	/*
	|--------------------------------------------------------------------------
	| FUNCTIONS
	|--------------------------------------------------------------------------
	*/
	/**
	 * Fix (or Set) the 'translation_of' field for the default language entry.
	 * NOTE: Call this method from Observer's 'created' event.
	 *
	 * @return $this
	 */
	public function setTranslationOfAttributeFromPrimaryKey()
	{
		// Don't perform this action during languages setup,
		// For performance concerns.
		if (Str::contains(Route::currentRouteAction(), 'Admin\app\Http\Controllers\LanguageController')) {
			return $this;
		}
		
		// Don't perform this action for non-translatable models.
		if (!isTranlatableModel($this)) {
			return $this;
		}
		
		// Perform this action for default language entries only.
		if ($this->translation_lang == config('appLang.abbr')) {
			$this->translation_of = $this->getKey();
			$this->save();
		}
		
		return $this;
	}
	
	/**
	 * Create (or Copy) the current entry in all other languages.
	 * NOTE: Call this method from Observer's 'created' event.
	 *
	 * @return array
	 */
	public function createNonTranslatableFieldsInOtherLanguages()
	{
		$createdIds = [];
		
		// Don't perform this action during languages setup,
		// For performance concerns.
		if (Str::contains(Route::currentRouteAction(), 'Admin\app\Http\Controllers\LanguageController')) {
			return $createdIds;
		}
		
		// Don't perform this action for non-translatable models.
		if (!isTranlatableModel($this)) {
			return $createdIds;
		}
		
		// Perform this action for default language entries only, to prevent infinite recursion.
		if ($this->translation_lang == config('appLang.abbr')) {
			// Get values to store in other languages
			$valuesToStore = $this->toArray();
			if (isset($valuesToStore[$this->getKeyName()])) {
				unset($valuesToStore[$this->getKeyName()]);
			}
			
			// Copy-Paste in other languages
			$languages = Language::where('abbr', '!=', $this->translation_lang)->get();
			if ($languages->count() > 0) {
				foreach ($languages as $language) {
					if (!empty($valuesToStore)) {
						$entry = new static();
						foreach ($valuesToStore as $field => $value) {
							// Reject all non fillable fields
							if (!$this->isFillable($field)) {
								continue;
							}
							
							if ($field == 'translation_lang') {
								$value = $language->abbr;
							}
							
							if ($field == 'translation_of') {
								$value = $this->getKey();
							}
							
							if (in_array($field, ['slug'])) {
								$value = $value . '-' . strtolower($language->abbr);
							}
							
							$entry->{$field} = $value;
						}
						$entry->save();
						$createdIds[] = $entry->getKey();
					}
				}
			}
		}
		
		return $createdIds;
	}
	
	/**
	 * Update all languages non-translatable entries fields, from the Default Language data.
	 * NOTE: Call this method from Observer's 'updated' event.
	 *
	 * @return array
	 */
	public function updateNonTranslatableFieldsInOtherLanguages()
	{
		$updatedIds = [];
		
		// For performance concerns,
		// Don't perform this action during languages setup.
		if (Str::contains(Route::currentRouteAction(), 'Admin\app\Http\Controllers\LanguageController')) {
			return $updatedIds;
		}
		
		// Don't perform this action for non-translatable models.
		if (!isTranlatableModel($this)) {
			return $updatedIds;
		}
		
		// Perform this action for default language entries only, to prevent infinite recursion.
		// NOTE: The primary key and the 'translation_of' field value are equal for the default language.
		if ($this->getKey() == $this->translation_of) {
			// Don't select the current translated entry to prevent infinite recursion
			$currentEntryInOtherLanguages = self::where($this->getKeyName(), '!=', $this->getKey())->where('translation_of', $this->translation_of)->get();
			
			// Copy-Paste for all languages
			if (!empty($currentEntryInOtherLanguages)) {
				// Get values to update in other languages
				$valuesToStore = $this->toArray();
				if (isset($valuesToStore[$this->getKeyName()])) {
					unset($valuesToStore[$this->getKeyName()]);
				}
				
				// Get protected fields that will ben not updated in other languages
				$protectedFields = array_merge([$this->getKeyName(), 'translation_lang', 'translation_of'], $this->translatable);
				
				foreach ($currentEntryInOtherLanguages as $entry) {
					$canSave = false;
					foreach ($valuesToStore as $field => $value) {
						// Reject all non fillable fields
						if (!$this->isFillable($field)) {
							continue;
						}
						
						// Don't overwrite translatable data
						if (in_array($field, $protectedFields)) {
							continue;
						}
						
						// Update only if fields value is different
						if (md5($entry->{$field}) != md5($value)) {
							$entry->{$field} = $value;
							$canSave = true;
						}
					}
					
					if ($canSave) {
						$entry->save();
						$updatedIds[] = $entry->getKey();
					}
				}
			}
		}
		
		return $updatedIds;
	}
	
	/**
	 * Delete the entry in other languages.
	 */
	public function deleteEntryInOtherLanguages()
	{
		// Don't perform this action for non-translatable models.
		if (!isTranlatableModel($this)) {
			return;
		}
		
		// Delete all translated entries
		$this->translated()->delete();
	}
	
	/**
	 * Find entry translation by ID.
	 *
	 * @param $id
	 * @param null $locale
	 * @return mixed
	 */
	public static function findTrans($id, $locale = null)
	{
		if (empty($locale)) {
			$locale = config('app.locale');
		}
		
		$entry = static::where('translation_of', $id)->where('translation_lang', $locale)->first();
		
		if (empty($entry)) {
			$entry = static::find($id);
		}
		
		return $entry;
	}
	
	/**
	 * Find entry translation by ID or fail.
	 *
	 * @param $id
	 * @param null $locale
	 * @return mixed
	 */
	public static function findTransOrFail($id, $locale = null)
	{
		$entry = static::findTrans($id, $locale);
		
		abort_if(empty($entry), 404);
		
		return $entry;
	}
	
	
	/*
	|--------------------------------------------------------------------------
	| RELATIONS
	|--------------------------------------------------------------------------
	*/
	public function translated()
	{
		return $this->hasMany(get_called_class(), 'translation_of');
	}
	
	
	/*
	|--------------------------------------------------------------------------
	| SCOPES
	|--------------------------------------------------------------------------
	*/
	public function scopeTrans($builder)
	{
		return $builder->where('translation_lang', config('app.locale'));
	}
	
	public function scopeTransIn($builder, $languageCode)
	{
		return $builder->where('translation_lang', $languageCode);
	}
	
	
	/*
	|--------------------------------------------------------------------------
	| ACCESSORS
	|--------------------------------------------------------------------------
	*/
	public function getTranslationOfAttribute()
	{
		$translationOf = (isset($this->attributes['translation_of'])) ? $this->attributes['translation_of'] : null;
		$entityId = (isset($this->attributes['id'])) ? $this->attributes['id'] : $translationOf;
		
		// Admin panel
		if (request()->segment(1) == admin_uri()) {
			return $translationOf;
		}
		
		// Front
		if (!empty($translationOf)) {
			if ($this->attributes['translation_lang'] == config('appLang.abbr')) {
				return $entityId;
			} else {
				return $translationOf;
			}
		} else {
			return $entityId;
		}
	}
	
	public function getTidAttribute()
	{
		$translationOf = (isset($this->attributes['translation_of'])) ? $this->attributes['translation_of'] : null;
		$entityId = (isset($this->attributes['id'])) ? $this->attributes['id'] : $translationOf;
		
		// Admin panel
		if (request()->segment(1) == admin_uri()) {
			return $translationOf;
		}
		
		// Front
		if (!empty($translationOf)) {
			if ($this->attributes['translation_lang'] == config('appLang.abbr')) {
				return $entityId;
			} else {
				return $translationOf;
			}
		} else {
			return $entityId;
		}
	}
	
	
	/*
	|--------------------------------------------------------------------------
	| MUTATORS
	|--------------------------------------------------------------------------
	*/
	public function setTranslationOfAttribute($value)
	{
		$entityId = (isset($this->attributes['id'])) ? $this->attributes['id'] : null;
		
		if (empty($value)) {
			if ($this->attributes['translation_lang'] == config('appLang.abbr')) {
				$this->attributes['translation_of'] = $entityId;
			} else {
				$this->attributes['translation_of'] = $value;
			}
		} else {
			$this->attributes['translation_of'] = $value;
		}
	}
}

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

namespace Larapen\Admin\app\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;

trait Crud
{
	/*
	|--------------------------------------------------------------------------
	| Methods for ENUM and SELECT crud fields.
	|--------------------------------------------------------------------------
	*/

	public static function getPossibleEnumValues($field_name)
	{
		$instance = new static(); // create an instance of the model to be able to get the table name
		$type = DB::select(DB::raw('SHOW COLUMNS FROM '.DB::getTablePrefix().$instance->getTable().' WHERE Field = "'.$field_name.'"'))[0]->Type;
		preg_match('/^enum\((.*)\)$/', $type, $matches);
		$enum = [];
		$exploded = explode(',', $matches[1]);
		foreach ($exploded as $value) {
			$enum[] = trim($value, "'");
		}

		return $enum;
	}

    public static function getEnumValuesAsAssociativeArray($field_name)
    {
        $instance = new static();
        $enum_values = $instance->getPossibleEnumValues($field_name);

        $array = array_flip($enum_values);

        foreach ($array as $key => $value) {
            $array[$key] = $key;
        }

        return $array;
    }

    public static function isColumnNullable($column_name)
    {
        $instance = new static(); // create an instance of the model to be able to get the table name

        try {
            $sql = "SELECT IS_NULLABLE 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_NAME='" . DB::getTablePrefix() . $instance->getTable() . "' 
                    AND COLUMN_NAME='" . $column_name . "' 
                    AND table_schema='" . env('DB_DATABASE') . "'";
            $answer = DB::select(DB::raw($sql))[0];
        } catch (\Exception $e) {
            return $instance->isColumnNullable2($column_name);
        }

        return $answer->IS_NULLABLE === 'YES';
    }

    public static function isColumnNullable2($column_name)
    {
        // create an instance of the model to be able to get the table name
        $instance = new static();

        // register the enum column type, because Doctrine doesn't support it
        DB::connection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        return ! DB::connection()->getDoctrineColumn($instance->getTable(), $column_name)->getNotnull();
    }

    
	/*
	|--------------------------------------------------------------------------
	| Methods for Fake Fields functionality (used in PageManager).
	|--------------------------------------------------------------------------
	*/

	/**
	 * Add fake fields as regular attributes, even though they are stored as JSON.
	 *
	 * @param array $columns - the database columns that contain the JSONs
	 */
	public function addFakes($columns = ['extras'])
	{
		foreach ($columns as $key => $column) {
			$column_contents = $this->{$column};
            
            if (isValidJson($this->{$column})) {
				$column_contents = json_decode($this->{$column});
			}
            
            if (is_array($column_contents) || is_object($column_contents)) {
                if (count($column_contents)) {
                    foreach ($column_contents as $fake_field_name => $fake_field_value) {
                        $this->setAttribute($fake_field_name, $fake_field_value);
                    }
                }
            }
		}
	}

	/**
	 * Return the entity with fake fields as attributes.
	 *
	 * @param array $columns - the database columns that contain the JSONs
	 *
	 * @return Model
	 */
	public function withFakes($columns = [])
	{
		$model = '\\'.get_class($this);

		if (! count($columns)) {
			$columns = (property_exists($model, 'fakeColumns')) ? $this->fakeColumns : ['extras'];
		}

		$this->addFakes($columns);

		return $this;
	}


	/*
    |--------------------------------------------------------------------------
    | Translation Methods
    |--------------------------------------------------------------------------
    */

	public function translations()
	{
		$model = '\\' . get_class($this);

		if (isset($this->translatable)) {
			return $model::where('translation_of', $this->id)->where('translation_lang', '!=', config('appLang.abbr'))->get();
		}

		return collect();
	}

	// get translations plus current item, plus original
	public function allTranslations()
	{
		$model = '\\' . get_class($this);

		// the translations
		$translations = $this->translations();

		// the current item
		$all_translations = $translations->push($this);

		// the original
		if ($this->translation_of) {
			$original = $model::find($this->translation_of);
			$all_translations = $all_translations->push($original);
		}

		return $all_translations;
	}

	public function translation($translation_lang = false)
	{
		if ($translation_lang == false) {
			$translation_lang = Lang::locale();
		}

		$model = '\\' . get_class($this);
		if (isset($this->translatable)) {
			return $model::where('translation_of', $this->id)->where('translation_lang', $translation_lang)->first();
		}

		return false;
	}

	public function translationLanguages()
	{
		$model = '\\' . get_class($this);
		$translations = $this->translations();

		$translated_in = [];

		if ($translations->count()) {
			foreach ($translations as $key => $translation) {
                // Don't add empty value
                if (!empty($translation->language)) {
                    $translated_in[] = $translation->language;
                }
			}
		}

		return collect($translated_in);
	}

	public function language()
	{
		return $this->belongsTo('\App\Models\Language', 'translation_lang', 'abbr');
	}

	/**
	 * Overwriting the Eloquent save() method, to set a default translation language, if necessary.
	 *
     * @param array $options
     */
	public function save(array $options = [])
	{
		if (isset($this->translatable)) {
			// set a default language (the one the user is currently using)
			if (!(isset($this->translation_lang)) || $this->translation_lang == '') {
				$this->translation_lang = Lang::locale();
			}

			// TODO: if some untranslatable attributes are empty, but its parent's are filled, copy them
		}

		parent::save($options);
	}
    

    /*
    |--------------------------------------------------------------------------
    | Methods for storing uploaded files (used in CRUD).
    |--------------------------------------------------------------------------
    */

    /**
     * Handle file upload and DB storage for a file:
     * - on CREATE
     *     - stores the file at the destination path
     *     - generates a name
     *     - stores the full path in the DB;
     * - on UPDATE
     *     - if the value is null, deletes the file and sets null in the DB
     *     - if the value is different, stores the different file and updates DB value.
     *
     * @param  [type] $value            Value for that column sent from the input.
     * @param  [type] $attribute_name   Model attribute name (and column in the db).
     * @param  [type] $disk             Filesystem disk used to store files.
     * @param  [type] $destination_path Path in disk where to store the files.
     */
    public function uploadFileToDisk($value, $attribute_name, $disk, $destination_path)
    {
        $request = \Request::instance();

        // if a new file is uploaded, delete the file from the disk
        if ($request->hasFile($attribute_name) &&
            $this->{$attribute_name} &&
            $this->{$attribute_name} != null) {
            Storage::disk($disk)->delete($this->{$attribute_name});
            $this->attributes[$attribute_name] = null;
        }

        // if the file input is empty, delete the file from the disk
        if (is_null($value) && $this->{$attribute_name} != null) {
            Storage::disk($disk)->delete($this->{$attribute_name});
            $this->attributes[$attribute_name] = null;
        }

        // if a new file is uploaded, store it on disk and its filename in the database
        if ($request->hasFile($attribute_name) && $request->file($attribute_name)->isValid()) {

            // 1. Generate a new file name
            $file = $request->file($attribute_name);
            $new_file_name = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();

            // 2. Move the new file to the correct path
            $file_path = $file->storeAs($destination_path, $new_file_name, $disk);

            // 3. Save the complete path to the database
            $this->attributes[$attribute_name] = $file_path;
        }
    }
	
	public function uploadFileToDiskCustom($value, $field_name, $attribute_name, $disk, $destination_path)
	{
		$request = \Request::instance();
		
		// if a new file is uploaded, delete the file from the disk
		if ($request->hasFile($field_name) &&
			$this->{$attribute_name} &&
			$this->{$attribute_name} != null) {
			Storage::disk($disk)->delete($this->{$attribute_name});
			$this->attributes[$attribute_name] = null;
		}
		
		// if the file input is empty, delete the file from the disk
		if (is_null($value) && $this->{$attribute_name} != null) {
			Storage::disk($disk)->delete($this->{$attribute_name});
			$this->attributes[$attribute_name] = null;
		}
		
		// if a new file is uploaded, store it on disk and its filename in the database
		if ($request->hasFile($field_name) && $request->file($field_name)->isValid()) {
			
			// 1. Generate a new file name
			$file = $request->file($field_name);
			$new_file_name = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
			
			// 2. Move the new file to the correct path
			$file_path = $file->storeAs($destination_path, $new_file_name, $disk);
			
			// 3. Save the complete path to the database
			$this->attributes[$attribute_name] = $file_path;
		}
	}

    /**
     * Handle multiple file upload and DB storage:
     * - if files are sent
     *     - stores the files at the destination path
     *     - generates random names
     *     - stores the full path in the DB, as JSON array;
     * - if a hidden input is sent to clear one or more files
     *     - deletes the file
     *     - removes that file from the DB.
     *
     * @param  [type] $value            Value for that column sent from the input.
     * @param  [type] $attribute_name   Model attribute name (and column in the db).
     * @param  [type] $disk             Filesystem disk used to store files.
     * @param  [type] $destination_path Path in disk where to store the files.
     */
    public function uploadMultipleFilesToDisk($value, $attribute_name, $disk, $destination_path)
    {
        $request = \Request::instance();
        $attribute_value = (array) $this->{$attribute_name};
        $files_to_clear = $request->get('clear_'.$attribute_name);

        // if a file has been marked for removal,
        // delete it from the disk and from the db
        if ($files_to_clear) {
            $attribute_value = (array) $this->{$attribute_name};
            foreach ($files_to_clear as $key => $filename) {
                Storage::disk($disk)->delete($filename);
                $attribute_value = Arr::where($attribute_value, function ($value, $key) use ($filename) {
                    return $value != $filename;
                });
            }
        }

        // if a new file is uploaded, store it on disk and its filename in the database
        if ($request->hasFile($attribute_name)) {
            foreach ($request->file($attribute_name) as $file) {
                if ($file->isValid()) {
                    // 1. Generate a new file name
                    $new_file_name = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();

                    // 2. Move the new file to the correct path
                    $file_path = $file->storeAs($destination_path, $new_file_name, $disk);

                    // 3. Add the public path to the database
                    $attribute_value[] = $file_path;
                }
            }
        }

        $this->attributes[$attribute_name] = json_encode($attribute_value);
    }
    

    /*
    |--------------------------------------------------------------------------
    | Methods for ALL models
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the attribute exists
     * @param $attr
     * @return bool
     */
    public function hasAttribute($attr)
    {
        return array_key_exists($attr, $this->attributes);
    }
	
	/**
	 * @param bool $xPanel
	 * @return string
	 */
	public function bulkDeleteBtn($xPanel = false)
	{
		// Button
		$out = '';
		$out .= '<button id="bulkDeleteBtn" class="btn btn-danger">';
		$out .= '<i class="fa fa-remove"></i> ';
		$out .= trans('admin::messages.Delete Selected Items');
		$out .= '</button>';
		
		return $out;
	}
}

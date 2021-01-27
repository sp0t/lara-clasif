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

namespace App\Models;

use App\Helpers\Files\Storage\StorageDisk;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\LocalizedScope;
use App\Observer\ResumeObserver;
use Larapen\Admin\app\Models\Crud;

class Resume extends BaseModel
{
	use Crud;
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'resumes';
	
	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	// protected $primaryKey = 'id';
	
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var boolean
	 */
	public $timestamps = true;
	
	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = ['id'];
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['country_code', 'user_id', 'name', 'filename', 'active'];
	
	/**
	 * The attributes that should be hidden for arrays
	 *
	 * @var array
	 */
	// protected $hidden = [];
	
	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['created_at', 'updated_at'];
	
	/*
	|--------------------------------------------------------------------------
	| FUNCTIONS
	|--------------------------------------------------------------------------
	*/
	protected static function boot()
	{
		parent::boot();
		
		Resume::observe(ResumeObserver::class);
		
		static::addGlobalScope(new ActiveScope());
		static::addGlobalScope(new LocalizedScope());
	}
	
	/*
	|--------------------------------------------------------------------------
	| RELATIONS
	|--------------------------------------------------------------------------
	*/
	public function post()
	{
		return $this->hasMany(Post::class);
	}
	
	public function user()
	{
		return $this->belongsToMany(User::class, 'user_id', 'id');
	}
	
	/*
	|--------------------------------------------------------------------------
	| SCOPES
	|--------------------------------------------------------------------------
	*/
	
	/*
	|--------------------------------------------------------------------------
	| ACCESSORS
	|--------------------------------------------------------------------------
	*/
	public function getNameAttribute()
	{
		$value = null;
		
		if (isset($this->attributes) && isset($this->attributes['name'])) {
			$value = $this->attributes['name'];
		}
		
		if (empty($value)) {
			$value = last(explode('/', $this->attributes['filename']));
		}
		
		return $value;
	}
	
	public function getFilenameAttribute()
	{
		if (!isset($this->attributes) || !isset($this->attributes['filename'])) {
			return null;
		}
		
		$value = $this->attributes['filename'];
		
		// Fix path
		$value = str_replace('uploads/resumes/', '', $value);
		$value = str_replace('resumes/', '', $value);
		$value = 'resumes/' . $value;
		
		$disk = StorageDisk::getDisk();
		
		if (!$disk->exists($value)) {
			return null;
		}
		
		return $value;
	}
	
	/*
	|--------------------------------------------------------------------------
	| MUTATORS
	|--------------------------------------------------------------------------
	*/
	public function setFilenameAttribute($value)
	{
		$diskName = StorageDisk::getDiskName();
		$field_name = 'resume.filename';
		$attribute_name = 'filename';
		
		// Set the right field name
		$request = \Request::instance();
		if (!$request->hasFile($field_name)) {
			$field_name = $attribute_name;
		}
		
		if (!isset($this->country_code) || !isset($this->user_id)) {
			$this->attributes[$attribute_name] = null;
			return false;
		}
		
		// Path
		$destination_path = 'resumes/' . strtolower($this->country_code) . '/' . $this->user_id;
		
		// Upload
		$this->uploadFileToDiskCustom($value, $field_name, $attribute_name, $diskName, $destination_path);
	}
}

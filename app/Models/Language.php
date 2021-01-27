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

use App\Models\Scopes\ActiveScope;
use App\Observer\LanguageObserver;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Larapen\Admin\app\Models\Crud;
use Larapen\Admin\app\Models\LanguageFeatures;

class Language extends BaseModel
{
	use Crud, LanguageFeatures, Sluggable, SluggableScopeHelpers;
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'languages';
	
	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'abbr';
	public $incrementing = false;
	
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var boolean
	 */
	public $timestamps = false;
	
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
	protected $fillable = [
		'abbr',
		'locale',
		'name',
		'native',
		'flag',
		'app_name',
		'script',
		'direction',
		'russian_pluralization',
		'active',
		'default',
		'parent_id',
		'lft',
		'rgt',
		'depth',
	];
	
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
	// protected $dates = [];
	
	/*
	|--------------------------------------------------------------------------
	| FUNCTIONS
	|--------------------------------------------------------------------------
	*/
	protected static function boot()
	{
		parent::boot();
		
		Language::observe(LanguageObserver::class);
		
		static::addGlobalScope(new ActiveScope());
	}
	
	/**
	 * Return the sluggable configuration array for this model.
	 *
	 * @return array
	 */
	public function sluggable()
	{
		return [
			'app_name' => [
				'source' => 'app_name_or_name',
			],
		];
	}
	
	/**
	 * @param bool $xPanel
	 * @return string
	 */
	public function syncFilesLinesBtn($xPanel = false)
	{
		$url = admin_url('languages/sync_files');
		
		$msg = trans('admin::messages.Fill the missing lines in all languages files from the master language.');
		$tooltip = ' data-toggle="tooltip" title="' . $msg . '"';
		
		// Button
		$out = '';
		$out .= '<a class="btn btn-success" href="' . $url . '"' . $tooltip . '>';
		$out .= '<i class="fa fa-exchange"></i> ';
		$out .= trans('admin::messages.Sync. Languages Files Lines');
		$out .= '</a>';
		
		return $out;
	}
	
	public function getNameHtml()
	{
		$currentUrl = preg_replace('#/(search)$#', '', url()->current());
		$url = $currentUrl . '/' . $this->getKey() . '/edit';
		
		$out = '<a href="' . $url . '">' . $this->name . '</a>';
		
		return $out;
	}
	
	public function getDefaultHtml()
	{
		return checkboxDisplay($this->default);
	}
	
	/*
	|--------------------------------------------------------------------------
	| RELATIONS
	|--------------------------------------------------------------------------
	*/
	
	/*
	|--------------------------------------------------------------------------
	| SCOPES
	|--------------------------------------------------------------------------
	*/
	public function scopeActive($query)
	{
		return $query->where('active', 1);
	}
	
	/*
	|--------------------------------------------------------------------------
	| ACCESSORS
	|--------------------------------------------------------------------------
	*/
	public function getIdAttribute($value)
	{
		return $this->attributes['abbr'];
	}
	
	// The app_name is created automatically from the "name" field if no app_name exists.
	public function getAppNameOrNameAttribute()
	{
		if ($this->app_name != '') {
			return $this->app_name;
		}
		return $this->name;
	}
	
	public function getNativeAttribute($value)
	{
		if ($value != '') {
			return $value;
		}
		return $this->attributes['name'];
	}
	
	/*
	|--------------------------------------------------------------------------
	| MUTATORS
	|--------------------------------------------------------------------------
	*/
}

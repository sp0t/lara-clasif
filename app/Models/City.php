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

use App\Helpers\Number;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\LocalizedScope;
use App\Models\Traits\CountryTrait;
use App\Observer\CityObserver;
use Larapen\Admin\app\Models\Crud;

class City extends BaseModel
{
	use Crud, CountryTrait;
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'cities';
	
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
	// protected $guarded = ['id'];
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'id',
		'country_code',
		'name',
		'asciiname',
		'latitude',
		'longitude',
		'subadmin1_code',
		'subadmin2_code',
		'population',
		'time_zone',
		'active',
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
	protected $dates = ['created_at', 'updated_at'];
	
	/*
	|--------------------------------------------------------------------------
	| FUNCTIONS
	|--------------------------------------------------------------------------
	*/
	protected static function boot()
	{
		parent::boot();
		
		City::observe(CityObserver::class);
		
		static::addGlobalScope(new ActiveScope());
		static::addGlobalScope(new LocalizedScope());
	}
	
	public function getAdmin2Html()
	{
		$out = $this->subadmin2_code;
		
		if (isset($this->subAdmin2) && !empty($this->subAdmin2)) {
			$out = $this->subAdmin2->name;
		}
		
		return $out;
	}
	
	public function getAdmin1Html()
	{
		$out = $this->subadmin1_code;
		
		if (isset($this->subAdmin1) && !empty($this->subAdmin1)) {
			$out = $this->subAdmin1->name;
		}
		
		return $out;
	}
	
	/*
	|--------------------------------------------------------------------------
	| RELATIONS
	|--------------------------------------------------------------------------
	*/
	public function posts()
	{
		return $this->hasMany(Post::class, 'city_id');
	}
	
	public function subAdmin2()
	{
		return $this->belongsTo(SubAdmin2::class, 'subadmin2_code', 'code');
	}
	
	public function subAdmin1()
	{
		return $this->belongsTo(SubAdmin1::class, 'subadmin1_code', 'code');
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
	public function getAsciinameAttribute($value)
	{
		return preg_replace(['#\s\s+#ui', '#\' #ui'], [' ', "'"], $value);
	}
	
	/**
	 * @param $value
	 * @return float
	 */
	public function getLatitudeAttribute($value)
	{
		return Number::toFloat($value);
	}
	
	/**
	 * @param $value
	 * @return float
	 */
	public function getLongitudeAttribute($value)
	{
		return Number::toFloat($value);
	}
	
	/*
	|--------------------------------------------------------------------------
	| MUTATORS
	|--------------------------------------------------------------------------
	*/
}

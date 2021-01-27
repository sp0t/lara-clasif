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

use App\Models\Scopes\LocalizedScope;
use App\Models\Traits\CountryTrait;
use App\Observer\SubAdmin1Observer;
use Larapen\Admin\app\Models\Crud;

class SubAdmin1 extends BaseModel
{
    use Crud, CountryTrait;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subadmin1';
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'code';
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
    protected $fillable = ['country_code', 'code', 'name', 'asciiname', 'active'];
    
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
	
		SubAdmin1::observe(SubAdmin1Observer::class);
	
		static::addGlobalScope(new LocalizedScope());
    }
	
	public function getNameHtml()
	{
		$currentUrl = preg_replace('#/(search)$#', '', url()->current());
		$editUrl = $currentUrl . '/' . $this->code . '/edit';
		
		$out = '<a href="' . $editUrl . '">' . $this->asciiname . '</a>';
		
		return $out;
	}
	
	public function adminDivisions2Btn($xPanel = false)
	{
		$url = admin_url('admins1/' . $this->code . '/admins2');
		
		$msg = trans('admin::messages.Admin. Divisions 2 of :admin_division1', ['admin_division1' => $this->asciiname]);
		$toolTip = ' data-toggle="tooltip" title="' . $msg . '"';
		
		$out = '';
		$out .= '<a class="btn btn-xs btn-default" href="' . $url . '"' . $toolTip . '>';
		$out .= '<i class="fa fa-eye"></i> ';
		$out .= mb_ucfirst(trans('admin::messages.admin. divisions 2'));
		$out .= '</a>';
		
		return $out;
	}
	
	public function citiesBtn($xPanel = false)
	{
		$url = admin_url('admins1/' . $this->code . '/cities');
		
		$msg = trans('admin::messages.Cities of :admin_division1', ['admin_division1' => $this->asciiname]);
		$toolTip = ' data-toggle="tooltip" title="' . $msg . '"';
		
		$out = '';
		$out .= '<a class="btn btn-xs btn-default" href="' . $url . '"' . $toolTip . '>';
		$out .= '<i class="fa fa-eye"></i> ';
		$out .= mb_ucfirst(trans('admin::messages.cities'));
		$out .= '</a>';
		
		return $out;
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
    
    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */
    public function getIdAttribute($value)
    {
        return $this->attributes['code'];
    }
    
    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}

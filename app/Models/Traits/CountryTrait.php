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

use App\Models\Country;
use App\Models\TimeZone;

trait CountryTrait
{
    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
	public function getCountryHtml()
	{
		$out = '';
		
		if (isset($this->country_code)) {
			$countryName = (isset($this->country) && isset($this->country->asciiname)) ? $this->country->asciiname : null;
			$countryName = (empty($countryName) && isset($this->country) && isset($this->country->name)) ? $this->country->name : $countryName;
			$countryName = (!empty($countryName)) ? $countryName : $this->country_code;
			
			$iconPath = 'images/flags/16/' . strtolower($this->country_code) . '.png';
			if (file_exists(public_path($iconPath))) {
				$out = '';
				$out .= '<a href="' . localUrl($this->country_code, '', true) . '" target="_blank">';
				$out .= '<img src="' . url($iconPath) . getPictureVersion() . '" data-toggle="tooltip" title="' . $countryName . '">';
				$out .= '</a>';
				
				return $out;
			} else {
				return $this->country_code;
			}
		}
		
		return $out;
	}
    
    
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }
    
    public function timeZone()
    {
        return $this->hasOne(TimeZone::class, 'country_code', 'country_code');
    }
    
    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    public function scopeCurrentCountry($builder)
    {
        return $builder->where('country_code', config('country.code'));
    }
    
    public function scopeCountryOf($builder, $countryCode)
    {
        return $builder->where('country_code', $countryCode);
    }
    
    
    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */
    
    
    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}

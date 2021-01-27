<?php

namespace Larapen\LaravelDistance;


class Helper
{
	/**
	 * Check if a country is a miles using country
	 *
	 * @param $countryCode
	 * @return bool
	 */
	public static function isMilesUsingCountry($countryCode)
	{
		if (in_array($countryCode, (array)config('distance.mileUseCountries'))) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Get the Distance Calculation Unit
	 *
	 * @param $countryCode
	 * @return string
	 */
	public static function getDistanceUnit($countryCode)
	{
		$unit = 'km';
		if (self::isMilesUsingCountry($countryCode)) {
			$unit = 'mi';
		}
		
		return $unit;
	}
}

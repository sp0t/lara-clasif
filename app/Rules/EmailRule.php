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

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class EmailRule implements Rule
{
	/**
	 * Determine if the validation rule passes.
	 *
	 * NOTE: Laravel's email validator says that *@* is valid e-mail address
	 *
	 * @param  string  $attribute
	 * @param  mixed  $value
	 * @return bool
	 */
	public function passes($attribute, $value)
	{
		$value = strtolower($value);
		
		if (!$this->simpleEmailAddressCheck($value)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message()
	{
		return trans('validation.email');
	}
	
	/**
	 * Custom email address validation
	 *
	 * NOTE: The 'filter_var($value, FILTER_VALIDATE_EMAIL)' function doesn't validate "not latin" domains.
	 *
	 * @param $value
	 * @return bool
	 */
	private function simpleEmailAddressCheck($value)
	{
		$posAtSign = strpos($value, '@');
		$posDot = strrpos($value, '.'); // Get the latest dot (.) position.
		
		return ($posAtSign !== false && $posDot !== false && $posDot > $posAtSign);
	}
}

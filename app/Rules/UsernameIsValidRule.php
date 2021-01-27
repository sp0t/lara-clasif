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

class UsernameIsValidRule implements Rule
{
	/**
	 * Determine if the validation rule passes.
	 *
	 * @param  string  $attribute
	 * @param  mixed  $value
	 * @return bool
	 */
	public function passes($attribute, $value)
	{
		$value = trim(strtolower($value));
		
		if ($this->hasInvalidCharacter($value)) {
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
		return trans('validation.username_is_valid_rule');
	}
	
	/* PRIVATES */
    
    /**
     * Determine whether the given username contains a non-alphanumeric character
	 * or only numeric characters (to prevent a phone number field).
     *
     * @param $value
     * @return bool
     */
    private function hasInvalidCharacter($value)
    {
		if (!ctype_alnum($value) || is_numeric($value)) {
            return true;
        }

        return false;
    }
}

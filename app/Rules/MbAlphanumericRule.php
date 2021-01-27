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

class MbAlphanumericRule implements Rule
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
		$value = trim(strip_tags($value));
		
		// Regex - Unicode character properties
		// https://www.php.net/manual/en/regexp.reference.unicode.php
		$pattern = [
			'\p{N}', // Number
			'\p{M}', // Mark
			'\p{P}', // Punctuation
			'\p{S}', // Symbol
			'\p{Z}', // Separator
		];
		$pattern = implode('', $pattern);
		$pattern = '/[^' . $pattern . ']/ui';
		
		// Check if the string contains characters other than the regex anti-rule (^)
		if (preg_match($pattern, $value)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message()
	{
		return trans('validation.mb_alphanumeric_rule');
	}
}

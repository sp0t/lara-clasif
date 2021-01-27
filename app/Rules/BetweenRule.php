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

class BetweenRule implements Rule
{
	public $min = 0;
	public $max = 999999;
	
	public function __construct($min, $max)
	{
		$this->min = $min;
		$this->max = $max;
	}
	
	/**
	 * Determine if the validation rule passes.
	 * Multi-bytes version of the Laravel "between" rule.
	 *
	 * @param  string  $attribute
	 * @param  mixed  $value
	 * @return bool
	 */
	public function passes($attribute, $value)
	{
		$value = strip_tags($value);
		
		if (mb_strlen($value) < $this->min) {
			return false;
		} else {
			if (mb_strlen($value) > $this->max) {
				return false;
			}
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
		return trans('validation.between_rule', ['min' => $this->min, 'max' => $this->max]);
	}
}

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

namespace App\Http\Requests\Admin;

use App\Rules\BetweenRule;
use App\Rules\LocaleOfLanguageRule;

class LanguageRequest extends Request
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'abbr'   => ['required', 'min:2', 'max:10'],
			'name'   => ['required', new BetweenRule(2, 255)],
			'native' => ['required', new BetweenRule(2, 255)],
			'locale' => ['required', 'min:2', 'max:20', new LocaleOfLanguageRule($this->abbr)],
		];
	}
	
	/**
	 * Extend the default getValidatorInstance method
	 * so fields can be modified or added before validation
	 *
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
	protected function getValidatorInstance()
	{
		// Add new data field before it gets sent to the validator
		$languages = (array)config('languages');
		$abbr = $this->abbr;
		
		$input = [];
		$input['name'] = (isset($languages[$abbr])) ? $languages[$abbr] : mb_ucfirst($abbr);
		if (!isset($this->native) || empty($this->native)) {
			$input['native'] = $input['name'];
		}
		
		request()->merge($input); // Required!
		$this->merge($input);
		
		// Fire the parent getValidatorInstance method
		return parent::getValidatorInstance();
	}
}

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

namespace App\Http\Requests;

use App\Rules\BetweenRule;
use App\Rules\BlacklistTitleRule;
use App\Rules\BlacklistWordRule;

class CompanyRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Validation Rules
		$rules = [
			'company.name'        => ['required', new BetweenRule(2, 200), new BlacklistTitleRule()],
			'company.description' => ['required', new BetweenRule(5, 1000), new BlacklistWordRule()],
		];
	
		// Check 'logo' is required
		if ($this->hasFile('company.logo')) {
			$rules['company.logo'] = [
				'required',
				'image',
				'mimes:' . getUploadFileTypes('image'),
				'max:' . (int)config('settings.upload.max_image_size', 1000)
			];
		}
        
        return $rules;
    }
}

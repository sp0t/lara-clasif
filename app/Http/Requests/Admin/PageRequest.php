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

class PageRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
		$rules = [
			'name'    => ['required', 'min:2', 'max:255'],
			'title'   => ['max:255'],
			'content' => ['max:65000'],
		];
	
		if ($this->filled('external_link')) {
			$rules['external_link'] = ['url'];
		} else {
			$rules['title'][] = 'required';
			$rules['title'][] = 'min:2';
			$rules['content'][] = 'required';
		}
	
		return $rules;
    }
}

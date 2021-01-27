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

use Illuminate\Validation\Rule;

class MetaTagRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
		$rules = [
			'page'        => ['required'],
			'title'       => ['required', 'max:200'],
			'description' => ['required', 'max:255'],
			'keywords'    => ['max:255'],
		];
	
		if (in_array($this->method(), ['POST', 'CREATE'])) {
			// Unique with additional Where Clauses
			$uniquePage = Rule::unique('meta_tags')->where(function ($query) {
				return $query->where('page', $this->page)->where('translation_lang', config('app.locale'));
			});
		
			$rules['page'][] = $uniquePage;
		}
	
		return $rules;
    }
	
	/**
	 * @return array
	 */
	public function messages()
	{
		$messages = [];
		
		$messages['page.unique'] = trans('admin::messages.A meta-tag entry already exists for this page.');
		
		return $messages;
	}
}

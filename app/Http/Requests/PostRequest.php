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

use App\Models\Package;
use App\Rules\BetweenRule;
use App\Rules\BlacklistDomainRule;
use App\Rules\BlacklistEmailRule;
use App\Rules\BlacklistTitleRule;
use App\Rules\BlacklistWordRule;
use App\Rules\EmailRule;
use App\Rules\MbAlphanumericRule;

class PostRequest extends Request
{
	public static $packages;
	public static $paymentMethods;
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		$cat = null;
		
		$rules = [
			'category_id'    => ['required', 'not_in:0'],
			'post_type_id'   => ['required', 'not_in:0'],
			'title'          => ['required', new BetweenRule(2, 150), new MbAlphanumericRule(), new BlacklistTitleRule()],
			'description'    => ['required', new BetweenRule(5, 12000), new MbAlphanumericRule(), new BlacklistWordRule()],
			'salary_type_id' => ['required', 'not_in:0'],
			'contact_name'   => ['required', new BetweenRule(2, 200)],
			'email'          => ['max:100', new BlacklistEmailRule(), new BlacklistDomainRule()],
			'phone'          => ['max:20'],
			'city_id'        => ['required', 'not_in:0'],
		];
		
		// CREATE
		if (in_array($this->method(), ['POST', 'CREATE'])) {
			// $rules['parent_id'] = ['required', 'not_in:0'];
			
			if (config('settings.single.publication_form_type') == '2') {
				// Package & PaymentMethod
				if (isset(self::$packages) and isset(self::$paymentMethods) and self::$packages->count() > 0 and self::$paymentMethods->count() > 0) {
					// Require 'package_id' if Packages are available
					$rules['package_id'] = 'required';
					
					// Require 'payment_method_id' if the selected package's price > 0
					if ($this->has('package_id')) {
						$package = Package::find($this->input('package_id'));
						if (!empty($package) and $package->price > 0) {
							$rules['payment_method_id'] = 'required|not_in:0';
						}
					}
				}
			}
			
			// reCAPTCHA
			$rules = $this->recaptchaRules($rules);
		}
		
		// UPDATE
		// if (in_array($this->method(), ['PUT', 'PATCH', 'UPDATE'])) {}
		
		// COMMON
		
		// Location
		if (in_array(config('country.admin_type'), ['1', '2']) && config('country.admin_field_active') == 1) {
			$rules['admin_code'] = ['required', 'not_in:0'];
		}
		
		// Email
		if ($this->filled('email')) {
			$rules['email'][] = 'email';
			$rules['email'][] = new EmailRule();
		}
		if (isEnabledField('email')) {
			if (isEnabledField('phone') && isEnabledField('email')) {
				if (auth()->check()) {
					$rules['email'][] = 'required_without:phone';
				} else {
					// Email address is required for Guests
					$rules['email'][] = 'required';
				}
			} else {
				$rules['email'][] = 'required';
			}
		}
		
		// Phone
		if (config('settings.sms.phone_verification') == 1) {
			if ($this->filled('phone')) {
				$countryCode = $this->input('country_code', config('country.code'));
				if ($countryCode == 'UK') {
					$countryCode = 'GB';
				}
				$rules['phone'][] = 'phone:' . $countryCode;
			}
		}
		if (isEnabledField('phone')) {
			if (isEnabledField('phone') && isEnabledField('email')) {
				$rules['phone'][] = 'required_without:email';
			} else {
				$rules['phone'][] = 'required';
			}
		}
		
		// Company
		if (!$this->filled('company_id') || empty($this->input('company_id'))) {
			$rules['company.name'] = ['required', new BetweenRule(2, 200), new BlacklistTitleRule()];
			$rules['company.description'] = ['required', new BetweenRule(5, 1000), new BlacklistWordRule()];
			
			// Check 'logo' is required
			if ($this->file('logo')) {
				$rules['logo'] = [
					'required',
					'image',
					'mimes:' . getUploadFileTypes('image'),
					'max:' . (int)config('settings.upload.max_image_size', 1000),
				];
			}
		} else {
			$rules['company_id'] = ['required', 'not_in:0'];
		}
		
		// Application URL
		if ($this->filled('application_url')) {
			$rules['application_url'] = ['url'];
		}
		
		/*
		 * Tags (Only allow letters, numbers, spaces and ',;_-' symbols)
		 *
		 * Explanation:
		 * [] 	=> character class definition
		 * p{L} => matches any kind of letter character from any language
		 * p{N} => matches any kind of numeric character
		 * _- 	=> matches underscore and hyphen
		 * + 	=> Quantifier — Matches between one to unlimited times (greedy)
		 * /u 	=> Unicode modifier. Pattern strings are treated as UTF-16. Also causes escape sequences to match unicode characters
		 */
		if ($this->filled('tags')) {
			$rules['tags'] = ['regex:/^[\p{L}\p{N} ,;_-]+$/u'];
		}
		
		return $rules;
	}
	
	/**
	 * @return array
	 */
	public function messages()
	{
		$messages = [];
		
		// Category & Sub-Category
		if ($this->filled('parent_id') && !empty($this->input('parent_id'))) {
			$messages['category_id.required'] = t('The :field is required.', ['field' => mb_strtolower(t('Sub-Category'))]);
			$messages['category_id.not_in'] = t('The :field is required.', ['field' => mb_strtolower(t('Sub-Category'))]);
		}
		
		if (config('settings.single.publication_form_type') == '2') {
			// Package & PaymentMethod
			$messages['package_id.required'] = trans('validation.required_package_id');
			$messages['payment_method_id.required'] = t('validation.required_payment_method_id');
			$messages['payment_method_id.not_in'] = t('validation.required_payment_method_id');
		}
		
		return $messages;
	}
}

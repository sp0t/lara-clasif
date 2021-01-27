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

use App\Helpers\RemoveFromString;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mews\Purifier\Facades\Purifier;

abstract class Request extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}
	
	/**
	 * Extend the default getValidatorInstance method
	 * so fields can be modified or added before validation
	 *
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
	protected function getValidatorInstance()
	{
		// Don't apply this to the Admin Panel
		if (!isFromAdminPanel()) {
			// $input = [];
			$input = $this->all();
			
			// title
			if ($this->filled('title')) {
				$input['title'] = strCleanerLite($this->input('title'));
				$input['title'] = onlyNumCleaner($input['title']);
				$input['title'] = RemoveFromString::contactInfo($input['title'], true);
			}
			
			// name
			if ($this->filled('name')) {
				$input['name'] = strCleanerLite($this->input('name'));
				if (
					Str::contains(get_called_class(), 'PostRequest')
					|| Str::contains(get_called_class(), 'UserRequest')
				) {
					$input['name'] = onlyNumCleaner($input['name']);
				}
			}
			
			// contact_name
			if ($this->filled('contact_name')) {
				$input['contact_name'] = strCleanerLite($this->input('contact_name'));
				$input['contact_name'] = onlyNumCleaner($input['contact_name']);
			}
			
			// company.name
			if ($this->filled('company.name')) {
				$input['company']['name'] = $this->input('company.name');
				$input['company']['name'] = onlyNumCleaner($input['company']['name']);
				$input['company']['name'] = RemoveFromString::contactInfo($input['company']['name'], true);
			}
			
			// company.description
			if ($this->filled('company.description')) {
				$input['company']['description'] = $this->input('company.description');
				$input['company']['description'] = onlyNumCleaner($input['company']['description']);
				$input['company']['description'] = RemoveFromString::contactInfo($input['company']['description'], true);
			}
			
			// description
			if ($this->filled('description')) {
				$input['description'] = $this->input('description');
				if (
					Str::contains(get_called_class(), 'PostRequest')
					|| Str::contains(get_called_class(), 'ResumeRequest')
				) {
					$input['description'] = onlyNumCleaner($input['description']);
				}
				if (config('settings.single.simditor_wysiwyg')) {
					try {
						$input['description'] = Purifier::clean($input['description']);
					} catch (\Exception $e) {
					}
				} else {
					$input['description'] = strCleaner($input['description']);
				}
				$input['description'] = RemoveFromString::contactInfo($input['description'], true);
			}
			
			// salary_min
			if ($this->filled('salary_min')) {
				$input['salary_min'] = str_replace(',', '.', $this->input('salary_min'));
				$input['salary_min'] = preg_replace('/[^0-9\.]/', '', $input['salary_min']);
			}
			
			// salary_max
			if ($this->filled('salary_max')) {
				$input['salary_max'] = str_replace(',', '.', $this->input('salary_max'));
				$input['salary_max'] = preg_replace('/[^0-9\.]/', '', $input['salary_max']);
			}
			
			// phone
			if ($this->filled('phone')) {
				$input['phone'] = phoneFormatInt($this->input('phone'), $this->input('country_code', session('country_code')));
			}
			
			// login (phone)
			if ($this->filled('login')) {
				$loginField = getLoginField($this->input('login'));
				if ($loginField == 'phone') {
					$input['login'] = phoneFormatInt($this->input('login'), $this->input('country_code', session('country_code')));
				}
			}
			
			// tags
			if ($this->filled('tags')) {
				$input['tags'] = tagCleaner($this->input('tags'));
			}
			
			request()->merge($input); // Required!
			$this->merge($input);
		}
		
		return parent::getValidatorInstance();
	}
	
	/**
	 * Handle a failed validation attempt.
	 *
	 * @param Validator $validator
	 * @throws ValidationException
	 */
	protected function failedValidation(Validator $validator)
	{
		if ($this->ajax() || $this->wantsJson() || $this->segment(1) == 'api') {
			// Get Errors
			$errors = (new ValidationException($validator))->errors();
			
			// Get Json
			$json = [
				'success' => false,
				'message' => t('An error occurred while validating the data.'),
				'data'    => $errors,
			];
			
			// Add a specific json attributes for 'bootstrap-fileinput' plugin
			if (Str::contains(get_called_class(), 'PhotoRequest')) {
				// Get errors in text
				$errorsTxt = t('Error found');
				if (is_array($errors) && count($errors) > 0) {
					foreach ($errors as $value) {
						if (is_array($value)) {
							foreach ($value as $v) {
								$errorsTxt .= '<br>- ' . $v;
							}
						} else {
							$errorsTxt .= '<br>- ' . $value;
						}
					}
				}
				
				// NOTE: 'bootstrap-fileinput' need 'errorkeys' (array) element & 'error' (text) element
				$json['error'] = $errorsTxt;
				$json['errorkeys'] = $errors;
			}
			
			throw new HttpResponseException(response()->json($json, JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
		}
		
		parent::failedValidation($validator);
	}
	
	/**
	 * reCAPTCHA Rules
	 *
	 * @param array $rules
	 * @return array
	 */
	protected function recaptchaRules($rules = [])
	{
		// reCAPTCHA
		if (
			config('settings.security.recaptcha_activation')
			&& config('recaptcha.site_key')
			&& config('recaptcha.secret_key')
		) {
			$rules['g-recaptcha-response'] = ['recaptcha'];
		}
		
		return $rules;
	}
}

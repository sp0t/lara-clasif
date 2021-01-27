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
use App\Rules\BlacklistDomainRule;
use App\Rules\BlacklistEmailRule;
use App\Rules\BlacklistTitleRule;
use App\Rules\BlacklistWordRule;
use App\Rules\EmailRule;
use App\Rules\UsernameIsAllowedRule;
use App\Rules\UsernameIsValidRule;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Router;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class UserRequest extends Request
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		if (in_array($this->method(), ['POST', 'CREATE'])) {
			return true;
		} else {
			return auth()->check();
		}
	}
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @param \Illuminate\Routing\Router $router
	 * @param \Illuminate\Filesystem\Filesystem $files
	 * @param \Illuminate\Config\Repository $config
	 * @return array
	 */
	public function rules(Router $router, Filesystem $files, Repository $config)
	{
		$rules = [];
		
		// CREATE
		if (in_array($this->method(), ['POST', 'CREATE'])) {
			$rules = $this->storeRules($router, $files, $config);
		}
		
		// UPDATE
		if (in_array($this->method(), ['PUT', 'PATCH', 'UPDATE'])) {
			$rules = $this->updateRules($router, $files, $config);
		}
		
		return $rules;
	}
	
	/**
	 * @param $router
	 * @param $files
	 * @param $config
	 * @return array
	 */
	private function storeRules($router, $files, $config)
	{
		$rules = [
			//'gender_id'  => ['required', 'not_in:0'],
			'name'         => ['required', new BetweenRule(2, 200)],
			'user_type_id' => ['required', 'not_in:0'],
			'country_code' => ['sometimes', 'required', 'not_in:0'],
			'phone'        => ['max:20'],
			'email'        => ['max:100', new BlacklistEmailRule(), new BlacklistDomainRule()],
			'password'     => [
				'required',
				'min:' . config('larapen.core.passwordLength.min', 6),
				'max:' . config('larapen.core.passwordLength.max', 60),
				'dumbpwd',
				'confirmed'
			],
			'term'         => ['accepted'],
		];
		
		// Email
		if ($this->filled('email')) {
			$rules['email'][] = 'email';
			$rules['email'][] = new EmailRule();
			$rules['email'][] = 'unique:users,email';
		}
		if (isEnabledField('email')) {
			if (isEnabledField('phone') && isEnabledField('email')) {
				$rules['email'][] = 'required_without:phone';
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
		if ($this->filled('phone')) {
			$rules['phone'][] = 'unique:users,phone';
		}
		
		// Username
		if (isEnabledField('username')) {
			if ($this->filled('username')) {
				$rules['username'] = [
					'between:3,100',
					'unique:users,username',
					new UsernameIsValidRule(),
					new UsernameIsAllowedRule($router, $files, $config)
				];
			}
		}
		
		// COMPANY: Check 'resume' is required
		if (config('larapen.core.register.showCompanyFields')) {
			if ($this->input('user_type_id') == 1) {
				$rules['company.name'] = ['required', new BetweenRule(2, 200), new BlacklistTitleRule()];
				$rules['company.description'] = ['required', new BetweenRule(5, 1000), new BlacklistWordRule()];
				
				// Check 'logo' is required
				if ($this->file('logo')) {
					$rules['logo'] = [
						'required',
						'image',
						'mimes:' . getUploadFileTypes('image'),
						'max:' . (int)config('settings.upload.max_image_size', 1000)
					];
				}
			}
		}
		
		// CANDIDATE: Check 'resume' is required
		if (config('larapen.core.register.showResumeFields')) {
			if ($this->input('user_type_id') == 2) {
				$rules['resume.filename'] = [
					'required',
					'mimes:' . getUploadFileTypes('file'),
					'max:' . (int)config('settings.upload.max_file_size', 1000)
				];
			}
		}
		
		// reCAPTCHA
		$rules = $this->recaptchaRules($rules);
		
		return $rules;
	}
	
	/**
	 * @param $router
	 * @param $files
	 * @param $config
	 * @return array
	 */
	private function updateRules($router, $files, $config)
	{
		if (Str::contains(Route::currentRouteAction(), 'Account\EditController@updateSettings')) {
			$rules = [
				'password' => [
					'min:' . config('larapen.core.passwordLength.min', 6),
					'max:' . config('larapen.core.passwordLength.max', 60),
					'dumbpwd',
					'confirmed'
				]
			];
		} else {
			// Check if these fields has changed
			$emailChanged = ($this->input('email') != auth()->user()->email);
			$phoneChanged = ($this->input('phone') != auth()->user()->phone);
			$usernameChanged = ($this->filled('username') && $this->input('username') != auth()->user()->username);
			
			// Validation Rules
			$rules = [];
			if (empty(auth()->user()->user_type_id) || auth()->user()->user_type_id == 0) {
				$rules['user_type_id'] = ['required', 'not_in:0'];
			} else {
				$rules['gender_id'] = ['required', 'not_in:0'];
				$rules['name'] = ['required', 'max:100'];
				$rules['phone'] = ['max:20'];
				$rules['email'] = ['required', 'email', new EmailRule(), new BlacklistEmailRule(), new BlacklistDomainRule()];
				$rules['username'] = ['between:3,100', new UsernameIsValidRule(), new UsernameIsAllowedRule($router, $files, $config)];
				
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
				if ($phoneChanged) {
					$rules['phone'][] = 'unique:users,phone';
				}
				
				// Email
				if ($emailChanged) {
					$rules['email'][] = 'unique:users,email';
				}
				
				// Username
				if ($usernameChanged) {
					$rules['username'][] = 'required';
					$rules['username'][] = 'unique:users,username';
				}
			}
		}
		
		return $rules;
	}
}

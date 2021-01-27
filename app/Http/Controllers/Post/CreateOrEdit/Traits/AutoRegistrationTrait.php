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

namespace App\Http\Controllers\Post\CreateOrEdit\Traits;

use App\Helpers\Ip;
use App\Models\Post;
use App\Models\Scopes\VerifiedScope;
use App\Models\User;
use App\Notifications\SendPasswordAndEmailVerification;
use Illuminate\Support\Facades\Hash;

trait AutoRegistrationTrait
{
	/**
	 * Auto Register a new user account.
	 *
	 * @param \App\Models\Post $post
	 * @return \App\Models\User|bool
	 */
	public function register(Post $post)
	{
		// Don't auto-register the User if he's logged in, ...
		// or when the 'auto_registration' option is disabled,
		// or when User uncheck the auto-registration checkbox.
		if (auth()->check() || config('settings.single.auto_registration') == 0 || !request()->filled('auto_registration')) {
			return false;
		}
		
		// Don't auto-register the User if Ad is empty, ...
		// or Email Address is not filled.
		if (empty($post) || empty($post->email)) {
			return false;
		}
		
		// Don't auto-register the User if his Email Address already exists
		$user = User::withoutGlobalScopes([VerifiedScope::class])->where('email', $post->email)->first();
		if (!empty($user)) {
			return false;
		}
		
		// AUTO-REGISTRATION
		
		// Conditions to Verify User's Email
		$emailVerificationRequired = config('settings.mail.email_verification') == 1 && !empty($post->email);
		
		// New User
		$user = new User();
		
		// Generate random password
		$randomPassword = getRandomPassword(8);
		
		$user->country_code   = config('country.code');
		$user->language_code  = config('app.locale');
		$user->user_type_id   = 1; // Employer
		$user->name           = $post->contact_name;
		$user->email          = $post->email;
		$user->password       = Hash::make($randomPassword);
		$user->phone          = $post->phone;
		$user->phone_hidden   = 0;
		$user->ip_addr        = Ip::get();
		$user->verified_email = 1;
		$user->verified_phone = 1;
		
		// Email verification key generation
		if ($emailVerificationRequired) {
			$user->email_token    = md5(microtime() . mt_rand());
			$user->verified_email = 0;
		}
		
		// Save
		$user->save();
		
		// Send Generated Password by Email
		try {
			$user->notify(new SendPasswordAndEmailVerification($user, $randomPassword));
		} catch (\Exception $e) {
			flash($e->getMessage())->error();
		}
		
		return $user;
	}
}

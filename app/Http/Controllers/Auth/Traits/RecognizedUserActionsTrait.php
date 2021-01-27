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

namespace App\Http\Controllers\Auth\Traits;

use App\Models\Post;
use App\Models\Scopes\VerifiedScope;
use App\Models\Scopes\ReviewedScope;
use App\Models\User;

trait RecognizedUserActionsTrait
{
	/**
	 * After Email or Phone verification from User creation,
	 * Match User's ads (posted as Guest)
	 *
	 * @param $user
	 */
	public function findAndMatchPostsToUser($user)
	{
		if (!empty($user)) {
			// Update Ads created with this email
			if (!empty($user->email)) {
				Post::withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
					->where('email', $user->email)
					->update(['user_id' => $user->id]);
			}
			
			// Update Ads created with this phone number (for this country)
			if (!empty($user->phone)) {
				Post::withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
					->currentCountry()
					->where('phone', $user->phone)
					->update(['user_id' => $user->id]);
			}
		}
	}
	
	/**
	 * After Email or Phone verification (from Post creation), match User's ads (posted as Guest) & User's data (if missed)
	 *
	 * @param $post
	 */
	public function findAndMatchUserToPost($post)
	{
		if (!empty($post)) {
			// Get User by Email
			if (!empty($post->email)) {
				$user = User::where('email', $post->email)->first();
			}
			
			// Get User by Phone number
			if (!empty($post->phone)) {
				if (!isset($user) || empty($user)) {
					$user = User::currentCountry()->where('phone', $post->phone)->first();
				}
			}
		}
		
		if (isset($user) && !empty($user)) {
			// Match the User's ads
			$postModelNeedToBeSaved = false;
			if (empty($post->user_id)) {
				$post->user_id = $user->id;
				$postModelNeedToBeSaved = true;
			}
			if (empty($post->email)) {
				$post->email = $user->email;
				$postModelNeedToBeSaved = true;
			}
			if (empty($post->phone)) {
				$post->phone = $user->phone;
				$postModelNeedToBeSaved = true;
			}
			if ($postModelNeedToBeSaved) {
				$post->save();
			}
			
			// If missed, Add User's data
			$userModelNeedToBeSaved = false;
			if (empty($user->country_code)) {
				$user->country_code = config('country.code');
				$userModelNeedToBeSaved = true;
			}
			if (empty($user->email)) {
				$user->email = $post->email;
				$userModelNeedToBeSaved = true;
			}
			if (empty($user->phone)) {
				$user->phone = $post->phone;
				$userModelNeedToBeSaved = true;
			}
			if ($userModelNeedToBeSaved) {
				$user->save();
			}
		}
	}
}

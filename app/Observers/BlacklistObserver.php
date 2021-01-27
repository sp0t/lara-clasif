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

namespace App\Observer;

use App\Models\Blacklist;
use App\Models\Post;
use App\Models\User;

class BlacklistObserver
{
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param Blacklist $blacklist
	 * @return void
	 */
	public function saved(Blacklist $blacklist)
	{
		// Check if an email address has been banned
		if ($blacklist->type == 'email') {
			// Check if it is a valid email address
			if (filter_var($blacklist->entry, FILTER_VALIDATE_EMAIL)) {
				$exceptEmailDomains   = [getDomain(), 'demosite.com', 'larapen.com'];
				$blacklistEmailDomain = substr(strrchr($blacklist->entry, '@'), 1);
				
				// Don't remove banned email address data for the "except" domains
				if (!in_array($blacklistEmailDomain, $exceptEmailDomains)) {
					// Delete the banned user related to the email address
					$user = User::where('email', $blacklist->entry)->first();
					if (!empty($user)) {
						$user->delete();
					}
					
					// Delete the banned user's posts related to the email address
					$posts = Post::where('email', $blacklist->entry)->get();
					if ($posts->count() > 0) {
						foreach ($posts as $post) {
							$post->delete();
						}
					}
				}
			}
		}
	}
}

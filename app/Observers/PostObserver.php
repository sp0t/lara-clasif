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

use App\Helpers\Files\Storage\StorageDisk;
use App\Models\Language;
use App\Models\Message;
use App\Models\Payment;
use App\Models\Picture;
use App\Models\Post;
use App\Models\SavedPost;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\StrictActiveScope;
use App\Notifications\PostActivated;
use App\Notifications\PostReviewed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PostObserver
{
	/**
	 * Listen to the Entry updating event.
	 *
	 * @param Post $post
	 * @return void
	 */
	public function updating(Post $post)
	{
		// Get the original object values
		$original = $post->getOriginal();
		
		if (config('settings.mail.confirmation') == 1) {
			try {
				// Post Email address or Phone was not verified, and Post was not approved (reviewed)
				if (($original['verified_email'] != 1 || $original['verified_phone'] != 1) && $original['reviewed'] != 1) {
					if (config('settings.single.posts_review_activation') == 1) {
						if ($post->verified_email == 1 && $post->verified_phone == 1) {
							if ($post->reviewed == 1) {
								$post->notify(new PostReviewed($post));
							} else {
								$post->notify(new PostActivated($post));
							}
						}
					} else {
						if ($post->verified_email == 1 && $post->verified_phone == 1) {
							$post->notify(new PostReviewed($post));
						}
					}
				}
				
				// Post Email address or Phone was not verified, and Post was approved (reviewed)
				if (($original['verified_email'] != 1 || $original['verified_phone'] != 1) && $original['reviewed'] == 1) {
					if ($post->verified_email == 1 && $post->verified_phone == 1) {
						$post->notify(new PostReviewed($post));
					}
				}
			} catch (\Exception $e) {
				flash($e->getMessage())->error();
			}
		}
	}
	
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param Post $post
	 * @return void
	 */
	public function deleting(Post $post)
	{
		// Storage Disk Init.
		$disk = StorageDisk::getDisk();
		
		// Delete all Messages
		$messages = Message::where('post_id', $post->id)->get();
		if ($messages->count() > 0) {
			foreach ($messages as $message) {
				$message->delete();
			}
		}
		
		// Delete all Saved Posts
		$savedPosts = SavedPost::where('post_id', $post->id)->get();
		if ($savedPosts->count() > 0) {
			foreach ($savedPosts as $savedPost) {
				$savedPost->delete();
			}
		}
		
		// Remove logo files (if exists)
		if (empty($post->company_id)) {
			if (!empty($post->logo)) {
				$filename = str_replace('uploads/', '', $post->logo);
				if (
					!Str::contains($filename, config('larapen.core.picture.default'))
					&& $disk->exists($filename)
				) {
					$disk->delete($filename);
				}
			}
		}
		
		// Delete all Pictures
		$pictures = Picture::where('post_id', $post->id)->get();
		if ($pictures->count() > 0) {
			foreach ($pictures as $picture) {
				$picture->delete();
			}
		}
		
		// Delete the Payment(s) of this Post
		$payments = Payment::withoutGlobalScope(StrictActiveScope::class)->where('post_id', $post->id)->get();
		if ($payments->count() > 0) {
			foreach ($payments as $payment) {
				$payment->delete();
			}
		}
		
		// Remove the ad media folder
		if (!empty($post->country_code) && !empty($post->id)) {
			$directoryPath = 'files/' . strtolower($post->country_code) . '/' . $post->id;
			
			if ($disk->exists($directoryPath)) {
				$disk->deleteDirectory($directoryPath);
			}
		}
		
		// Removing Entries from the Cache
		$this->clearCache($post);
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param Post $post
	 * @return void
	 */
	public function saved(Post $post)
	{
		// Create a new email token if the post's email is marked as unverified
		if ($post->verified_email != 1) {
			if (empty($post->email_token)) {
				$post->email_token = md5(microtime() . mt_rand());
				$post->save();
			}
		}
		
		// Create a new phone token if the post's phone number is marked as unverified
		if ($post->verified_phone != 1) {
			if (empty($post->phone_token)) {
				$post->phone_token = mt_rand(100000, 999999);
				$post->save();
			}
		}
		
		// Removing Entries from the Cache
		$this->clearCache($post);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param Post $post
	 * @return void
	 */
	public function deleted(Post $post)
	{
		//...
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $post
	 */
	private function clearCache($post)
	{
		Cache::forget($post->country_code . '.sitemaps.posts.xml');
		
		Cache::forget($post->country_code . '.home.getPosts.sponsored');
		Cache::forget($post->country_code . '.home.getPosts.latest');
		Cache::forget($post->country_code . '.home.getFeaturedPostsCompanies');
		
		Cache::forget('post.withoutGlobalScopes.with.city.pictures.' . $post->id);
		Cache::forget('post.with.city.pictures.' . $post->id);
		
		try {
			$languages = Language::withoutGlobalScopes([ActiveScope::class])->get(['abbr']);
		} catch (\Exception $e) {
			$languages = collect([]);
		}
		
		if ($languages->count() > 0) {
			foreach ($languages as $language) {
				Cache::forget('post.withoutGlobalScopes.with.city.pictures.' . $post->id . '.' . $language->abbr);
				Cache::forget('post.with.city.pictures.' . $post->id . '.' . $language->abbr);
			}
		}
		
		Cache::forget('posts.similar.category.' . $post->category_id . '.post.' . $post->id);
		Cache::forget('posts.similar.city.' . $post->city_id . '.post.' . $post->id);
	}
}

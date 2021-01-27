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

use App\Models\Post;
use App\Models\PostType;
use Illuminate\Support\Facades\Cache;

class PostTypeObserver extends TranslatedModelObserver
{
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param PostType $postType
	 * @return void
	 */
	public function deleting($postType)
	{
		parent::deleting($postType);
		
		// Delete all the postType's posts
		$posts = Post::where('post_type_id', $postType->id)->get();
		if ($posts->count() > 0) {
			foreach ($posts as $post) {
				$post->delete();
			}
		}
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param PostType $postType
	 * @return void
	 */
	public function saved(PostType $postType)
	{
		// Removing Entries from the Cache
		$this->clearCache($postType);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param PostType $postType
	 * @return void
	 */
	public function deleted(PostType $postType)
	{
		// Removing Entries from the Cache
		$this->clearCache($postType);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $postType
	 */
	private function clearCache($postType)
	{
		Cache::flush();
	}
}

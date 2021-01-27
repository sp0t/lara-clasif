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

namespace App\Models\Traits;


trait ConversationTrait
{
	/*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
	/**
	 * Check if a Conversation has a New Messages (Programmable Way)
	 *
	 * @param $conversation
	 * @return bool
	 */
	public static function conversationHasNewMessages($conversation)
	{
		$hasNewMessages = false;
		
		try {
			if (auth()->check()) {
				if ($conversation->is_read != 1) {
					if (!empty($conversation->latestReply)) {
						if ($conversation->latestReply->from_user_id != auth()->user()->id) {
							$hasNewMessages = true;
						}
					} else {
						if ($conversation->from_user_id != auth()->user()->id) {
							$hasNewMessages = true;
						}
					}
				}
			}
		} catch (\Exception $e) {
			$hasNewMessages = false;
		}
		
		return $hasNewMessages;
	}
	
	/**
	 * Count the current User's Conversations that have New Messages
	 *
	 * @param null $countLimit
	 * @return int
	 */
	public static function countConversationsWithNewMessages($countLimit = null)
	{
		$conversations = self::with('latestReply')
			->whereHas('post', function($query) {
				$query->currentCountry();
			})
			->byUserId(auth()->user()->id)
			->where('parent_id', 0)
			->orderByDesc('id');
		
		$nb = 0;
		if ($conversations->count() > 0) {
			// Using Cursors method when processing large amounts of data.
			// ie. The Cursor method may be used to greatly reduce the memory usage.
			foreach($conversations->cursor() as $key => $conversation) {
				if (self::conversationHasNewMessages($conversation)) {
					$nb++;
				}
				if ($nb >= (int)$countLimit) break;
			}
		}
		
		return $nb;
	}
	
	/*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
	public function scopeByUserId($builder, $userId = null)
	{
		if (empty($userId)) {
			if (auth()->check()) {
				$userId = auth()->user()->id;
			}
		}
		
		return $builder->where(function($query) use ($userId) {
			$query->where('to_user_id', $userId)->orWhere('from_user_id', $userId);
		})->where(function($query) use ($userId) {
			$query->where('deleted_by', '!=', $userId)->orWhereNull('deleted_by');
		});
	}
}
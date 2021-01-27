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
use App\Models\Message;

class MessageObserver
{
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param Message $message
	 * @return void
	 */
	public function deleting(Message $message)
	{
		// Storage Disk Init.
		$disk = StorageDisk::getDisk();
		
		// Delete the message's file
		if (!empty($message->filename)) {
			if ($disk->exists($message->filename)) {
				$disk->delete($message->filename);
			}
		}
		
		// If it is a Conversation, Delete it and its Messages if exist
		if ($message->parent_id == 0) {
			$conversationMessages = Message::where('parent_id', $message->id)->get();
			if ($conversationMessages->count() > 0) {
				foreach ($conversationMessages as $conversationMessage) {
					$conversationMessage->delete();
				}
			}
		}
	}
}

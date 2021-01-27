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
use App\Helpers\Files\Tools\FileStorage;
use App\Models\Language;
use App\Models\Picture;
use App\Models\Scopes\ActiveScope;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PictureObserver
{
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param Picture $picture
	 * @return void
	 */
	public function deleting(Picture $picture)
	{
		// Delete all pictures files
		if (!empty($picture->filename)) {
			// Storage Disk Init.
			$disk = StorageDisk::getDisk();
			
			$filePath = str_replace('uploads' . DIRECTORY_SEPARATOR, '', $picture->filename);
			
			// Get the picture filename (without path)
			$filename = last(explode(DIRECTORY_SEPARATOR, $filePath));
			
			// Get the picture's directory
			$fileDir = dirname($filePath);
			
			if ($disk->exists($fileDir)) {
				$meta = FileStorage::getMetaData($disk, $fileDir);
				if (isset($meta['type']) && $meta['type'] == 'dir') {
					// Get all the files in the picture's directory
					$files = $disk->files($fileDir);
					if (!empty($files)) {
						foreach ($files as $file) {
							// Don't delete the default picture
							if (Str::contains($file, config('larapen.core.picture.default'))) {
								continue;
							}
							// Delete the picture with its thumbs (by making a search with the picture original filename)
							if (Str::contains($file, $filename)) {
								$disk->delete($file);
							}
						}
					}
					
					if (!Str::contains($filePath, config('larapen.core.picture.default'))) {
						FileStorage::removeEmptySubDirs($disk, $fileDir);
					}
				}
			}
		}
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param Picture $picture
	 * @return void
	 */
	public function saved(Picture $picture)
	{
		// Removing Entries from the Cache
		$this->clearCache($picture);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param Picture $picture
	 * @return void
	 */
	public function deleted(Picture $picture)
	{
		// Removing Entries from the Cache
		$this->clearCache($picture);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $picture
	 */
	private function clearCache($picture)
	{
		Cache::forget('post.withoutGlobalScopes.with.city.pictures.' . $picture->post_id);
		Cache::forget('post.with.city.pictures.' . $picture->post_id);
		
		try {
			$languages = Language::withoutGlobalScopes([ActiveScope::class])->get(['abbr']);
		} catch (\Exception $e) {
			$languages = collect([]);
		}
		
		if ($languages->count() > 0) {
			foreach ($languages as $language) {
				Cache::forget('post.withoutGlobalScopes.with.city.pictures.' . $picture->post_id . '.' . $language->abbr);
				Cache::forget('post.with.city.pictures.' . $picture->post_id . '.' . $language->abbr);
			}
		}
	}
}

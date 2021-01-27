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

namespace App\Helpers\Files\Response;

use Illuminate\Filesystem\FilesystemAdapter;

class AudioVideoResponse
{
	/**
	 * Stream specified video/audio file.
	 *
	 * @param $disk
	 * @param $filePath
	 */
	public static function create($disk, $filePath)
	{
		if (!$disk instanceof FilesystemAdapter) {
			abort(404);
		}
		
		if (!$disk->exists($filePath)) {
			abort(404);
		}
		
		$mime = $disk->getMimetype($filePath);
		$size = $disk->size($filePath);
		$time = date('r', $disk->lastModified($filePath));
		$fm = $disk->getDriver()->readStream($filePath);
		$begin = 0;
		$end = $size - 1;
		$filename = last(explode(DIRECTORY_SEPARATOR, $filePath));
		
		if (isset($_SERVER['HTTP_RANGE'])) {
			if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)) {
				$begin = intval($matches[1]);
				if (!empty($matches[2])) {
					$end = intval($matches[2]);
				}
			}
		}
		
		if (isset($_SERVER['HTTP_RANGE'])) {
			header('HTTP/1.1 206 Partial Content');
		} else {
			header('HTTP/1.1 200 OK');
		}
		
		header("Content-Type: $mime");
		header('Cache-Control: public, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Accept-Ranges: bytes');
		header('Content-Length:' . (($end - $begin) + 1));
		if (isset($_SERVER['HTTP_RANGE'])) {
			header("Content-Range: bytes $begin-$end/$size");
		}
		header("Content-Disposition: inline; filename={$filename}");
		header("Content-Transfer-Encoding: binary");
		header("Last-Modified: $time");
		
		$cur = $begin;
		fseek($fm, $begin, 0);
		
		while (!feof($fm) && $cur <= $end && (connection_status() == 0)) {
			print fread($fm, min(1024 * 16, ($end - $cur) + 1));
			$cur += 1024 * 16;
		}
	}
}

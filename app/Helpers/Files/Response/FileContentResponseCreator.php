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

class FileContentResponseCreator
{
	/**
	 * @var ImageResponse
	 */
	private static $imageResponse;
	
	/**
	 * @var AudioVideoResponse
	 */
	private static $audioVideoResponse;
	
	/**
	 * @param ImageResponse $imageResponse
	 * @param AudioVideoResponse $audioVideoResponse
	 */
	public function __construct(ImageResponse $imageResponse, AudioVideoResponse $audioVideoResponse)
	{
		self::$imageResponse = $imageResponse;
		self::$audioVideoResponse = $audioVideoResponse;
	}
	
	/**
	 * Return download or preview response for given file.
	 *
	 * @param $disk
	 * @param $filePath
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\StreamedResponse|void
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
		$type = self::getFileType($mime);
		
		if ($type === 'image') {
			return self::$imageResponse->create($disk, $filePath);
		} elseif (self::shouldStream($mime, $type)) {
			return self::$audioVideoResponse->create($disk, $filePath);
		} else {
			return self::createBasicResponse($disk, $filePath);
		}
	}
	
	/**
	 * Create a basic response for specified upload content.
	 *
	 * @param $disk
	 * @param $filePath
	 * @return \Symfony\Component\HttpFoundation\StreamedResponse
	 */
	private static function createBasicResponse($disk, $filePath)
	{
		if (!$disk instanceof FilesystemAdapter) {
			abort(404);
		}
		
		$stream = $disk->readStream($filePath);
		$mime = $disk->getMimetype($filePath);
		$size = $disk->getSize($filePath);
		$shortName = last(explode(DIRECTORY_SEPARATOR, $filePath));
		
		return response()->stream(function () use ($stream) {
			fpassthru($stream);
		}, 200, [
			"Content-Type"        => $mime,
			"Content-Length"      => $size,
			"Content-disposition" => "inline; filename=\"" . $shortName . "\"",
		]);
	}
	
	/**
	 * Extract file type
	 *
	 * @param $mime
	 * @return string
	 */
	private static function getFileType($mime)
	{
		if (strstr($mime, 'video/')){
			return 'video';
		} else if (strstr($mime, 'audio/')) {
			return 'audio';
		} else if (strstr($mime, 'image/')) {
			return 'image';
		} else {
			return 'file';
		}
	}
	
	/**
	 * Should file with given mime be streamed.
	 *
	 * @param string $mime
	 * @param string $type
	 *
	 * @return bool
	 */
	private static function shouldStream($mime, $type)
	{
		return $type === 'video' || $type === 'audio' || $mime === 'application/ogg';
	}
}

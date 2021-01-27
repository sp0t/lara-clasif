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

namespace App\Helpers;


class RemoveFromString
{
	/**
	 * Remove Direct Contact Info from string
	 *
	 * @param $string
	 * @param bool $beforeFormSubmit
	 * @param bool $altText
	 * @return string
	 */
	public static function contactInfo($string, $beforeFormSubmit = false, $altText = false)
	{
		if ($beforeFormSubmit) {
			if (config('settings.single.remove_url_before')) {
				$string = self::links($string, $altText);
			}
			if (config('settings.single.remove_email_before')) {
				$string = self::emails($string, $altText);
			}
			if (config('settings.single.remove_phone_before')) {
				$string = self::phoneNumbers($string, $altText);
			}
		} else {
			if (config('settings.single.remove_url_after')) {
				$string = self::links($string, $altText);
			}
			if (config('settings.single.remove_email_after')) {
				$string = self::emails($string, $altText);
			}
			if (config('settings.single.remove_phone_after')) {
				$string = self::phoneNumbers($string, $altText);
			}
		}
		
		return $string;
	}
	
	/**
	 * Remove Links & URL from string
	 *
	 * @param $string
	 * @param bool $altText
	 * @param bool $removeLinksText
	 * @return string
	 */
	public static function links($string, $altText = false, $removeLinksText = false)
	{
		if (!is_string($string)) return '';
		
		$replace = ($altText) ? ' [***] ' : ' ';
		
		if (!$removeLinksText) {
			$string = preg_replace('/<a.*?>(.*?)<\/a>/ui', '\1', $string);
		} else {
			$string = preg_replace('/<a.*?>.*?<\/a>/ui', $replace, $string);
		}
		$string = preg_replace('/\b((https?|ftp|file):\/\/|www\.)[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/ui', $replace, $string);
		$string = preg_replace('/ +/', ' ', $string);
		
		return $string;
	}
	
	/**
	 * Remove Email Addresses from string
	 *
	 * @param $string
	 * @param bool $altText
	 * @return string
	 */
	public static function emails($string, $altText = false)
	{
		if (!is_string($string)) return '';
		
		$replace = ($altText) ? ' [***] ' : ' ';
		$patterns = [
			'/[a-z0-9\-\._%\+]+@[a-z0-9\-\.]+\.[a-z]{2,4}\b/i',
			'/[a-z0-9\-_]+(\.[a-z0-9\-_]+)*@[a-z0-9\-]+(\.[a-z0-9\-]+)*(\.[a-z]{2,3})/i',
			'/([a-z0-9\-\._]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-z0-9\-]+\.)+))([a-z]{2,4}|[0-9]{1,3})(\]?)/i',
		];
		foreach ($patterns as $key => $pattern) {
			$string = preg_replace($pattern, $replace, $string);
		}
		$string = preg_replace('/ +/', ' ', $string);
		
		return $string;
	}
	
	/**
	 * Remove Phone Numbers from string
	 *
	 * @param $string
	 * @param bool $altText
	 * @return string
	 */
	public static function phoneNumbers($string, $altText = false)
	{
		if (!is_string($string) && !is_numeric($string) && !ctype_alnum($string)) return '';
		
		$replace = ($altText) ? ' [***] ' : ' ';
		$pattern = '/([\(\)\\s]?[\+\\s]?[0-9]+[\-\.\(\)\\s]?[0-9]+[\-\.\(\)\\s]?){4,}/ui';
		
		$string = preg_replace($pattern, $replace, $string);
		$string = preg_replace('/ +/', ' ', $string);
		
		return $string;
	}
}

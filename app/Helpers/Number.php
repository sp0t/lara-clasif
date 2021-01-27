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

use Illuminate\Support\Str;

class Number
{
	/**
	 * Converts a number into a short version, eg: 1000 -> 1k
	 *
	 * @param $value
	 * @param int $precision
	 * @return string
	 */
	public static function short($value, $precision = 1)
	{
		if ($value < 900) {
			// 0 - 900
			$valueFormat = number_format($value, $precision);
			$suffix = '';
		} else if ($value < 900000) {
			// 0.9k-850k
			$valueFormat = number_format($value / 1000, $precision);
			$suffix = 'K';
		} else if ($value < 900000000) {
			// 0.9m-850m
			$valueFormat = number_format($value / 1000000, $precision);
			$suffix = 'M';
		} else if ($value < 900000000000) {
			// 0.9b-850b
			$valueFormat = number_format($value / 1000000000, $precision);
			$suffix = 'B';
		} else {
			// 0.9t+
			$valueFormat = number_format($value / 1000000000000, $precision);
			$suffix = 'T';
		}
		
		// Remove unnecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
		// Intentionally does not affect partials, eg "1.50" -> "1.50"
		if ($precision > 0) {
			$dotZero = '.' . str_repeat('0', $precision);
			$valueFormat = str_replace($dotZero, '', $valueFormat);
		}
		
		return $valueFormat . $suffix;
	}
	
	/**
	 * Transform the given number to display it using the Currency format settings
	 * NOTE: Doesn't transform non-numeric value
	 *
	 * @param $value
	 * @return int|mixed|string
	 */
	public static function transform($value)
	{
		if (!is_numeric($value)) {
			return $value;
		}
		
		$value = self::format($value);
		
		return $value;
	}
	
	/**
	 * Transform the given number to display it using the Currency format settings
	 * NOTE: Transform non-numeric value
	 *
	 * @param $value
	 * @return mixed|string
	 */
	public static function format($value)
	{
		// Convert string to numeric
		$value = self::getFloatRawFormat($value);
		
		// Currency format - Ex: USD 100,234.56 | EUR 100 234,56
		$value = number_format(
			$value,
			(int)config('currency.decimal_places', 2),
			config('currency.decimal_separator', '.'),
			config('currency.thousand_separator', ',')
		);
		
		return $value;
	}
	
	/**
	 * Get Float Raw Format
	 *
	 * @param $value
	 * @return mixed|string
	 */
	public static function getFloatRawFormat($value)
	{
		if (is_numeric($value)) {
			return $value;
		}
		
		$value = trim($value);
		$value = strtr($value, [' ' => '']);
		$value = preg_replace('/ +/', '', $value);
		$value = str_replace(',', '.', $value);
		$value = preg_replace('/[^0-9\.]/', '', $value);
		
		return $value;
	}
	
	/**
	 * @param $value
	 * @return int|mixed|string
	 */
	public static function money($value)
	{
		if (config('settings.other.decimals_superscript')) {
			return static::moneySuperscript($value);
		}
		
		$value = self::transform($value);
		
		// In line current
		if (config('currency.in_left') == 1) {
			$value = config('currency.symbol') . $value;
		} else {
			$value = $value . ' ' . config('currency.symbol');
		}
		
		// Remove decimal value if it's null
		$defaultDecimal = str_pad('', (int)config('currency.decimal_places', 2), '0');
		$value = str_replace(config('currency.decimal_separator', '.') . $defaultDecimal, '', $value);
		
		return $value;
	}
	
	/**
	 * @param $value
	 * @return int|mixed|string
	 */
	public static function moneySuperscript($value)
	{
		$value = self::transform($value);
		
		$tmp = explode(config('currency.decimal_separator', '.'), $value);
		
		if (isset($tmp[1]) && !empty($tmp[1])) {
			if (config('currency.in_left') == 1) {
				$value = config('currency.symbol') . $tmp[0] . '<sup>' . $tmp[1] . '</sup>';
			} else {
				$value = $tmp[0] . '<sup>' . config('currency.symbol') . $tmp[1] . '</sup>';
			}
		} else {
			if (config('currency.in_left') == 1) {
				$value = config('currency.symbol') . $value;
			} else {
				$value = $value . ' ' . config('currency.symbol');
			}
			
			// Remove decimal value if it's null
			$defaultDecimal = str_pad('', (int)config('currency.decimal_places', '2'), '0');
			$value = str_replace(config('currency.decimal_separator', '.') . $defaultDecimal, '', $value);
		}
		
		return $value;
	}
	
	/**
	 * @param null $locale
	 * @return array
	 */
	public static function getSeparators($locale = null)
	{
		if (empty($locale)) {
			$locale = config('app.locale');
		}
		
		$separators = [];
		$separators['thousand'] = (Str::startsWith($locale, 'fr')) ? ' ' : ',';
		$separators['decimal'] = (Str::startsWith($locale, 'fr')) ? ',' : '.';
		
		return $separators;
	}
	
	/**
	 * @param null $locale
	 * @return \Illuminate\Config\Repository|mixed|null
	 */
	public static function setLanguage($locale = null)
	{
		if (empty($locale)) {
			$locale = config('app.locale');
		}
		
		// Set locale
		if (setlocale(LC_ALL, $locale)) {
			setlocale(LC_ALL, $locale);
		} else {
			setlocale(LC_ALL, 'en_US');
		}
		
		return $locale;
	}
	
	/**
	 * @param $int
	 * @param $nb
	 * @return string
	 */
	public static function leadZero($int, $nb)
	{
		$diff = $nb - strlen($int);
		if ($diff <= 0) {
			return $int;
		} else {
			return str_repeat('0', $diff) . $int;
		}
	}
	
	/**
	 * @param $value
	 * @param $limit
	 * @return mixed
	 */
	public static function zeroPad($value, $limit)
	{
		return (strlen($value) >= $limit) ? $value : self::zeroPad("0" . $value, $limit);
	}
	
	/**
	 * @param $value
	 * @param int $decimals
	 * @return string
	 */
	public static function localeFormat($value, $decimals = 2)
	{
		self::setLanguage();
		
		$locale = localeconv();
		$value = number_format($value, $decimals, $locale['decimal_point'], $locale['thousands_sep']);
		
		return $value;
	}
	
	/**
	 * Clean Float Value
	 * Fixed: MySQL don't accept the comma format number
	 *
	 * This function takes the last comma or dot (if any) to make a clean float,
	 * ignoring thousand separator, currency or any other letter.
	 *
	 * Example:
	 * $num = '1.999,369€';
	 * var_dump(Number::toFloat($num)); // float(1999.369)
	 * $otherNum = '126,564,789.33 m²';
	 * var_dump(Number::toFloat($otherNum)); // float(126564789.33)
	 *
	 * @param $value
	 * @return float
	 */
	public static function toFloat($value)
	{
		// Check negative numbers
		$isNegative = false;
		if (substr(trim($value), 0, 1) == '-') {
			$isNegative = true;
		}
		
		$dotPos = strrpos($value, '.');
		$commaPos = strrpos($value, ',');
		$sepPos = (($dotPos > $commaPos) && $dotPos) ? $dotPos : ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);
		
		if (!$sepPos) {
			$value = preg_replace('/[^0-9]/', '', $value);
			$value = floatval($value);
			
			if ($isNegative) {
				$value = '-' . $value;
			}
			
			return $value;
		}
		
		$integer = preg_replace('/[^0-9]/', '', substr($value, 0, $sepPos));
		$decimal = preg_replace('/[^0-9]/', '', substr($value, $sepPos + 1, strlen($value)));
		$decimal = rtrim($decimal, '0');
		
		if (intval($decimal) == 0) {
			$value = intval($integer);
		} else {
			$value = intval($integer) . '.' . $decimal;
		}
		
		if ($isNegative) {
			$value = '-' . $value;
		}
		
		return $value;
	}
}

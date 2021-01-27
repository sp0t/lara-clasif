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

class ArrayHelper
{
	/**
	 * @param $array
	 * @param $field
	 * @param bool $createSubArray
	 * @return array
	 */
	public static function groupBy($array, $field, $createSubArray = true)
	{
		$arraySorted = [];
		if (is_object(current($array))) {
			foreach ($array as $item) {
				if ($createSubArray) {
					if (!@is_array($arraySorted[$item->$field])) {
						$arraySorted[$item->$field] = [];
					}
					$arraySorted[$item->$field][] = $item;
				} else {
					$arraySorted[$item->$field] = $item;
				}
			}
		} else if (is_array(current($array))) {
			foreach ($array as $item) {
				if ($createSubArray) {
					if (!@is_array($arraySorted[$item[$field]])) {
						$arraySorted[$item[$field]] = [];
					}
					$arraySorted[$item[$field]][] = $item;
				} else {
					$arraySorted[$item[$field]] = $item;
				}
			}
		} else {
			return $array;
		}
		
		return $arraySorted;
	}
	
	/**
	 * Sort multidimensional array by sub-array key
	 *
	 * @param $array
	 * @param $field
	 * @param string $order
	 * @param bool $keepIndex
	 * @return array|\Illuminate\Support\Collection|\stdClass
	 */
	public static function sortBy($array, $field, $order = 'asc', $keepIndex = true)
	{
		// Check if Laravel Collection given
		$isLaravelCollection = false;
		if (class_exists('\Illuminate\Support\Collection')) {
			if ($array instanceof \Illuminate\Support\Collection) {
				$array = $array->toArray();
				$isLaravelCollection = true;
			}
		}
		
		// Check if Object given
		$isObject = false;
		if (is_object($array)) {
			$array = self::fromObject($array);
			$isObject = true;
		}
		
		if (empty($array)) {
			return ($isLaravelCollection) ? self::toCollection([]) : (($isObject) ? self::toObject([]) : []);
		}
		
		// Get sorting order
		$int = 1;
		if (strtolower($order) == 'desc') {
			$int = -1;
		}
		
		// Sorting
		if ($keepIndex) {
			uasort($array, function ($a, $b) use ($field, $int) {
				if ($a[$field] == $b[$field]) {
					return 0;
				}
				return ($a[$field] < $b[$field]) ? -$int : $int;
			});
		} else {
			usort($array, function ($a, $b) use ($field, $int) {
				if ($a[$field] == $b[$field]) {
					return 0;
				}
				return ($a[$field] < $b[$field]) ? -$int : $int;
			});
		}
		
		return ($isLaravelCollection) ? self::toCollection($array) : (($isObject) ? self::toObject($array) : $array);
	}
	
	/**
	 * Sort multidimensional array by sub-array key (Multi-bytes version)
	 *
	 * @param $array
	 * @param $field
	 * @param string $locale
	 * @param string $order
	 * @param bool $keepIndex
	 * @return array|\Illuminate\Support\Collection|\stdClass
	 */
	public static function mbSortBy($array, $field, $locale = 'en_US', $order = 'asc', $keepIndex = true)
	{
		// Check if Laravel Collection given
		$isLaravelCollection = false;
		if (class_exists('\Illuminate\Support\Collection')) {
			if ($array instanceof \Illuminate\Support\Collection) {
				$array = $array->toArray();
				$isLaravelCollection = true;
			}
		}
		
		// Check if Object given
		$isObject = false;
		if (is_object($array)) {
			$array = self::fromObject($array);
			$isObject = true;
		}
		
		if (empty($array)) {
			return ($isLaravelCollection) ? self::toCollection([]) : (($isObject) ? self::toObject([]) : []);
		}
		
		if (!class_exists('\Collator')) {
			$array = ($isLaravelCollection) ? self::toCollection($array) : (($isObject) ? self::toObject($array) : $array);
			return self::sortBy($array, $field, $order, $keepIndex);
		}
		
		try {
			$collator = \Collator::create($locale);
		} catch (\Exception $e) {
			$array = ($isLaravelCollection) ? self::toCollection($array) : (($isObject) ? self::toObject($array) : $array);
			return self::sortBy($array, $field, $order, $keepIndex);
		}
		
		// Get sorting order
		$int = 1;
		if (strtolower($order) == 'desc') {
			$int = -1;
		}
		
		// Sorting
		if ($keepIndex) {
			uasort($array, function ($a, $b) use ($collator, $field, $int) {
				$arr = [$a[$field], $b[$field]];
				$collator->asort($arr, \Collator::SORT_STRING);
				
				$res = $collator->compare(array_pop($arr), $a[$field]);
				if ($res === false) {
					return -1;
				}
				
				return ($res <= 0) ? $int : -$int;
			});
		} else {
			usort($array, function ($a, $b) use ($collator, $field, $int) {
				$arr = [$a[$field], $b[$field]];
				$collator->asort($arr, \Collator::SORT_STRING);
				
				$res = $collator->compare(array_pop($arr), $a[$field]);
				if ($res === false) {
					return -1;
				}
				
				return ($res <= 0) ? $int : -$int;
			});
		}
		
		return ($isLaravelCollection) ? self::toCollection($array) : (($isObject) ? self::toObject($array) : $array);
	}
	
	/**
	 * Object to Array
	 *
	 * @param $object
	 * @param int $level
	 * @return array|mixed
	 */
	public static function fromObject($object, $level = 0)
	{
		if (!is_array($object) && !is_object($object)) {
			return $object;
		}
		
		if ($level <= 0) {
			$array = [];
			foreach ($object as $key => $value) {
				if (is_array($value) || is_object($value)) {
					$array[$key] = self::fromObject($value);
				} else {
					$array[$key] = $value;
				}
			}
			
			return $array;
		} else {
			// First we convert the object into a json string
			$json = json_encode($object, 0, $level);
			
			// Then we convert the json string to an array
			$array = json_decode($json, true);
			
			return $array;
		}
	}
	
	/**
	 * Array to Object
	 *
	 * @param $array
	 * @param int $level
	 * @return array|mixed|\stdClass
	 */
	public static function toObject($array, $level = 0)
	{
		if (!is_array($array)) {
			return $array;
		}
		
		if ($level <= 0) {
			$object = new \stdClass();
			if (is_array($array) && !empty($array)) {
				foreach ($array as $key => $value) {
					$key = trim($key);
					if ($key != '') {
						$object->$key = self::toObject($value);
					}
				}
				
				return $object;
			} else {
				return [];
			}
		} else {
			// First we convert the array to a json string
			$json = json_encode($array, 0, $level);
			
			// The we convert the json string to a stdClass()
			$object = json_decode($json);
			
			return $object;
		}
	}
	
	/**
	 * Array to Laravel Collection
	 *
	 * @param $array
	 * @return array|\Illuminate\Support\Collection
	 */
	public static function toCollection($array)
	{
		if (!is_array($array)) {
			return $array;
		}
		
		$newArray = [];
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$newArray[$key] = self::toCollection($value);
			} else {
				$newArray[$key] = $value;
			}
		}
		
		$newArray = collect($newArray);
		
		return $newArray;
	}
	
	/**
	 * array_unique multi dimension
	 *
	 * @param $array
	 * @return array|\stdClass
	 */
	public static function unique($array)
	{
		if (is_object($array)) {
			$array = self::fromObject($array);
			$array = self::unique($array);
			$array = self::toObject($array);
		} else {
			$array = array_map('serialize', $array);
			$array = array_map('base64_encode', $array);
			$array = array_unique($array);
			$array = array_map('base64_decode', $array);
			$array = array_map('unserialize', $array);
		}
		
		return $array;
	}
	
	/**
	 * shuffle for associative arrays, preserves key => value pairs.
	 *
	 * Shuffle associative and non-associative array while preserving key, value pairs.
	 * Also returns the shuffled array instead of shuffling it in place.
	 *
	 * @param $array
	 * @return array
	 */
	public static function shuffleAssoc($array)
	{
		if (!is_array($array)) return $array;
		if (empty($array)) return $array;
		
		$keys = array_keys($array);
		shuffle($keys);
		$random = [];
		foreach ($keys as $key) {
			$random[$key] = $array[$key];
		}
		
		return $random;
	}
	
	/**
	 * This function will remove all the specified keys from an array and return the final array.
	 * Arguments: The first argument is the array that should be edited
	 *            The arguments after the first argument is a list of keys that must be removed.
	 * Example: array_remove_key($arr, "one", "two", "three");
	 * Return: The function will return an array after deleting the said keys
	 */
	public static function removeKey()
	{
		$args = func_get_args();
		$arr = $args[0];
		$keys = array_slice($args, 1);
		foreach ($arr as $k => $v) {
			if (in_array($k, $keys)) {
				unset($arr[$k]);
			}
		}
		
		return $arr;
	}
	
	/**
	 * This function will remove all the specified values from an array and return the final array.
	 * Arguments: The first argument is the array that should be edited
	 *            The arguments after the first argument is a list of values that must be removed.
	 * Example: array_remove_value($arr,"one","two","three");
	 * Return: The function will return an array after deleting the said values
	 */
	public static function removeValue()
	{
		$args = func_get_args();
		$arr = $args[0];
		$values = array_slice($args, 1);
		foreach ($arr as $k => $v) {
			if (in_array($v, $values)) {
				unset($arr[$k]);
			}
		}
		
		return $arr;
	}
	
	/**
	 * array_diff_assoc() recursive
	 *
	 * @param $array1
	 * @param $array2
	 * @param bool $checkValues
	 * @return array
	 */
	public static function diffAssoc($array1, $array2, $checkValues = false)
	{
		$difference = [];
		foreach ($array1 as $key => $value) {
			if (is_array($value)) {
				if (!isset($array2[$key]) || !is_array($array2[$key])) {
					$difference[$key] = $value;
				} else {
					$newDiff = self::diffAssoc($value, $array2[$key]);
					if (!empty($newDiff))
						$difference[$key] = $newDiff;
				}
			} else if (!array_key_exists($key, $array2)) {
				$difference[$key] = $value;
			}
			
			// Check if the values is different
			if ($checkValues) {
				if (array_key_exists($key, $array2) && $array2[$key] !== $value) {
					$difference[$key] = $value;
				}
			}
		}
		return $difference;
	}
	
	/**
	 * Set an array item to a given value using "dot" notation with a limit.
	 *
	 * If no key is given to the method, the entire array will be replaced.
	 *
	 * @param $array
	 * @param $key
	 * @param $value
	 * @param null $limit
	 * @return mixed
	 */
	public static function setWithLimit(&$array, $key, $value, $limit = null)
	{
		if (is_null($key)) {
			return $array = $value;
		}
		
		$keys = explode('.', $key, $limit);
		
		while (count($keys) > 1) {
			$key = array_shift($keys);
			
			// If the key doesn't exist at this depth, we will just create an empty array
			// to hold the next value, allowing us to create the arrays to hold final
			// values at the correct depth. Then we'll keep digging into the array.
			if (!isset($array[$key]) || !is_array($array[$key])) {
				$array[$key] = [];
			}
			
			$array = &$array[$key];
		}
		
		$array[array_shift($keys)] = $value;
		
		return $array;
	}
}

<?php

namespace Larapen\LaravelDistance;

use Larapen\LaravelDistance\Libraries\mysql\DistanceStatement;

class Distance
{
	/**
	 * Get 'SELECT' statement column
	 *
	 * @param $aLon
	 * @param $aLat
	 * @param float $bLon
	 * @param float $bLat
	 * @return bool|string
	 */
	public static function select($aLon, $aLat, $bLon, $bLat)
	{
		return DistanceStatement::select($aLon, $aLat, $bLon, $bLat);
	}
	
	/**
	 * Get 'HAVING' statement condition
	 *
	 * @param null $distance
	 * @return string
	 */
	public static function having($distance = null)
	{
		return DistanceStatement::having($distance);
	}
	
	/**
	 * Get 'ORDER BY' rule
	 *
	 * @param null $order
	 * @return string
	 */
	public static function orderBy($order = null)
	{
		return DistanceStatement::orderBy($order);
	}
}

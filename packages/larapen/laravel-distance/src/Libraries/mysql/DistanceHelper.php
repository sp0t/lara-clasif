<?php
/*
	========================================================================================================================
	Haversine Formula
	=================
	The haversine formula is an equation important in navigation,
	giving great-circle distances between two points on a sphere from their longitudes and latitudes.
	
	FORMULA
	=======
	a = sin²(Δφ/2) + cos φ1 ⋅ cos φ2 ⋅ sin²(Δλ/2)
	c = 2 ⋅ atan2( √a, √(1−a) )
	d = R ⋅ c
	Where: φ (Phi) is latitude, λ (Lambda) is longitude, R is Earth's radius (Radius = 6371 km (3959 mi));
	Note that angles need to be in radians to pass to trig functions!
	-----
	3959 * acos(cos(radians('.$lat.')) * cos(radians(a.lat)) * cos(radians(a.lon) - radians('.$lon.')) + sin(radians('.$lat.')) * sin(radians(a.lat)))) as distance
	
	JavaScript
	==========
	var R = 6371e3; // metres (Calculation: 6371 km x 1000 = 6371000 m (metres) || 3959mi x 1760 = 6967840 yd (yards))
	var φ1 = lat1.toRadians();
	var φ2 = lat2.toRadians();
	var Δφ = (lat2-lat1).toRadians();
	var Δλ = (lon2-lon1).toRadians();
	
	var a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
			Math.cos(φ1) * Math.cos(φ2) *
			Math.sin(Δλ/2) * Math.sin(Δλ/2);
	var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
	
	var d = R * c;
	
	SOURCES
	=======
	http://www.movable-type.co.uk/scripts/latlong.html
	https://developers.google.com/maps/solutions/store-locator/clothing-store-locator#findnearsql
	========================================================================================================================
	
	========================================================================================================================
	Orthodromy Formula
	==================
	An orthodromic or great-circle route on the Earth's surface is the shortest possible real way between any two points.
	
	FORMULA
	=======
	Ortho(A, B) = R x acos[[cos(LatA) x cos(LatB) x cos(LongB-LongA)] + [sin(LatA) x sin(LatB)]]
	
	Where: R is the radius of the Earth (Radius = 6371 km (3959 mi))
	
	NOTE
	====
	The Geonames lat & lon data are in Decimal Degrees (wgs84)
	Decimal Degrees to Radians = RADIANS(DecimalDegrees) or DecimalDegrees * Pi/180
	
	SOURCES
	=======
	https://fr.wikipedia.org/wiki/Orthodromie
	http://www.lion1906.com/Pages/english/orthodromy_and_co.html
	========================================================================================================================
*/

namespace Larapen\LaravelDistance\Libraries\mysql;

use App\Helpers\Number;
use Illuminate\Support\Facades\DB;
use Larapen\LaravelDistance\Helper;

class DistanceHelper
{
	/**
	 * Get the MySQL version
	 *
	 * @param null $pdo
	 * @return int|mixed
	 */
	public static function getMySqlVersion($pdo = null)
	{
		$version = 0;
		
		try {
			if (empty($pdo)) {
				$pdo = DB::connection()->getPdo();
			}
			
			if ($pdo instanceof \PDO) {
				$version = $pdo->query('SELECT VERSION()')->fetchColumn();
				
				$tmp = [];
				preg_match('/^[0-9\.]+/', $version, $tmp);
				if (isset($tmp[0])) {
					$version = $tmp[0];
				}
			}
		} catch (\Exception $e) {}
		
		return $version;
	}
	
	/**
	 * Check if the entered value is the MySQL minimal version
	 *
	 * @param $min
	 * @return bool
	 */
	public static function isMySqlMinVersion($min)
	{
		// Get the MySQL version
		$version = self::getMySqlVersion();
		
		return (version_compare($version, $min) >= 0);
	}
	
	/**
	 * Get MySQL Point Property Functions (related to the MySQL version)
	 *
	 * @param $property
	 * @return string
	 */
	public static function getPointPropertyFunc($property)
	{
		$properties = ['X', 'Y'];
		
		if (in_array($property, $properties)) {
			if (self::isMySqlMinVersion('5.7.6')) {
				$property = 'ST_' . $property;
			}
		}
		
		return $property;
	}
	
	/**
	 * Check If a MySQL function exists
	 *
	 * @param $name
	 * @return bool
	 */
	public static function checkIfFunctionExists($name)
	{
		// Get the app's database name
		$schema = config('database.connections.' . config('database.default', 'mysql') . '.database');
		
		// Check with method #1
		try {
			$sql = 'SHOW FUNCTION STATUS;';
			$entries = DB::select(DB::raw($sql));
			$entries = collect($entries)->whereStrict('Db', $schema)->whereStrict('Name', $name);
			$exists = !$entries->isEmpty();
		} catch (\Exception $e) {
			$exists = false;
		}
		
		// Check with method #2
		if (!$exists) {
			try {
				$sql = 'SELECT ROUTINE_NAME FROM INFORMATION_SCHEMA.ROUTINES WHERE ROUTINE_TYPE="FUNCTION" AND ROUTINE_SCHEMA="' . $schema . '"';
				$entries = DB::select(DB::raw($sql));
				$entries = collect($entries)->whereStrict('ROUTINE_NAME', $name);
				$exists = !$entries->isEmpty();
			} catch (\Exception $e) {
				$exists = false;
			}
		}
		
		return $exists;
	}
	
	/**
	 * Create a MySQL function
	 *
	 * @param $sql
	 * @return bool
	 */
	public static function createFunction($sql)
	{
		try {
			
			// Drop the function, If exists
			$name = self::extractFunctionName($sql);
			if (!empty($name)) {
				$dropSql = 'DROP FUNCTION IF EXISTS ' . $name . ';';
				DB::statement($dropSql);
			}
			
			// Create the function
			DB::statement($sql);
			
			return true;
			
		} catch (\Exception $e) {
			return false;
		}
	}
	
	/**
	 * Extract a MySQL function's name from SQL code
	 *
	 * @param $sql
	 * @return string|null
	 */
	private static function extractFunctionName($sql)
	{
		$name = null;
		
		$tmp = [];
		preg_match('#FUNCTION([^\(]+)\(#i', $sql, $tmp);
		if (isset($tmp[1]) && !empty(trim($tmp[1]))) {
			$name = trim($tmp[1]);
		}
		
		return $name;
	}
	
	/**
	 * Get Distance Range
	 *
	 * @return array
	 */
	public static function distanceRange()
	{
		$distanceRange = [];
		for (
			$iDist = config('distance.distanceRange.min', 0);
			$iDist <= config('distance.distanceRange.max', 500);
			$iDist += config('distance.distanceRange.interval', 50)
		) {
			$distanceRange[$iDist] = $iDist;
		}
		
		return $distanceRange;
	}
	
	/**
	 * Check If a MySQL Distance Calculation function exists
	 *
	 * @param $name
	 * @return bool
	 */
	public static function checkIfDistanceCalculationFunctionExists($name)
	{
		if (request()->ajax()) {
			return false;
		}
		
		if (self::isMySqlMinVersion('5.7.6')) {
			// The 'ST_Distance_Sphere' function is included in MySQL 5.7.6+
			if ($name == 'ST_Distance_Sphere') {
				return true;
			}
		} else {
			/*
			If the MySQL version is < 5.7.6,
			and (by surprise) the admin user has selected the 'MySQL 5.7 Spherical Calculation (ST_Distance_Sphere)'
			that not exists under MySQL 5.7.5 and lower as 'Distance Calculation Formula',
			then set 'Haversine' as the default 'Distance Calculation Formula'.
			*/
			if ($name == 'ST_Distance_Sphere') {
				$name = 'haversine';
			}
		}
		
		if (config('distance.functions.canBeCreated') == false) {
			return false;
		}
		
		return self::checkIfFunctionExists($name);
	}
	
	/**
	 * Create the MySQL Distance Calculation function, If doesn't exist,
	 * Using the Haversine or the Orthodromy formula
	 *
	 * @param string $name
	 * @return bool
	 */
	public static function createDistanceCalculationFunction($name = 'haversine')
	{
		if (request()->ajax()) {
			return false;
		}
		
		if (self::isMySqlMinVersion('5.7.6')) {
			if ($name == 'ST_Distance_Sphere') {
				return true;
			}
		}
		
		if (config('distance.functions.canBeCreated') == false) {
			return false;
		}
		
		if ($name == 'haversine') {
			return self::createHaversineFunction();
		}
		
		if ($name == 'orthodromy') {
			return self::createOrthodromyFunction();
		}
		
		return false;
	}
	
	/**
	 * @param null $name
	 */
	public static function dropDistanceCalculationFunction($name = null)
	{
		$functions = ['haversine', 'orthodromy'];
		foreach ($functions as $function) {
			if (!empty($name) && $name != $function) {
				continue;
			}
			
			$sql = 'DROP FUNCTION IF EXISTS `' . $function . '`;';
			DB::statement($sql);
		}
	}
	
	/**
	 * Create the MySQL Haversine function
	 *
	 * This is a polyfill of the MySQL 'ST_Distance_Sphere' function using the Haversine formula.
	 * ---------------
	 * Unit Conversion
	 * ---------------
	 * The general rule for units is that the output length units are the same as the input length units.
	 *
	 * The OSM way geometry data has length units of degrees of latitude and longitude (SRID=4326).
	 * Therefore, the output units from 'ST_Distance' will also have length units of degrees, which are not really useful.
	 *
	 * There are several things you can do:
	 * - Use 'ST_Distance_Sphere' for fast/approximate distances in metres
	 * - Use 'ST_Distance_Spheroid' for accuracy distances in metres
	 * - Convert the lat/long geometry data types to geography, which automatically makes 'ST_Distance' and other functions to use linear units of metres
	 *
	 * More Info: https://stackoverflow.com/questions/13222061/unit-of-return-value-of-st-distance
	 * ---------------
	 *
	 * USAGE
	 * - Results in Km   : (haversine(POINT(lon1, lat1), POINT(lon2, lat2)) / 1000) AS distance
	 * - Results in Miles: ((haversine(POINT(lon1, lat1), POINT(lon2, lat2)) / 1000) * 0.62137119) AS distance
	 * Where
	 * - X / 1000        => Meters To Km
	 * - X * 0.621371192 => Km To Miles
	 *
	 * @return bool
	 */
	private static function createHaversineFunction()
	{
		// Point Property Functions
		$ptX = self::getPointPropertyFunc('X');
		$ptY = self::getPointPropertyFunc('Y');
		
		// Create the function
		$sql = 'CREATE FUNCTION haversine (point1 POINT, point2 POINT)
	RETURNS FLOAT
	NO SQL DETERMINISTIC
	COMMENT "Returns the distance in degrees on the Earth between two known points of latitude and longitude."
BEGIN
	DECLARE R INTEGER DEFAULT 6371000;
	DECLARE lat1 FLOAT;
	DECLARE lat2 FLOAT;
	DECLARE latDelta FLOAT;
	DECLARE lonDelta FLOAT;
	DECLARE a FLOAT;
	DECLARE c FLOAT;
	DECLARE d FLOAT;
	
	SET lat1 = RADIANS(' . $ptY . '(point1));
	SET lat2 = RADIANS(' . $ptY . '(point2));
	SET latDelta = RADIANS(' . $ptY . '(point2) - ' . $ptY . '(point1));
	SET lonDelta = RADIANS(' . $ptX . '(point2) - ' . $ptX . '(point1));
	
	SET a = SIN(latDelta / 2) * SIN(latDelta / 2) + COS(lat1) * COS(lat2) * SIN(lonDelta / 2) * SIN(lonDelta / 2);
	SET c = 2 * ATAN2(SQRT(a), SQRT(1-a));
	SET d = R * c;
	
	RETURN FLOOR(d);
END;';
		
		return self::createFunction($sql);
	}
	
	/**
	 * Get the MySQL Haversine formula as SQL string
	 *
	 * NOTE: Replace 6371 (the '$R' value) with 3958.756 if you want the answer in miles.
	 *
	 * @param $point1
	 * @param $point2
	 * @return string
	 */
	public static function haversine($point1, $point2)
	{
		// Point Property Functions
		$ptX = self::getPointPropertyFunc('X');
		$ptY = self::getPointPropertyFunc('Y');
		
		// Earth's Radius (6371 km OR 3958.756 mi)
		$R = 6371; // in Km
		if (Helper::isMilesUsingCountry(config('distance.countryCode'))) {
			$R = 3958.756; // in Miles
		}
		$R = Number::toFloat($R); // Prevent $R (3958.756) to become 3958,756 (in other languages)
		
		$lat1 = 'RADIANS(' . $ptY . '(' . $point1 . '))';
		$lat2 = 'RADIANS(' . $ptY . '(' . $point2 . '))';
		$latDelta = 'RADIANS(' . $ptY . '(' . $point2 . ') - ' . $ptY . '(' . $point1 . '))';
		$lonDelta = 'RADIANS(' . $ptX . '(' . $point2 . ') - ' . $ptX . '(' . $point1 . '))';
		
		$a = 'SIN(' . $latDelta . '/2) * SIN(' . $latDelta . '/2) + COS(' . $lat1 . ') * COS(' . $lat2 . ') * SIN(' . $lonDelta . '/2) * SIN(' . $lonDelta . '/2)';
		$c = '2 * ATAN2(SQRT(' . $a . '), SQRT(1-' . $a . '))';
		$formula = $R . ' * ' . $c;
		
		// Get the Distance calculation SQL query
		$sql = '(' . $formula . ') AS ' . config('distance.rename');
		
		return $sql;
	}
	
	/**
	 * Create the MySQL Orthodromy function
	 *
	 * This is a polyfill of the MySQL 'ST_Distance_Sphere' function using the Orthodromy formula.
	 * ---------------
	 * Unit Conversion
	 * ---------------
	 * The general rule for units is that the output length units are the same as the input length units.
	 *
	 * The OSM way geometry data has length units of degrees of latitude and longitude (SRID=4326).
	 * Therefore, the output units from 'ST_Distance' will also have length units of degrees, which are not really useful.
	 *
	 * There are several things you can do:
	 * - Use 'ST_Distance_Sphere' for fast/approximate distances in metres
	 * - Use 'ST_Distance_Spheroid' for accuracy distances in metres
	 * - Convert the lat/long geometry data types to geography, which automatically makes 'ST_Distance' and other functions to use linear units of metres
	 *
	 * More Info: https://stackoverflow.com/questions/13222061/unit-of-return-value-of-st-distance
	 * ---------------
	 *
	 * USAGE
	 * - Results in Km   : (orthodromy(POINT(lon1, lat1), POINT(lon2, lat2)) / 1000) AS distance
	 * - Results in Miles: ((orthodromy(POINT(lon1, lat1), POINT(lon2, lat2)) / 1000) * 0.62137119) AS distance
	 * Where
	 * - X / 1000        => Meters To Km
	 * - X * 0.621371192 => Km To Miles
	 *
	 * @return bool
	 */
	private static function createOrthodromyFunction()
	{
		// Point Property Functions
		$ptX = self::getPointPropertyFunc('X');
		$ptY = self::getPointPropertyFunc('Y');
		
		// Create the function
		$sql = 'CREATE FUNCTION orthodromy (point1 POINT, point2 POINT)
	RETURNS FLOAT
	NO SQL DETERMINISTIC
	COMMENT "Returns the distance in degrees on the Earth between two known points of latitude and longitude."
BEGIN
	DECLARE R FLOAT UNSIGNED DEFAULT 6371000;
	DECLARE lat1 FLOAT;
	DECLARE lat2 FLOAT;
	DECLARE lonDelta FLOAT;
	DECLARE a FLOAT;
	DECLARE c FLOAT;
	DECLARE d FLOAT;
 
	SET lat1 = RADIANS(' . $ptY . '(point1));
	SET lat2 = RADIANS(' . $ptY . '(point2));
	SET lonDelta = RADIANS(' . $ptX . '(point2) - ' . $ptX . '(point1));
	
	SET c = ACOS((COS(lat1) * COS(lat2) * COS(lonDelta)) + (SIN(lat1) * SIN(lat2)));
	SET d = R * c;
 
	RETURN FLOOR(d);
END;';
		
		return self::createFunction($sql);
	}
	
	/**
	 * Get the MySQL Orthodromy formula as SQL string
	 *
	 * NOTE: Replace 6371 (the '$R' value) with 3958.756 if you want the answer in miles.
	 *
	 * @param $point1
	 * @param $point2
	 * @return string
	 */
	public static function orthodromy($point1, $point2)
	{
		// Point Property Functions
		$ptX = self::getPointPropertyFunc('X');
		$ptY = self::getPointPropertyFunc('Y');
		
		// Earth's Radius (6371 km OR 3958.756 mi)
		$R = 6371; // in Km
		if (Helper::isMilesUsingCountry(config('distance.countryCode'))) {
			$R = 3958.756; // in Miles
		}
		$R = Number::toFloat($R); // Prevent $R (3958.756) to become 3958,756 (in other languages)
		
		$lat1 = 'RADIANS(' . $ptY . '(' . $point1 . '))';
		$lat2 = 'RADIANS(' . $ptY . '(' . $point2 . '))';
		$lonDelta = 'RADIANS(' . $ptX . '(' . $point2 . ') - ' . $ptX . '(' . $point1 . '))';
		
		$c = 'ACOS((COS(' . $lat1 . ') * COS(' . $lat2 . ') * COS(' . $lonDelta . ')) + (SIN(' . $lat1 . ') * SIN(' . $lat2 . ')))';
		$formula = $R . ' * ' . $c;
		
		// Get the Distance calculation SQL query
		$sql = '(' . $formula . ') AS ' . config('distance.rename');
		
		return $sql;
	}
}

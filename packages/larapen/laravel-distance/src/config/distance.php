<?php

return [
	
	// Database & connection settings
	'database' => [
		'connection' => null,
		'name'       => '',
	],
	
	// Formula's function settings
	'functions' => [
		'available'    => ['haversine', 'orthodromy', 'ST_Distance_Sphere'],
		'default'      => 'haversine',
		// If the MySQL 5.7.6 'ST_Distance_Sphere' function is not available,
		// then polyfill it (using the 'haversine' or 'orthodromy' formula)
		'canBeCreated' => false,
	],
	
	// Renaming of the Distance calculation (Used in the SELECT statement)
	'rename' => 'distance',
	
	// 'ASC' or 'DESC' - OrderBy rename column (example: 'distance ASC')
	'orderBy' => 'ASC',
	
	// Default distance value (Used in the HAVING statement)
	'defaultDistance' => 50,
	
	// Distance Range
	'distanceRange' => [
		'min'      => 0,
		'max'      => 500,
		'interval' => 50,
	],
	
	// Country for which distance must be calculated
	'countryCode' => 'US',
	
	/*
	 |-----------------------------------------------------------------------------------------------
	 | Miles unit use countries
	 |-----------------------------------------------------------------------------------------------
	 | While most countries replaced the mile with the kilometre when switching to the International System of Units,
	 | the international mile continues to be used in some countries, such as:
	 | Liberia (LR), Myanmar (MM), the United Kingdom (UK) and the United States (US).
	 |
	 | More information here: https://en.wikipedia.org/wiki/Mile
	 |
	 */
	'mileUseCountries' => ['LR', 'MM', 'UK', 'US'],

];

<?php
try {
	
	/* FILES */
	// Remove specific DB Cache
	$functions = ['haversine', 'orthodromy'];
	foreach ($functions as $function) {
		$cacheId = 'checkIfMySQLFunctionExists.' . $function;
		if (\Cache::has($cacheId)) {
			\Cache::forget($cacheId);
		}
	}
	
} catch (\Exception $e) {
}

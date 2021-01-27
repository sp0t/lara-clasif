<?php
try {
	
	/* DATABASE */
	$setting = \App\Models\Setting::where('key', 'backup')->first();
	if (empty($setting)) {
		$settingData = [
			'key'         => 'backup',
			'name'        => 'Backup',
			'value'       => null,
			'description' => 'Backup Configuration',
			'field'       => null,
			'parent_id'   => 0,
			'lft'         => 34,
			'rgt'         => 35,
			'depth'       => 1,
			'active'      => 1,
		];
		$setting = \App\Models\Setting::create($settingData);
	}
	
} catch (\Exception $e) {
}

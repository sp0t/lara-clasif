<?php
try {
	
	/* DATABASE */
	if (\Schema::hasColumn('cache', 'value')) {
		Schema::table('cache', function ($table) {
			$table->dropColumn('value');
		});
		\Schema::table('cache', function ($table) {
			$table->mediumText('value')->after('key')->nullable();
		});
	}
	$setting = \App\Models\Setting::where('key', 'optimization')->first();
	if (empty($setting)) {
		$settingData = [
			'key'         => 'optimization',
			'name'        => 'Optimization',
			'value'       => null,
			'description' => 'Optimization Tools',
			'field'       => null,
			'parent_id'   => 0,
			'lft'         => 24,
			'rgt'         => 25,
			'depth'       => 1,
			'active'      => 1,
		];
		$setting = \App\Models\Setting::create($settingData);
	}
	$setting = \App\Models\Setting::where('key', 'upload')->first();
	if (!empty($setting)) {
		$setting->lft = 14;
		$setting->rgt = 15;
		$setting->save();
	}
	$setting = \App\Models\Setting::where('key', 'geo_location')->first();
	if (!empty($setting)) {
		$setting->lft = 16;
		$setting->rgt = 17;
		$setting->save();
	}
	$setting = \App\Models\Setting::where('key', 'security')->first();
	if (!empty($setting)) {
		$setting->lft = 18;
		$setting->rgt = 19;
		$setting->save();
	}
	$setting = \App\Models\Setting::where('key', 'social_auth')->first();
	if (!empty($setting)) {
		$setting->lft = 20;
		$setting->rgt = 21;
		$setting->save();
	}
	$setting = \App\Models\Setting::where('key', 'social_link')->first();
	if (!empty($setting)) {
		$setting->lft = 22;
		$setting->rgt = 23;
		$setting->save();
	}
	$setting = \App\Models\Setting::where('key', 'seo')->first();
	if (!empty($setting)) {
		$setting->lft = 26;
		$setting->rgt = 27;
		$setting->save();
	}
	$setting = \App\Models\Setting::where('key', 'other')->first();
	if (!empty($setting)) {
		$setting->lft = 28;
		$setting->rgt = 29;
		$setting->save();
	}
	$setting = \App\Models\Setting::where('key', 'cron')->first();
	if (!empty($setting)) {
		$setting->lft = 30;
		$setting->rgt = 31;
		$setting->save();
	}
	$setting = \App\Models\Setting::where('key', 'footer')->first();
	if (!empty($setting)) {
		$setting->lft = 32;
		$setting->rgt = 33;
		$setting->save();
	}
	
	/* FILES */
	\File::delete(app_path('Console/Commands/AdsCleaner.php'));
	
} catch (\Exception $e) {}

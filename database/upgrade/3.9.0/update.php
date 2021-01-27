<?php
try {
	
	\File::delete(config_path('laravel-backup.php'));
	\File::delete(base_path('packages/larapen/admin/src/config/laravel-backup.php'));
	
	if (!\File::exists(storage_path('framework/plugins'))) {
		\File::makeDirectory(storage_path('framework/plugins'));
	}
	
} catch (\Exception $e) {}

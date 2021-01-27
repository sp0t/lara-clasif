<?php
try {
	
	/* FILES */
	\File::delete(app_path('Helpers/Search.php'));
	
	$migrationFilesPath = rtrim(base_path('database/upgrade/'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	$versionsDirsPaths = array_filter(glob($migrationFilesPath . '*'), 'is_dir');
	foreach ($versionsDirsPaths as $versionPath) {
		$version = last(explode(DIRECTORY_SEPARATOR, $versionPath));
		if (!preg_match('#^([0-9]+)\.([0-9]+)\.([0-9]+)$#', $version)) {
			\File::deleteDirectory($versionPath);
		}
	}
	
	
	/* DATABASE */
	if (\Schema::hasColumn('users', 'provider_id')) {
		\Schema::table('users', function ($table) {
			$table->string('provider_id', 50)->nullable()->change();
		});
	}
	
} catch (\Exception $e) {
}

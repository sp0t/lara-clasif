<?php
try {
	
	/* DATABASE */
	if (!\Schema::hasColumn('posts', 'archived_manually')) {
		\Schema::table('posts', function ($table) {
			$table->tinyInteger('archived_manually', false, true)->after('archived_at')->nullable()->default(0);
		});
	}
	
	
	/* FILES */
	\File::deleteDirectory(app_path('Helpers/Validator/'));
	
	\File::delete(app_path('Providers/ValidatorServiceProvider.php'));
	
} catch (\Exception $e) {}

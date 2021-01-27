<?php
try {
	
	/* FILES */
	\File::deleteDirectory(base_path('packages/torann/'));
	\File::delete(app_path('Http/Controllers/InstallController.php'));
	\File::delete(app_path('Http/Controllers/UpgradeController.php'));
	
	/* DATABASE */
	
} catch (\Exception $e) {
}

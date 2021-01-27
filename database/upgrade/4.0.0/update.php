<?php
try {
	
	\File::delete(app_path('Http/Controllers/Admin/AjaxController.php'));
	
	\File::deleteDirectory(base_path('packages/larapen/admin/src/app/Http/Controllers/Auth/'));
	\File::deleteDirectory(base_path('packages/larapen/admin/src/app/Http/Middleware/'));
	\File::deleteDirectory(base_path('packages/larapen/admin/src/app/Http/Requests/'));
	\File::deleteDirectory(base_path('packages/larapen/admin/src/database/'));
	
	\File::delete(base_path('packages/larapen/admin/src/app/Http/Controllers/BackupController.php'));
	\File::delete(base_path('packages/larapen/admin/src/app/Http/Controllers/DashboardController.php'));
	\File::delete(base_path('packages/larapen/admin/src/app/Http/Controllers/LanguageController.php'));
	\File::delete(base_path('packages/larapen/admin/src/app/Http/Controllers/SettingController.php'));
	\File::delete(base_path('packages/larapen/admin/src/config/backup.php'));
	\File::delete(base_path('packages/larapen/admin/src/RouteCrud.php'));
	
} catch (\Exception $e) {}

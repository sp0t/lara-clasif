<?php
try {
	
	\File::delete(app_path('Helpers/Geo.php'));
	\File::delete(app_path('Http/Controllers/Admin/CacheController.php'));
	\File::delete(app_path('Http/Controllers/Admin/MaintenanceController.php'));
	\File::delete(app_path('Http/Controllers/Admin/TestCronController.php'));
	\File::delete(app_path('Http/Controllers/Post/PackageController.php'));
	\File::delete(app_path('Http/Middleware/ClearCache.php'));
	\File::delete(base_path('bootstrap/autoload.php'));
	\File::delete(base_path('config/compile.php'));
	\File::delete(base_path('config/elfinder.php'));
	
	\File::deleteDirectory(base_path('packages/barryvdh/'));
	\File::deleteDirectory(base_path('packages/larapen/admin/src/public/'), true);
	\File::deleteDirectory(base_path('packages/larapen/admin/src/resources/lang/'), true);
	\File::deleteDirectory(base_path('packages/larapen/admin/src/resources/views/'), true);
	\File::deleteDirectory(base_path('packages/larapen/admin/src/resources/error_views/'));
	\File::deleteDirectory(base_path('packages/larapen/admin/src/resources/views-elfinder/'));
	\File::deleteDirectory(public_path('vendor/admin/elfinder/'));
	\File::deleteDirectory(public_path('vendor/admin/colorbox/'));
	\File::deleteDirectory(storage_path('database/upgrade/'));
	
	if (\Jackiedo\DotenvEditor\Facades\DotenvEditor::keyExists('APP_FALLBACK_LOCALE')) {
		\Jackiedo\DotenvEditor\Facades\DotenvEditor::deleteKey('APP_FALLBACK_LOCALE');
	}
	if (\Jackiedo\DotenvEditor\Facades\DotenvEditor::keyExists('APP_LOG_LEVEL')) {
		\Jackiedo\DotenvEditor\Facades\DotenvEditor::deleteKey('APP_LOG_LEVEL');
	}
	if (\Jackiedo\DotenvEditor\Facades\DotenvEditor::keyExists('APP_LOG')) {
		\Jackiedo\DotenvEditor\Facades\DotenvEditor::deleteKey('APP_LOG');
	}
	if (\Jackiedo\DotenvEditor\Facades\DotenvEditor::keyExists('APP_LOG_MAX_FILES')) {
		\Jackiedo\DotenvEditor\Facades\DotenvEditor::deleteKey('APP_LOG_MAX_FILES');
	}
	if (\Jackiedo\DotenvEditor\Facades\DotenvEditor::keyExists('MULTI_COUNTRIES_SEO_LINKS')) {
		\Jackiedo\DotenvEditor\Facades\DotenvEditor::deleteKey('MULTI_COUNTRIES_SEO_LINKS');
	}
	\Jackiedo\DotenvEditor\Facades\DotenvEditor::setKey('LOG_CHANNEL', 'daily');
	\Jackiedo\DotenvEditor\Facades\DotenvEditor::setKey('LOG_LEVEL', 'debug');
	\Jackiedo\DotenvEditor\Facades\DotenvEditor::setKey('LOG_DAYS', 2);
	\Jackiedo\DotenvEditor\Facades\DotenvEditor::save();
	
	if (\File::exists(public_path('uploads/app/'))) {
		\File::moveDirectory(public_path('uploads/app/'), storage_path('app/public/app/'), true);
	}
	if (\File::exists(public_path('uploads/files/'))) {
		\File::moveDirectory(public_path('uploads/files/'), storage_path('app/public/files/'), true);
	}
	if (\File::exists(public_path('uploads/pictures/'))) {
		\File::moveDirectory(public_path('uploads/pictures/'), storage_path('app/public/pictures/'), true);
	}
	if (\File::exists(public_path('uploads/resumes/'))) {
		\File::moveDirectory(public_path('uploads/resumes/'), storage_path('app/public/resumes/'), true);
	}
	\File::move(public_path('uploads/index.html'), public_path('app/public/index.html'));
	
} catch (\Exception $e) {}

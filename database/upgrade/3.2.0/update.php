<?php
try {
	
	\File::delete(app_path('Http/Controllers/Account/MessagesController.php'));
	\File::delete(app_path('Http/Middleware/Admin.php'));
	\File::delete(base_path('config/javascript.php'));
	
	\File::moveDirectory(public_path('vendor/adminlte/plugins/jquery/'), public_path('vendor/adminlte/plugins/jQuery/'));
	\File::move(public_path('vendor/adminlte/plugins/jQuery/jQuery-2.2.0.min.js'), public_path('vendor/adminlte/plugins/jQuery/jquery-2.2.0.min.js'));
	\File::move(public_path('vendor/adminlte/plugins/jQuery/jQuery-2.2.3.min.js'), public_path('vendor/adminlte/plugins/jQuery/jquery-2.2.3.min.js'));
	
} catch (\Exception $e) {}

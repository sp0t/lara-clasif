<?php
try {
	
	/* DATABASE */
	if (!\Schema::hasColumn('users', 'email_verified_at')) {
		\Schema::table('users', function ($table) {
			$table->timestamp('email_verified_at')->after('email')->nullable();
		});
	}
	
	/* FILES */
	\File::delete(app_path('Http/Middleware/TransformInput.php'));
	\File::delete(app_path('Http/Requests/RegisterRequest.php'));
	
} catch (\Exception $e) {
}

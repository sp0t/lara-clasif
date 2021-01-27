<?php
try {
	
	/* .ENV */
	$needToBeSaved = false;
	if (\Jackiedo\DotenvEditor\Facades\DotenvEditor::keyExists('RECAPTCHA_PUBLIC_KEY')) {
		$recaptchaPublicKey = \Jackiedo\DotenvEditor\Facades\DotenvEditor::getValue('RECAPTCHA_PUBLIC_KEY');
		\Jackiedo\DotenvEditor\Facades\DotenvEditor::setKey('RECAPTCHA_SITE_KEY', $recaptchaPublicKey);
		\Jackiedo\DotenvEditor\Facades\DotenvEditor::deleteKey('RECAPTCHA_PUBLIC_KEY');
		$needToBeSaved = true;
	}
	if (\Jackiedo\DotenvEditor\Facades\DotenvEditor::keyExists('RECAPTCHA_PRIVATE_KEY')) {
		$recaptchaPrivateKey = \Jackiedo\DotenvEditor\Facades\DotenvEditor::getValue('RECAPTCHA_PRIVATE_KEY');
		\Jackiedo\DotenvEditor\Facades\DotenvEditor::setKey('RECAPTCHA_SECRET_KEY', $recaptchaPrivateKey);
		\Jackiedo\DotenvEditor\Facades\DotenvEditor::deleteKey('RECAPTCHA_PRIVATE_KEY');
		$needToBeSaved = true;
	}
	if ($needToBeSaved) {
		\Jackiedo\DotenvEditor\Facades\DotenvEditor::save();
	}
	
	/* FILES */
	\File::deleteDirectory(base_path('packages/mcamara/laravel-localization/src/Exceptions/'));
	\File::deleteDirectory(base_path('packages/mcamara/laravel-localization/src/Facades/'));
	\File::deleteDirectory(base_path('packages/mcamara/laravel-localization/src/Middleware/'));
	\File::deleteDirectory(base_path('packages/mcamara/laravel-localization/src/Traits/'));
	\File::delete(base_path('packages/mcamara/laravel-localization/src/LanguageNegotiator.php'));
	\File::delete(base_path('packages/mcamara/laravel-localization/src/LaravelLocalization.php'));
	\File::delete(base_path('packages/mcamara/laravel-localization/src/LaravelLocalizationServiceProvider.php'));
	
} catch (\Exception $e) {}

<?php
/**
 * Laravel reCAPTCHA
 * Author: Bedigit
 * Web: www.bedigit.com
 */

/*
 * To configure correctly please visit https://developers.google.com/recaptcha/docs/start
 */
return [
	
	/*
	 * The site key
	 * Get site key @ www.google.com/recaptcha/admin
	 */
	'site_key' => env('RECAPTCHA_SITE_KEY', ''),
	
	/*
	 * The secret key
	 * Get secret key @ www.google.com/recaptcha/admin
	 */
	'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),
	
	/*
	 * The language code
	 * Get more info @ https://developers.google.com/recaptcha/docs/versions
	 */
	'lang' => 'en',
	
	/*
	 * ReCATCHA version
	 * Supported: "v2", "invisible", "v3",
	 *
	 * Get more info @ https://developers.google.com/recaptcha/docs/versions
	 */
	'version' => env('RECAPTCHA_VERSION', 'v2'),
	
	/*
	 * IP addresses for which validation will be skipped
	 */
	'skip_ip' => [],
	
	/*
	 * Default route called to check the Google reCAPTCHA token
	 */
	'validation_route' => env('RECAPTCHA_VALIDATION_ROUTE', 'lara-recaptcha/validate'),
	
	/*
	 * The name of the parameter used to send Google reCAPTCHA token to verify route
	 */
	'token_parameter_name' => env('RECAPTCHA_TOKEN_PARAMETER_NAME', 'token'),
];

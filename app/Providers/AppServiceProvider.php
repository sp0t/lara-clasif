<?php
/**
 * JobClass - Job Board Web Application
 * Copyright (c) BedigitCom. All Rights Reserved
 *
 * Website: http://www.bedigit.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from Codecanyon,
 * Please read the full License from here - http://codecanyon.net/licenses/standard
 */

namespace App\Providers;

use App\Helpers\Files\Storage\StorageDisk;
use App\Models\Language;
use App\Models\Permission;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Jenssegers\Date\Date;

class AppServiceProvider extends ServiceProvider
{
	private $cacheExpiration = 86400; // Cache for 1 day (60 * 60 * 24)
	
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}
	
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		try {
			// Specified key was too long error
			Schema::defaultStringLength(191);
		} catch (\Exception $e) {
			//...
		}
		
		// Create the local storage symbolic link
		$this->checkAndCreateStorageSymlink();
		
		// Setup ACL system
		$this->setupAclSystem();
		
		// Force HTTPS protocol
		$this->forceHttps();
		
		// Create setting config var for the default language
		$this->getDefaultLanguage();
		
		// Create config vars from settings table
		$this->createConfigVars();
		
		// Update the config vars
		$this->setConfigVars();
		
		// Check the Multi-Countries feature
		// To prevent the Locale (Language Abbr) & the Country Code conflict,
		// Don't hive the Default Locale in URL
		if (config('settings.seo.multi_countries_urls')) {
			config()->set('laravellocalization.hideDefaultLocaleInURL', false);
		}
		
		// Date default encoding & translation
		// The translation option is overwritten when applying the front-end settings
		if (config('settings.app.date_force_utf8')) {
			Date::setUTF8(true);
		}
		appSetLocale(config('appLang.locale', 'en_US'));
	}
	
	/**
	 * Check the local storage symbolic link and Create it if does not exist.
	 */
	private function checkAndCreateStorageSymlink()
	{
		$symlink = public_path('storage');
		
		try {
			if (!is_link($symlink)) {
				// Symbolic links on windows are created by symlink() which accept only absolute paths.
				// Relative paths on windows are not supported for symlinks: http://php.net/manual/en/function.symlink.php
				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
					$exitCode = Artisan::call('storage:link');
				} else {
					symlink('../storage/app/public', './storage');
				}
			}
		} catch (\Exception $e) {
			$message = ($e->getMessage() != '') ? $e->getMessage() : 'Error with the PHP symlink() function';
			
			$docSymlink = 'http://support.bedigit.com/help-center/articles/71/images-dont-appear-in-my-website';
			$docDirExists = 'https://support.bedigit.com/help-center/articles/1/10/80/symlink-file-exists-or-no-such-file-or-directory';
			if (
				Str::contains($message, 'File exists')
				|| Str::contains($message, 'No such file or directory')
			) {
				$docSymlink = $docDirExists;
			}
			
			$message = $message . ' - Please <a href="' . $docSymlink . '" target="_blank">see this article</a> for more information.';
			
			flash($message)->error();
		}
	}
	
	/**
	 * Force HTTPS protocol
	 */
	private function forceHttps()
	{
		if (config('larapen.core.forceHttps') == true) {
			URL::forceScheme('https');
		}
	}
	
	/**
	 * Create setting config var for the default language
	 */
	private function getDefaultLanguage()
	{
		try {
			// Get the DB default language
			$defaultLang = Cache::remember('language.default', $this->cacheExpiration, function () {
				$defaultLang = Language::where('default', 1)->first();
				
				return $defaultLang;
			});
			
			if (!empty($defaultLang)) {
				// Create DB default language settings
				config()->set('appLang', $defaultLang->toArray());
				
				// Set dates default locale
				appSetLocale(config('appLang.locale'));
			} else {
				config()->set('appLang.abbr', config('app.locale'));
			}
		} catch (\Exception $e) {
			config()->set('appLang.abbr', config('app.locale'));
		}
	}
	
	/**
	 * Create config vars from settings table
	 */
	private function createConfigVars()
	{
		// Get some default values
		config()->set('settings.app.purchase_code', config('larapen.core.purchaseCode'));
		config()->set('settings.app.default_date_format', config('larapen.core.defaultDateFormat'));
		config()->set('settings.app.default_datetime_format', config('larapen.core.defaultDatetimeFormat'));
		
		// Check DB connection and catch it
		try {
			// Get all settings from the database
			$settings = Cache::remember('settings.active', $this->cacheExpiration, function () {
				$settings = Setting::where('active', 1)->get();
				
				return $settings;
			});
			
			// Bind all settings to the Laravel config, so you can call them like
			if ($settings->count() > 0) {
				foreach ($settings as $setting) {
					if (is_array($setting->value) && count($setting->value) > 0) {
						foreach ($setting->value as $subKey => $value) {
							if (!empty($value)) {
								config()->set('settings.' . $setting->key . '.' . $subKey, $value);
							}
						}
					}
				}
			}
		} catch (\Exception $e) {
			config()->set('settings.error', true);
			config()->set('settings.app.logo', config('larapen.core.logo'));
		}
	}
	
	/**
	 * Update the config vars
	 */
	private function setConfigVars()
	{
		// Cache
		$this->setCacheConfigVars();
		
		// App
		config()->set('app.name', config('settings.app.app_name'));
		config()->set('app.timezone', config('settings.app.default_timezone', config('app.timezone')));
		// reCAPTCHA
		config()->set('recaptcha.site_key', env('RECAPTCHA_SITE_KEY', config('settings.security.recaptcha_site_key')));
		config()->set('recaptcha.secret_key', env('RECAPTCHA_SECRET_KEY', config('settings.security.recaptcha_secret_key')));
		config()->set('recaptcha.version', env('RECAPTCHA_VERSION', config('settings.security.recaptcha_version', 'v2')));
		$recaptchaSkipIps = env('RECAPTCHA_SKIP_IPS', config('settings.security.recaptcha_skip_ips', ''));
		$recaptchaSkipIpsArr = preg_split('#[:,;\s]+#ui', $recaptchaSkipIps);
		$recaptchaSkipIpsArr = array_filter(array_map('trim', $recaptchaSkipIpsArr));
		config()->set('recaptcha.skip_ip', $recaptchaSkipIpsArr);
		// Mail
		config()->set('mail.default', env('MAIL_MAILER', env('MAIL_DRIVER', config('settings.mail.driver'))));
		config()->set('mail.from.address', env('MAIL_FROM_ADDRESS', config('settings.mail.email_sender')));
		config()->set('mail.from.name', env('MAIL_FROM_NAME', config('settings.app.app_name')));
		// Sendmail
		config()->set('mail.mailers.sendmail.path', env('MAIL_SENDMAIL', config('settings.mail.sendmail_path')));
		// SMTP
		config()->set('mail.mailers.smtp.host', env('MAIL_HOST', config('settings.mail.host')));
		config()->set('mail.mailers.smtp.port', env('MAIL_PORT', config('settings.mail.port')));
		config()->set('mail.mailers.smtp.encryption', env('MAIL_ENCRYPTION', config('settings.mail.encryption')));
		config()->set('mail.mailers.smtp.username', env('MAIL_USERNAME', config('settings.mail.username')));
		config()->set('mail.mailers.smtp.password', env('MAIL_PASSWORD', config('settings.mail.password')));
		// Mailgun
		config()->set('services.mailgun.domain', env('MAILGUN_DOMAIN', config('settings.mail.mailgun_domain')));
		config()->set('services.mailgun.secret', env('MAILGUN_SECRET', config('settings.mail.mailgun_secret')));
		config()->set('services.mailgun.endpoint', env('MAILGUN_ENDPOINT', config('settings.mail.mailgun_endpoint', 'api.mailgun.net')));
		// Postmark
		config()->set('services.postmark.token', env('POSTMARK_TOKEN', config('settings.mail.postmark_token')));
		// Amazon SES
		config()->set('services.ses.key', env('SES_KEY', config('settings.mail.ses_key')));
		config()->set('services.ses.secret', env('SES_SECRET', config('settings.mail.ses_secret')));
		config()->set('services.ses.region', env('SES_REGION', config('settings.mail.ses_region')));
		// Mandrill
		config()->set('services.mandrill.secret', env('MANDRILL_SECRET', config('settings.mail.mandrill_secret')));
		// Sparkpost
		config()->set('services.sparkpost.secret', env('SPARKPOST_SECRET', config('settings.mail.sparkpost_secret')));
		// Facebook
		config()->set('services.facebook.client_id', env('FACEBOOK_CLIENT_ID', config('settings.social_auth.facebook_client_id')));
		config()->set('services.facebook.client_secret', env('FACEBOOK_CLIENT_SECRET', config('settings.social_auth.facebook_client_secret')));
		// LinkedIn
		config()->set('services.linkedin.client_id', env('LINKEDIN_CLIENT_ID', config('settings.social_auth.linkedin_client_id')));
		config()->set('services.linkedin.client_secret', env('LINKEDIN_CLIENT_SECRET', config('settings.social_auth.linkedin_client_secret')));
		// Twitter
		config()->set('services.twitter.client_id', env('TWITTER_CLIENT_ID', config('settings.social_auth.twitter_client_id')));
		config()->set('services.twitter.client_secret', env('TWITTER_CLIENT_SECRET', config('settings.social_auth.twitter_client_secret')));
		// Google
		config()->set('services.google.client_id', env('GOOGLE_CLIENT_ID', config('settings.social_auth.google_client_id')));
		config()->set('services.google.client_secret', env('GOOGLE_CLIENT_SECRET', config('settings.social_auth.google_client_secret')));
		config()->set('services.googlemaps.key', env('GOOGLE_MAPS_API_KEY', config('settings.other.googlemaps_key')));
		// Meta-tags
		config()->set('meta-tags.title', config('settings.app.slogan'));
		config()->set('meta-tags.open_graph.site_name', config('settings.app.app_name'));
		config()->set('meta-tags.twitter.creator', config('settings.seo.twitter_username'));
		config()->set('meta-tags.twitter.site', config('settings.seo.twitter_username'));
		// Cookie Consent
		config()->set('cookie-consent.enabled', env('COOKIE_CONSENT_ENABLED', config('settings.other.cookie_consent_enabled')));
		
		// Admin panel
		config()->set('larapen.admin.skin', config('settings.style.admin_skin'));
		config()->set('larapen.admin.default_date_format', config('settings.app.default_date_format'));
		config()->set('larapen.admin.default_datetime_format', config('settings.app.default_datetime_format'));
		if (Str::contains(config('settings.footer.show_powered_by'), 'fa')) {
			config()->set('larapen.admin.show_powered_by', Str::contains(config('settings.footer.show_powered_by'), 'fa-check-square-o') ? 1 : 0);
		} else {
			config()->set('larapen.admin.show_powered_by', config('settings.footer.show_powered_by'));
		}
		
		// Backup Disks Setup
		StorageDisk::setBackupDisks();
	}
	
	/**
	 * Update the Cache config vars
	 */
	private function setCacheConfigVars()
	{
		config()->set('cache.default', env('CACHE_DRIVER', 'file'));
		// Memcached
		config()->set('cache.stores.memcached.persistent_id', env('MEMCACHED_PERSISTENT_ID'));
		config()->set('cache.stores.memcached.sasl', [
			env('MEMCACHED_USERNAME'),
			env('MEMCACHED_PASSWORD'),
		]);
		$memcachedServers = [];
		$i = 1;
		while (getenv('MEMCACHED_SERVER_' . $i . '_HOST')) {
			if ($i == 1) {
				$host = '127.0.0.1';
				$port = 11211;
			} else {
				$host = null;
				$port = null;
			}
			$memcachedServers[$i]['host'] = env('MEMCACHED_SERVER_' . $i . '_HOST', $host);
			$memcachedServers[$i]['port'] = env('MEMCACHED_SERVER_' . $i . '_PORT', $port);
			$i++;
		}
		config()->set('cache.stores.memcached.servers', $memcachedServers);
		// Redis
		config()->set('database.redis.client', env('REDIS_CLIENT', 'predis'));
		config()->set('database.redis.default.host', env('REDIS_HOST', '127.0.0.1'));
		config()->set('database.redis.default.password', env('REDIS_PASSWORD', null));
		config()->set('database.redis.default.port', env('REDIS_PORT', 6379));
		config()->set('database.redis.default.database', env('REDIS_DB', 0));
		config()->set('database.redis.options.cluster', env('REDIS_CLUSTER', 'predis'));
		if (config('settings.optimization.redis_cluster_activation')) {
			$redisClusters = [];
			$i = 1;
			while (getenv('REDIS_CLUSTER_' . $i . '_HOST')) {
				$redisClusters[$i]['host'] = env('REDIS_CLUSTER_' . $i . '_HOST');
				$redisClusters[$i]['password'] = env('REDIS_CLUSTER_' . $i . '_PASSWORD');
				$redisClusters[$i]['port'] = env('REDIS_CLUSTER_' . $i . '_PORT');
				$redisClusters[$i]['database'] = env('REDIS_CLUSTER_' . $i . '_DB');
				$i++;
			}
			config()->set('database.redis.clusters.default', $redisClusters);
		}
		// Check if the caching is disabled, then disabled it!
		if (config('settings.optimization.cache_driver') == 'array') {
			config()->set('settings.optimization.cache_expiration', '-1');
		}
	}
	
	/**
	 * Setup ACL system
	 * Check & Migrate Old admin authentication to ACL system
	 */
	private function setupAclSystem()
	{
		if (isFromAdminPanel()) {
			// Check & Fix the default Permissions
			if (!Permission::checkDefaultPermissions()) {
				Permission::resetDefaultPermissions();
			}
		}
	}
}

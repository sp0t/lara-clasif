<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
	/**
	 * The policy mappings for the application.
	 *
	 * @var array
	 */
	protected $policies = [
		// 'App\Model' => 'App\Policies\ModelPolicy',
	];
	
	/**
	 * Register any authentication / authorization services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->registerPolicies();
		
		// Check and Load the API Plugin
		$apiPlugin = load_installed_plugin('apijc');
		if (!empty($apiPlugin)) {
			
			Passport::routes();
			
			Passport::tokensExpireIn(now()->addDays(15));
			
			Passport::refreshTokensExpireIn(now()->addDays(30));
			
			/*
			 * The implicit grant is similar to the authorization code grant;
			 * however, the token is returned to the client without exchanging an authorization code.
			 */
			Passport::enableImplicitGrant();
			
		}
	}
}

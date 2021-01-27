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

namespace App\Http\Controllers\Auth;

use App\Helpers\Ip;
use App\Http\Controllers\FrontController;
use App\Models\Blacklist;
use App\Models\Permission;
use App\Models\Post;
use App\Models\User;
use App\Models\Scopes\ReviewedScope;
use App\Models\Scopes\VerifiedScope;
use App\Notifications\UserActivated;
use App\Notifications\UserNotification;
use App\Helpers\Auth\Traits\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends FrontController
{
	use AuthenticatesUsers;
	
	// If not logged in redirect to
	protected $loginPath = 'login';
	
	// After you've logged in redirect to
	protected $redirectTo = 'account';
	
	// Supported Providers
	private $network = ['facebook', 'linkedin', 'twitter', 'google'];
	
	/**
	 * SocialController constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		// Set default URLs
		$isFromLoginPage = Str::contains(url()->previous(), '/' . trans('routes.login'));
		$this->loginPath = $isFromLoginPage ? config('app.locale') . '/' . trans('routes.login') : url()->previous();
		$this->redirectTo = $isFromLoginPage ? config('app.locale') . '/account' : url()->previous();
	}
	
	/**
	 * Redirect the user to the Provider authentication page.
	 *
	 * @return mixed
	 */
	public function redirectToProvider()
	{
		// Get the Provider and verify that if it's supported
		$provider = getSegment(2);
		if (!in_array($provider, $this->network)) {
			abort(404);
		}
		
		// If previous page is not the Login page...
		if (!Str::contains(url()->previous(), trans('routes.login'))) {
			// Save the previous URL to retrieve it after success or failed login.
			session()->put('url.intended', url()->previous());
		}
		
		// Redirect to the Provider's website
		return Socialite::driver($provider)->redirect();
	}
	
	/**
	 * Obtain the user information from Provider.
	 *
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function handleProviderCallback()
	{
		// Get the Provider and verify that if it's supported
		$provider = getSegment(2);
		if (!in_array($provider, $this->network)) {
			abort(404);
		}
		
		// Check and retrieve previous URL to show the login error on it.
		if (session()->has('url.intended')) {
			$this->loginPath = session()->get('url.intended');
		}
		
		// Get the Country Code
		$countryCode = config('country.code', config('ipCountry.code'));
		
		// API CALL - GET USER FROM PROVIDER
		try {
			$userData = Socialite::driver($provider)->user();
			
			// Data not found
			if (!$userData) {
				$message = t("Unknown error. Please try again in a few minutes.");
				flash($message)->error();
				
				return redirect($this->loginPath);
			}
			
			// Email not found
			if (!$userData || !filter_var($userData->getEmail(), FILTER_VALIDATE_EMAIL)) {
				$message = t("Email address not found. You can't use your :provider account on our website.", ['provider' => mb_ucfirst($provider)]);
				flash($message)->error();
				
				return redirect($this->loginPath);
			}
		} catch (\Exception $e) {
			$message = $e->getMessage();
			if (is_string($message) && !empty($message)) {
				flash($message)->error();
			} else {
				$message = "Unknown error. The social network API doesn't work.";
				flash($message)->error();
			}
			
			return redirect($this->loginPath);
		}
		
		// Debug
		// dd($userData);
		
		// DATA MAPPING
		try {
			$mapUser = [];
			
			// Get User Name (First Name & Last Name)
			$mapUser['name'] = (isset($userData->name) && is_string($userData->name)) ? $userData->name : '';
			if ($mapUser['name'] == '') {
				// facebook
				if (isset($userData->user['first_name']) && isset($userData->user['last_name'])) {
					$mapUser['name'] = $userData->user['first_name'] . ' ' . $userData->user['last_name'];
				}
			}
			if ($mapUser['name'] == '') {
				// linkedin
				$mapUser['name'] = (isset($userData->user['formattedName'])) ? $userData->user['formattedName'] : '';
				if ($mapUser['name'] == '') {
					if (isset($userData->user['firstName']) && isset($userData->user['lastName'])) {
						$mapUser['name'] = $userData->user['firstName'] . ' ' . $userData->user['lastName'];
					}
				}
			}
			
			// Check if the user's email address has been banned
			$bannedUser = Blacklist::ofType('email')->where('entry', $userData->getEmail())->first();
			if (!empty($bannedUser)) {
				$message = t('This user has been banned.');
				flash()->error($message);
				
				return redirect()->guest(trans('routes.login'));
			}
			
			// GET LOCAL USER
			$user = User::withoutGlobalScopes([VerifiedScope::class])->where('provider', $provider)->where('provider_id', $userData->getId())->first();
			
			// CREATE LOCAL USER IF DON'T EXISTS
			if (empty($user)) {
				// Before... Check if user has not signup with an email
				$user = User::withoutGlobalScopes([VerifiedScope::class])->where('email', $userData->getEmail())->first();
				if (empty($user)) {
					$userInfo = [
						'country_code'   => $countryCode,
						'language_code'  => config('app.locale'),
						'name'           => $mapUser['name'],
						'email'          => $userData->getEmail(),
						'ip_addr'        => Ip::get(),
						'verified_email' => 1,
						'verified_phone' => 1,
						'provider'       => $provider,
						'provider_id'    => $userData->getId(),
						'created_at'     => date('Y-m-d H:i:s'),
					];
					$user = new User($userInfo);
					$user->save();
					
					// Update Ads created by this email
					if (isset($user->id) && $user->id > 0) {
						Post::withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])->where('email', $userInfo['email'])->update(['user_id' => $user->id]);
					}
					
					// Send Admin Notification Email
					if (config('settings.mail.admin_notification') == 1) {
						try {
							// Get all admin users
							$admins = User::permission(Permission::getStaffPermissions())->get();
							if ($admins->count() > 0) {
								Notification::send($admins, new UserNotification($user));
								/*
								foreach ($admins as $admin) {
									Notification::route('mail', $admin->email)->notify(new UserNotification($user));
								}
								*/
							}
						} catch (\Exception $e) {
							flash($e->getMessage())->error();
						}
					}
					
					/*
					// Send Confirmation Email or SMS
					if (config('settings.mail.confirmation') == 1) {
						try {
							$user->notify(new UserActivated($user));
						} catch (\Exception $e) {
							flash($e->getMessage())->error();
						}
					}
					*/
					
				} else {
					// Update 'created_at' if empty (for time ago module)
					if (empty($user->created_at)) {
						$user->created_at = date('Y-m-d H:i:s');
					}
					$user->verified_email = 1;
					$user->verified_phone = 1;
					$user->save();
				}
			} else {
				// Update 'created_at' if empty (for time ago module)
				if (empty($user->created_at)) {
					$user->created_at = date('Y-m-d H:i:s');
				}
				$user->verified_email = 1;
				$user->verified_phone = 1;
				$user->save();
			}
			
			// GET A SESSION FOR USER
			if (Auth::loginUsingId($user->id)) {
				return redirect()->intended($this->redirectTo);
			} else {
				$message = t("Error on user's login.");
				flash($message)->error();
				
				return redirect($this->loginPath);
			}
		} catch (\Exception $e) {
			$message = $e->getMessage();
			if (is_string($message) && !empty($message)) {
				flash($message)->error();
			} else {
				$message = "Unknown error. The service does not work.";
				flash($message)->error();
			}
			
			return redirect($this->loginPath);
		}
	}
}

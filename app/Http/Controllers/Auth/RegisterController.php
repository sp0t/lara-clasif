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
use App\Helpers\Localization\Country as CountryLocalization;
use App\Helpers\Localization\Helpers\Country as CountryLocalizationHelper;
use App\Http\Controllers\Auth\Traits\VerificationTrait;
use App\Http\Controllers\FrontController;
use App\Http\Requests\UserRequest;
use App\Models\Gender;
use App\Models\Permission;
use App\Models\Resume;
use App\Models\User;
use App\Models\UserType;
use App\Notifications\UserActivated;
use App\Notifications\UserNotification;
use App\Helpers\Auth\Traits\RegistersUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Torann\LaravelMetaTags\Facades\MetaTag;

class RegisterController extends FrontController
{
	use RegistersUsers, VerificationTrait;
	
	/**
	 * Where to redirect users after login / registration.
	 *
	 * @var string
	 */
	protected $redirectTo = '/account';
	
	/**
	 * @var array
	 */
	public $msg = [];
	
	/**
	 * RegisterController constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		// From Laravel 5.3.4 or above
		$this->middleware(function ($request, $next) {
			$this->commonQueries();
			
			return $next($request);
		});
	}
	
	/**
	 * Common Queries
	 */
	public function commonQueries()
	{
		$this->redirectTo = config('app.locale') . '/account';
	}
	
	/**
	 * Show the form the create a new user account.
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function showRegistrationForm()
	{
		$data = [];
		
		// References
		$data['countries'] = CountryLocalizationHelper::transAll(CountryLocalization::getCountries());
		$data['genders']   = Gender::trans()->get();
		$data['userTypes'] = UserType::all();
		
		// Meta Tags
		MetaTag::set('title', getMetaTag('title', 'register'));
		MetaTag::set('description', strip_tags(getMetaTag('description', 'register')));
		MetaTag::set('keywords', getMetaTag('keywords', 'register'));
		
		return view('auth.register.index', $data);
	}
	
	/**
	 * Register a new user account.
	 *
	 * @param UserRequest $request
	 * @return $this|\Illuminate\Http\RedirectResponse
	 */
	public function register(UserRequest $request)
	{
		// Conditions to Verify User's Email or Phone
		$emailVerificationRequired = config('settings.mail.email_verification') == 1 && $request->filled('email');
		$phoneVerificationRequired = config('settings.sms.phone_verification') == 1 && $request->filled('phone');
		
		// New User
		$user  = new User();
		$input = $request->only($user->getFillable());
		foreach ($input as $key => $value) {
			$user->{$key} = $value;
		}
		
		$user->country_code   = config('country.code');
		$user->language_code  = config('app.locale');
		$user->password       = Hash::make($request->input('password'));
		$user->phone_hidden   = $request->input('phone_hidden');
		$user->ip_addr        = Ip::get();
		$user->verified_email = 1;
		$user->verified_phone = 1;
		
		// Email verification key generation
		if ($emailVerificationRequired) {
			$user->email_token    = md5(microtime() . mt_rand());
			$user->verified_email = 0;
		}
		
		// Mobile activation key generation
		if ($phoneVerificationRequired) {
			$user->phone_token    = mt_rand(100000, 999999);
			$user->verified_phone = 0;
		}
		
		// Save
		$user->save();
		
		// Add Job seekers resume
		if ($request->input('user_type_id') == 2) {
			if ($request->hasFile('filename')) {
				// Save user's resume
				$resumeInfo = [
					'country_code' => config('country.code'),
					'user_id'      => $user->id,
					'active'       => 1,
				];
				$resume     = new Resume($resumeInfo);
				$resume->save();
				
				// Upload user's resume
				$resume->filename = $request->file('filename');
				$resume->save();
			}
		}
		
		// Message Notification & Redirection
		$request->session()->flash('message', t("Your account has been created."));
		$nextUrl = config('app.locale') . '/register/finish';
		
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
		
		// Send Verification Link or Code
		if ($emailVerificationRequired || $phoneVerificationRequired) {
			
			// Save the Next URL before verification
			session(['userNextUrl' => $nextUrl]);
			
			// Email
			if ($emailVerificationRequired) {
				// Send Verification Link by Email
				$this->sendVerificationEmail($user);
				
				// Show the Re-send link
				$this->showReSendVerificationEmailLink($user, 'user');
			}
			
			// Phone
			if ($phoneVerificationRequired) {
				// Send Verification Code by SMS
				$this->sendVerificationSms($user);
				
				// Show the Re-send link
				$this->showReSendVerificationSmsLink($user, 'user');
				
				// Go to Phone Number verification
				$nextUrl = config('app.locale') . '/verify/user/phone/';
			}
			
			// Send Confirmation Email or SMS,
			// When User clicks on the Verification Link or enters the Verification Code.
			// Done in the "app/Observers/UserObserver.php" file.
			
		} else {
			
			// Send Confirmation Email or SMS
			if (config('settings.mail.confirmation') == 1) {
				try {
					$user->notify(new UserActivated($user));
				} catch (\Exception $e) {
					flash($e->getMessage())->error();
				}
			}
			
			// Redirect to the user area If Email or Phone verification is not required
			if (Auth::loginUsingId($user->id)) {
				return redirect()->intended(config('app.locale') . '/account');
			}
			
		}
		
		// Redirection
		return redirect($nextUrl);
	}
	
	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
	 */
	public function finish()
	{
		// Keep Success Message for the page refreshing
		session()->keep(['message']);
		if (!session()->has('message')) {
			return redirect(config('app.locale') . '/');
		}
		
		// Meta Tags
		MetaTag::set('title', session('message'));
		MetaTag::set('description', session('message'));
		
		return view('auth.register.finish');
	}
}

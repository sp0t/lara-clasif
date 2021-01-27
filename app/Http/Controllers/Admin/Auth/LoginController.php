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

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Requests\Admin\LoginRequest;
use App\Helpers\Auth\Traits\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Larapen\Admin\app\Http\Controllers\Controller;

class LoginController extends Controller
{
    use AuthenticatesUsers;
    
    protected $loginPath;
    protected $maxAttempts = 5;
    protected $decayMinutes = 30;
    protected $redirectTo;
    protected $redirectAfterLogout;
    protected $data;
    
    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->middleware('guest')->except(['except' => 'logout']);
        
        $this->loginPath = admin_uri('login');
        $this->redirectTo = admin_uri();
        $this->redirectAfterLogout = admin_uri('login');
    }
    
    // -------------------------------------------------------
    // Laravel overwrites for loading admin views
    // -------------------------------------------------------
    
    /**
     * Show the application login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        // Remembering Login
        if (Auth::viaRemember()) {
            return redirect()->intended($this->redirectTo);
        }
        
        $this->data['title'] = trans('admin::messages.login'); // set the page title
        
        return view('admin::auth.login', $this->data);
    }
    
    /**
     * @param LoginRequest $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function login(LoginRequest $request)
    {
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            
            return $this->sendLockoutResponse($request);
        }
        
        // Get credentials values
        $credentials = [
            'email'    => $request->input('email'),
            'password' => $request->input('password'),
            'blocked'  => 0,
        ];
        
        // Auth the User
        if (Auth::attempt($credentials)) {
            return redirect()->intended($this->redirectTo);
        }
        
        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);
        
        return redirect($this->loginPath)->withErrors(['error' => trans('auth.failed')])->withInput();
    }
    
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function logout(Request $request)
    {
        // Get the current Country
        if (session()->has('country_code')) {
            $countryCode = session('country_code');
        }
        
        // Remove all session vars
        $this->guard()->logout();
        $request->session()->flush();
        $request->session()->regenerate();
        
        // Retrieve the current Country
        if (isset($countryCode) && !empty($countryCode)) {
            session(['country_code' => $countryCode]);
        }
        
        return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/');
    }
}

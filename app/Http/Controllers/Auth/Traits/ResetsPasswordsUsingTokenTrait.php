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

namespace App\Http\Controllers\Auth\Traits;

use App\Models\PasswordReset;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

trait ResetsPasswordsUsingTokenTrait
{
    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPasswordUsingToken(Request $request)
    {
        // Form validation
        $rules = [
            'token'    => 'required',
            'login'    => 'required',
            'password' => 'required|between:6,60|dumbpwd|confirmed',
        ];
        $this->validate($request, $rules);

        // Check if Password request exists
        $passwordReset = PasswordReset::where('token', $request->input('token'))->where('phone', $request->input('login'))->first();
        if (empty($passwordReset)) {
            return back()->withErrors(['token' => t('The code does not match  your email or phone number.')])->withInput();
        }

        // Get User
        $user = User::where('phone', $passwordReset->phone)->first();
        if (empty($passwordReset)) {
            return back()->withErrors(['phone' => t('The entered value is not registered with us.')])->withInput();
        }

        // Update the User
        $user->password = Hash::make($request->input('password'));
        $user->verified_phone = 1;
        if ($user->can(Permission::getStaffPermissions())) {
            $user->verified_email = 1;
        }
        $user->save();

        // Remove password reset data
        $passwordReset->delete();

        // Log-in the User
        if (Auth::loginUsingId($user->id)) {
            return redirect()->intended(config('app.locale') . '/account');
        } else {
            flash(t('These credentials do not match our records.'))->error();

            return redirect(config('app.locale') . '/' . trans('routes.login'));
        }
    }
}

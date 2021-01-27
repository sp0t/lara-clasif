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
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;

trait SendsPasswordResetSmsTrait
{
    /**
     * Send a reset code to the given user.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetTokenSms(Request $request)
    {
        // Form validation
        $rules = ['login' => 'required'];
        $this->validate($request, $rules);

        // Check if the phone exists
        $user = User::where('phone', $request->input('phone'))->first();
        if (empty($user)) {
            return back()->withErrors(['phone' => t('The entered value is not registered with us.')])->withInput();
        }

        // Create the token in database
        $token = mt_rand(100000, 999999);
        $passwordReset = PasswordReset::where('phone', $request->input('phone'))->first();
        if (empty($passwordReset)) {
            $passwordResetInfo = [
                'email'      => null,
                'phone'      => $request->input('phone'),
                'token'      => $token,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $passwordReset = new PasswordReset($passwordResetInfo);
        } else {
            $passwordReset->token = $token;
            $passwordReset->created_at = date('Y-m-d H:i:s');
        }
        $passwordReset->save();

        try {
            // Send the token by SMS
            $passwordReset->notify(new ResetPasswordNotification($user, $token, 'phone'));
        } catch (\Exception $e) {
            flash($e->getMessage())->error();
        }

        // Got to Token verification Form
        return redirect(config('app.locale') . '/password/token');
    }

    /**
     * URL: Token Form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showTokenRequestForm()
    {
        return view('token');
    }

    /**
     * URL: Token Form POST method
     *
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function sendResetToken(Request $request)
    {
        // Form validation
        $rules = ['code' => 'required'];
        $this->validate($request, $rules);

        // Check if the token exists
        $passwordReset = PasswordReset::where('token', $request->input('code'))->first();
        if (empty($passwordReset)) {
            return back()->withErrors(['code' => t('The entered code is invalid.')])->withInput();
        }

        // Go to Reset Password Form
        return redirect(config('app.locale') . '/password/reset/' . $request->input('code'));
    }
}

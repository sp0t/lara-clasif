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

namespace App\Helpers;

use App\Models\Permission;
use App\Models\Post;
use App\Models\Package;
use App\Models\Payment as PaymentModel;
use App\Notifications\PaymentNotification;
use App\Notifications\PaymentSent;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;

class Payment
{
	public static $country;
	public static $lang;
	public static $msg = [];
	public static $uri = [];
	
	// API FEATURES...
	public static $messages = [];
	public static $errors = [];
	
	/**
	 * Apply actions after successful Payment
	 *
	 * @param $params
	 * @param $post
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public static function paymentConfirmationActions($params, $post)
	{
		// Save the Payment in database
		$payment = self::register($post, $params);
		
		if (isFromApi()) {
			$request = request();
			
			// Transform Entity using its Eloquent Resource
			$payment = (new \App\Plugins\apijc\app\Http\Resources\PaymentResource($payment))->toArray($request);
			
			$msg = self::$msg['checkout']['success'];
			return self::response($payment, $msg);
		} else {
			// Successful transaction
			flash(self::$msg['checkout']['success'])->success();
			
			// Redirect
			session()->flash('message', self::$msg['post']['success']);
			
			return redirect(self::$uri['nextUrl']);
		}
	}
	
	/**
	 * Apply actions when Payment failed
	 *
	 * @param $post
	 * @param null $errorMessage
	 * @return $this|\Illuminate\Http\JsonResponse
	 * @throws \Exception
	 */
	public static function paymentFailureActions($post, $errorMessage = null)
	{
		// Remove the entry
		self::removeEntry($post);
		
		// Return to Form
		$message = '';
		$message .= self::$msg['checkout']['error'];
		if (!empty($errorMessage)) {
			$message .= '<br>' . $errorMessage;
		}
		
		if (isFromApi()) {
			self::$errors[] = $message;
			return self::error(400);
		} else {
			flash($message)->error();
			
			// Redirect
			return redirect(self::$uri['previousUrl'] . '?error=payment')->withInput();
		}
	}
	
	/**
	 * Apply actions when API failed
	 *
	 * @param $post
	 * @param $exception
	 * @return $this|\Illuminate\Http\JsonResponse
	 * @throws \Exception
	 */
	public static function paymentApiErrorActions($post, $exception)
	{
		// Remove the entry
		self::removeEntry($post);
		
		if (isFromApi()) {
			self::$errors[] = $exception->getMessage();
			return self::error(400);
		} else {
			// Remove local parameters into the session (if exists)
			if (Session::has('params')) {
				Session::forget('params');
			}
			
			// Return to Form
			flash($exception->getMessage())->error();
			
			// Redirect
			return redirect(self::$uri['previousUrl'] . '?error=paymentApi')->withInput();
		}
	}
	
	/**
	 * Save the payment and Send payment confirmation email
	 *
	 * @param Post $post
	 * @param $params
	 * @return PaymentModel|\Illuminate\Http\JsonResponse|null
	 */
	public static function register(Post $post, $params)
	{
		if (empty($post)) {
			return null;
		}
		
		// Update ad 'reviewed'
		$post->reviewed = 1;
		$post->featured = 1;
		$post->save();
		
		// Get the payment info
		$paymentInfo = [
			'post_id'           => $post->id,
			'package_id'        => $params['package_id'],
			'payment_method_id' => $params['payment_method_id'],
			'transaction_id'    => (isset($params['transaction_id'])) ? $params['transaction_id'] : null,
		];
		
		// Check the uniqueness of the payment
		$payment = PaymentModel::where('post_id', $paymentInfo['post_id'])
			->where('package_id', $paymentInfo['package_id'])
			->where('payment_method_id', $params['payment_method_id'])
			->first();
		if (!empty($payment)) {
			return $payment;
		}
		
		// Save the payment
		$payment = new PaymentModel($paymentInfo);
		$payment->save();
		
		// SEND EMAILS
		
		// Get all admin users
		$admins = User::permission(Permission::getStaffPermissions())->get();
		
		// Send Payment Email Notifications
		if (config('settings.mail.payment_notification') == 1) {
			// Send Confirmation Email
			try {
				$post->notify(new PaymentSent($payment, $post));
			} catch (\Exception $e) {
				if (isFromApi()) {
					self::$errors[] = $e->getMessage();
					return self::error(400);
				} else {
					flash($e->getMessage())->error();
				}
			}
			
			// Send to Admin the Payment Notification Email
			try {
				if ($admins->count() > 0) {
					Notification::send($admins, new PaymentNotification($payment, $post));
					/*
					foreach ($admins as $admin) {
						Notification::route('mail', $admin->email)->notify(new PaymentNotification($payment, $post));
					}
					*/
				}
			} catch (\Exception $e) {
				if (isFromApi()) {
					self::$errors[] = $e->getMessage();
					return self::error(400);
				} else {
					flash($e->getMessage())->error();
				}
			}
		}
		
		return $payment;
	}
	
	/**
	 * Remove the ad for public - If there are no free packages
	 *
	 * @param Post $post
	 * @return bool
	 * @throws \Exception
	 */
	public static function removeEntry(Post $post)
	{
		if (empty($post)) {
			return false;
		}
		
		// Don't delete the ad when user try to UPGRADE her ads
		if (empty($post->tmp_token)) {
			return false;
		}
		
		if (auth()->check()) {
			// Delete the ad if user is logged in and there are no free package
			if (Package::where('price', 0)->count() == 0) {
				// But! User can access to the ad from her area to UPGRADE it!
				// You can UNCOMMENT the line below if you don't want the feature above.
				// $post->delete();
			}
		} else {
			// Delete the ad if user is a guest
			$post->delete();
		}
		
		return true;
	}
	
	// API FEATURES...
	
	/**
	 * @param $result
	 * @param null $message
	 * @param array $messages
	 * @param array $errors
	 * @param array $headers
	 * @return \Illuminate\Http\JsonResponse
	 */
	public static function response($result, $message = null, $messages = [], $errors = [], $headers = [])
	{
		$messages = !empty($messages) ? $messages : self::$messages;
		$errors = !empty($errors) ? $errors : self::$errors;
		
		$response = [
			'success'  => true,
			'message'  => $message,
			'data'     => $result,
			'messages' => $messages,
			'errors'   => $errors,
			'code'     => 200,
		];
		
		return response()->json($response, 200, $headers, JSON_UNESCAPED_UNICODE);
	}
	
	/**
	 * @param int $code
	 * @param null $error
	 * @param array $errors
	 * @param array $headers
	 * @return \Illuminate\Http\JsonResponse
	 */
	public static function error($code = 404, $error = null, $errors = [], $headers = [])
	{
		$error = !empty($error) ? $error : t('Whoops! Something went wrong!');
		$errors = !empty($errors) ? $errors : self::$errors;
		
		$response = [
			'success' => false,
			'message' => $error,
			'data'    => $errors,
			'code'    => $code,
		];
		
		return response()->json($response, $code, $headers, JSON_UNESCAPED_UNICODE);
	}
}

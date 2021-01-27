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

namespace App\Http\Controllers\Post\CreateOrEdit\Traits;

use App\Models\PaymentMethod;
use App\Models\Scopes\VerifiedScope;
use App\Models\Scopes\ReviewedScope;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Session;

trait PaymentTrait
{
	/**
	 * Send Payment
	 *
	 * @param Request $request
	 * @param Post $post
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function sendPayment(Request $request, Post $post)
	{
		// Set URLs
		$this->uri['previousUrl'] = str_replace(['#entryToken', '#entryId'], [$post->tmp_token, $post->id], $this->uri['previousUrl']);
		
		// Get Payment Method
		$paymentMethod = PaymentMethod::find($request->input('payment_method_id'));
		
		if (!empty($paymentMethod)) {
			// Load Payment Plugin
			$plugin = load_installed_plugin(strtolower($paymentMethod->name));
			
			// Payment using the selected Payment Method
			if (!empty($plugin)) {
				// Send the Payment
				try {
					return call_user_func($plugin->class . '::sendPayment', $request, $post);
				} catch (\Exception $e) {
					flash($e->getMessage())->error();
					return redirect($this->uri['previousUrl'] . '?error=pluginLoading')->withInput();
				}
			}
		}
		
		return redirect($this->uri['previousUrl'] . '?error=paymentMethodNotFound')->withInput();
	}
	
	/**
	 * Payment Confirmation
	 * URL: /posts/create/{postIdOrToken}/payment/success
	 * - Success URL when Credit Card is used
	 * - Payment Process URL when no Credit Card is used
	 *
	 * @param null $postIdOrToken
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
	 */
	public function paymentConfirmation($postIdOrToken = null)
	{
		// Get session parameters
		$params = Session::get('params');
		if (empty($params)) {
			flash($this->msg['checkout']['error'])->error();
			return redirect('/?error=paymentSessionNotFound');
		}
		
		// Get the entry
		$post = Post::withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])->find($params['post_id']);
		if (empty($post)) {
			flash($this->msg['checkout']['error'])->error();
			return redirect('/?error=paymentEntryNotFound');
		}
		
		// GO TO PAYMENT METHODS
		
		if (!isset($params['payment_method_id'])) {
			flash($this->msg['checkout']['error'])->error();
			return redirect('/?error=paymentMethodParameterNotFound');
		}
		
		// Get Payment Method
		$paymentMethod = PaymentMethod::find($params['payment_method_id']);
		if (empty($paymentMethod)) {
			flash($this->msg['checkout']['error'])->error();
			return redirect('/?error=paymentMethodEntryNotFound');
		}
		
		// Load Payment Plugin
		$plugin = load_installed_plugin(strtolower($paymentMethod->name));
		
		// Check if the Payment Method exists
		if (empty($plugin)) {
			flash($this->msg['checkout']['error'])->error();
			return redirect('/?error=paymentMethodPluginNotFound');
		}
		
		// Payment using the selected Payment Method
		try {
			return call_user_func($plugin->class . '::paymentConfirmation', $params, $post);
		} catch (\Exception $e) {
			flash($e->getMessage())->error();
			return redirect('/?error=paymentMethodPluginError');
		}
	}
	
	/**
	 * Payment Cancel
	 * URL: /posts/create/{postIdOrToken}/payment/cancel
	 *
	 * @param null $postIdOrToken
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function paymentCancel($postIdOrToken = null)
	{
		// Set the error message
		flash($this->msg['checkout']['cancel'])->error();
		
		// Get session parameters
		$params = Session::get('params');
		if (empty($params)) {
			return redirect('/?error=paymentCancelled');
		}
		
		// Get ad details
		$post = Post::withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])->find($params['post_id']);
		if (empty($post)) {
			return redirect('/?error=paymentCancelled');
		}
		
		// Delete the Post only if it's new Entry
		if (!empty($post->tmp_token)) {
			// $post->delete();
		}
		
		// Redirect to the form page
		$this->uri['previousUrl'] = str_replace('#entryId', $postIdOrToken, $this->uri['previousUrl']);
		return redirect($this->uri['previousUrl'] . '?error=paymentCancelled')->withInput();
	}
}

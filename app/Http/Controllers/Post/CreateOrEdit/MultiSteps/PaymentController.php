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

namespace App\Http\Controllers\Post\CreateOrEdit\MultiSteps;

use App\Helpers\UrlGen;
use App\Http\Requests\PackageRequest;
use App\Models\Post;
use App\Models\Package;
use App\Models\Scopes\StrictActiveScope;
use App\Models\Scopes\VerifiedScope;
use App\Models\Scopes\ReviewedScope;
use App\Http\Controllers\FrontController;
use Torann\LaravelMetaTags\Facades\MetaTag;
use App\Helpers\Payment as PaymentHelper;
use App\Http\Controllers\Post\CreateOrEdit\Traits\PaymentTrait;

class PaymentController extends FrontController
{
	use PaymentTrait;
	
	public $request;
	public $data;
	public $msg = [];
	public $uri = [];
	public $packages;
	public $paymentMethods;
	
	/**
	 * PackageController constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		// From Laravel 5.3.4 or above
		$this->middleware(function ($request, $next) {
			$this->request = $request;
			$this->commonQueries();
			
			return $next($request);
		});
	}
	
	/**
	 * Common Queries
	 */
	public function commonQueries()
	{
		// Messages
		if (getSegment(2) == 'create') {
			$this->msg['post']['success'] = t("Your ad has been created.");
		} else {
			$this->msg['post']['success'] = t("Your ad has been updated.");
		}
		$this->msg['checkout']['success'] = t("We have received your payment.");
		$this->msg['checkout']['cancel']  = t("We have not received your payment. Payment cancelled.");
		$this->msg['checkout']['error']   = t("We have not received your payment. An error occurred.");
		
		// Set URLs
		if (getSegment(2) == 'create') {
			$this->uri['previousUrl']      = config('app.locale') . '/posts/create/#entryToken/payment';
			$this->uri['nextUrl']          = config('app.locale') . '/posts/create/#entryToken/finish';
			$this->uri['paymentCancelUrl'] = url(config('app.locale') . '/posts/create/#entryToken/payment/cancel');
			$this->uri['paymentReturnUrl'] = url(config('app.locale') . '/posts/create/#entryToken/payment/success');
		} else {
			$this->uri['previousUrl']      = config('app.locale') . '/posts/#entryId/payment';
			$this->uri['nextUrl']          = config('app.locale') . '/' . trans('routes.v-post', ['slug' => '#title', 'id' => '#entryId']);
			$this->uri['paymentCancelUrl'] = url(config('app.locale') . '/posts/#entryId/payment/cancel');
			$this->uri['paymentReturnUrl'] = url(config('app.locale') . '/posts/#entryId/payment/success');
		}
		
		// Payment Helper init.
		PaymentHelper::$country = collect(config('country'));
		PaymentHelper::$lang    = collect(config('lang'));
		PaymentHelper::$msg     = $this->msg;
		PaymentHelper::$uri     = $this->uri;
		
		// Get Packages
		$this->packages = Package::trans()->applyCurrency()->with('currency')->orderBy('lft')->get();
		view()->share('packages', $this->packages);
		view()->share('countPackages', $this->packages->count());
		
		// Keep the Post's creation message
		// session()->keep(['message']);
		if (getSegment(2) == 'create') {
			if (session()->has('tmpPostId')) {
				session()->flash('message', t('Your ad has been created.'));
			}
		}
	}
	
	/**
	 * Show the form the create a new ad post.
	 *
	 * @param $postIdOrToken
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
	 */
	public function getForm($postIdOrToken)
	{
		// Check if the form type is 'Single Step Form', and make redirection to it (permanently).
		if (config('settings.single.publication_form_type') == '2') {
			return redirect(lurl('edit/' . $postIdOrToken), 301)->header('Cache-Control', 'no-store, no-cache, must-revalidate');
		}
		
		$data = [];
		
		// Get Post
		if (getSegment(2) == 'create') {
			if (!session()->has('tmpPostId')) {
				return redirect('posts/create');
			}
			$post = Post::currentCountry()->with([
				'latestPayment' => function ($builder) {
					$builder->with(['package'])->withoutGlobalScope(StrictActiveScope::class);
				},
			])->withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
				->where('id', session('tmpPostId'))
				->where('tmp_token', $postIdOrToken)
				->first();
		} else {
			$post = Post::currentCountry()->with([
				'latestPayment' => function ($builder) {
					$builder->with(['package'])->withoutGlobalScope(StrictActiveScope::class);
				},
			])->withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
				->where('user_id', auth()->user()->id)
				->where('id', $postIdOrToken)
				->first();
		}
		
		if (empty($post)) {
			abort(404);
		}
		
		view()->share('post', $post);
		
		// Meta Tags
		if (getSegment(2) == 'create') {
			MetaTag::set('title', getMetaTag('title', 'create'));
			MetaTag::set('description', strip_tags(getMetaTag('description', 'create')));
			MetaTag::set('keywords', getMetaTag('keywords', 'create'));
		} else {
			MetaTag::set('title', t('Update My Ad'));
			MetaTag::set('description', t('Update My Ad'));
		}
		
		return view('post.createOrEdit.multiSteps.packages', $data);
	}
	
	/**
	 * Store a new ad post.
	 *
	 * @param $postIdOrToken
	 * @param PackageRequest $request
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function postForm($postIdOrToken, PackageRequest $request)
	{
		// Get Post
		if (getSegment(2) == 'create') {
			if (!session()->has('tmpPostId')) {
				return redirect('posts/create');
			}
			$post = Post::with(['latestPayment'])
				->withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
				->where('id', session('tmpPostId'))
				->where('tmp_token', $postIdOrToken)
				->first();
		} else {
			$post = Post::with(['latestPayment'])
				->withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
				->where('user_id', auth()->user()->id)
				->where('id', $postIdOrToken)
				->first();
		}
		
		if (empty($post)) {
			abort(404);
		}
		
		// MAKE A PAYMENT (IF NEEDED)
		
		// Check if the selected Package has been already paid for this Post
		$alreadyPaidPackage = false;
		if (!empty($post->latestPayment)) {
			if ($post->latestPayment->package_id == $request->input('package_id')) {
				$alreadyPaidPackage = true;
			}
		}
		
		// Check if Payment is required
		$package = Package::find($request->input('package_id'));
		if (!empty($package)) {
			if ($package->price > 0 && $request->filled('payment_method_id') && !$alreadyPaidPackage) {
				// Send the Payment
				return $this->sendPayment($request, $post);
			}
		}
		
		// IF NO PAYMENT IS MADE (CONTINUE)
		
		// Get the next URL
		if (getSegment(2) == 'create') {
			$request->session()->flash('message', t('Your ad has been created.'));
			$nextStepUrl = config('app.locale') . '/posts/create/' . $postIdOrToken . '/finish';
		} else {
			flash(t("Your ad has been updated."))->success();
			$nextStepUrl = UrlGen::postUri($post);
		}
		
		// Redirect
		return redirect($nextStepUrl);
	}
}

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

use App\Http\Controllers\Post\CreateOrEdit\MultiSteps\Traits\EditTrait;
use App\Http\Controllers\Auth\Traits\VerificationTrait;
use App\Http\Requests\PostRequest;
use App\Models\Company;
use App\Models\PostType;
use App\Models\Category;
use App\Models\Package;
use App\Models\PaymentMethod;
use App\Models\SalaryType;
use App\Http\Controllers\FrontController;
use App\Helpers\Localization\Helpers\Country as CountryLocalizationHelper;
use App\Helpers\Localization\Country as CountryLocalization;

class EditController extends FrontController
{
	use EditTrait, VerificationTrait;
	
	public $data;
	public $msg = [];
	public $uri = [];
	
	/**
	 * EditController constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
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
		// References
		$data = [];
		
		// Get Countries
		$data['countries'] = $this->countries = CountryLocalizationHelper::transAll(CountryLocalization::getCountries());
		$this->countries   = $data['countries'];
		view()->share('countries', $data['countries']);
		
		// Get Categories
		$data['categories'] = Category::trans()->where('parent_id', 0)->with([
			'children' => function ($query) {
				$query->trans();
			},
		])->orderBy('lft')->get();
		view()->share('categories', $data['categories']);
		
		// Get Post Types
		$data['postTypes'] = PostType::trans()->get();
		view()->share('postTypes', $data['postTypes']);
		
		// Get Salary Types
		$data['salaryTypes'] = SalaryType::trans()->get();
		view()->share('salaryTypes', $data['salaryTypes']);
		
		// Get the User's Company
		if (auth()->check()) {
			$data['companies'] = Company::where('user_id', auth()->user()->id)->get();
			view()->share('companies', $data['companies']);
		}
		
		// Count Packages
		$data['countPackages'] = Package::trans()->applyCurrency()->count();
		view()->share('countPackages', $data['countPackages']);
		
		// Count Payment Methods
		$data['countPaymentMethods'] = $this->countPaymentMethods;
		
		// Save common's data
		$this->data = $data;
	}
	
	/**
	 * Show the form the create a new ad post.
	 *
	 * @param $postId
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
	 */
	public function getForm($postId)
	{
		// Check if the form type is 'Single Step Form', and make redirection to it (permanently).
		if (config('settings.single.publication_form_type') == '2') {
			return redirect(lurl('edit/' . $postId), 301)->header('Cache-Control', 'no-store, no-cache, must-revalidate');
		}
		
		return $this->getUpdateForm($postId);
	}
	
	/**
	 * Store a new ad post.
	 *
	 * @param $postId
	 * @param PostRequest $request
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function postForm($postId, PostRequest $request)
	{
		return $this->postUpdateForm($postId, $request);
	}
}

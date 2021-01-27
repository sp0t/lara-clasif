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

namespace App\Http\Controllers\Account;

use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use Torann\LaravelMetaTags\Facades\MetaTag;

class CompanyController extends AccountBaseController
{
	private $perPage  = 10;
	public  $pagePath = 'companies';
	
	public function __construct()
	{
		parent::__construct();
		
		$this->perPage = (is_numeric(config('settings.listing.items_per_page'))) ? config('settings.listing.items_per_page') : $this->perPage;
		
		view()->share('pagePath', $this->pagePath);
	}
	
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function index()
	{
		// Get all User's Companies
		$companies = $this->companies->paginate($this->perPage);
		
		// Meta Tags
		MetaTag::set('title', t('My Companies List'));
		MetaTag::set('description', t('My Companies List - :app_name', ['app_name' => config('settings.app.app_name')]));
		
		return view('account.company.index')->with('companies', $companies);
	}
	
	/**
	 * Show the form for creating a new resource.
	 */
	public function create()
	{
		// Meta Tags
		MetaTag::set('title', t('Create a new company'));
		MetaTag::set('description', t('Create a new company - :app_name', ['app_name' => config('settings.app.app_name')]));
		
		return view('account.company.create');
	}
	
	/**
	 * Store a newly created resource in storage.
	 *
	 * @param CompanyRequest $request
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function store(CompanyRequest $request)
	{
		// Get Company Info
		$companyInfo = $request->input('company');
		if (!isset($companyInfo['user_id']) || empty($companyInfo['user_id'])) {
			$companyInfo += ['user_id' => auth()->user()->id];
		}
		if (!isset($companyInfo['country_code']) || empty($companyInfo['country_code'])) {
			$companyInfo += ['country_code' => config('country.code')];
		}
		
		// Create the User's Company
		$company = new Company($companyInfo);
		$company->save();
		
		flash(t("Your company has created successfully."))->success();
		
		// Save the Company's Logo
		if ($request->hasFile('company.logo')) {
			$company->logo = $request->file('company.logo');
			$company->save();
		}
		
		// Redirection
		return redirect(config('app.locale') . '/account/companies');
	}
	
	/**
	 * Display the specified resource.
	 *
	 * @param $id
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function show($id)
	{
		return redirect(config('app.locale') . '/account/companies/' . $id . '/edit');
	}
	
	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param $id
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function edit($id)
	{
		// Get the Company
		$company = Company::where('id', $id)->where('user_id', auth()->user()->id)->firstOrFail();
		
		// Meta Tags
		MetaTag::set('title', t('Edit the Company'));
		MetaTag::set('description', t('Edit the Company - :app_name', ['app_name' => config('settings.app.app_name')]));
		
		return view('account.company.edit')->with('company', $company);
	}
	
	/**
	 * Update the specified resource in storage.
	 *
	 * @param $id
	 * @param CompanyRequest $request
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function update($id, CompanyRequest $request)
	{
		$company = Company::where('id', $id)->where('user_id', auth()->user()->id)->firstOrFail();
		
		// Get Company Info
		$companyInfo = $request->input('company');
		if (!isset($companyInfo['user_id']) || empty($companyInfo['user_id'])) {
			$companyInfo += ['user_id' => auth()->user()->id];
		}
		if (!isset($companyInfo['country_code']) || empty($companyInfo['country_code'])) {
			$companyInfo += ['country_code' => config('country.code')];
		}
		
		// Make an Update
		$company->update($companyInfo);
		
		flash(t("Your company details has updated successfully."))->success();
		
		// Save the Company's Logo
		if ($request->hasFile('company.logo')) {
			$company->logo = $request->file('company.logo');
			$company->save();
		}
		
		// Redirection
		return redirect(config('app.locale') . '/account/companies');
	}
	
	/**
	 * Remove the specified resource from storage.
	 *
	 * @param null $id
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function destroy($id = null)
	{
		// Get Entries ID
		$ids = [];
		if (request()->filled('entries')) {
			$ids = request()->input('entries');
		} else {
			if (!is_numeric($id) && $id <= 0) {
				$ids = [];
			} else {
				$ids[] = $id;
			}
		}
		
		// Delete
		$nb = 0;
		foreach ($ids as $item) {
			$company = Company::where('id', $item)->where('user_id', auth()->user()->id)->firstOrFail();
			if (!empty($company)) {
				// Delete Entry
				$nb = $company->delete();
			}
		}
		
		// Confirmation
		if ($nb == 0) {
			flash(t("No deletion is done. Please try again."))->error();
		} else {
			$count = count($ids);
			if ($count > 1) {
				flash(t("x :entities has been deleted successfully.", ['entities' => t('companies'), 'count' => $count]))->success();
			} else {
				flash(t("1 :entity has been deleted successfully.", ['entity' => t('company')]))->success();
			}
		}
		
		return redirect(config('app.locale') . '/account/companies');
	}
}

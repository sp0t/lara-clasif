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

namespace App\Http\Controllers\Search;

use App\Models\Company;
use Illuminate\Http\Request;
use Torann\LaravelMetaTags\Facades\MetaTag;

class CompanyController extends BaseController
{
	private $perPage         = 10;
	public  $isCompanySearch = true;
	public  $company;
	
	public function __construct(Request $request)
	{
		parent::__construct($request);
		
		$this->perPage = (is_numeric(config('settings.listing.items_per_page'))) ? config('settings.listing.items_per_page') : $this->perPage;
	}
	
	/**
	 * Listing of Companies
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function index()
	{
		// Get Companies List
		$companies = Company::whereHas('posts', function ($query) {
			$query->currentCountry();
		})->withCount([
			'posts' => function ($query) {
				$query->currentCountry();
			},
		]);
		
		// Apply search filter
		if (request()->filled('q')) {
			$keywords  = rawurldecode(request()->get('q'));
			$companies = $companies->where('name', 'LIKE', '%' . $keywords . '%')->whereOr('description', 'LIKE', '%' . $keywords . '%');
		}
		
		// Get Companies List with pagination
		$companies = $companies->orderByDesc('id')->paginate($this->perPage);
		
		// Meta Tags
		MetaTag::set('title', t('Companies List'));
		MetaTag::set('description', t('Companies List - :app_name', ['app_name' => config('settings.app.app_name')]));
		
		return view('search.company.index')->with('companies', $companies);
	}
	
	/**
	 * Show a Company profiles (with its Jobs ads)
	 *
	 * @param $countryCode
	 * @param null $companyId
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function profile($countryCode, $companyId = null)
	{
		// Check multi-countries site parameters
		if (!config('settings.seo.multi_countries_urls')) {
			$companyId = $countryCode;
		}
		
		// Get Company
		$this->company = Company::findOrFail($companyId);
		
		// Get the Company's Jobs
		$data = $this->jobs($this->company->id);
		
		// Share the Company's info with the view
		$data['company'] = $this->company;
		
		return view('search.company.profile', $data);
	}
	
	/**
	 * Get the Company Jobs ads
	 *
	 * @param $companyId
	 * @return array
	 */
	private function jobs($companyId)
	{
		view()->share('isCompanySearch', $this->isCompanySearch);
		
		// Search
		$search = new $this->searchClass();
		$data   = $search->setCompany($companyId)->fetch();
		
		// Get Titles
		$bcTab     = $this->getBreadcrumb();
		$htmlTitle = $this->getHtmlTitle();
		view()->share('bcTab', $bcTab);
		view()->share('htmlTitle', $htmlTitle);
		
		// Meta Tags
		$title = $this->getTitle();
		MetaTag::set('title', $title);
		MetaTag::set('description', $title);
		
		// Translation vars
		view()->share('uriPathCompanyId', $companyId);
		
		return $data;
	}
}

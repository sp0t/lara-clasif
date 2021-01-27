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

use App\Models\City;
use Torann\LaravelMetaTags\Facades\MetaTag;

class CityController extends BaseController
{
	public $isCitySearch = true;
	
	protected $city = null;
	
	/**
	 * @param $countryCode
	 * @param $cityName
	 * @param null $cityId
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function index($countryCode, $cityName, $cityId = null)
	{
		// Check multi-countries site parameters
		if (!config('settings.seo.multi_countries_urls')) {
			$cityId   = $cityName;
			$cityName = $countryCode;
		}
		
		view()->share('isCitySearch', $this->isCitySearch);
		
		// Get the City
		$this->city = City::findOrFail((int)$cityId);
		view()->share('city', $this->city);
		
		// Search
		$search = new $this->searchClass();
		$data   = $search->setLocationByCity($this->city)->fetch();
		
		// Get Titles
		$bcTab     = $this->getBreadcrumb();
		$htmlTitle = $this->getHtmlTitle();
		view()->share('bcTab', $bcTab);
		view()->share('htmlTitle', $htmlTitle);
		
		// Meta Tags
		$title       = $this->getTitle();
		$description = t('Jobs in :location',
				['location' => $this->city->name]) . ', ' . config('country.name') . '. ' . t('Looking for a job') . ' - ' . $this->city->name . ', ' . config('country.name');
		
		MetaTag::set('title', $title);
		MetaTag::set('description', $description);
		
		// Translation vars
		view()->share('uriPathCityName', $cityName);
		view()->share('uriPathCityId', $cityId);
		
		return view('search.serp', $data);
	}
}

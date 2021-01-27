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

namespace App\Http\Controllers\Search\Traits;

use App\Helpers\ArrayHelper;
use App\Models\Category;
use App\Models\City;
use App\Models\SubAdmin1;
use App\Models\SubAdmin2;
use Illuminate\Support\Str;

trait PreSearchTrait
{
	/**
	 * Get Category
	 *
	 * @param $catId
	 * @param null $subCatId
	 * @return null
	 */
	public function getCategory($catId, $subCatId = null)
	{
		if (empty($catId)) {
			return null;
		}
		
		$this->isCatSearch = true;
		view()->share('isCatSearch', $this->isCatSearch);
		
		// Get Category
		$this->cat = Category::findTrans($catId);
		
		// Check SubCategory Request
		if (!empty($subCatId)) {
			$this->isSubCatSearch = true;
			view()->share('isSubCatSearch', $this->isSubCatSearch);
			
			// Get SubCategory
			$this->subCat = Category::findTrans($subCatId);
			view()->share('subCat', $this->subCat);
		}
		
		view()->share('cat', $this->cat);
		
		return $this->cat;
	}
	
	/**
	 * Get City
	 *
	 * @param null $cityId
	 * @param null $location
	 * @return array|null|\stdClass
	 */
	public function getCity($cityId = null, $location = null)
	{
		if (empty($cityId) && empty($location)) {
			return null;
		}
		
		// Search by administrative division name with magic word "area:" - Example: "area:New York"
		if (!empty($location)) {
			$location = preg_replace('/\s+\:/', ':', $location);
			if (Str::contains($location, t('area:'))) {
				$adminName = last(explode(t('area:'), $location));
				$adminName = trim($adminName);
				
				$fullUrl = url(request()->getRequestUri());
				$fullUrlNoParams = head(explode('?', $fullUrl));
				$url = qsurl($fullUrlNoParams, array_merge(request()->except(['l', 'location']), ['d' => config('country.code'), 'r' => $adminName]));
				
				headerLocation($url);
			}
		}
		
		$this->isCitySearch = true;
		view()->share('isCitySearch', $this->isCitySearch);
		
		// Get City by Id
		$this->city = null;
		if (!empty($cityId)) {
			$this->city = City::find($cityId);
		}
		
		$cityName = rawurldecode($location);
		
		// Get City by Name
		if (empty($this->city) && !empty($location)) {
			$this->city = City::currentCountry()->where('name', 'LIKE', $cityName)->first();
			if (empty($this->city)) {
				$this->city = City::currentCountry()->where('name', 'LIKE', $cityName . '%')->first();
				if (empty($this->city)) {
					$this->city = City::currentCountry()->where('name', 'LIKE', '%' . $cityName)->first();
					if (empty($this->city)) {
						$this->city = City::currentCountry()->where('name', 'LIKE', '%' . $cityName . '%')->first();
					}
				}
			}
		}
		
		// City not found
		if (empty($this->city)) {
			$this->city = ArrayHelper::toObject([
				'id'             => -999999,
				'name'           => Str::limit($cityName, 70),
				'longitude'      => -999999,
				'latitude'       => -999999,
				'subadmin1_code' => '',
				'subadmin2_code' => '',
			]);
		}
		
		view()->share('city', $this->city);
		
		return $this->city;
	}
	
	/**
	 * Get Administrative Division
	 *
	 * @param $adminName
	 * @return array|null|\stdClass
	 */
	public function getAdmin($adminName)
	{
		if (empty($adminName) || request()->filled('l')) {
			return null;
		}
		
		$this->isAdminSearch = true;
		view()->share('isAdminSearch', $this->isAdminSearch);
		
		if (in_array(config('country.admin_type'), ['1', '2'])) {
			$adminName = rawurldecode($adminName);
			
			$adminModel = '\App\Models\SubAdmin' . config('country.admin_type');
			$this->admin = $adminModel::currentCountry()->where('name', 'LIKE', $adminName)->first();
			if (empty($this->admin)) {
				$this->admin = $adminModel::currentCountry()->where('name', 'LIKE', $adminName . '%')->first();
				if (empty($this->admin)) {
					$this->admin = $adminModel::currentCountry()->where('name', 'LIKE', '%' . $adminName)->first();
					if (empty($this->admin)) {
						$this->admin = $adminModel::currentCountry()->where('name', 'LIKE', '%' . $adminName . '%')->first();
					}
				}
			}
			
			// Admin not found
			if (empty($this->admin)) {
				$this->admin = ArrayHelper::toObject([
					'code' => '',
					'name' => Str::limit($adminName, 70),
				]);
			}
			
			view()->share('admin', $this->admin);
			
			return $this->admin;
		} else {
			// Get the Popular City (Redirect to search by City)
			$this->city = $this->getPopularCityByAdminName($adminName);
			if (empty($this->city)) {
				abort(404);
			}
			
			$fullUrl = url(request()->getRequestUri());
			$fullUrlNoParams = head(explode('?', $fullUrl));
			$url = qsurl($fullUrlNoParams, array_merge(request()->except(['r']), ['l' => $this->city->id, 'location' => $adminName]), null, false);
			
			headerLocation($url);
		}
		
		return null;
	}
	
	/**
	 * Get the Popular City in the Administrative Division
	 *
	 * @param $adminName
	 * @return mixed
	 */
	public function getPopularCityByAdminName($adminName)
	{
		if (trim($adminName) == '') {
			return $this->getPopularCity();
		}
		
		// Init.
		$adminName = rawurldecode($adminName);
		
		// Get Admin 1
		$admin1 = SubAdmin1::currentCountry()
			->where('name', 'LIKE', '%' . $adminName . '%')
			->orderBy('name')
			->first();
		
		// Get Admins 2
		if (!empty($admin1)) {
			$admins2 = SubAdmin2::currentCountry()->where('subadmin1_code', $admin1->code)
				->orderBy('name')
				->get(['code']);
		} else {
			$admins2 = SubAdmin2::currentCountry()
				->where('name', 'LIKE', '%' . $adminName . '%')
				->orderBy('name')
				->get(['code']);
		}
		
		// Split the Admin Name value, ...
		// If $admin1 and $admins2 are not found
		if (empty($admin1) && $admins2->count() <= 0) {
			$tmp = preg_split('#(-| )+#', $adminName);
			
			// Sort by length DESC
			usort($tmp, function ($a, $b) {
				return strlen($b) - strlen($a);
			});
			
			if (count($tmp) > 0) {
				foreach ($tmp as $partOfAdminName) {
					// Get Admin 1
					$admin1 = SubAdmin1::currentCountry()
						->where('name', 'LIKE', '%' . $partOfAdminName . '%')
						->orderBy('name')
						->first();
					
					// Get Admins 2
					if (!empty($admin)) {
						$admins2 = SubAdmin2::currentCountry()->where('subadmin1_code', $admin1->code)
							->orderBy('name')
							->get(['code']);
						
						// If $admin1 is found, $admins2 is optional
						break;
					} else {
						$admins2 = SubAdmin2::currentCountry()
							->where('name', 'LIKE', '%' . $partOfAdminName . '%')
							->orderBy('name')
							->get(['code']);
						
						// If $admin1 is null, $admins2 is required
						if ($admins2->count() > 0) {
							break;
						}
					}
				}
			}
		}
		
		// Get City
		if (!empty($admin1)) {
			if ($admins2->count() > 0) {
				$city = City::currentCountry()
					->where('subadmin1_code', $admin1->code)
					->whereIn('subadmin2_code', $admins2->toArray())
					->orderBy('population', 'DESC')
					->first();
				if (empty($city)) {
					$city = City::currentCountry()
						->where('subadmin1_code', $admin1->code)
						->orderBy('population', 'DESC')
						->first();
				}
			} else {
				$city = City::currentCountry()
					->where('subadmin1_code', $admin1->code)
					->orderBy('population', 'DESC')
					->first();
			}
		} else {
			if ($admins2->count() > 0) {
				$city = City::currentCountry()
					->whereIn('subadmin2_code', $admins2->toArray())
					->orderBy('population', 'DESC')
					->first();
			} else {
				// If the Popular City in the Administrative Division is not found,
				// Get the Popular City in the Country.
				$city = $this->getPopularCity();
			}
		}
		
		// If no city is found, Get the Country's popular City
		if (empty($city)) {
			$city = $this->getPopularCity();
		}
		
		return $city;
	}
	
	/**
	 * Get the Popular City in the Country
	 *
	 * @return mixed
	 */
	public function getPopularCity()
	{
		return City::currentCountry()->orderBy('population', 'DESC')->first();
	}
}

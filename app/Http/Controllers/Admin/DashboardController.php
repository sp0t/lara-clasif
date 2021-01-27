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

namespace App\Http\Controllers\Admin;

/*
 * $colorOptions = ['luminosity' => 'light', 'hue' => ['red','orange','yellow','green','blue','purple','pink']];
 * $colorOptions = ['luminosity' => 'light'];
 */

use App\Helpers\ArrayHelper;
use App\Helpers\RandomColor;
use App\Models\Post;
use App\Models\Country;
use App\Models\User;
use Jenssegers\Date\Date;
use Larapen\Admin\app\Http\Controllers\PanelController;

class DashboardController extends PanelController
{
	public $data = [];
	
	protected $countCountries;
	
	/**
	 * Create a new controller instance.
	 */
	public function __construct()
	{
		$this->middleware('admin');
		
		parent::__construct();
		
		// Get the Mini Stats data
		// Count Ads
		$countActivatedPosts = Post::verified()->count();
		$countUnactivatedPosts = Post::unverified()->count();
		
		// Count Users
		$countActivatedUsers = 0;
		$countUnactivatedUsers = 0;
		$countUsers = 0;
		try {
			$countActivatedUsers = User::doesntHave('permissions')->verified()->count();
			$countUnactivatedUsers = User::doesntHave('permissions')->unverified()->count();
			$countUsers = User::doesntHave('permissions')->count();
		} catch (\Exception $e) {}
		
		// Count activated countries
		$this->countCountries = Country::where('active', 1)->count();
		
		view()->share('countActivatedPosts', $countActivatedPosts);
		view()->share('countUnactivatedPosts', $countUnactivatedPosts);
		view()->share('countActivatedUsers', $countActivatedUsers);
		view()->share('countUnactivatedUsers', $countUnactivatedUsers);
		view()->share('countUsers', $countUsers);
		view()->share('countCountries', $this->countCountries);
	}
	
	/**
	 * Show the admin dashboard.
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function dashboard()
	{
		// Dashboard Latest Entries Chart: 'bar' or 'line'
		config()->set('settings.app.dashboard_latest_entries_chart', 'bar');
		
		// Limit latest entries
		$latestEntriesLimit = config('settings.app.latest_entries_limit', 5);
		
		// -----
		
		// Get latest Ads
		$this->data['latestPosts'] = Post::take($latestEntriesLimit)->orderBy('id', 'DESC')->get();
		
		// Get latest Users
		$this->data['latestUsers'] = User::take($latestEntriesLimit)->orderBy('id', 'DESC')->get();
		
		// Get latest entries charts
		$statDayNumber = 30;
		$this->data['latestPostsChart'] = $this->getLatestPostsChart($statDayNumber);
		$this->data['latestUsersChart'] = $this->getLatestUsersChart($statDayNumber);
		
		// Get entries per country charts
		if (config('settings.app.show_countries_charts')) {
			$countriesLimit = 10;
			$this->data['postsPerCountry'] = $this->getPostsPerCountryChart($countriesLimit);
			$this->data['usersPerCountry'] = $this->getUsersPerCountryChart($countriesLimit);
		}
		
		// -----
		
		// Page Title
		$this->data['title'] = trans('admin::messages.dashboard');
		
		return view('admin::dashboard.index', $this->data);
	}
	
	/**
	 * @param int $statDayNumber
	 * @return array
	 */
	private function getLatestPostsChart($statDayNumber = 30)
	{
		// Init.
		$statDayNumber = (is_numeric($statDayNumber)) ? $statDayNumber : 30;
		$currentDate = Date::now();
		
		$stats = [];
		for ($i = 1; $i <= $statDayNumber; $i++) {
			$dateObj = ($i == 1) ? $currentDate : $currentDate->subDay();
			$date = $dateObj->toDateString();
			
			// Ads Stats
			$countActivatedPosts = Post::verified()
				->where('created_at', '>=', $date)
				->where('created_at', '<=', $date . ' 23:59:59')
				->count();
			
			$countUnactivatedPosts = Post::unverified()
				->where('created_at', '>=', $date)
				->where('created_at', '<=', $date . ' 23:59:59')
				->count();
			
			$stats['posts'][$i]['y'] = mb_ucfirst($dateObj->formatLocalized('%b %d'));
			$stats['posts'][$i]['activated'] = $countActivatedPosts;
			$stats['posts'][$i]['unactivated'] = $countUnactivatedPosts;
		}
		
		$stats['posts'] = array_reverse($stats['posts'], true);
		
		$data = json_encode(array_values($stats['posts']), JSON_NUMERIC_CHECK);
		
		$boxData = [
			'title' => trans('admin::messages.Ads Stats'),
			'data'  => $data,
		];
		$boxData = ArrayHelper::toObject($boxData);
		
		return $boxData;
	}
	
	/**
	 * @param int $statDayNumber
	 * @return array
	 */
	private function getLatestUsersChart($statDayNumber = 30)
	{
		// Init.
		$statDayNumber = (is_numeric($statDayNumber)) ? $statDayNumber : 30;
		$currentDate = Date::now();
		
		$stats = [];
		for ($i = 1; $i <= $statDayNumber; $i++) {
			$dateObj = ($i == 1) ? $currentDate : $currentDate->subDay();
			$date = $dateObj->toDateString();
			
			// Users Stats
			$countActivatedUsers = User::doesntHave('permissions')
				->verified()
				->where('created_at', '>=', $date)
				->where('created_at', '<=', $date . ' 23:59:59')
				->count();
			
			$countUnactivatedUsers = User::doesntHave('permissions')
				->unverified()
				->where('created_at', '>=', $date)
				->where('created_at', '<=', $date . ' 23:59:59')
				->count();
			
			$stats['users'][$i]['y'] = mb_ucfirst($dateObj->formatLocalized('%b %d'));
			$stats['users'][$i]['activated'] = $countActivatedUsers;
			$stats['users'][$i]['unactivated'] = $countUnactivatedUsers;
		}
		
		$stats['users'] = array_reverse($stats['users'], true);
		
		$data = json_encode(array_values($stats['users']), JSON_NUMERIC_CHECK);
		
		$boxData = [
			'title' => trans('admin::messages.Users Stats'),
			'data'  => $data,
		];
		$boxData = ArrayHelper::toObject($boxData);
		
		return $boxData;
	}
	
	/**
	 * @param int $limit
	 * @param array $colorOptions
	 * @return array
	 */
	private function getPostsPerCountryChart($limit = 10, $colorOptions = [])
	{
		// Init.
		$limit = (is_numeric($limit) && $limit > 0) ? $limit : 10;
		$colorOptions = (is_array($colorOptions)) ? $colorOptions : [];
		$data = [];
		
		// Get Data
		if ($this->countCountries > 1) {
			$countries = Country::active()->has('posts')->withCount('posts')->get()->sortByDesc(function ($country) {
				return $country->posts_count;
			})->take($limit);
			
			// Format Data
			if ($countries->count() > 0) {
				foreach ($countries as $country) {
					$data['datasets'][0]['data'][] = $country->posts_count;
					$data['datasets'][0]['backgroundColor'][] = RandomColor::one($colorOptions);
					$data['labels'][] = (!empty($country->asciiname)) ? $country->asciiname : $country->name;
				}
				$data['datasets'][0]['label'] = trans('admin::messages.Posts Dataset');
			}
		}
		
		$data = json_encode($data, JSON_NUMERIC_CHECK);
		
		$boxData = [
			'title'          => trans('admin::messages.Ads per Country') . ' (' . trans('admin::messages.Most active Countries') . ')',
			'data'           => $data,
			'countCountries' => $this->countCountries,
		];
		$boxData = ArrayHelper::toObject($boxData);
		
		return $boxData;
	}
	
	/**
	 * @param int $limit
	 * @param array $colorOptions
	 * @return array
	 */
	private function getUsersPerCountryChart($limit = 10, $colorOptions = [])
	{
		// Init.
		$limit = (is_numeric($limit) && $limit > 0) ? $limit : 10;
		$colorOptions = (is_array($colorOptions)) ? $colorOptions : [];
		$data = [];
		
		// Get Data
		if ($this->countCountries > 1) {
			$countries = Country::active()->has('users')->withCount('users')->get()->sortByDesc(function ($country) {
				return $country->users_count;
			})->take($limit);
			
			// Format Data
			if ($countries->count() > 0) {
				foreach ($countries as $country) {
					$data['datasets'][0]['data'][] = $country->users_count;
					$data['datasets'][0]['backgroundColor'][] = RandomColor::one($colorOptions);
					$data['labels'][] = (!empty($country->asciiname)) ? $country->asciiname : $country->name;
				}
				$data['datasets'][0]['label'] = trans('admin::messages.Users Dataset');
			}
		}
		
		$data = json_encode($data, JSON_NUMERIC_CHECK);
		
		$boxData = [
			'title'          => trans('admin::messages.Users per Country') . ' (' . trans('admin::messages.Most active Countries') . ')',
			'data'           => $data,
			'countCountries' => $this->countCountries,
		];
		$boxData = ArrayHelper::toObject($boxData);
		
		return $boxData;
	}
	
	/**
	 * Redirect to the dashboard.
	 *
	 * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
	 */
	public function redirect()
	{
		// The '/admin' route is not to be used as a page, because it breaks the menu's active state.
		return redirect(admin_uri('dashboard'));
	}
}

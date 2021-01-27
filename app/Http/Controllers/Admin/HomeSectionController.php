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

use App\Http\Controllers\Admin\Traits\SettingsTrait;
use App\Models\HomeSection;
use Illuminate\Support\Facades\Cache;
use Larapen\Admin\app\Http\Controllers\PanelController;
use App\Http\Requests\Admin\Request as StoreRequest;
use App\Http\Requests\Admin\Request as UpdateRequest;
use Prologue\Alerts\Facades\Alert;

class HomeSectionController extends PanelController
{
	use SettingsTrait;
	
	/**
	 * HomeSectionController constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->middleware('demo.restriction')->only(['reset']);
	}
	
	public function setup()
	{
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel('App\Models\HomeSection');
		$this->xPanel->setRoute(admin_uri('homepage'));
		$this->xPanel->setEntityNameStrings(trans('admin::messages.homepage section'), trans('admin::messages.homepage sections'));
		$this->xPanel->denyAccess(['create', 'delete']);
		$this->xPanel->allowAccess(['reorder']);
		$this->xPanel->enableReorder('name', 1);
		if (!request()->input('order')) {
			$this->xPanel->orderBy('lft', 'ASC');
		}
		
		$this->xPanel->addButtonFromModelFunction('top', 'reset_homepage_reorder', 'resetHomepageReOrderBtn', 'end');
		$this->xPanel->addButtonFromModelFunction('top', 'reset_homepage_settings', 'resetHomepageSettingsBtn', 'end');
		$this->xPanel->removeButton('update');
		$this->xPanel->addButtonFromModelFunction('line', 'configure', 'configureBtn', 'beginning');
		
		/*
		|--------------------------------------------------------------------------
		| COLUMNS AND FIELDS
		|--------------------------------------------------------------------------
		*/
		// COLUMNS
		$this->xPanel->addColumn([
			'name'          => 'name',
			'label'         => trans("admin::messages.Section"),
			'type'          => 'model_function',
			'function_name' => 'getNameHtml',
		]);
		$this->xPanel->addColumn([
			'name'          => 'active',
			'label'         => trans("admin::messages.Active"),
			'type'          => 'model_function',
			'function_name' => 'getActiveHtml',
		]);
		
		// FIELDS
		// ...
	}
	
	public function store(StoreRequest $request)
	{
		return parent::storeCrud();
	}
	
	public function update(UpdateRequest $request)
	{
		return $this->updateTrait($request);
	}
	
	/**
	 * Homepage Sections Actions (Reset Order & Settings)
	 *
	 * @param $action
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function reset($action)
	{
		// Reset the homepage sections reorder.
		if ($action == 'reset_reorder') {
			HomeSection::where('method', 'getSearchForm')->update(['lft' => 0, 'rgt' => 1, 'active' => 1]);
			HomeSection::where('method', 'getSponsoredPosts')->update(['lft' => 2, 'rgt' => 3, 'active' => 1]);
			HomeSection::where('method', 'getLatestPosts')->update(['lft' => 4, 'rgt' => 5, 'active' => 1]);
			HomeSection::where('method', 'getCategories')->update(['lft' => 6, 'rgt' => 7, 'active' => 1]);
			HomeSection::where('method', 'getLocations')->update(['lft' => 8, 'rgt' => 9, 'active' => 1]);
			HomeSection::where('method', 'getFeaturedPostsCompanies')->update(['lft' => 10, 'rgt' => 11, 'active' => 1]);
			HomeSection::where('method', 'getStats')->update(['lft' => 12, 'rgt' => 13, 'active' => 1]);
			HomeSection::where('method', 'getTopAdvertising')->update(['lft' => 14, 'rgt' => 15, 'active' => 0]);
			HomeSection::where('method', 'getBottomAdvertising')->update(['lft' => 16, 'rgt' => 17, 'active' => 0]);
			
			$message = trans("admin::messages.The homepage sections reorganization were been reset successfully.");
			Alert::success($message)->flash();
		}
		
		// Reset all the homepage settings.
		if ($action == 'reset_settings') {
			HomeSection::where('method', 'getSearchForm')->update(['value' => null, 'active' => 1]);
			HomeSection::where('method', 'getSponsoredPosts')->update(['value' => null, 'active' => 1]);
			HomeSection::where('method', 'getLatestPosts')->update(['value' => null, 'active' => 1]);
			HomeSection::where('method', 'getCategories')->update(['value' => null, 'active' => 1]);
			HomeSection::where('method', 'getLocations')->update(['value' => null, 'active' => 1]);
			HomeSection::where('method', 'getFeaturedPostsCompanies')->update(['value' => null, 'active' => 1]);
			HomeSection::where('method', 'getStats')->update(['value' => null, 'active' => 1]);
			HomeSection::where('method', 'getTopAdvertising')->update(['value' => null, 'active' => 0]);
			HomeSection::where('method', 'getBottomAdvertising')->update(['value' => null, 'active' => 0]);
			
			// Delete files which has 'header-' as prefix
			try {
				
				// List all files in the "app/logo/" path,
				// Filter the ones that match the "*header-*.*" pattern,
				// And delete them.
				$allFiles = $this->disk->files('app/logo/');
				$matchingFiles = preg_grep('/.+\/header-.+\./', $allFiles);
				$this->disk->delete($matchingFiles);
				
			} catch (\Exception $e) {}
			
			$message = trans("admin::messages.All the homepage settings were been reset successfully.");
			Alert::success($message)->flash();
		}
		
		if (in_array($action, ['reset_reorder', 'reset_settings'])) {
			Cache::flush();
		} else {
			$message = trans("admin::messages.No action has been performed.");
			Alert::warning($message)->flash();
		}
		
		return redirect()->back();
	}
}

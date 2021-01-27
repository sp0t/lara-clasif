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

use App\Models\City;
use App\Models\Country;
use App\Models\Scopes\ActiveScope;
use App\Models\SubAdmin1;
use App\Models\SubAdmin2;
use Larapen\Admin\app\Http\Controllers\PanelController;
use App\Http\Requests\Admin\CityRequest as StoreRequest;
use App\Http\Requests\Admin\CityRequest as UpdateRequest;

class CityController extends PanelController
{
	public $parentEntity = null;
	public $countryCode = null;
	public $admin1Code = null;
	public $admin2Code = null;
	
	public function setup()
	{
		// Parents Entities
		$parentEntities = ['countries', 'admins1', 'admins2'];
		
		// Get the parent Entity slug
		$this->parentEntity = request()->segment(2);
		if (!in_array($this->parentEntity, $parentEntities)) {
			abort(404);
		}
		
		// Country => City
		if ($this->parentEntity == 'countries') {
			// Get the Country Code
			$this->countryCode = request()->segment(3);
			
			// Get the Country's name
			$country = Country::findOrFail($this->countryCode);
		}
		
		// Admin1 => City
		if ($this->parentEntity == 'admins1') {
			// Get the Admin1 Codes
			$this->admin1Code = request()->segment(3);
			
			// Get the Admin1's name
			$admin1 = SubAdmin1::findOrFail($this->admin1Code);
			
			// Get the Country Code
			$this->countryCode = $admin1->country_code;
			
			// Get the Country's name
			$country = Country::findOrFail($this->countryCode);
		}
		
		// Admin2 => City
		if ($this->parentEntity == 'admins2') {
			// Get the Admin2 Codes
			$this->admin2Code = request()->segment(3);
			
			// Get the Admin2's name
			$admin2 = SubAdmin2::findOrFail($this->admin2Code);
			
			// Get the Admin1 Codes
			$this->admin1Code = $admin2->subadmin1_code;
			
			// Get the Admin1's name
			$admin1 = SubAdmin1::findOrFail($this->admin1Code);
			
			// Get the Country Code
			$this->countryCode = $admin1->country_code;
			
			// Get the Country's name
			$country = Country::findOrFail($this->countryCode);
		}
		
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel('App\Models\City');
		$this->xPanel->with(['country', 'subAdmin1', 'subAdmin2']);
		$this->xPanel->enableParentEntity();
		$this->xPanel->allowAccess(['parent']);
		
		// Country => City
		if ($this->parentEntity == 'countries') {
			$this->xPanel->setRoute(admin_uri('countries/' . $this->countryCode . '/cities'));
			$this->xPanel->setEntityNameStrings(
				trans('admin::messages.city') . ' &rarr; ' . '<strong>' . $country->name . '</strong>',
				trans('admin::messages.cities') . ' &rarr; ' . '<strong>' . $country->name . '</strong>'
			);
			$this->xPanel->setParentKeyField('country_code');
			$this->xPanel->addClause('where', 'country_code', '=', $this->countryCode);
			$this->xPanel->setParentRoute(admin_uri('countries'));
			$this->xPanel->setParentEntityNameStrings(trans('admin::messages.country'), trans('admin::messages.countries'));
		}
		
		// Admin1 => City
		if ($this->parentEntity == 'admins1') {
			$this->xPanel->setRoute(admin_uri('admins1/' . $this->admin1Code . '/cities'));
			$this->xPanel->setEntityNameStrings(
				trans('admin::messages.city') . ' &rarr; ' . '<strong>' . $admin1->name . '</strong>' . ', ' . '<strong>' . $country->name . '</strong>',
				trans('admin::messages.cities') . ' &rarr; ' . '<strong>' . $admin1->name . '</strong>' . ', ' . '<strong>' . $country->name . '</strong>'
			);
			$this->xPanel->setParentKeyField('subadmin1_code');
			$this->xPanel->addClause('where', 'subadmin1_code', '=', $this->admin1Code);
			$this->xPanel->setParentRoute(admin_uri('countries/' . $this->countryCode . '/admins1'));
			$this->xPanel->setParentEntityNameStrings(
				trans('admin::messages.admin. division 1') . ' &rarr; ' . '<strong>' . $country->name . '</strong>',
				trans('admin::messages.admin. divisions 1') . ' &rarr; ' . '<strong>' . $country->name . '</strong>'
			);
		}
		
		// Admin2 => City
		if ($this->parentEntity == 'admins2') {
			$this->xPanel->setRoute(admin_uri('admins2/' . $this->admin2Code . '/cities'));
			$this->xPanel->setEntityNameStrings(
				trans('admin::messages.city') . ' &rarr; ' . '<strong>' . $admin2->name . '</strong>' . ', ' . '<strong>' . $admin1->name . '</strong>' . ', ' . '<strong>' . $country->name . '</strong>',
				trans('admin::messages.cities') . ' &rarr; ' . ' <strong>' . $admin2->name . '</strong>' . ', ' . '<strong>' . $admin1->name . '</strong>' . ', ' . '<strong>' . $country->name . '</strong>'
			);
			$this->xPanel->setParentKeyField('subadmin2_code');
			$this->xPanel->addClause('where', 'subadmin2_code', '=', $this->admin2Code);
			$this->xPanel->setParentRoute(admin_uri('admins1/' . $this->admin1Code . '/admins2'));
			$this->xPanel->setParentEntityNameStrings(
				trans('admin::messages.admin. division 2') . ' &rarr; ' . '<strong>' . $admin1->name . '</strong>' . ', ' . '<strong>' . $country->name . '</strong>',
				trans('admin::messages.admin. divisions 2') . ' &rarr; ' . '<strong>' . $admin1->name . '</strong>' . ', ' . '<strong>' . $country->name . '</strong>'
			);
		}
		
		$this->xPanel->addButtonFromModelFunction('top', 'bulk_delete_btn', 'bulkDeleteBtn', 'end');
		
		/*
		|--------------------------------------------------------------------------
		| COLUMNS AND FIELDS
		|--------------------------------------------------------------------------
		*/
		// COLUMNS
		$this->xPanel->addColumn([
			'name'  => 'id',
			'label' => '',
			'type'  => 'checkbox',
			'orderable' => false,
		]);
		$this->xPanel->addColumn([
			'name'  => 'country_code',
			'label' => trans("admin::messages.Country Code"),
		]);
		$this->xPanel->addColumn([
			'name'  => 'name',
			'label' => trans("admin::messages.Local Name"),
		]);
		$this->xPanel->addColumn([
			'name'  => 'asciiname',
			'label' => trans("admin::messages.Name"),
		]);
		$this->xPanel->addColumn([
			'name'          => 'subadmin1_code',
			'label'         => trans("admin::messages.Admin1 Code"),
			'type'          => 'model_function',
			'function_name' => 'getAdmin1Html',
		]);
		$this->xPanel->addColumn([
			'name'          => 'subadmin2_code',
			'label'         => trans("admin::messages.Admin2 Code"),
			'type'          => 'model_function',
			'function_name' => 'getAdmin2Html',
		]);
		$this->xPanel->addColumn([
			'name'          => 'active',
			'label'         => trans("admin::messages.Active"),
			'type'          => 'model_function',
			'function_name' => 'getActiveHtml',
		]);
		
		// FIELDS
		$this->xPanel->addField([
			'name'    => 'id',
			'type'    => 'hidden',
			'default' => $this->autoIncrementId(),
		], 'create');
		
		// Country => City
		if (!empty($this->countryCode)) {
			$this->xPanel->addField([
				'name'  => 'country_code',
				'type'  => 'hidden',
				'value' => $this->countryCode,
			], 'create');
		} else {
			if (!empty($this->admin1Code)) {
				$this->xPanel->addField([
					'name'  => 'country_code',
					'type'  => 'hidden',
					'value' => $this->countryCode,
				], 'create');
			} else {
				if (!empty($this->admin2Code)) {
					$this->xPanel->addField([
						'name'  => 'country_code',
						'type'  => 'hidden',
						'value' => $this->countryCode,
					], 'create');
				} else {
					$this->xPanel->addField([
						'name'       => 'country_code',
						'label'      => trans('admin::messages.Country Code'),
						'type'       => 'select2',
						'attribute'  => 'asciiname',
						'model'      => 'App\Models\Country',
						'attributes' => [
							'placeholder' => trans('admin::messages.Enter the country code (ISO Code)'),
						],
					]);
				}
			}
		}
		
		// Admin1 => City
		if (!empty($this->admin1Code)) {
			$this->xPanel->addField([
				'name'  => 'subadmin1_code',
				'type'  => 'hidden',
				'value' => $this->admin1Code,
			], 'create');
		} else {
			if (!empty($this->admin2Code)) {
				$this->xPanel->addField([
					'name'  => 'subadmin1_code',
					'type'  => 'hidden',
					'value' => $this->admin1Code,
				], 'create');
			} else {
				$this->xPanel->addField([
					'name'        => 'subadmin1_code',
					'label'       => trans("admin::messages.Admin1 Code"),
					'type'        => 'select2_from_array',
					'options'     => $this->subAdmin1s(),
					'allows_null' => true,
				]);
			}
		}
		
		// Admin2 => City
		if (!empty($this->admin2Code)) {
			$this->xPanel->addField([
				'name'  => 'subadmin2_code',
				'type'  => 'hidden',
				'value' => $this->admin2Code,
			], 'create');
		} else {
			if (!empty($this->admin1Code)) {
				$this->xPanel->addField([
					'name'        => 'subadmin2_code',
					'label'       => trans("admin::messages.Admin2 Code"),
					'type'        => 'select2_from_array',
					'options'     => $this->subAdmin2s(),
					'allows_null' => true,
				]);
			}
		}
		
		$this->xPanel->addField([
			'name'              => 'name',
			'label'             => trans('admin::messages.Local Name'),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.Local Name'),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'asciiname',
			'label'             => trans("admin::messages.Name"),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.Enter the country name (In English)'),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'latitude',
			'label'             => trans("admin::messages.Latitude"),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans("admin::messages.Latitude"),
			],
			'hint'              => trans('admin::messages.In decimal degrees (wgs84)'),
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'longitude',
			'label'             => trans("admin::messages.Longitude"),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans("admin::messages.Longitude"),
			],
			'hint'              => trans('admin::messages.In decimal degrees (wgs84)'),
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'population',
			'label'             => trans("admin::messages.Population"),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans("admin::messages.Population"),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'time_zone',
			'label'             => trans("admin::messages.Time Zone ID"),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.Enter the time zone ID (example: Europe/Paris)'),
			],
			'hint'              => trans('admin::messages.Please check the TimeZoneId code format here:') . ' <a href="http://download.geonames.org/export/dump/timeZones.txt" target="_blank">http://download.geonames.org/export/dump/timeZones.txt</a>',
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'  => 'active',
			'label' => trans("admin::messages.Active"),
			'type'  => 'checkbox',
		]);
	}
	
	public function store(StoreRequest $request)
	{
		return parent::storeCrud();
	}
	
	public function update(UpdateRequest $request)
	{
		return parent::updateCrud();
	}
	
	/**
	 * Increment new cities IDs
	 *
	 * @return int
	 */
	public function autoIncrementId()
	{
		// Note: 10793747 is the higher ID found in Geonames cities database
		// To guard against any MySQL error we will increment new IDs from 14999999
		$startId = 14999999;
		
		// Count all non-Geonames entries
		$lastAddedEntry = City::withoutGlobalScope(ActiveScope::class)->where('id', '>=', $startId)->orderBy('id', 'DESC')->first();
		$lastAddedId = (!empty($lastAddedEntry)) ? $lastAddedEntry->id : $startId;
		
		// Set new ID
		$newId = $lastAddedId + 1;
		
		return $newId;
	}
	
	private function subAdmin1s()
	{
		// Get the Administratives Divisions
		$admins = SubAdmin1::where('country_code', $this->countryCode)->get();
		
		$tab = [];
		if ($admins->count() > 0) {
			foreach ($admins as $admin) {
				$tab[$admin->code] = $admin->name . ' (' . $admin->code . ')';
			}
		}
		
		return $tab;
	}
	
	private function subAdmin2s()
	{
		// Get the Admin1 Code
		if (empty($this->admin1Code)) {
			$city = $this->xPanel->model->find(request()->segment(5));
			if (!empty($city)) {
				$this->admin1Code = $city->subadmin1_code;
			}
		}
		
		// Get the Administratives Divisions
		$admins = SubAdmin2::where('country_code', $this->countryCode)->where('subadmin1_code', $this->admin1Code)->get();
		
		$tab = [];
		if ($admins->count() > 0) {
			foreach ($admins as $admin) {
				$tab[$admin->code] = $admin->name . ' (' . $admin->code . ')';
			}
		}
		
		return $tab;
	}
}

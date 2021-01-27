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

use App\Http\Controllers\Admin\Traits\SubAdminTrait;
use App\Models\Country;
use App\Models\SubAdmin1;
use Larapen\Admin\app\Http\Controllers\PanelController;
use App\Http\Requests\Admin\SubAdmin2Request as StoreRequest;
use App\Http\Requests\Admin\SubAdmin2Request as UpdateRequest;

class SubAdmin2Controller extends PanelController
{
	use SubAdminTrait;
	
	public $parentEntity = null;
	public $countryCode = null;
	public $admin1Code = null;
	
	public function setup()
	{
		// Parents Entities
		$parentEntities = ['admins1'];
		
		// Get the parent Entity slug
		$this->parentEntity = request()->segment(2);
		if (!in_array($this->parentEntity, $parentEntities)) {
			abort(404);
		}
		
		// Admin1 => Admin2
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
		
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel('App\Models\SubAdmin2');
		$this->xPanel->enableParentEntity();
		$this->xPanel->allowAccess(['parent']);
		
		// Admin1 => Admin2
		if ($this->parentEntity == 'admins1') {
			$this->xPanel->setRoute(admin_uri('admins1/' . $this->admin1Code . '/admins2'));
			$this->xPanel->setEntityNameStrings(
				trans('admin::messages.admin. division 2') . ' &rarr; ' . '<strong>' . $admin1->name . '</strong>' . ', ' . '<strong>' . $country->name . '</strong>',
				trans('admin::messages.admin. divisions 2') . ' &rarr; ' . '<strong>' . $admin1->name . '</strong>' . ', ' . '<strong>' . $country->name . '</strong>'
			);
			$this->xPanel->setParentKeyField('subadmin1_code');
			$this->xPanel->addClause('where', 'subadmin1_code', '=', $this->admin1Code);
			$this->xPanel->setParentRoute(admin_uri('countries/' . $this->countryCode . '/admins1'));
			$this->xPanel->setParentEntityNameStrings(
				trans('admin::messages.admin. division 1') . ' &rarr; ' . '<strong>' . $country->name . '</strong>',
				trans('admin::messages.admin. divisions 1') . ' &rarr; ' . '<strong>' . $country->name . '</strong>'
			);
		}
		
		$this->xPanel->addButtonFromModelFunction('top', 'bulk_delete_btn', 'bulkDeleteBtn', 'end');
		$this->xPanel->addButtonFromModelFunction('line', 'cities', 'citiesBtn', 'beginning');
		
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
			'name'  => 'code',
			'label' => trans("admin::messages.Code"),
		]);
		$this->xPanel->addColumn([
			'name'  => 'name',
			'label' => trans("admin::messages.Local Name"),
		]);
		$this->xPanel->addColumn([
			'name'          => 'asciiname',
			'label'         => trans("admin::messages.Name"),
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
		$this->xPanel->addField([
			'name'  => 'country_code',
			'type'  => 'hidden',
			'value' => $this->countryCode,
		], 'create');
		$this->xPanel->addField([
			'name'  => 'subadmin1_code',
			'type'  => 'hidden',
			'value' => $this->admin1Code,
		], 'create');
		$this->xPanel->addField([
			'name'    => 'code',
			'type'    => 'hidden',
			'default' => $this->autoIncrementCode($this->admin1Code . '.'),
		], 'create');
		$this->xPanel->addField([
			'name'              => 'name',
			'label'             => trans("admin::messages.Local Name"),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans("admin::messages.Local Name"),
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
				'placeholder' => trans('admin::messages.Enter the name (In English)'),
			],
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
}

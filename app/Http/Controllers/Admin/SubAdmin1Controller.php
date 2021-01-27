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
use Larapen\Admin\app\Http\Controllers\PanelController;
use App\Http\Requests\Admin\SubAdmin1Request as StoreRequest;
use App\Http\Requests\Admin\SubAdmin1Request as UpdateRequest;

class SubAdmin1Controller extends PanelController
{
	use SubAdminTrait;
	
	public $countryCode = null;
	
	public function setup()
	{
		// Get the Country Code
		$this->countryCode = request()->segment(3);
		
		// Get the Country's name
		$country = Country::findOrFail($this->countryCode);
		
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel('App\Models\SubAdmin1');
		$this->xPanel->setRoute(admin_uri('countries/' . $this->countryCode . '/admins1'));
		$this->xPanel->setEntityNameStrings(
			trans('admin::messages.admin. division 1') . ' &rarr; ' . '<strong>' . $country->name . '</strong>',
			trans('admin::messages.admin. divisions 1') . ' &rarr; ' . '<strong>' . $country->name . '</strong>'
		);
		
		$this->xPanel->enableParentEntity();
		$this->xPanel->setParentKeyField('country_code');
		$this->xPanel->addClause('where', 'country_code', '=', $this->countryCode);
		$this->xPanel->setParentRoute(admin_uri('countries'));
		$this->xPanel->setParentEntityNameStrings(trans('admin::messages.country'), trans('admin::messages.countries'));
		$this->xPanel->allowAccess(['parent']);
		
		$this->xPanel->addButtonFromModelFunction('top', 'bulk_delete_btn', 'bulkDeleteBtn', 'end');
		$this->xPanel->addButtonFromModelFunction('line', 'cities', 'citiesBtn', 'beginning');
		$this->xPanel->addButtonFromModelFunction('line', 'admin_divisions2', 'adminDivisions2Btn', 'beginning');
		
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
			'name'    => 'code',
			'type'    => 'hidden',
			'default' => $this->autoIncrementCode($this->countryCode . '.'),
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

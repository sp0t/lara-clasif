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

use Larapen\Admin\app\Http\Controllers\PanelController;
use App\Http\Requests\Admin\CountryRequest as StoreRequest;
use App\Http\Requests\Admin\CountryRequest as UpdateRequest;

class CountryController extends PanelController
{
	public function setup()
	{
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel('App\Models\Country');
		$this->xPanel->setRoute(admin_uri('countries'));
		$this->xPanel->setEntityNameStrings(trans('admin::messages.country'), trans('admin::messages.countries'));
		
		$this->xPanel->addButtonFromModelFunction('top', 'bulk_delete_btn', 'bulkDeleteBtn', 'end');
		$this->xPanel->addButtonFromModelFunction('line', 'cities', 'citiesBtn', 'beginning');
		$this->xPanel->addButtonFromModelFunction('line', 'admin_divisions1', 'adminDivisionsBtn', 'beginning');
		
		/*
		|--------------------------------------------------------------------------
		| COLUMNS AND FIELDS
		|--------------------------------------------------------------------------
		*/
		// COLUMNS
		$this->xPanel->addColumn([
			'name'      => 'id',
			'label'     => '',
			'type'      => 'checkbox',
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
			'name'              => 'code',
			'label'             => trans('admin::messages.Code'),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.Enter the country code (ISO Code)'),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		], 'create');
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
			'name'              => 'capital',
			'label'             => trans('admin::messages.Capital') . ' (' . trans('admin::messages.Optional') . ')',
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.Capital'),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'continent_code',
			'label'             => trans('admin::messages.Continent'),
			'type'              => 'select2',
			'attribute'         => 'name',
			'model'             => 'App\Models\Continent',
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'tld',
			'label'             => trans('admin::messages.TLD') . ' (' . trans('admin::messages.Optional') . ')',
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.Enter the country tld (E.g. .bj for Benin)'),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'phone',
			'label'             => trans("admin::messages.Phone Ind.") . ' (' . trans('admin::messages.Optional') . ')',
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.Enter the country phone ind. (E.g. +229 for Benin)'),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'currency_code',
			'label'             => trans("admin::messages.Currency Code"),
			'type'              => 'select2',
			'attribute'         => 'code',
			'model'             => 'App\Models\Currency',
			'hint'              => trans('admin::messages.Default country currency'),
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'   => 'background_image',
			'label'  => trans("admin::messages.Background Image"),
			'type'   => 'image',
			'upload' => true,
			'disk'   => 'public',
			'hint'   => trans('admin::messages.Choose a picture from your computer.') . '<br>' . trans('admin::messages.This picture will override the homepage header background image for this country.'),
		]);
		$this->xPanel->addField([
			'name'              => 'languages',
			'label'             => trans("admin::messages.Languages"),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.eg. en,de,fr,it'),
			],
			'hint'              => trans('admin::messages.languages_codes_list_hint', ['url' => admin_url('languages')]),
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'admin_type',
			'label'             => trans("admin::messages.Administrative Division's Type"),
			'type'              => 'enum',
			'hint'              => trans("admin::messages.eg. 0 => Default value, 1 => Admin. Division 1, 2 => Admin. Division 2"),
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'admin_field_active',
			'label'             => trans("admin::messages.Active Administrative Division's Field"),
			'type'              => 'checkbox',
			'hint'              => trans("admin::messages.Active the administrative division's field in the items form. You need to set the :admin_type_hint to 1 or 2.", [
				'admin_type_hint' => trans("admin::messages.Administrative Division's Type"),
			]),
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
				//'style' => 'margin-top: 20px;',
			],
		]);
		/*
		$this->xPanel->addField([
			'name'  => 'active',
			'label' => trans("admin::messages.Active"),
			'type'  => 'checkbox',
		]);
		*/
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

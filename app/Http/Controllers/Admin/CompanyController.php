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
use App\Http\Requests\Admin\CompanyRequest as StoreRequest;
use App\Http\Requests\Admin\CompanyRequest as UpdateRequest;

class CompanyController extends PanelController
{
	public function setup()
	{
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel('App\Models\Company');
		$this->xPanel->setRoute(admin_uri('companies'));
		$this->xPanel->setEntityNameStrings(trans('admin::messages.company'), trans('admin::messages.companies'));
		$this->xPanel->denyAccess(['create']);
		if (!request()->input('order')) {
			$this->xPanel->orderBy('id', 'DESC');
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
			'name'          => 'logo', // Put unused field column
			'label'         => trans("admin::messages.Logo"),
			'type'          => 'model_function',
			'function_name' => 'getLogoHtml',
		]);
		$this->xPanel->addColumn([
			'name'          => 'name',
			'label'         => trans('admin::messages.Name'),
			'type'          => 'model_function',
			'function_name' => 'getNameHtml',
		]);
		$this->xPanel->addColumn([
			'name'  => 'description',
			'label' => trans('admin::messages.Description'),
		]);
		$this->xPanel->addColumn([
			'name'          => 'country_code',
			'label'         => trans("admin::messages.Country"),
			'type'          => 'model_function',
			'function_name' => 'getCountryHtml',
		]);
		
		// FIELDS
		$this->xPanel->addField([
			'name'       => 'name',
			'label'      => trans('admin::messages.Company Name'),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans('admin::messages.Company Name'),
			],
		]);
		$this->xPanel->addField([
			'name'   => 'logo',
			'label'  => trans('admin::messages.Logo') . ' (Supported file extensions: jpg, jpeg, png, gif)',
			'type'   => 'image',
			'upload' => true,
			'disk'   => 'public',
		]);
		$this->xPanel->addField([
			'name'       => 'description',
			'label'      => trans("admin::messages.Company Description"),
			'type'       => 'textarea',
			'attributes' => [
				'placeholder' => trans("admin::messages.Company Description"),
				'rows'        => 10,
			],
		]);
		$this->xPanel->addField([
			'name'       => "address",
			'label'      => trans('admin::messages.Address'),
			'type'       => "text",
			'attributes' => [
				'placeholder' => trans('admin::messages.Address'),
			],
		]);
		$this->xPanel->addField([
			'name'              => 'phone',
			'label'             => trans('admin::messages.Phone'),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.Phone'),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'fax',
			'label'             => trans('admin::messages.Fax'),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.Fax'),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'email',
			'label'             => trans('admin::messages.User Email'),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.User Email'),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'website',
			'label'             => trans('admin::messages.Company Website'),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.Company Website'),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => "facebook",
			'label'             => 'Facebook',
			'type'              => "text",
			'attributes'        => [
				'placeholder' => 'Facebook',
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => "twitter",
			'label'             => 'Twitter',
			'type'              => "text",
			'attributes'        => [
				'placeholder' => 'Twitter',
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => "linkedin",
			'label'             => 'Linkedin',
			'type'              => "text",
			'attributes'        => [
				'placeholder' => 'Linkedin',
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => "googleplus",
			'label'             => 'Google+',
			'type'              => "text",
			'attributes'        => [
				'placeholder' => 'Google+',
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => "pinterest",
			'label'             => 'Pinterest',
			'type'              => "text",
			'attributes'        => [
				'placeholder' => 'Pinterest',
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
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

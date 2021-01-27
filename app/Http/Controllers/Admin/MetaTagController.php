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

use App\Models\MetaTag;
use Larapen\Admin\app\Http\Controllers\PanelController;
use App\Http\Requests\Admin\MetaTagRequest as StoreRequest;
use App\Http\Requests\Admin\MetaTagRequest as UpdateRequest;

class MetaTagController extends PanelController
{
	public function setup()
	{
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel('App\Models\MetaTag');
		$this->xPanel->setRoute(admin_uri('meta_tags'));
		$this->xPanel->setEntityNameStrings(trans('admin::messages.meta tag'), trans('admin::messages.meta tags'));
		$this->xPanel->enableDetailsRow();
		$this->xPanel->allowAccess(['details_row']);
		
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
			'name'          => 'page',
			'label'         => trans("admin::messages.Page"),
			'type'          => 'model_function',
			'function_name' => 'getPageHtml',
		]);
		$this->xPanel->addColumn([
			'name'  => 'title',
			'label' => trans("admin::messages.Title"),
		]);
		$this->xPanel->addColumn([
			'name'  => 'description',
			'label' => trans("admin::messages.Description"),
		]);
		$this->xPanel->addColumn([
			'name'          => 'active',
			'label'         => trans("admin::messages.Active"),
			'type'          => 'model_function',
			'function_name' => 'getActiveHtml',
			'on_display'    => 'checkbox',
		]);
		
		// FIELDS
		$this->xPanel->addField([
			'name'        => 'page',
			'label'       => trans("admin::messages.Page"),
			'type'        => 'select2_from_array',
			'options'     => MetaTag::getDefaultPages(),
			'allows_null' => false,
		]);
		$this->xPanel->addField([
			'name'       => 'title',
			'label'      => trans("admin::messages.Title"),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans("admin::messages.Title"),
			],
			'hint'       => trans('admin::messages.You can use dynamic variables such as {app_name} and {country} - e.g. {app_name} will be replaced with the name of your website and {country} by the selected country.'),
		]);
		$this->xPanel->addField([
			'name'       => 'description',
			'label'      => trans("admin::messages.Description"),
			'type'       => 'textarea',
			'attributes' => [
				'placeholder' => trans("admin::messages.Description"),
			],
			'hint'       => trans('admin::messages.You can use dynamic variables such as {app_name} and {country} - e.g. {app_name} will be replaced with the name of your website and {country} by the selected country.'),
		]);
		$this->xPanel->addField([
			'name'       => 'keywords',
			'label'      => trans("admin::messages.Keywords"),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans("admin::messages.Keywords"),
			],
			'hint'       => trans('admin::messages.You can use dynamic variables such as {app_name} and {country} - e.g. {app_name} will be replaced with the name of your website and {country} by the selected country.'),
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

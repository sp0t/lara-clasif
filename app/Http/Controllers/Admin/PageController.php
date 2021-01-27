<?php
/**
 * LaraClassified - Geo Classified Ads Software
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
use App\Http\Requests\Admin\PageRequest as StoreRequest;
use App\Http\Requests\Admin\PageRequest as UpdateRequest;

class PageController extends PanelController
{
	public function setup()
	{
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel('App\Models\Page');
		$this->xPanel->setRoute(admin_uri('pages'));
		$this->xPanel->setEntityNameStrings(trans('admin::messages.page'), trans('admin::messages.pages'));
		$this->xPanel->enableReorder('name', 1);
		$this->xPanel->enableDetailsRow();
		$this->xPanel->allowAccess(['reorder', 'details_row']);
		if (!request()->input('order')) {
			$this->xPanel->orderBy('lft', 'ASC');
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
			'name'          => 'name',
			'label'         => trans("admin::messages.Name"),
			'type'          => 'model_function',
			'function_name' => 'getNameHtml',
		]);
		$this->xPanel->addColumn([
			'name'  => 'title',
			'label' => trans("admin::messages.Title"),
		]);
		$this->xPanel->addColumn([
			'name'          => 'active',
			'label'         => trans("admin::messages.Active"),
			'type'          => "model_function",
			'function_name' => 'getActiveHtml',
			'on_display'    => 'checkbox',
		]);
		
		// FIELDS
		$this->xPanel->addField([
			'name'       => 'name',
			'label'      => trans("admin::messages.Name"),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans("admin::messages.Name"),
			],
		]);
		$this->xPanel->addField([
			'name'              => 'slug',
			'label'             => trans('admin::messages.Slug'),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.Will be automatically generated from your name, if left empty.'),
			],
			'hint'              => trans('admin::messages.Will be automatically generated from your name, if left empty.'),
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'external_link',
			'label'             => trans("admin::messages.External Link"),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => "http://",
			],
			'hint'              => trans('admin::messages.Redirect this page to the URL above.') . ' ' . trans('admin::messages.NOTE: Leave this field empty if you don\'t want redirect this page.'),
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'       => 'title',
			'label'      => trans("admin::messages.Title"),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans("admin::messages.Title"),
			],
		]);
		$this->xPanel->addField([
			'name'       => 'content',
			'label'      => trans("admin::messages.Content"),
			'type'       => (config('settings.other.simditor_wysiwyg'))
				? 'simditor'
				: 'textarea',
			'attributes' => [
				'placeholder' => trans("admin::messages.Content"),
				'id'          => 'pageContent',
				'rows'        => 20,
			],
		]);
		$this->xPanel->addField([
			'name'  => 'type',
			'label' => trans('admin::messages.Type'),
			'type'  => 'enum',
		]);
		$this->xPanel->addField([
			'name'   => 'picture',
			'label'  => trans('admin::messages.Picture'),
			'type'   => 'image',
			'upload' => true,
			'disk'   => 'public',
		]);
		$this->xPanel->addField([
			'name'                => 'name_color',
			'label'               => trans('admin::messages.Page Name Color'),
			'type'                => 'color_picker',
			'colorpicker_options' => [
				'customClass' => 'custom-class',
			],
			'wrapperAttributes'   => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'                => 'title_color',
			'label'               => trans('admin::messages.Page Title Color'),
			'type'                => 'color_picker',
			'colorpicker_options' => [
				'customClass' => 'custom-class',
			],
			'wrapperAttributes'   => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'  => 'target_blank',
			'label' => trans("admin::messages.Open the link in new window"),
			'type'  => 'checkbox',
		]);
		$this->xPanel->addField([
			'name'  => 'excluded_from_footer',
			'label' => trans("admin::messages.Exclude from footer"),
			'type'  => 'checkbox',
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

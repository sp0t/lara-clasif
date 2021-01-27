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
use App\Http\Requests\Admin\CurrencyRequest as StoreRequest;
use App\Http\Requests\Admin\CurrencyRequest as UpdateRequest;

class CurrencyController extends PanelController
{
	public function setup()
	{
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel('App\Models\Currency');
		$this->xPanel->setRoute(admin_uri('currencies'));
		$this->xPanel->setEntityNameStrings(trans('admin::messages.currency'), trans('admin::messages.currencies'));
		
		$this->xPanel->addButtonFromModelFunction('top', 'bulk_delete_btn', 'bulkDeleteBtn', 'end');
		
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
			'label' => trans("admin::messages.Code") . ' (ISO 4217)',
		]);
		$this->xPanel->addColumn([
			'name'          => 'name',
			'label'         => trans("admin::messages.Name"),
			'type'          => 'model_function',
			'function_name' => 'getNameHtml',
		]);
		$this->xPanel->addColumn([
			'name'  => 'html_entity',
			'label' => trans("admin::messages.Html Entity"),
		]);
		$this->xPanel->addColumn([
			'name'          => 'in_left',
			'label'         => trans("admin::messages.Symbol in left"),
			'type'          => 'model_function',
			'function_name' => 'getPositionHtml',
		]);
		
		// FIELDS
		$this->xPanel->addField([
			'name'       => 'code',
			'label'      => trans('admin::messages.Code') . ' (ISO 4217)',
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans('admin::messages.Enter the currency code (ISO Code)'),
			],
		], 'create');
		$this->xPanel->addField([
			'name'              => 'name',
			'label'             => trans("admin::messages.Name"),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans("admin::messages.Name"),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'html_entity',
			'label'             => trans('admin::messages.Html Entity'),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.Enter the html entity code'),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'font_arial',
			'label'             => trans('admin::messages.Font Arial'),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.Enter the font arial code'),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'font_code2000',
			'label'             => trans('admin::messages.Font Code2000'),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.Enter the font code2000 code'),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'unicode_decimal',
			'label'             => trans('admin::messages.Unicode Decimal'),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.Enter the unicode decimal code'),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'unicode_hex',
			'label'             => trans('admin::messages.Unicode Hex'),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.Enter the unicode hex code'),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'in_left',
			'label'             => trans("admin::messages.Symbol in left"),
			'type'              => 'checkbox',
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
				'style' => 'margin-top: 20px;',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'decimal_places',
			'label'             => trans('admin::messages.Decimal Places'),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.Enter the decimal places'),
			],
			'hint'              => trans('admin::messages.Number after decimal. Ex: 2 => 150.00 [or] 3 => 150.000'),
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'decimal_separator',
			'label'             => trans('admin::messages.Decimal Separator'),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.Enter the decimal separator'),
				'maxlength'   => 1,
			],
			'hint'              => trans('admin::messages.Ex: "." => 150.00 [or] "," => 150,00'),
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'thousand_separator',
			'label'             => trans('admin::messages.Thousand Separator'),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.Enter the thousand separator'),
				'maxlength'   => 1,
			],
			'hint'              => trans('admin::messages.Ex: "," => 150,000.00 [or] whitespace => 150 000.000'),
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

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
 * Increase PHP page execution time for this controller.
 * NOTE: This function has no effect when PHP is running in safe mode (http://php.net/manual/en/ini.sect.safe-mode.php#ini.safe-mode).
 * There is no workaround other than turning off safe mode or changing the time limit (max_execution_time) in the php.ini.
 */
set_time_limit(0);

use App\Helpers\Lang\LangManager;
use App\Http\Requests\Admin\LanguageRequest as StoreRequest;
use App\Http\Requests\Admin\LanguageRequest as UpdateRequest;
use App\Models\Language;
use Larapen\Admin\app\Http\Controllers\PanelController;
use Prologue\Alerts\Facades\Alert;

class LanguageController extends PanelController
{
	/**
	 * LanguageController constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->middleware('demo.restriction')->only(['syncFilesLines']);
	}
	
	public function setup()
	{
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel('App\Models\Language');
		$this->xPanel->setRoute(admin_uri('languages'));
		$this->xPanel->setEntityNameStrings(trans('admin::messages.language'), trans('admin::messages.languages'));
		$this->xPanel->enableReorder('name', 1);
		$this->xPanel->allowAccess(['reorder']);
		if (!request()->input('order')) {
			$this->xPanel->orderBy('lft', 'ASC');
		}
		
		$this->xPanel->addButtonFromModelFunction('top', 'sync_files', 'syncFilesLinesBtn', 'end');
		
		/*
		|--------------------------------------------------------------------------
		| COLUMNS AND FIELDS
		|--------------------------------------------------------------------------
		*/
		// COLUMNS
		$this->xPanel->addColumn([
			'name'  => 'abbr',
			'label' => trans('admin::messages.Code') . ' (ISO 639-1)',
		]);
		$this->xPanel->addColumn([
			'name'          => 'name',
			'label'         => trans('admin::messages.language_name'),
			'type'          => 'model_function',
			'function_name' => 'getNameHtml',
		]);
		$this->xPanel->addColumn([
			'name'  => 'direction',
			'label' => trans("admin::messages.Direction"),
		]);
		$this->xPanel->addColumn([
			'name'          => 'active',
			'label'         => trans('admin::messages.active'),
			'type'          => "model_function",
			'function_name' => 'getActiveHtml',
		]);
		$this->xPanel->addColumn([
			'name'          => 'default',
			'label'         => trans('admin::messages.default'),
			'type'          => "model_function",
			'function_name' => 'getDefaultHtml',
		]);
		
		// FIELDS
		$infoLine = [
			'name' => 'info_line_1',
			'type' => 'custom_html',
		];
		$this->xPanel->addField(array_merge($infoLine, [
			'value' => trans('admin::messages.language_info_line_create'),
		]), 'create');
		$this->xPanel->addField(array_merge($infoLine, [
			'value' => trans('admin::messages.language_info_line_update', ['abbr' => request()->segment(3)]),
		]), 'update');
		
		$this->xPanel->addField([
			'label'             => mb_ucwords(trans("admin::messages.language")),
			'name'              => 'abbr',
			'type'              => 'select2_from_array',
			'options'           => $this->languagesList(),
			'allows_null'       => true,
			'hint'              => trans('admin::messages.language_abbr_field_hint', ['languages' => @implode(', ', $this->includedLanguages())]),
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		], 'create');
		
		$this->xPanel->addField([
			'name'              => 'native',
			'label'             => mb_ucwords(trans('admin::messages.native_name')),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => mb_ucwords(trans('admin::messages.native_name')),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		
		$this->xPanel->addField([
			'name'  => 'separator_1',
			'type'  => 'custom_html',
			'value' => '<div style="clear: both;"></div>',
		], 'create');
		
		$this->xPanel->addField([
			'label'             => trans("admin::messages.Locale Code (eg. en_US)"),
			'name'              => 'locale',
			'type'              => 'select2_from_array',
			'options'           => $this->localesList(),
			'allows_null'       => true,
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		
		$this->xPanel->addField([
			'name'              => 'direction',
			'label'             => trans("admin::messages.Direction"),
			'type'              => 'enum',
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		
		$this->xPanel->addField([
			'name'              => 'russian_pluralization',
			'label'             => trans('admin::messages.Russian Pluralization'),
			'type'              => 'checkbox',
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
				'style' => 'margin-top: 20px;',
			],
		]);
		
		$this->xPanel->addField([
			'name'    => 'active',
			'type'    => 'hidden',
			'default' => 1,
		], 'create');
		$this->xPanel->addField([
			'name'  => 'active',
			'label' => trans('admin::messages.active'),
			'type'  => 'checkbox',
		], 'update');
		
		$this->xPanel->addField([
			'name'  => 'default',
			'label' => trans('admin::messages.default'),
			'type'  => 'checkbox',
			'hint'  => trans('admin::messages.language_default_info'),
		], 'update');
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
	 * (Try to) Fill the missing lines in all languages files
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function syncFilesLines()
	{
		$errorFound = false;
		
		try {
			// Get the current Default Language
			$defaultLang = Language::where('default', 1)->first();
			
			// Init. the language manager
			$manager = new LangManager();
			
			// Get all the others languages
			$locales = $manager->getLocales($defaultLang->abbr);
			if (!empty($locales)) {
				foreach ($locales as $locale) {
					$manager->syncLines($defaultLang->abbr, $locale);
				}
			}
		} catch (\Exception $e) {
			Alert::error($e->getMessage())->flash();
			$errorFound = true;
		}
		
		// Check if error occurred
		if (!$errorFound) {
			$message = trans("admin::messages.The languages files were been synchronized.");
			Alert::success($message)->flash();
		}
		
		return redirect()->back();
	}
	
	/**
	 * @return array
	 */
	private function languagesList()
	{
		$entries = (array)config('languages');
		
		$entries = collect($entries)->map(function ($name, $code) {
			$name = $name . ' (' . $code . ')';
			
			if (in_array($code, $this->includedLanguages())) {
				$name .= ' &#10004;';
			}
			
			return $name;
		})->toArray();
		
		return $entries;
	}
	
	/**
	 * @return array
	 */
	private function localesList()
	{
		$entries = (array)config('locales');
		
		$entries = collect($entries)->map(function ($name, $code) {
			$name = $name . ' &rarr; ' . $code;
			
			return $name;
		})->toArray();
		
		return $entries;
	}
	
	/**
	 * @return array
	 */
	private function includedLanguages()
	{
		$manager = new LangManager();
		return $manager->getTranslatedLanguages();
	}
}

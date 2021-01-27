<?php

namespace Larapen\Admin\PanelTraits;

use Illuminate\Support\Arr;

trait Read
{
	/*
	|--------------------------------------------------------------------------
	|                                   READ
	|--------------------------------------------------------------------------
	*/
	
	/**
	 * Find and retrieve an entry in the database or fail.
	 *
	 * @param $id
	 * @return mixed
	 */
	public function getEntry($id)
	{
		if (!$this->entry) {
			$this->entry = $this->model->findOrFail($id);
			$this->entry = $this->entry->withFakes();
		}
		
		return $this->entry;
	}
	
	/**
	 * Make the query JOIN all relationships used in the columns, too,
	 * so there will be less database queries overall.
	 */
	public function autoEagerLoadRelationshipColumns()
	{
		$relationships = $this->getColumnsRelationships();
		if (count($relationships)) {
			$this->with($relationships);
		}
	}
	
	/**
	 * Get all entries from the database.
	 *
	 * @param null $lang
	 * @return mixed
	 */
	public function getEntries($lang = null)
	{
		// If lang is not set, get the default language
		if (empty($lang)) {
			$lang = \Lang::locale();
		}
		
		$this->autoEagerLoadRelationshipColumns();
		
		if (property_exists($this->model, 'translatable')) {
			$entries = $this->query->where('translation_lang', $lang)->get();
		} else {
			$entries = $this->query->get();
		}
		
		// add the fake columns for each entry
		foreach ($entries as $key => $entry) {
			$entry->addFakes($this->getFakeColumnsAsArray());
		}
		
		return $entries;
	}
	
	/**
	 * Get the fields for the create or update forms.
	 *
	 * @param $form
	 * @param bool $id
	 * @return mixed
	 */
	public function getFields($form, $id = false)
	{
		switch (strtolower($form)) {
			case 'create':
				return $this->getCreateFields();
				break;
			
			case 'update':
				return $this->getUpdateFields($id);
				break;
			
			default:
				return $this->getCreateFields();
				break;
		}
	}
	
	/**
	 * Check if the create/update form has upload fields.
	 * Upload fields are the ones that have "upload" => true defined on them.
	 *
	 * @param $form
	 * @param bool $id
	 * @return bool
	 */
	public function hasUploadFields($form, $id = false)
	{
		$fields = $this->getFields($form, $id);
		$upload_fields = Arr::where($fields, function ($value, $key) {
			return isset($value['upload']) && $value['upload'] == true;
		});
		
		return count($upload_fields) ? true : false;
	}
	
	/**
	 * Enable the DETAILS ROW functionality:.
	 *
	 * In the table view, show a plus sign next to each entry.
	 * When clicking that plus sign, an AJAX call will bring whatever content you want from the EntityCrudController::showDetailsRow($id) and show it to the user.
	 */
	public function enableDetailsRow()
	{
		$this->details_row = true;
	}
	
	/**
	 * Disable the DETAILS ROW functionality:.
	 */
	public function disableDetailsRow()
	{
		$this->details_row = false;
	}
	
	/**
	 * Set the number of rows that should be show on the table page (list view).
	 */
	public function setDefaultPageLength($value)
	{
		$this->default_page_length = $value;
	}
	
	/**
	 * Get the number of rows that should be show on the table page (list view).
	 */
	public function getDefaultPageLength()
	{
		// return the custom value for this crud panel, if set using setPageLength()
		if ($this->default_page_length) {
			return $this->default_page_length;
		}
		
		// otherwise return the default value in the config file
		if (config('larapen.admin.default_page_length')) {
			return config('larapen.admin.default_page_length');
		}
		
		return 25;
	}
	
	public function enableParentEntity()
	{
		$this->parent_entity = true;
	}
	
	public function disableParentEntity()
	{
		$this->parent_entity = false;
	}
	
	public function hasParentEntity()
	{
		return $this->parent_entity;
	}
	
	/*
	|--------------------------------------------------------------------------
	|                                EXPORT BUTTONS
	|--------------------------------------------------------------------------
	*/
	
	/**
	 * Tell the list view to show the DataTables export buttons.
	 */
	public function enableExportButtons()
	{
		$this->export_buttons = true;
	}
	
	/**
	 * Check if export buttons are enabled for the table view.
	 * @return bool
	 */
	public function exportButtons()
	{
		return $this->export_buttons;
	}
}

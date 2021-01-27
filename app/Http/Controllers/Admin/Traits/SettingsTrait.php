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

namespace App\Http\Controllers\Admin\Traits;


trait SettingsTrait
{
	/**
	 * @param $id
	 * @param null $childId
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function edit($id, $childId = null)
	{
		$this->xPanel->hasAccessOrFail('update');
		
		$entry = null;
		if (!empty($childId)) {
			$entry = $this->xPanel->getEntryWithParentAndChildKeys($id, $childId);
			$id = $childId;
		}
		
		$this->data['entry'] = (isset($entry) && !empty($entry)) ? $entry : $this->xPanel->getEntry($id);
		
		// Add the 'field' field
		$fieldColValue = json_decode($this->data['entry']->field, true);
		$this->addField($fieldColValue);
		
		// ...
		$this->data['xPanel'] = $this->xPanel;
		$this->data['saveAction'] = $this->getSaveAction();
		$this->data['fields'] = $this->xPanel->getUpdateFields($id);
		$this->data['title'] = trans('admin::messages.edit') . ' ' . $this->xPanel->entity_name;
		
		$this->data['id'] = $id;
		
		return view('admin::panel.edit', $this->data);
	}
	
	/**
	 * @param $request
	 * @return mixed
	 */
	public function updateTrait($request)
	{
		$this->data['entry'] = $this->xPanel->getEntry($request->input('id'));
		
		// Add the 'field' field
		$fieldColValue = json_decode($this->data['entry']->field, true);
		$this->addField($fieldColValue);
		
		return parent::updateCrud();
	}
	
	/**
	 * Add fake fields as array of the default json
	 *
	 * @param $fieldColValue
	 */
	public function addField($fieldColValue)
	{
		// Get the fake feature items
		$fakeFeatureItems = [
			'fake'     => true,
			'store_in' => "value",
		];
		
		// Is a multi-fields settings
		if (isset($fieldColValue['0']) && is_array($fieldColValue['0'])) {
			foreach ($fieldColValue as $key => $fieldColItem) {
				if (!is_array($fieldColItem)) continue;
				
				try {
					$fieldColItemFull = array_merge($fieldColItem, $fakeFeatureItems);
					$this->addField($fieldColItemFull);
				} catch (\Exception $e) {}
			}
		} else {
			// Is a one field settings (with a valid json data)
			if (isset($fieldColValue['name'])) {
				if (isset($fieldColValue['label']) && !isset($fieldColValue['disableTrans'])) {
					if (isset($fieldColValue['plugin'])) {
						$fieldColValue['label'] = trans($fieldColValue['plugin'] . '::messages.' . $fieldColValue['label']);
					} else {
						$fieldColValue['label'] = trans('admin::messages.' . $fieldColValue['label']);
					}
				}
				if (isset($fieldColValue['hint']) && !isset($fieldColValue['disableTrans'])) {
					$checkClearedHintContent = trim(strip_tags($fieldColValue['hint']));
					if (!empty($checkClearedHintContent)) {
						if (isset($fieldColValue['plugin'])) {
							$fieldColValue['hint'] = trans($fieldColValue['plugin'] . '::messages.' . $fieldColValue['hint']);
						} else {
							$fieldColValue['hint'] = trans('admin::messages.' . $fieldColValue['hint']);
						}
					}
					$fieldColValue['hint'] = str_replace('#admin#', admin_url(), $fieldColValue['hint']);
				}
				if (
					(isset($fieldColValue['type']) && $fieldColValue['type'] == 'custom_html') &&
					(!isset($fieldColValue['disableTrans']))
				) {
					$checkClearedValueContent = trim(strip_tags($fieldColValue['value']));
					if (!empty($checkClearedValueContent)) {
						$fieldColValue['value'] = trans('admin::messages.' . $fieldColValue['value']);
					}
					$fieldColValue['value'] = str_replace('#admin#', admin_url(), $fieldColValue['value']);
				}
				/*
				if (isset($fieldColValue['attributes'], $fieldColValue['attributes']['placeholder']) && !isset($fieldColValue['disableTrans'])) {
					if (isset($fieldColValue['plugin'])) {
						$fieldColValue['attributes']['placeholder'] = trans($fieldColValue['plugin'] . '::messages.' . $fieldColValue['attributes']['placeholder']);
					} else {
						$fieldColValue['attributes']['placeholder'] = trans('admin::messages.' . $fieldColValue['attributes']['placeholder']);
					}
				}
				*/
			} else {
				// Is a one field settings (without a valid json data)
				$fieldColValue = [
					'name'  => 'value',
					'label' => 'Value',
					'type'  => 'text',
				];
			}
			
			// Add the fake field to xPanel
			$this->xPanel->addField($fieldColValue);
		}
	}
}

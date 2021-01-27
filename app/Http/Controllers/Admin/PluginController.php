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

use App\Helpers\ArrayHelper;
use Larapen\Admin\app\Http\Controllers\Controller;
use Prologue\Alerts\Facades\Alert;

class PluginController extends Controller
{
	public $data = [];
	
	public function __construct()
	{
		parent::__construct();
		
		$this->middleware('demo.restriction')->except(['index']);
		
		$this->data['plugins'] = [];
	}
	
	public function index()
	{
		// Load all Plugins Services Provider
		$plugins = plugin_list();
		
		// Append the Plugin Options
		$plugins = collect($plugins)->map(function ($item, $key) {
			if (is_object($item)) {
				$item = ArrayHelper::fromObject($item);
			}
			
			if (isset($item['item_id']) && !empty($item['item_id'])) {
				$item['installed'] = plugin_check_purchase_code($item);
			}
			
			// Append the Options
			$pluginClass = plugin_namespace($item['name'], ucfirst($item['name']));
			$item['options'] = (method_exists($pluginClass, 'getOptions')) ? (array)call_user_func($pluginClass . '::getOptions') : null;
			$item = ArrayHelper::toObject($item);
			
			return $item;
		})->toArray();
		
		$this->data['plugins'] = $plugins;
		$this->data['title'] = 'Plugins';
		
		return view('admin::plugin', $this->data);
	}
	
	public function install($name)
	{
		// Get plugin details
		$plugin = load_plugin($name);
		
		// Install the plugin
		if (!empty($plugin)) {
			if (request()->has('purchase_code')) {
				$rules = $messages = [];
				$purchaseCodeData = plugin_purchase_code_data($plugin, request()->input('purchase_code'));
				if ($purchaseCodeData->valid == false) {
					$rules['purchase_code_valid'] = 'required';
					if ($purchaseCodeData->message != '') {
						$messages = [
							'purchase_code_valid.required' => trans('admin::messages.plugin_purchase_code_invalid_message', [
									'plugin_name' => $plugin->display_name
								]) . ' ERROR: <strong>' . $purchaseCodeData->message . '</strong>',
						];
					}
				}
				
				if (!empty($messages)) {
					$this->validate(request(), $rules, $messages);
				} else {
					$this->validate(request(), $rules);
				}
				
				if ($purchaseCodeData->valid) {
					$res = call_user_func($plugin->class . '::installed');
					if (!$res) {
						$res = call_user_func($plugin->class . '::install');
					}
					
					// Result Notification
					if ($res) {
						Alert::success(trans('admin::messages.The plugin :plugin_name has been successfully installed', ['plugin_name' => $plugin->display_name]))->flash();
					} else {
						Alert::error(trans('admin::messages.Failed to install the plugin ":plugin_name"', ['plugin_name' => $plugin->display_name]))->flash();
					}
				} else {
					if (isset($messages['purchase_code_valid.required'])) {
						$message = strip_tags($messages['purchase_code_valid.required']);
						Alert::error($message)->flash();
					}
				}
			} else {
				$res = call_user_func($plugin->class . '::install');
				
				// Result Notification
				if ($res) {
					Alert::success(trans('admin::messages.The plugin :plugin_name has been successfully installed', ['plugin_name' => $plugin->display_name]))->flash();
				} else {
					Alert::error(trans('admin::messages.Failed to install the plugin ":plugin_name"', ['plugin_name' => $plugin->display_name]))->flash();
				}
			}
		}
		
		return redirect(admin_uri('plugins'));
	}
	
	public function uninstall($name)
	{
		// Get plugin details
		$plugin = load_plugin($name);
		
		// Uninstall the plugin
		if (!empty($plugin)) {
			$res = call_user_func($plugin->class . '::uninstall');
			
			// Result Notification
			if ($res) {
				plugin_clear_uninstall($name);
				
				Alert::success(trans('admin::messages.The plugin :plugin_name has been uninstalled', ['plugin_name' => $plugin->display_name]))->flash();
			} else {
				Alert::error(trans('admin::messages.Failed to Uninstall the plugin ":plugin_name"', ['plugin_name' => $plugin->display_name]))->flash();
			}
		}
		
		return redirect(admin_uri('plugins'));
	}
	
	public function delete($plugin)
	{
		// ...
		// Alert::success(trans('admin::messages.The plugin has been removed'))->flash();
		// Alert::error(trans('admin::messages.Failed to remove the plugin ":plugin_name"', ['plugin_name' => $plugin]))->flash();
		
		return redirect(admin_uri('plugins'));
	}
}

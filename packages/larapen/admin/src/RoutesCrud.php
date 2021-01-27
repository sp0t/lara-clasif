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

namespace Larapen\Admin;

use Illuminate\Support\Facades\Route;

class RoutesCrud
{
	/**
	 * @param $name
	 * @param $controller
	 * @param array $options
	 */
	public static function resource($name, $controller, array $options = [])
	{
		// CRUD Routes
		Route::post($name . '/search', $controller . '@search')->name('crud.' . $name . '.search');
		Route::get($name . '/reorder', $controller . '@reorder')->name('crud.' . $name . '.reorder');
		Route::get($name . '/reorder/{lang}', $controller . '@reorder')->name('crud.' . $name . '.reorder');
		Route::post($name . '/reorder', $controller . '@saveReorder')->name('crud.' . $name . '.save.reorder');
		Route::post($name . '/reorder/{lang}', $controller . '@saveReorder')->name('crud.' . $name . '.save.reorder');
		Route::get($name . '/{id}/details', $controller . '@showDetailsRow')->name('crud.' . $name . '.showDetailsRow');
		Route::get($name . '/{id}/translate/{lang}', $controller . '@translateItem')->name('crud.' . $name . '.translateItem');
		Route::post($name . '/bulk_delete', $controller . '@bulkDelete')->name('crud.' . $name . '.bulkDelete');
		
		$optionsWithDefaultRouteNames = array_merge([
			'names' => [
				'index'   => 'crud.' . $name . '.index',
				'create'  => 'crud.' . $name . '.create',
				'store'   => 'crud.' . $name . '.store',
				'edit'    => 'crud.' . $name . '.edit',
				'update'  => 'crud.' . $name . '.update',
				'show'    => 'crud.' . $name . '.show',
				'destroy' => 'crud.' . $name . '.destroy',
			],
		], $options);
		
		Route::resource($name, $controller, $optionsWithDefaultRouteNames);
	}
}
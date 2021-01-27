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

use App\Helpers\DBTool;
use App\Models\Payment;
use App\Models\Permission;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Larapen\Admin\app\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\City;
use App\Models\SubAdmin1;
use App\Models\SubAdmin2;
use Illuminate\Http\Request;
use Prologue\Alerts\Facades\Alert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InlineRequestController extends Controller
{
	public static $msg = 'demo_mode_message';
	
	/**
	 * @param $table
	 * @param $field
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function make($table, $field, Request $request)
	{
		$primaryKey = $request->input('primaryKey');
		$status = 0;
		$result = [
			'table'      => $table,
			'field'      => $field,
			'primaryKey' => $primaryKey,
			'status'     => $status,
		];
		
		// Check parameters
		if (!auth()->check() || !auth()->user()->can(Permission::getStaffPermissions())) {
			return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
		}
		if (!Schema::hasTable($table)) {
			return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
		}
		if (!Schema::hasColumn($table, $field)) {
			return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
		}
		$sql = 'SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = "' . DB::getTablePrefix() . $table . '" AND COLUMN_NAME = "' . $field . '"';
		$info = DB::select(DB::raw($sql));
		if (empty($info)) {
			return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
		} else {
			if (isset($info[0]) && isset($info[0]->DATA_TYPE)) {
				if ($info[0]->DATA_TYPE != 'tinyint' && $table != 'settings') {
					return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
				}
				if ($info[0]->DATA_TYPE != 'text' && $table == 'settings' && $field == 'value') {
					return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
				}
			} else {
				return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
			}
		}
		
		// Check Demo Website
		if (isDemo()) {
			Alert::info(t(self::$msg))->flash();
			return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
		}
		
		// Get model namespace
		$namespace = '\\App\Models\\';
		
		// Get model name
		$model = null;
		$modelsPath = app_path('Models');
		$modelFiles = array_filter(\File::glob($modelsPath . DIRECTORY_SEPARATOR . '*.php'), 'is_file');
		if (count($modelFiles) > 0) {
			foreach ($modelFiles as $filePath) {
				$filename = last(explode(DIRECTORY_SEPARATOR, $filePath));
				$modelName = head(explode('.', $filename));
				
				if (
					!Str::contains(strtolower($filename), '.php')
					|| Str::contains(strtolower($modelName), 'base')
				) {
					continue;
				}
				
				eval('$modelChecker = new ' . $namespace . $modelName . '();');
				if (\Schema::hasTable($modelChecker->getTable())) {
					if ($modelChecker->getTable() == $table) {
						$model = $modelName;
						break;
					}
				}
			}
		}
		
		// Get table data
		$item = null;
		if (!empty($model)) {
			$model = $namespace . $model;
			$item = $model::find($primaryKey);
		}
		
		// Check item
		if (empty($item)) {
			return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
		}
		
		// UPDATE - the tinyint field
		
		if ($table == 'countries' && $field == 'active') {
			
			// COUNTRY activation (Geonames: Country, Admin Divisions & Cities data installation)
			if (strtolower(config('settings.geo_location.default_country_code')) != strtolower($item->code)) {
				$resImport = false;
				if ($item->{$field} == 0) {
					$resImport = $this->importGeonamesSql($item->code);
				} else {
					$resImport = $this->removeGeonamesDataByCountryCode($item->code);
				}
				
				// Save data
				if ($resImport) {
					$item->{$field} = ($item->{$field} != 1) ? 1 : 0;
					$item->save();
				}
				
				$isDefaultCountry = 0;
			} else {
				$isDefaultCountry = 1;
				$resImport = true;
			}
			
		} else if ($table == 'payments' && $field == 'active') {
			
			// PAYMENT activation
			// Save data
			$item->{$field} = ($item->{$field} != 1) ? 1 : 0;
			$item->save();
			
			// Update the 'reviewed' & 'featured' fields of the related Post (Used by the Offline Payment plugin)
			if ($item->{$field} == 1) {
				$post = Post::find($item->post_id);
				if (!empty($post)) {
					$post->reviewed = 1;
					$post->featured = 1;
					$post->save();
				}
			} else {
				$postActivePayments = Payment::where('post_id', $item->post_id)->where('id', '!=', $item->id)->where('active', 1);
				if ($postActivePayments->count() <= 0) {
					$post = Post::find($item->post_id);
					if (!empty($post)) {
						$post->reviewed = 0;
						$post->featured = 0;
						$post->save();
					}
				}
			}
			
		} else {
			
			// Save data
			$item->{$field} = ($item->{$field} != 1) ? 1 : 0;
			$item->save();
			
		}
		
		
		// JS data
		$result = [
			'table'      => $table,
			'field'      => $field,
			'primaryKey' => $primaryKey,
			'status'     => 1,
			'fieldValue' => $item->{$field},
		];
		
		if (isset($isDefaultCountry)) {
			$result['isDefaultCountry'] = $isDefaultCountry;
		}
		if (isset($resImport)) {
			$result['resImport'] = $resImport;
		}
		
		
		return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
	}
	
	
	/**
	 * Import the Geonames data for the country
	 *
	 * @param $countryCode
	 * @return bool
	 */
	private function importGeonamesSql($countryCode)
	{
		// Check Demo Website
		if (isDemo()) {
			Alert::info(t(self::$msg))->flash();
			return false;
		}
		
		// Remove all country data
		$this->removeGeonamesDataByCountryCode($countryCode);
		
		// Default Country SQL File
		$filePath = storage_path('database/geonames/countries/' . strtolower($countryCode) . '.sql');
		
		// Check if file exists
		if (!File::exists($filePath)) {
			return false;
		}
		
		// Import the SQL file
		$res = DBTool::importSqlFile(DB::connection()->getPdo(), $filePath, DB::getTablePrefix());
		
		return $res;
	}
	
	/**
	 * Remove all the country's data
	 *
	 * @param $countryCode
	 * @return bool
	 */
	private function removeGeonamesDataByCountryCode($countryCode)
	{
		// Check Demo Website
		if (isDemo()) {
			Alert::info(t(self::$msg))->flash();
			return false;
		}
		
		$deletedRows = SubAdmin1::countryOf($countryCode)->delete();
		$deletedRows = SubAdmin2::countryOf($countryCode)->delete();
		$deletedRows = City::countryOf($countryCode)->delete();
		
		// Delete all Posts entries
		$posts = Post::countryOf($countryCode)->get();
		if ($posts->count() > 0) {
			foreach ($posts as $post) {
				$post->delete();
			}
		}
		
		return true;
	}
}

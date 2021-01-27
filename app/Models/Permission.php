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

namespace App\Models;

use App\Helpers\DBTool;
use App\Observer\PermissionObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Larapen\Admin\app\Models\Crud;
use Spatie\Permission\Models\Permission as OriginalPermission;

class Permission extends OriginalPermission
{
	use Crud;
	
	protected $fillable = ['name', 'guard_name', 'updated_at', 'created_at'];
	
	/*
	|--------------------------------------------------------------------------
	| FUNCTIONS
	|--------------------------------------------------------------------------
	*/
	protected static function boot()
	{
		parent::boot();
		
		Permission::observe(PermissionObserver::class);
	}
	
	/**
	 * Default Super Admin users permissions
	 *
	 * @return array
	 */
	public static function getSuperAdminPermissions()
	{
		$permissions = [
			'list-permission',
			'create-permission',
			'update-permission',
			'delete-permission',
			'list-role',
			'create-role',
			'update-role',
			'delete-role',
		];
		
		return $permissions;
	}
	
	/**
	 * Default Staff users permissions
	 *
	 * @return array
	 */
	public static function getStaffPermissions()
	{
		$permissions = [
			'access-dashboard',
		];
		
		return $permissions;
	}
	
	/**
	 * Check default permissions
	 *
	 * @return bool
	 */
	public static function checkDefaultPermissions()
	{
		if (!Role::checkSuperAdminRole() || !Permission::checkSuperAdminPermissions()) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Check Super Admin permissions
	 * NOTE: Must use try {...} catch {...}
	 *
	 * @return bool
	 */
	public static function checkSuperAdminPermissions()
	{
		try {
			$superAdminPermissions = array_merge((array)Permission::getSuperAdminPermissions(), (array)Permission::getStaffPermissions());
			if (!empty($superAdminPermissions)) {
				foreach ($superAdminPermissions as $superAdminPermission) {
					$permission = self::where('name', $superAdminPermission)->first();
					if (empty($permission)) {
						return false;
					}
				}
			} else {
				return false;
			}
		} catch (\Exception $e) {}
		
		return true;
	}
	
	/**
	 * Reset default permissions
	 * NOTE: Must use try {...} catch {...}
	 */
	public static function resetDefaultPermissions()
	{
		try {
			// Create the default Super Admin role
			$role = Role::resetDefaultRole();
			if (empty($role)) {
				return;
			}
			
			// Remove all current permissions & their relationship
			$permissions = Permission::all();
			$permissions->each(function ($item, $key) {
				if ($item->roles()->count() > 0) {
					$item->roles()->detach();
				}
				$item->delete();
			});
			
			// Reset permissions table ID auto-increment
			DB::statement('ALTER TABLE ' . DBTool::table(config('permission.table_names.permissions')) . ' AUTO_INCREMENT = 1;');
			
			// Create default Super Admin permissions
			$superAdminPermissions = array_merge((array)Permission::getSuperAdminPermissions(), (array)Permission::getStaffPermissions());
			if (!empty($superAdminPermissions)) {
				foreach ($superAdminPermissions as $superAdminPermission) {
					$permission = self::where('name', $superAdminPermission)->first();
					if (empty($permission)) {
						$permission = self::create(['name' => $superAdminPermission]);
					}
					$role->givePermissionTo($permission);
				}
			}
			
			// Assign the Super Admin role to the old admin users
			$admins = User::where('is_admin', 1)->get();
			if ($admins->count() > 0) {
				foreach ($admins as $admin) {
					if (isset($role->name)) {
						$admin->removeRole($role->name);
						$admin->assignRole($role->name);
					}
				}
			}
		} catch (\Exception $e) {}
	}
	
	public function createDefaultEntriesBtn($xPanel = false)
	{
		if (!config('larapen.admin.allow_permission_create')) {
			return null;
		}
		
		$url = admin_url('permissions/create_default_entries');
		
		$out = '';
		$out .= '<a class="btn btn-success" href="' . $url . '">';
		$out .= '<i class="fa fa-industry"></i> ';
		$out .= trans('admin::messages.Reset the Permissions');
		$out .= '</a>';
		
		return $out;
	}
	
	/**
	 * Get all Admin Controllers public methods
	 *
	 * @return array
	 */
	public static function defaultPermissions()
	{
		$permissions = self::getRoutesPermissions();
		$permissions = collect($permissions)->mapWithKeys(function ($item) {
			return [$item['permission'] => $item['permission']];
		})->sort()->toArray();
		
		// dd($permissions);
		
		return $permissions;
	}
	
	/**
	 * @return array
	 */
	public static function getRoutesPermissions()
	{
		$routeCollection = Route::getRoutes();
		
		$defaultAccess = ['list', 'create', 'update', 'delete', 'reorder', 'details_row'];
		$defaultAllowAccess = ['list', 'create', 'update', 'delete'];
		$defaultDenyAccess = ['reorder', 'details_row'];
		
		// Controller's Action => Access
		$accessOfActionMethod = [
			'index'                    => 'list',
			'show'                     => 'list',
			'create'                   => 'create',
			'store'                    => 'create',
			'translateItem'            => 'create',
			'edit'                     => 'update',
			'update'                   => 'update',
			'reorder'                  => 'update',
			'saveReorder'              => 'update',
			'destroy'                  => 'delete',
			'bulkDelete'               => 'delete',
			'saveAjaxRequest'          => 'update',
			'dashboard'                => 'access', // Dashboard
			'redirect'                 => 'access', // Dashboard
			'syncFilesLines'           => 'update', // Languages
			'createDefaultPermissions' => 'create', // Permissions
			'reset'                    => 'delete', // Homepage Sections
			'download'                 => 'download', // Backup
			'make'                     => 'make', // Inline Requests
			'install'                  => 'install', // Plugins
			'uninstall'                => 'uninstall', // Plugins
			'reSendVerificationEmail'  => 'resend-verification-notification',
			'reSendVerificationSms'    => 'resend-verification-notification',
			
			'createBulkCountriesSubDomain' => 'create', // Domain Mapping
			'generate'                     => 'create',
		];
		$tab = $data = [];
		foreach ($routeCollection as $key => $value) {
			
			// Init.
			$data['filePath'] = null;
			$data['actionMethod'] = null;
			$data['methods'] = [];
			$data['permission'] = null;
			
			// Get & Clear the route prefix
			$routePrefix = $value->getPrefix();
			$routePrefix = trim($routePrefix, '/');
			if ($routePrefix != 'admin') {
				$routePrefix = head(explode('/', $routePrefix));
			}
			
			if ($routePrefix == 'admin') {
				$data['methods'] = $value->methods();
				
				$data['uri'] = $value->uri();
				$data['uri'] = preg_replace('#\{[^\}]+\}#', '*', $data['uri']);
				
				$controllerActionPath = $value->getActionName();
				
				try {
					$controllerNamespace = '\\' . preg_replace('#@.+#i', '', $controllerActionPath);
					$reflector = new \ReflectionClass($controllerNamespace);
					$data['filePath'] = $filePath = $reflector->getFileName();
				} catch (\Exception $e) {
					$data['filePath'] = $filePath = null;
				}
				
				$data['actionMethod'] = $actionMethod = $value->getActionMethod();
				$access = (isset($accessOfActionMethod[$actionMethod])) ? $accessOfActionMethod[$actionMethod] : null;
				
				if (!empty($filePath) && file_exists($filePath)) {
					$content = file_get_contents($filePath);
					
					if (Str::contains($content, 'extends PanelController')) {
						$allowAccess = [];
						$denyAccess = [];
						
						if (Str::contains($controllerActionPath, '\PermissionController')) {
							if (!config('larapen.admin.allow_permission_create')) {
								$denyAccess[] = 'create';
							}
							if (!config('larapen.admin.allow_permission_update')) {
								$denyAccess[] = 'update';
							}
							if (!config('larapen.admin.allow_permission_delete')) {
								$denyAccess[] = 'delete';
							}
						} else if (Str::contains($controllerActionPath, '\RoleController')) {
							if (!config('larapen.admin.allow_role_create')) {
								$denyAccess[] = 'create';
							}
							if (!config('larapen.admin.allow_role_update')) {
								$denyAccess[] = 'update';
							}
							if (!config('larapen.admin.allow_role_delete')) {
								$denyAccess[] = 'delete';
							}
						} else {
							// Get allowed accesses
							$tmp = '';
							preg_match('#->allowAccess\(([^\)]+)\);#', $content, $tmp);
							$allowAccessStr = (isset($tmp[1]) && !empty($tmp)) ? $tmp[1] : '';
							
							if (!empty($allowAccessStr)) {
								$tmp = '';
								preg_match_all("#'([^']+)'#", $allowAccessStr, $tmp);
								$allowAccess = (isset($tmp[1]) && !empty($tmp)) ? $tmp[1] : [];
								
								if (empty($denyAccess)) {
									$tmp = '';
									preg_match_all('#"([^"]+)"#', $allowAccessStr, $tmp);
									$allowAccess = (isset($tmp[1]) && !empty($tmp)) ? $tmp[1] : [];
								}
							}
							
							// Get denied accesses
							$tmp = '';
							preg_match('#->denyAccess\(([^\)]+)\);#', $content, $tmp);
							$denyAccessStr = (isset($tmp[1]) && !empty($tmp)) ? $tmp[1] : '';
							
							if (!empty($denyAccessStr)) {
								$tmp = '';
								preg_match_all("#'([^']+)'#", $denyAccessStr, $tmp);
								$denyAccess = (isset($tmp[1]) && !empty($tmp)) ? $tmp[1] : [];
								
								if (empty($denyAccess)) {
									$tmp = '';
									preg_match_all('#"([^"]+)"#', $denyAccessStr, $tmp);
									$denyAccess = (isset($tmp[1]) && !empty($tmp)) ? $tmp[1] : [];
								}
							}
						}
						
						$allowAccess = array_merge((array) $defaultAllowAccess, (array) $allowAccess);
						$denyAccess = array_merge((array) $defaultDenyAccess, (array) $denyAccess);
						
						$availableAccess = array_merge(array_diff($allowAccess, $defaultAccess), $defaultAccess);
						$availableAccess = array_diff($availableAccess, $denyAccess);
						
						if (in_array($access, $defaultAccess)) {
							if (!in_array($access, $availableAccess)) {
								continue;
							}
						}
					}
				}
				
				if (Str::contains($controllerActionPath, '\ActionController')) {
					$data['permission'] = strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $actionMethod));
				} else {
					$tmp = '';
					preg_match('#\\\([a-zA-Z0-9]+)Controller@#', $controllerActionPath, $tmp);
					$controllerSlug = (isset($tmp[1]) && !empty($tmp)) ? $tmp[1] : '';
					$controllerSlug = strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $controllerSlug));
					$data['permission'] = (!empty($access)) ? $access . '-' . $controllerSlug : null;
				}
				
				if (empty($data['permission'])) {
					continue;
				}
				
				if ($data['filePath']) {
					unset($data['filePath']);
				}
				if ($data['actionMethod']) {
					unset($data['actionMethod']);
				}
				
				// Save It!
				$tab[$key] = $data;
			}
			
		}
		
		return $tab;
	}
	
	/*
	|--------------------------------------------------------------------------
	| RELATIONS
	|--------------------------------------------------------------------------
	*/
	
	/*
	|--------------------------------------------------------------------------
	| SCOPES
	|--------------------------------------------------------------------------
	*/
	
	/*
	|--------------------------------------------------------------------------
	| ACCESSORS
	|--------------------------------------------------------------------------
	*/
	
	/*
	|--------------------------------------------------------------------------
	| MUTATORS
	|--------------------------------------------------------------------------
	*/
}

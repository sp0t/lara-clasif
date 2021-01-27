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

use App\Http\Requests\Admin\RoleRequest as StoreRequest;
use App\Http\Requests\Admin\RoleRequest as UpdateRequest;
use App\Models\Permission;
use App\Models\Role;
use Larapen\Admin\app\Http\Controllers\PanelController;

class RoleController extends PanelController
{
	/**
	 * PermissionController constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->middleware('demo.restriction')->only(['store', 'update', 'destroy']);
	}
	
	public function setup()
	{
		$role_model = config('permission.models.role');
		$permission_model = config('permission.models.permission');
		
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel($role_model);
		$this->xPanel->setRoute(admin_uri('roles'));
		$this->xPanel->setEntityNameStrings(trans('admin::messages.role'), trans('admin::messages.roles'));
		
		$this->xPanel->removeButton('delete');
		$this->xPanel->addButtonFromModelFunction('line', 'delete', 'deleteBtn', 'end');
		
		
		/*
		|--------------------------------------------------------------------------
		| COLUMNS AND FIELDS
		|--------------------------------------------------------------------------
		*/
		// COLUMNS
		$this->xPanel->addColumn([
			'name'  => 'name',
			'label' => trans('admin::messages.name'),
			'type'  => 'text',
		]);
		
		// FIELDS
		$this->xPanel->addField([
			'name'  => 'name',
			'label' => trans('admin::messages.name'),
			'type'  => 'text',
		], 'create');
		
		$entity = $this->xPanel->getModel()->find(request()->segment(3));
		if (!empty($entity)) {
			$this->xPanel->addField([
				'name'  => 'name',
				'type'  => 'custom_html',
				'value' => '<h3><strong>' . trans('admin::messages.name') . ':</strong> ' . $entity->name . '</h3>',
			], 'update');
		}
		
		$this->xPanel->addField([
			'label'     => mb_ucfirst(trans('admin::messages.permission_plural')),
			'type'      => 'checklist',
			'name'      => 'permissions',
			'entity'    => 'permissions',
			'attribute' => 'name',
			'model'     => $permission_model,
			'pivot'     => true,
		]);
		
		if (!config('larapen.admin.allow_role_create')) {
			$this->xPanel->denyAccess('create');
		}
		if (!config('larapen.admin.allow_role_update')) {
			$this->xPanel->denyAccess('update');
		}
		if (!config('larapen.admin.allow_role_delete')) {
			$this->xPanel->denyAccess('delete');
		}
	}
	
	public function store(StoreRequest $request)
	{
		$this->setRoleDefaultPermissions();
		
		// Otherwise, changes won't have effect
		\Cache::forget('spatie.permission.cache');
		
		return parent::storeCrud();
	}
	
	public function update(UpdateRequest $request)
	{
		$this->setRoleDefaultPermissions();
		
		// Otherwise, changes won't have effect
		\Cache::forget('spatie.permission.cache');
		
		return parent::updateCrud();
	}
	
	/**
	 * Set role's default (or required) permissions
	 */
	public function setRoleDefaultPermissions()
	{
		// Get request permissions
		$permissionIds = request()->input('permissions');
		$permissionIds = collect($permissionIds)->map(function ($item, $key) {
			return (int)$item;
		})->toArray();
		
		// Set staff default permissions
		// Give the minimum admin panel permissions to the role.
		$staffPermissionsArr = Permission::getStaffPermissions();
		$staffPermissions = Permission::whereIn('name', $staffPermissionsArr);
		if ($staffPermissions->count() > 0) {
			$staffPermissionsIds = collect($staffPermissions->get())->keyBy('id')->keys()->toArray();
			$permissionIds = array_merge($permissionIds, $staffPermissionsIds);
		}
		
		// Set the Super Admin default permissions (If needed)
		$role = Role::find(request()->segment(3));
		if (!empty($role)) {
			// Get the Super Admin role
			$superAdminRole = Role::getSuperAdminRole();
			
			// If the role is the Super Admin role,
			// Then give the minimum permissions to the 'super-admin' role.
			if (strtolower($role->name) == strtolower($superAdminRole)) {
				$superAdminPermissionsArr = Permission::getSuperAdminPermissions();
				$superAdminPermissions = Permission::whereIn('name', $superAdminPermissionsArr);
				if ($superAdminPermissions->count() > 0) {
					$superAdminPermissionsIds = collect($superAdminPermissions->get())->keyBy('id')->keys()->toArray();
					$permissionIds = array_merge($permissionIds, $superAdminPermissionsIds);
				}
			}
		}
		
		// Update the request value
		request()->request->set('permissions', $permissionIds);
	}
}

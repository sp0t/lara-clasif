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

namespace App\Observer;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Cache;
use Prologue\Alerts\Facades\Alert;

class RoleObserver
{
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param Role $role
	 * @return bool
	 */
	public function deleting(Role $role)
	{
		// Check if default permission exist, to prevent recursion of the deletion.
		if (Permission::checkDefaultPermissions()) {
			$superAdminRole = Role::getSuperAdminRole();
			if (strtolower($role->name) == strtolower($superAdminRole)) {
				Alert::warning(trans('admin::messages.You cannot delete the Super Admin role.'))->flash();
				
				// Since Laravel detach all pivot entries before starting deletion,
				// Re-give the Super Admin permissions to the role.
				$role->syncPermissions(Permission::getSuperAdminPermissions());
				
				return false;
			}
		}
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param Role $role
	 * @return void
	 */
	public function saved(Role $role)
	{
		// Removing Entries from the Cache
		$this->clearCache($role);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param Role $role
	 * @return void
	 */
	public function deleted(Role $role)
	{
		// Removing Entries from the Cache
		$this->clearCache($role);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $role
	 */
	private function clearCache($role)
	{
		Cache::flush();
	}
}

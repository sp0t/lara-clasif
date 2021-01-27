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

namespace App\Http\Middleware;

use App\Models\Permission;
use App\Models\Scopes\VerifiedScope;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Prologue\Alerts\Facades\Alert;

class Clearance
{
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		try {
			if (!Schema::hasTable('permissions')) {
				return $next($request);
			}
		} catch (\Exception $e) {
			return $next($request);
		}
		
		// If user has this //permission
		if (userHasSuperAdminPermissions()) {
			return $next($request);
		}
		
		// Get all routes that have permissions
		$routesPermissions = Permission::getRoutesPermissions();
		if (!empty($routesPermissions)) {
			foreach ($routesPermissions as $key => $route) {
				if (!isset($route['uri']) || !isset($route['permission']) || !isset($route['methods'])) {
					continue;
				}
				
				// If the current route found, ...
				if ($request->is($route['uri']) && in_array($request->method(), $route['methods'])) {
					
					// Check if user has permission to perform this action
					if (!auth()->user()->can($route['permission'])) {
						return $this->unauthorized($request);
					}
					
				}
			}
		}
		
		// If the logged admin user has permissions to manage users and is has not 'super-admin' role,
		// don't allow him to manage 'super-admin' role's users.
		if (isFromAdminPanel() && auth()->check()) {
			$userController = 'Controllers\Admin\UserController';
			if (
				Str::contains(Route::currentRouteAction(), $userController . '@edit')
				|| Str::contains(Route::currentRouteAction(), $userController . '@update')
				|| Str::contains(Route::currentRouteAction(), $userController . '@show')
				|| Str::contains(Route::currentRouteAction(), $userController . '@destroy')
			) {
				// Get the current possible 'super-admin' User ID
				$userId = request()->segment(3);
				if (!empty($userId) && is_numeric($userId)) {
					// If the logged admin user has not 'super-admin' role...
					if (!auth()->user()->can(Permission::getSuperAdminPermissions())) {
						try {
							$user = User::withoutGlobalScopes([VerifiedScope::class])
								->where('id', $userId)
								->role('super-admin')
								->first(['id', 'created_at']);
							
							// If the current User ID is for a 'super-admin' role's user,
							// don't allow the logged user (admin) to manage him (since he doesn't have 'super-admin' role).
							if (!empty($user)) {
								return $this->unauthorized($request);
							}
						} catch (\Exception $e) {
						}
					}
				}
			}
		}
		
		return $next($request);
	}
	
	/**
	 * Unauthorized access
	 *
	 * @param $request
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
	 */
	private function unauthorized($request)
	{
		// abort('401');
		if ($request->ajax() || $request->wantsJson()) {
			return response(trans('admin::messages.unauthorized'), 401);
		} else {
			Alert::error(trans('admin::messages.unauthorized'))->flash();
			
			return redirect()->back();
		}
	}
}

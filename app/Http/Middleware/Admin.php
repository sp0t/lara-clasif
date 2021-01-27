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
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Schema;
use Prologue\Alerts\Facades\Alert;

class Admin
{
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @param null $guard
	 * @return mixed
	 */
	public function handle($request, Closure $next, $guard = null)
	{
		if (!auth()->check()) {
			// Block access if user is guest (not logged in)
			if ($request->ajax() || $request->wantsJson()) {
				return response(trans('admin::messages.unauthorized'), 401);
			} else {
				if ($request->path() != admin_uri('login')) {
					Alert::error(trans('admin::messages.unauthorized'))->flash();
					return redirect()->guest(admin_uri('login'));
				}
			}
		} else {
			try {
				if (!Schema::hasTable('permissions')) {
					return $next($request);
				}
			} catch (\Exception $e) {
				return $next($request);
			}
			
			$user = User::all()->count();
			if (!($user == 1)) {
				// If user does //not have this permission
				if (!auth()->guard($guard)->user()->can(Permission::getStaffPermissions())) {
					if ($request->ajax() || $request->wantsJson()) {
						return response(trans('admin::messages.unauthorized'), 401);
					} else {
						auth()->logout();
						Alert::error(trans('admin::messages.unauthorized'))->flash();
						
						return redirect()->guest(admin_uri('login'));
					}
				}
			}
		}
		
		return $next($request);
	}
}

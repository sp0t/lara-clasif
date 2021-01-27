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

use Closure;

class HttpsProtocol
{
	/**
	 * Redirects any non-secure requests to their secure counterparts.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param Closure $next
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function handle($request, Closure $next)
	{
		if (config('larapen.core.forceHttps') == true) {
			// $request->setTrustedProxies([$request->getClientIp()], $request::HEADER_X_FORWARDED_ALL);
			if (!$request->secure()) {
				/* $request->server('HTTP_X_FORWARDED_PROTO') != 'https' */
				// Production is not currently secure
				// return redirect()->secure($request->getRequestUri());
			}
		}
		
		return $next($request);
	}
}

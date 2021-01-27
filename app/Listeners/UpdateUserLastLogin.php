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

namespace App\Listeners;

use App\Events\UserWasLogged;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Request as Request;
use Jenssegers\Date\Date;

class UpdateUserLastLogin
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    
    /**
     * Handle the event.
     *
     * @param  UserWasLogged $event
     * @return void
     */
    public function handle(UserWasLogged $event)
    {
        // $event->user->last_login_at = (config('timezone.id')) ? Date::now(config('timezone.id')) : Date::now();
		$event->user->last_login_at = Date::now();
        $event->user->save(['canBeSaved' => true]);
    }
}

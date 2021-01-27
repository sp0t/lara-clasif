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

namespace App\Http\Controllers\Install\Traits\Db;

use Illuminate\Support\Facades\Artisan;

/*
 * NOTE: THIS IS UNUSED WAITING ONE LARAVEL'S MIGRATION SYSTEM UPDATE
 * For now it's not possible to rollback a specific Laravel migration (very important for the plugins migrations).
 */
trait MigrationsTrait
{
	/**
	 * Import from Laravel Migrations
	 * php artisan migrate --force
	 */
	protected function importFromMigrations()
	{
		Artisan::call('migrate', ['--force' => true]);
	}
	
	/**
	 * Import from Laravel Seeders
	 * php artisan db:seed --force
	 */
	protected function importFromSeeders()
	{
		Artisan::call('db:seed', ['--force' => true]);
	}
}

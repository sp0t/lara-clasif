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

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class Backup extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'makeBackup:exec {type?}';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Make backup of the website';
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		// Get type of backup
		$type = $this->argument('type');
		if (!empty($type) && !in_array($type, ['all', 'database', 'files', 'languages'])) {
			dd('The type: ' . $type . ' is not supported.');
		}
		
		// Set the backup type configurations
		setBackupConfig($type);
		
		// Backup's package arguments
		$flags = config('backup.backup.admin_flags', false);
		if ($type == 'database') {
			$flags = [
				'--disable-notifications' => true,
				'--only-db'               => true,
			];
		}
		if ($type == 'files') {
			$flags = [
				'--disable-notifications' => true,
				'--only-files'            => true,
			];
		}
		if ($type == 'languages') {
			$flags = [
				'--disable-notifications' => true,
				'--only-files'            => true,
			];
		}
		
		// Start the backup process
		try {
			if ($flags && is_array($flags)) {
				Artisan::call('backup:run', $flags);
			} else {
				Artisan::call('backup:run');
			}
		} catch (\Exception $e) {
			dd($e->getMessage());
		}
	}
}

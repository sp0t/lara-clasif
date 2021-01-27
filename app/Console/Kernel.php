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

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		\App\Console\Commands\Inspire::class,
		\App\Console\Commands\AdsClear::class,
		\App\Console\Commands\Backup::class,
		\App\Console\Commands\BackupClear::class,
	];
	
	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		// CLEAR ADS
		$schedule->command('ads:clear')->hourly();
		
		// BACKUPS
		// Scheduling Task Every N Hours: $schedule->command('signature')->cron('0 */5 * * *'); // every 5 hours
		// Scheduling Task Every N Days: $schedule->command('signature')->cron('0 0 */3 * *'); // every 3 days
		if (config('settings.backup.backup_all')) {
			$days = (int)config('settings.backup.backup_frequency_all');
			if ($days > 0) {
				// $expression = '*/' . $days . ' * * * *'; // every N hours (Only for tests purpose!)
				$expression = '0 0 */' . $days . ' * *'; // every N days
				$schedule->command('makeBackup:exec')->cron($expression);
			}
		}
		if (config('settings.backup.backup_database')) {
			$days = (int)config('settings.backup.backup_frequency_database');
			if ($days > 0) {
				// $expression = '*/' . $days . ' * * * *'; // every N hours (Only for tests purpose!)
				$expression = '0 0 */' . $days . ' * *'; // every N days
				$schedule->command('makeBackup:exec database')->cron($expression);
			}
		}
		if (config('settings.backup.backup_files')) {
			$days = (int)config('settings.backup.backup_frequency_files');
			if ($days > 0) {
				// $expression = '*/' . $days . ' * * * *'; // every N hours (Only for tests purpose!)
				$expression = '0 0 */' . $days . ' * *'; // every N days
				$schedule->command('makeBackup:exec files')->cron($expression);
			}
		}
		if (config('settings.backup.backup_languages')) {
			$days = (int)config('settings.backup.backup_frequency_languages');
			if ($days > 0) {
				// $expression = '*/' . $days . ' * * * *'; // every N hours (Only for tests purpose!)
				$expression = '0 0 */' . $days . ' * *'; // every N days
				$schedule->command('makeBackup:exec languages')->cron($expression);
			}
		}
		
		// BACKUPS (CLEANUP)
		if (config('settings.backup.backup_cleanup_keep_days')) {
			$days = (int)config('settings.backup.backup_cleanup_keep_days');
			if ($days > 0) {
				// $schedule->command('clearBackup:exec')->hourly(); // every hours (Only for tests purpose!)
				$schedule->command('clearBackup:exec')->daily();
			}
		}
		
		// CLEAR CACHE & VIEWS
		if (!env('DISABLE_CACHE_AUTO_CLEAR') || (int)env('DISABLE_CACHE_AUTO_CLEAR', 0) != 1) {
			$schedule->command('cache:clear')->weeklyOn(7, '6:00');
			$schedule->command('cache:clear')->weeklyOn(7, '6:05'); // To prevent file lock issues (Optional)
			$schedule->command('view:clear')->weeklyOn(7, '6:00');
		}
	}
	
	/**
	 * Register the Closure based commands for the application.
	 *
	 * @return void
	 */
	protected function commands()
	{
		require base_path('routes/console.php');
	}
}

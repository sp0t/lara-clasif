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

/*
 * Increase PHP page execution time for this controller.
 * NOTE: This function has no effect when PHP is running in safe mode (http://php.net/manual/en/ini.sect.safe-mode.php#ini.safe-mode).
 * There is no workaround other than turning off safe mode or changing the time limit (max_execution_time) in the php.ini.
 */
set_time_limit(0);

use App\Models\City;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Scopes\VerifiedScope;
use App\Models\User;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ReviewedScope;
use App\Notifications\PostArchived;
use App\Notifications\PostDeleted;
use App\Notifications\PostWilBeDeleted;
use App\Models\Post;
use App\Models\Country;
use Illuminate\Console\Command;
use Jenssegers\Date\Date;

class AdsClear extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'ads:clear';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Delete all old Ads.';
	
	/**
	 * Default Ads Expiration Duration
	 *
	 * @var int
	 */
	private $unactivatedPostsExpiration      = 30;  // Delete the unactivated Posts after this expiration
	private $activatedPostsExpiration        = 150; // Archive the activated Posts after this expiration
	private $archivedPostsExpiration         = 7;   // Delete the archived Posts after this expiration
	private $manuallyArchivedPostsExpiration = 180; // Delete the manually archived Posts after this expiration
	
	/**
	 * AdsCleaner constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->unactivatedPostsExpiration      = (int)config('settings.cron.unactivated_posts_expiration', $this->unactivatedPostsExpiration);
		$this->activatedPostsExpiration        = (int)config('settings.cron.activated_posts_expiration', $this->activatedPostsExpiration);
		$this->archivedPostsExpiration         = (int)config('settings.cron.archived_posts_expiration', $this->archivedPostsExpiration);
		$this->manuallyArchivedPostsExpiration = (int)config('settings.cron.manually_archived_posts_expiration', $this->manuallyArchivedPostsExpiration);
	}
	
	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		if (isDemoDomain(env('APP_URL'))) {
			$msg = t('demo_mode_message');
			(isCli()) ? $this->warn($msg) : $this->printWeb($msg);
			exit();
		}
		
		// Get all countries
		$countries = Country::withoutGlobalScope(ActiveScope::class)->get();
		if ($countries->count() <= 0) {
			$msg = 'No country found.';
			(isCli()) ? $this->warn($msg) : $this->printWeb($msg);
			exit();
		}
		
		// Browse countries
		foreach ($countries as $country) {
			
			// Get country's ads
			$posts = Post::withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])->countryOf($country->code);
			
			if ($posts->count() <= 0) {
				$msg = 'No ads in "' . $country->name . '" (' . strtoupper($country->code) . ') website.';
				(isCli()) ? $this->info($msg) : $this->printWeb($msg);
				
				continue;
			}
			
			/*
			 * Items Processing (Using Eloquent Cursor Method)
			 * The cursor method allows you to iterate through your database records using a cursor, which will only execute a single query.
			 * When processing large amounts of data, the cursor method may be used to greatly reduce your memory usage
			 */
			foreach ($posts->cursor() as $post) {
				try {
					$this->itemProcessing($post, $country);
				} catch (\Exception $e) {
					dd($e);
				}
			}
			
		}
		
		$msg = 'END.';
		(isCli()) ? $this->info($msg) : $this->printWeb($msg);
	}
	
	/**
	 * @param \App\Models\Post $post
	 * @param \App\Models\Country $country
	 * @throws \Exception
	 */
	private function itemProcessing(Post $post, Country $country)
	{
		// Get the Country's TimeZone from the City
		$city       = City::find($post->city_id);
		$timeZoneId = (!empty($city)) ? $city->time_zone : 'Europe/London';
		
		// Get today date with the TimeZone
		$today = Date::now($timeZoneId);
		
		// Debug
		// dd($today->diffInDays($post->created_at));
		
		/* Non-activated Ads */
		if (!isVerifiedPost($post)) {
			// Delete non-active Ads after '$this->unactivatedPostsExpiration' days
			if ($today->diffInDays($post->created_at) >= $this->unactivatedPostsExpiration) {
				$post->delete();
				
				return;
			}
		} /* Activated Ads */
		else {
			/* Admin's Ads */
			if (isset($post->user_id)) {
				$possibleAdminUser = User::find($post->user_id);
				if (!empty($possibleAdminUser)) {
					if ($possibleAdminUser->can(Permission::getStaffPermissions())) {
						// Delete all Admin Ads after '$this->activatedPostsExpiration' days
						if ($today->diffInDays($post->created_at) >= $this->activatedPostsExpiration) {
							$post->delete();
							
							return;
						}
					}
				}
			}
			
			/* Users's Ads */
			
			/* Check if the Ad is featured (Premium Ads) */
			if ($post->featured == 1) {
				// Get all Packages
				$packages = Package::transIn(config('appLang.abbr', config('app.locale')))->get();
				
				/* Is it a website with Premium Ads features enabled? */
				if ($packages->count() > 0) {
					// Check the Ad's transactions (Get the last transaction)
					$payment = Payment::where('post_id', $post->id)->orderBy('id', 'DESC')->first();
					if (!empty($payment)) {
						// Get Package info
						$package = Package::find($payment->package_id);
						if (!empty($package)) {
							// Un-featured the Ad after {$package->duration} days (related to the Payment date)
							if ($today->diffInDays($payment->created_at) >= $package->duration) {
								
								// Un-featured
								$post->featured = 0;
								$post->save();
								
								return;
							}
						}
					}
				}
			} /* It is a free website */
			else {
				// Auto-archive
				if ($post->archived != 1) {
					// Archive all activated Ads after '$this->activatedPostsExpiration' days
					if ($today->diffInDays($post->created_at) >= $this->activatedPostsExpiration) {
						// Archive
						$post->archived    = 1;
						$post->archived_at = $today;
						$post->save();
						
						if ($country->active == 1) {
							try {
								// Send Notification Email to the Author
								$post->notify(new PostArchived($post, $this->archivedPostsExpiration));
							} catch (\Exception $e) {
								$msg = $e->getMessage() . PHP_EOL;
								(isCli()) ? $this->warn($msg) : $this->printWeb($msg);
							}
						}
						
						return;
					}
				}
				
				// Auto-delete
				if ($post->archived == 1) {
					// Debug
					// $today = $today->addDays(4);
					
					// Count days since the Ad has been archived
					$countDaysSinceAdHasBeenArchived = $today->diffInDays($post->archived_at);
					
					// Send one alert email each X day started from Y days before the final deletion until the Ad deletion (using 'archived_at')
					// Start alert email sending from 7 days earlier (for example)
					$daysEarlier       = 7; // In days (Y)
					$intervalOfSending = 2; // In days (X)
					
					if ($post->archived_manually != 1) {
						$archivedPostsExpirationSomeDaysEarlier = $this->archivedPostsExpiration - $daysEarlier;
					} else {
						$archivedPostsExpirationSomeDaysEarlier = $this->manuallyArchivedPostsExpiration - $daysEarlier;
					}
					
					if ($countDaysSinceAdHasBeenArchived >= $archivedPostsExpirationSomeDaysEarlier) {
						// Update the '$daysEarlier' to show in the mail
						$daysEarlier = $daysEarlier - $countDaysSinceAdHasBeenArchived;
						
						if ($daysEarlier > 0) {
							// Using 'deletion_mail_sent_at'
							if ($today->diffInDays($post->deletion_mail_sent_at) >= $intervalOfSending) {
								try {
									$post->notify(new PostWilBeDeleted($post, $daysEarlier));
								} catch (\Exception $e) {
									$msg = $e->getMessage() . PHP_EOL;
									(isCli()) ? $this->warn($msg) : $this->printWeb($msg);
								}
								
								// Update the field 'deletion_mail_sent_at' with today timestamp
								$post->deletion_mail_sent_at = $today;
								$post->save();
							}
						}
					}
					
					// Delete all archived Ads '$this->archivedPostsExpiration' days later (using 'archived_at')
					if ($countDaysSinceAdHasBeenArchived >= $this->archivedPostsExpiration) {
						if ($country->active == 1) {
							try {
								// Send Notification Email to the Author
								$post->notify(new PostDeleted($post));
							} catch (\Exception $e) {
								$msg = $e->getMessage() . PHP_EOL;
								(isCli()) ? $this->warn($msg) : $this->printWeb($msg);
							}
						}
						
						// Delete
						$post->delete();
						
						return;
					}
				}
			}
		}
	}
	
	/**
	 * @param $var
	 */
	private function printWeb($var)
	{
		// Only for Debug !
		// echo '<pre>'; print_r($var); echo '</pre>';
	}
}

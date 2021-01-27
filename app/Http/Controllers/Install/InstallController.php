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

namespace App\Http\Controllers\Install;

/*
 * Increase PHP page execution time for this controller.
 * NOTE: This function has no effect when PHP is running in safe mode (http://php.net/manual/en/ini.sect.safe-mode.php#ini.safe-mode).
 * There is no workaround other than turning off safe mode or changing the time limit (max_execution_time) in the php.ini.
 */
set_time_limit(0);

use App\Http\Controllers\Controller;
use App\Http\Controllers\Install\Traits\Install\ApiTrait;
use App\Http\Controllers\Install\Traits\Install\CheckerTrait;
use App\Http\Controllers\Install\Traits\Install\DbTrait;
use App\Http\Controllers\Install\Traits\Install\EnvTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class InstallController extends Controller
{
	use ApiTrait, CheckerTrait, EnvTrait, DbTrait;
	
	public $baseUrl;
	public $installUrl;
	
	/**
	 * InstallController constructor.
	 *
	 * @param Request $request
	 */
	public function __construct(Request $request)
	{
		// From Laravel 5.3.4 or above
		$this->middleware(function ($request, $next) {
			$this->commonQueries($request);
			
			return $next($request);
		});
		
		// Create SQL destination path if not exists
		if (!File::exists(storage_path('app/database/geonames/countries'))) {
			File::makeDirectory(storage_path('app/database/geonames/countries'), 0755, true);
		}
		
		// Base URL
		$this->baseUrl = getRawBaseUrl();
		view()->share('baseUrl', $this->baseUrl);
		config(['app.url' => $this->baseUrl]);
		
		// Installation URL
		$this->installUrl = $this->baseUrl . '/install';
		view()->share('installUrl', $this->installUrl);
	}
	
	/**
	 * Common Queries
	 *
	 * @param Request $request
	 */
	public function commonQueries(Request $request)
	{
		// Delete all front&back office sessions
		$request->session()->forget('country_code');
		$request->session()->forget('time_zone');
		$request->session()->forget('language_code');
		
		// Get country code by the user IP address
		$ipCountryCode = $this->getCountryCodeFromIPAddr();
	}
	
	/**
	 * Check for current step
	 *
	 * @param Request $request
	 * @param null $liveData
	 * @return int
	 */
	public function step(Request $request, $liveData = null)
	{
		$step = 0;
		
		$data = $request->session()->get('compatibilities');
		if (isset($data)) {
			$step = 1;
		} else {
			return $step;
		}
		
		$data = $request->session()->get('site_info');
		if (isset($data)) {
			$step = 3;
		} else {
			return $step;
		}
		
		$data = $request->session()->get('database');
		if (isset($data)) {
			$step = 4;
		} else {
			return $step;
		}
		
		$data = $request->session()->get('database_imported');
		if (isset($data)) {
			$step = 5;
		} else {
			return $step;
		}
		
		$data = $request->session()->get('cron_jobs');
		if (isset($data)) {
			$step = 6;
		} else {
			return $step;
		}
		
		return $step;
	}
	
	/**
	 * STEP 0 - Starting installation
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function starting(Request $request)
	{
		$exitCode = Artisan::call('cache:clear');
		$exitCode = Artisan::call('config:clear');
		
		// Get the Query String
		$queryString = (request()->all() ? ('?' . httpBuildQuery(request()->all())) : '');
		
		return redirect($this->installUrl . '/system_compatibility' . $queryString);
	}
	
	/**
	 * STEP 1 - Check System Compatibility
	 *
	 * @param Request $request
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
	 */
	public function systemCompatibility(Request $request)
	{
		// Begin check
		$request->session()->forget('compatibilities');
		
		// Get the system compatibilities data
		$compatibilities = $this->getSystemCompatibilitiesData();
		
		// Auto-Checking: Skip this step If the system is Ok.
		if ($this->checkSystemCompatibility($request, $compatibilities)) {
			$request->session()->put('compatibilities', $compatibilities);
			
			// Get the Query String
			$queryString = (request()->all() ? ('?' . httpBuildQuery(request()->all())) : '');
			
			return redirect($this->installUrl . '/site_info' . $queryString);
		}
		
		// Check the compatibilities manually
		$result = true;
		foreach ($compatibilities as $compatibility) {
			if (!$compatibility['check']) {
				$result = false;
			}
		}
		
		// Retry if something not work yet
		try {
			if ($result) {
				$request->session()->put('compatibilities', $compatibilities);
			}
			
			return view('install.compatibilities', [
				'compatibilities' => $compatibilities,
				'result'          => $result,
				'step'            => $this->step($request),
				'current'         => 1,
			]);
		} catch (\Exception $e) {
			$exitCode = Artisan::call('cache:clear');
			$exitCode = Artisan::call('config:clear');
			
			return redirect($this->installUrl . '/system_compatibility');
		}
	}
	
	/**
	 * STEP 2 - Set Site Info
	 *
	 * @param Request $request
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
	 */
	public function siteInfo(Request $request)
	{
		if ($this->step($request) < 1) {
			return redirect($this->installUrl . '/system_compatibility');
		}
		
		// Make sure session is working
		$rules = [
			'site_name'       => 'required',
			'site_slogan'     => 'required',
			'name'            => 'required',
			'purchase_code'   => 'required',
			'email'           => 'required|email',
			'password'        => 'required',
			'default_country' => 'required',
		];
		$sendmail_rules = [
			'sendmail_path' => 'required',
		];
		$smtp_rules = [
			'smtp_hostname'   => 'required',
			'smtp_port'       => 'required',
			'smtp_username'   => 'required',
			'smtp_password'   => 'required',
			'smtp_encryption' => 'required',
		];
		$mailgun_rules = [
			'mailgun_domain' => 'required',
			'mailgun_secret' => 'required',
		];
		$postmark_rules = [
			'postmark_token' => 'required',
		];
		$ses_rules = [
			'ses_key'    => 'required',
			'ses_secret' => 'required',
			'ses_region' => 'required',
		];
		$mandrill_rules = [
			'mandrill_secret' => 'required',
		];
		$sparkpost_rules = [
			'sparkpost_secret' => 'required',
		];
		
		// Validate and save posted data
		if ($request->isMethod('post')) {
			$request->session()->forget('site_info');
			
			// Check purchase code
			$messages = [];
			$purchaseCodeData = $this->purchaseCodeChecker($request->input('purchase_code'));
			if ($purchaseCodeData->valid == false) {
				$rules['purchase_code_valid'] = 'required';
				if ($purchaseCodeData->message != '') {
					$messages = ['purchase_code_valid.required' => 'The :attribute field is required. ERROR: <strong>' . $purchaseCodeData->message . '</strong>'];
				}
			}
			
			if ($request->input('mail_driver') == 'sendmail') {
				$rules = array_merge($rules, $sendmail_rules);
			}
			if ($request->input('mail_driver') == 'smtp') {
				$rules = array_merge($rules, $smtp_rules);
			}
			if ($request->input('mail_driver') == 'mailgun') {
				$rules = array_merge($rules, $mailgun_rules);
			}
			if ($request->input('mail_driver') == 'postmark') {
				$rules = array_merge($rules, $postmark_rules);
			}
			if ($request->input('mail_driver') == 'ses') {
				$rules = array_merge($rules, $ses_rules);
			}
			if ($request->input('mail_driver') == 'mandrill') {
				$rules = array_merge($rules, $mandrill_rules);
			}
			if ($request->input('mail_driver') == 'sparkpost') {
				$rules = array_merge($rules, $sparkpost_rules);
			}
			
			if (!empty($messages)) {
				$this->validate($request, $rules, $messages);
			} else {
				$this->validate($request, $rules);
			}
			
			// Check SMTP connection
			if ($request->input('mail_driver') == 'smtp') {
				$rules = [];
				$messages = [];
				try {
					$transport = new \Swift_SmtpTransport($request->input('smtp_hostname'), $request->input('smtp_port'), $request->input('smtp_encryption'));
					$transport->setUsername($request->input('smtp_username'));
					$transport->setPassword($request->input('smtp_password'));
					$mailer = new \Swift_Mailer($transport);
					$mailer->getTransport()->start();
				} catch (\Swift_TransportException $e) {
					$rules['smtp_valid'] = 'required';
					if ($e->getMessage() != '') {
						$messages = ['smtp_valid.required' => 'Can\'t connect to SMTP server. ERROR: <strong>' . $e->getMessage() . '</strong>'];
					}
				} catch (\Exception $e) {
					$rules['smtp_valid'] = 'required';
					if ($e->getMessage() != '') {
						$messages = ['smtp_valid.required' => 'Can\'t connect to SMTP server. ERROR: <strong>' . $e->getMessage() . '</strong>'];
					}
				}
				if (!empty($messages)) {
					$this->validate($request, $rules, $messages);
				} else {
					$this->validate($request, $rules);
				}
			}
			
			// Save data in session
			$siteInfo = $request->all();
			$request->session()->put('site_info', $siteInfo);
			
			return redirect($this->installUrl . '/database');
		}
		
		$siteInfo = $request->session()->get('site_info');
		if (!empty($request->old())) {
			$siteInfo = $request->old();
		}
		
		return view('install.site_info', [
			'site_info'       => $siteInfo,
			'rules'           => $rules,
			'sendmail_rules'  => $sendmail_rules,
			'smtp_rules'      => $smtp_rules,
			'mailgun_rules'   => $mailgun_rules,
			'postmark_rules'  => $postmark_rules,
			'ses_rules'       => $ses_rules,
			'mandrill_rules'  => $mandrill_rules,
			'sparkpost_rules' => $sparkpost_rules,
			'step'            => $this->step($request),
			'current'         => 2,
		]);
	}
	
	/**
	 * STEP 3 - Database configuration
	 *
	 * @param Request $request
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
	 */
	public function database(Request $request)
	{
		if ($this->step($request) < 2) {
			return redirect($this->installUrl . '/site_info');
		}
		
		// Check required fields
		$rules = [
			'host'     => 'required',
			'port'     => 'required',
			'username' => 'required',
			'database' => 'required',
		];
		
		// Validate and save posted data
		if ($request->isMethod('post')) {
			$request->session()->forget('database');
			
			$this->validate($request, $rules);
			
			// Check the Database Connection
			$messages = [];
			try {
				// Database Parameters
				$driver = config('database.connections.' . config('database.default') . '.driver', 'mysql');
				$port = (int)$request->input('port');
				$options = [
					\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
					\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
					\PDO::ATTR_EMULATE_PREPARES   => true,
					\PDO::ATTR_CURSOR             => \PDO::CURSOR_FWDONLY,
				];
				
				// Get the Connexion's DSN
				if (empty($request->socket)) {
					$dsn = $driver . ':host=' . $request->input('host') . ';port=' . $port . ';dbname=' . $request->input('database') . ';charset=utf8';
				} else {
					$dsn = $driver . ':unix_socket=' . $request->input('socket') . ';dbname=' . $request->input('database') . ';charset=utf8';
				}
				
				// Connect to the Database Server
				$pdo = new \PDO($dsn, $request->input('username'), $request->input('password'), $options);
				
			} catch (\PDOException $e) {
				$rules['database_connection'] = 'required';
				$messages = ['database_connection.required' => 'Can\'t connect to the database server. ERROR: <strong>' . $e->getMessage() . '</strong>'];
			} catch (\Exception $e) {
				$rules['database_connection'] = 'required';
				$messages = ['database_connection.required' => 'The database connection failed. ERROR: <strong>' . $e->getMessage() . '</strong>'];
			}
			
			if (!empty($messages)) {
				$this->validate($request, $rules, $messages);
			} else {
				$this->validate($request, $rules);
			}
			
			// Get database info and Save it in session
			$database = $request->all();
			$request->session()->put('database', $database);
			
			// Write config file
			$this->writeEnv($request);
			
			// Return to Import Database page
			return redirect($this->installUrl . '/database_import');
		}
		
		$database = $request->session()->get('database');
		if (!empty($request->old())) {
			$database = $request->old();
		}
		
		return view('install.database', [
			'database' => $database,
			'rules'    => $rules,
			'step'     => $this->step($request),
			'current'  => 3,
		]);
	}
	
	/**
	 * STEP 4 - Import Database
	 *
	 * @param Request $request
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
	 */
	public function databaseImport(Request $request)
	{
		if ($this->step($request) < 3) {
			return redirect($this->installUrl . '/database');
		}
		
		// Get database connexion info & site info
		$database = $request->session()->get('database');
		$siteInfo = $request->session()->get('site_info');
		
		if ($request->input('action') == 'import') {
			$request->session()->forget('database_imported');
			
			$this->submitDatabaseImport($request, $siteInfo, $database);
			
			// The database is now imported !
			$request->session()->put('database_imported', true);
			
			$request->session()->flash('alert-success', trans('messages.install.database_import.success'));
			
			return redirect($this->installUrl . '/cron_jobs');
		}
		
		return view('install.database_import', [
			'database' => $database,
			'step'     => $this->step($request),
			'current'  => 3,
		]);
	}
	
	/**
	 * STEP 5 - Set Cron Jobs
	 *
	 * @param Request $request
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
	 */
	public function cronJobs(Request $request)
	{
		if ($this->step($request) < 5) {
			return redirect($this->installUrl . '/database');
		}
		
		$request->session()->put('cron_jobs', true);
		
		return view('install.cron_jobs', [
			'step'    => $this->step($request),
			'current' => 5,
		]);
	}
	
	/**
	 * STEP 6 - Finish
	 *
	 * @param Request $request
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
	 */
	public function finish(Request $request)
	{
		if ($this->step($request) < 6) {
			return redirect($this->installUrl . '/database');
		}
		
		$request->session()->put('install_finish', true);
		
		// Delete all front&back office cookies
		if (isset($_COOKIE['ip_country_code'])) {
			unset($_COOKIE['ip_country_code']);
		}
		
		// Clear all Cache
		$exitCode = Artisan::call('cache:clear');
		sleep(2);
		$exitCode = Artisan::call('view:clear');
		sleep(1);
		File::delete(File::glob(storage_path('logs') . DIRECTORY_SEPARATOR . 'laravel*.log'));
		
		// Rendering final Info
		return view('install.finish', [
			'step'    => $this->step($request),
			'current' => 6,
		]);
	}
	
	// PRIVATE METHODS
	// Check out Traits
}

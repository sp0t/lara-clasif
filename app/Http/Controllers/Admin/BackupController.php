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

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Artisan;
use Exception;
use Illuminate\Routing\Controller;
use League\Flysystem\Adapter\Local;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
	public $data = [];
	
	/**
	 * BackupController constructor.
	 */
	public function __construct()
	{
		$this->middleware('demo.restriction')->except(['index']);
	}
	
	public function index()
	{
		if (!count(config('backup.backup.destination.disks'))) {
			dd(trans('admin::messages.no_disks_configured'));
		}
		
		$this->data['backups'] = [];
		
		foreach (config('backup.backup.destination.disks') as $disk_name) {
			$disk = Storage::disk($disk_name);
			$adapter = $disk->getDriver()->getAdapter();
			$files = $disk->allFiles();
			
			// make an array of backup files, with their filesize and creation date
			foreach ($files as $k => $f) {
				// only take the zip files into account
				if (substr($f, -4) == '.zip' && $disk->exists($f)) {
					$this->data['backups'][] = [
						'file_path'     => $f,
						'file_name'     => str_replace('backups' . DIRECTORY_SEPARATOR, '', $f),
						'file_size'     => $disk->size($f),
						'last_modified' => $disk->lastModified($f),
						'disk'          => $disk_name,
						'download'      => ($adapter instanceof Local) ? true : false,
					];
				}
			}
		}
		
		// reverse the backups, so the newest one would be on top
		$this->data['backups'] = array_reverse($this->data['backups']);
		$this->data['title'] = 'Backups';
		
		return view('admin::backup', $this->data);
	}
	
	public function create()
	{
		try {
			ini_set('max_execution_time', 1800);
			
			$type = request()->get('type');
			
			// Set the Backup config vars
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
			
			$output = Artisan::output();
			
			// Log the results
			Log::info("Backup -- new backup started from admin interface \r\n" . $output);
			
			// Return the results as a response to the ajax call
			echo $output;
		} catch (Exception $e) {
			Response::make($e->getMessage(), 500);
		}
		
		return 'success';
	}
	
	/**
	 * Downloads a backup zip file.
	 */
	public function download()
	{
		$disk = Storage::disk(request()->input('disk'));
		$file_name = request()->input('file_name');
		$adapter = $disk->getDriver()->getAdapter();
		
		if ($adapter instanceof Local) {
			$storage_path = $disk->getDriver()->getAdapter()->getPathPrefix();
			
			if ($disk->exists($file_name)) {
				return response()->download($storage_path . $file_name);
			} else {
				abort(404, trans('admin::messages.backup_doesnt_exist'));
			}
		} else {
			abort(404, trans('admin::messages.only_local_downloads_supported'));
		}
	}
	
	/**
	 * Deletes a backup file.
	 *
	 * @param $file_name
	 * @return string
	 */
	public function delete($file_name)
	{
		$disk = Storage::disk(request()->input('disk'));
		
		if ($disk->exists($file_name)) {
			$disk->delete($file_name);
			
			return 'success';
		} else {
			abort(404, trans('admin::messages.backup_doesnt_exist'));
		}
	}
}

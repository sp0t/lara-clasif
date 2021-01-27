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

namespace App\Http\Controllers\Post;

use App\Helpers\ArrayHelper;
use App\Helpers\UrlGen;
use App\Http\Requests\ReportRequest;
use App\Models\Permission;
use App\Models\Post;
use App\Models\ReportType;
use App\Http\Controllers\FrontController;
use App\Models\User;
use App\Notifications\ReportSent;
use Illuminate\Support\Facades\Notification;
use Torann\LaravelMetaTags\Facades\MetaTag;

class ReportController extends FrontController
{
	/**
	 * ReportController constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		// From Laravel 5.3.4 or above
		$this->middleware(function ($request, $next) {
			$this->commonQueries();
			
			return $next($request);
		});
		
		$this->middleware('demo.restriction')->only(['sendReport']);
	}
	
	/**
	 * Common Queries
	 */
	public function commonQueries()
	{
		// Get Report abuse types
		$reportTypes = ReportType::trans()->get();
		view()->share('reportTypes', $reportTypes);
	}
	
	public function showReportForm($postId)
	{
		$data = [];
		
		// Get Post
		$data['post'] = Post::findOrFail($postId);
		
		// Meta Tags
		$data['title'] = t('Report for :title', ['title' => mb_ucfirst($data['post']->title)]);
		$description   = t('Send a report for :title', ['title' => mb_ucfirst($data['post']->title)]);
		
		MetaTag::set('title', $data['title']);
		MetaTag::set('description', strip_tags($description));
		
		// Open Graph
		$this->og->title($data['title'])->description($description);
		view()->share('og', $this->og);
		
		return view('post.report', $data);
	}
	
	/**
	 * @param $postId
	 * @param ReportRequest $request
	 * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function sendReport($postId, ReportRequest $request)
	{
		// Get Post
		$post = Post::findOrFail($postId);
		
		// Store Report
		$report            = $request->all();
		$report['post_id'] = $post->id;
		$report            = ArrayHelper::toObject($report);
		
		// Send Abuse Report to admin
		try {
			if (config('settings.app.email')) {
				Notification::route('mail', config('settings.app.email'))->notify(new ReportSent($post, $report));
			} else {
				$admins = User::permission(Permission::getStaffPermissions())->get();
				if ($admins->count() > 0) {
					Notification::send($admins, new ReportSent($post, $report));
					/*
                    foreach ($admins as $admin) {
						Notification::route('mail', $admin->email)->notify(new ReportSent($post, $report));
                    }
					*/
				}
			}
			
			flash(t('Your report has sent successfully to us. Thank you!'))->success();
		} catch (\Exception $e) {
			flash($e->getMessage())->error();
			
			return back()->withInput();
		}
		
		return redirect(UrlGen::postUri($post));
	}
	
}

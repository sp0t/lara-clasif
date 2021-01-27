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

use App\Models\Blacklist;
use App\Models\Post;
use App\Models\User;
use Larapen\Admin\app\Http\Controllers\PanelController;
use App\Http\Requests\Admin\BlacklistRequest as StoreRequest;
use App\Http\Requests\Admin\BlacklistRequest as UpdateRequest;
use Prologue\Alerts\Facades\Alert;

class BlacklistController extends PanelController
{
	public function setup()
	{
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel('App\Models\Blacklist');
		$this->xPanel->setRoute(admin_uri('blacklists'));
		$this->xPanel->setEntityNameStrings(trans('admin::messages.blacklist'), trans('admin::messages.blacklists'));
		$this->xPanel->orderBy('id', 'DESC');
		
		$this->xPanel->addButtonFromModelFunction('top', 'bulk_delete_btn', 'bulkDeleteBtn', 'end');
		
		/*
		|--------------------------------------------------------------------------
		| COLUMNS AND FIELDS
		|--------------------------------------------------------------------------
		*/
		// COLUMNS
		$this->xPanel->addColumn([
			'name'  => 'id',
			'label' => '',
			'type'  => 'checkbox',
			'orderable' => false,
		]);
		$this->xPanel->addColumn([
			'name'  => 'type',
			'label' => trans("admin::messages.Type"),
		]);
		$this->xPanel->addColumn([
			'name'  => 'entry',
			'label' => trans("admin::messages.Entry"),
		]);
		
		// FIELDS
		$this->xPanel->addField([
			'name'  => 'type',
			'label' => trans("admin::messages.Type"),
			'type'  => 'enum',
		]);
		$this->xPanel->addField([
			'name'       => 'entry',
			'label'      => trans("admin::messages.Entry"),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans("admin::messages.Entry"),
			],
		]);
	}
	
	public function store(StoreRequest $request)
	{
		return parent::storeCrud();
	}
	
	public function update(UpdateRequest $request)
	{
		return parent::updateCrud();
	}
	
	/**
	 * Ban user by email address (from link)
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function banUserByEmail()
	{
		// Get email address
		$email = request()->get('email');
		
		// Get previous URL
		$previousUrl = url()->previous();
		
		// Exceptions
		if (empty($email) || isDemo($previousUrl)) {
			if (isDemo($previousUrl)) {
				$message = t('demo_mode_message');
			} else {
				$message = trans("admin::messages.no_action_is_performed");
			}
			if (isFromAdminPanel($previousUrl)) {
				Alert::info($message)->flash();
			} else {
				flash($message)->info();
			}
			
			return redirect()->back();
		}
		
		// Check the email has been banned
		$banned = Blacklist::where('type', 'email')->where('entry', $email)->first();
		if (!empty($banned)) {
			// Delete the banned user related to the email address
			$user = User::where('email', $banned->entry)->first();
			if (!empty($user)) {
				$user->delete();
			}
			
			// Delete the banned user's posts related to the email address
			$posts = Post::where('email', $banned->entry)->get();
			if ($posts->count() > 0) {
				foreach ($posts as $post) {
					$post->delete();
				}
			}
		} else {
			// Add the email address to the blacklist
			$banned = new Blacklist();
			$banned->type = 'email';
			$banned->entry = $email;
			$banned->save();
		}
		
		$message = trans("admin::messages.email_address_banned_successfully", ['email' => $email]);
		if (isFromAdminPanel($previousUrl)) {
			Alert::success($message)->flash();
		} else {
			flash($message)->success();
		}
		
		// Get next URL
		$nextUrl = '/';
		if (isFromAdminPanel($previousUrl)) {
			$tmp = preg_split('#\/[0-9]+\/edit#ui', $previousUrl);
			$nextUrl = isset($tmp[0]) ? $tmp[0] : $previousUrl;
		}
		
		return redirect($nextUrl)->header('Cache-Control', 'no-store, no-cache, must-revalidate');
	}
}

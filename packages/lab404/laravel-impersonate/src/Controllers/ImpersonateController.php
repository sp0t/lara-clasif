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

namespace Larapen\Impersonate\Controllers;

use Illuminate\Http\Request;
use Lab404\Impersonate\Services\ImpersonateManager;
use Prologue\Alerts\Facades\Alert;

class ImpersonateController extends \Lab404\Impersonate\Controllers\ImpersonateController
{
	/** @var ImpersonateManager */
	protected $manager;
	
	/**
	 * ImpersonateController constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->middleware('auth');
		$this->middleware('demo.restriction');
		
		$this->manager = app()->make(ImpersonateManager::class);
	}
	
	/**
	 * @param Request $request
	 * @param int $id
	 * @param null $guardName
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function take(Request $request, $id, $guardName = null)
	{
		$guardName = $guardName ?? $this->manager->getDefaultSessionGuard();
		
		// If the Domain Mapping plugin is installed,
		// Then, the impersonate feature need to be disabled
		if (config('plugins.domainmapping.installed')) {
			Alert::error(t('Cannot impersonate when the Domain Mapping plugin is installed'))->flash();
			
			return redirect()->back();
		}
		
		// Cannot impersonate yourself
		if ($id == $request->user()->getKey() && ($this->manager->getCurrentAuthGuardName() == $guardName)) {
			Alert::error('Cannot impersonate yourself')->flash();
			
			return redirect()->back();
		}
		
		// Cannot impersonate again if you're already impersonate a user
		if ($this->manager->isImpersonating()) {
			abort(403);
		}
		
		if (!$request->user()->canImpersonate()) {
			Alert::error('The current user can\'t impersonate')->flash();
			
			return redirect()->back();
		}
		
		$userToImpersonate = $this->manager->findUserById($id, $guardName);
		
		if ($userToImpersonate->canBeImpersonated()) {
			if ($this->manager->take($request->user(), $userToImpersonate, $guardName)) {
				$takeRedirect = $this->manager->getTakeRedirectTo();
				if ($takeRedirect !== 'back') {
					return redirect()->to($takeRedirect);
				}
			}
		} else {
			Alert::error(t('The destination user can\'t be impersonated'))->flash();
		}
		
		return redirect()->back();
	}
	
	/**
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function leave()
	{
		if (!$this->manager->isImpersonating()) {
			abort(403);
		}
		
		$this->manager->leave();
		
		$leaveRedirect = $this->manager->getLeaveRedirectTo();
		if ($leaveRedirect !== 'back') {
			return redirect()->to($leaveRedirect);
		}
		
		return redirect()->back();
	}
}

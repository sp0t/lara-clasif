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

namespace App\Http\Controllers\Auth\Traits;


use App\Notifications\EmailVerification;
use Illuminate\Support\Facades\Request;
use Larapen\LaravelLocalization\Facades\LaravelLocalization;
use Prologue\Alerts\Facades\Alert;

trait EmailVerificationTrait
{
	/**
	 * Send verification message
	 *
	 * @param $entity
	 * @param bool $displayFlashMessage
	 * @return bool
	 */
	public function sendVerificationEmail($entity, $displayFlashMessage = true)
	{
		// Get Entity
		$entityRef = $this->getEntityRef();
		if (empty($entity) || empty($entityRef)) {
            $message = t("Entity ID not found.");
            
            if (isFromAdminPanel()) {
                Alert::error($message)->flash();
            } else {
                flash($message)->error();
            }
            
			return false;
		}
		
		// Send Confirmation Email
		try {
			if (request()->filled('locale')) {
				$locale = (array_key_exists(request()->get('locale'), LaravelLocalization::getSupportedLocales()))
					? request()->get('locale')
					: null;
				
				if (!empty($locale)) {
					$entity->notify((new EmailVerification($entity, $entityRef))->locale($locale));
				} else {
					$entity->notify(new EmailVerification($entity, $entityRef));
				}
			} else {
				$entity->notify(new EmailVerification($entity, $entityRef));
			}
			
			if ($displayFlashMessage) {
				$message = t("An activation link has been sent to you to verify your email address.");
				flash($message)->success();
			}
			
			session(['verificationEmailSent' => true]);
			
			return true;
		} catch (\Exception $e) {
			$message = changeWhiteSpace($e->getMessage());
            if (isFromAdminPanel()) {
                Alert::error($message)->flash();
            } else {
                flash($message)->error();
            }
		}
		
		return false;
	}
	
	/**
	 * Show the ReSend Verification Message Link
	 *
	 * @param $entity
	 * @param $entityRefId
	 * @return bool
	 */
	public function showReSendVerificationEmailLink($entity, $entityRefId)
	{
		// Get Entity
		$entityRef = $this->getEntityRef($entityRefId);
		if (empty($entity) || empty($entityRef)) {
			return false;
		}
		
		// Show ReSend Verification Email Link
		if (session()->has('verificationEmailSent')) {
			$message = t("Resend the verification message to verify your email address.");
			$message .= ' <a href="' . lurl('verify/' . $entityRef['slug'] . '/' . $entity->id . '/resend/email') . '" class="btn btn-warning">' . t("Re-send") . '</a>';
			flash($message)->warning();
		}
		
		return true;
	}
	
	/**
	 * URL: Re-Send the verification message
	 *
	 * @param $entityId
	 * @return \Illuminate\Http\RedirectResponse
	 */
    public function reSendVerificationEmail($entityId)
    {
        // Non-admin data resources
        $entityRefId = getSegment(2);
        
        // Admin data resources
        if (isFromAdminPanel()) {
            $entityRefId = Request::segment(3);
        }
        
        // Keep Success Message If exists
        if (session()->has('message')) {
            session()->keep(['message']);
        }
        
        // Get Entity
        $entityRef = $this->getEntityRef($entityRefId);
        if (empty($entityRef)) {
            $message = t("Entity ID not found.");
            
            if (isFromAdminPanel()) {
                Alert::error($message)->flash();
            } else {
                flash($message)->error();
            }
            
            return back();
        }
        
        // Get Entity by Id
        $model = $entityRef['namespace'];
        $entity = $model::withoutGlobalScopes($entityRef['scopes'])->where('id', $entityId)->first();
        if (empty($entity)) {
            $message = t("Entity ID not found.");
            
            if (isFromAdminPanel()) {
                Alert::error($message)->flash();
            } else {
                flash($message)->error();
            }
            
            return back();
        }
        
        // Check if the Email is already verified
        if ($entity->verified_email == 1 || isDemo()) {
            if (isDemo()) {
                $message = t('demo_mode_message');
				if (isFromAdminPanel()) {
					Alert::info($message)->flash();
				} else {
					flash($message)->info();
				}
            } else {
                $message = t("Your :field is already verified.", ['field' => t('Email Address')]);
				if (isFromAdminPanel()) {
					Alert::error($message)->flash();
				} else {
					flash($message)->error();
				}
            }
            
            // Remove Notification Trigger
            $this->clearEmailSession();
            
            return back();
        }
        
        // Re-Send the confirmation
        if ($this->sendVerificationEmail($entity, false)) {
            if (isFromAdminPanel()) {
                $message = 'The activation link has been sent to the user to verify his email address.';
                Alert::success($message)->flash();
            } else {
                $message = t("The activation link has been sent to you to verify your email address.");
                flash($message)->success();
            }
            
            // Remove Notification Trigger
            $this->clearEmailSession();
        }
        
        return back();
    }
    
    /**
     * Remove Notification Trigger (by clearing the sessions)
     */
    private function clearEmailSession()
    {
        if (session()->has('verificationEmailSent')) {
            session()->forget('verificationEmailSent');
        }
    }
}

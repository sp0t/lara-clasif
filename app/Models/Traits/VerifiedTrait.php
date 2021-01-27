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

namespace App\Models\Traits;


use Larapen\LaravelLocalization\Facades\LaravelLocalization;

trait VerifiedTrait
{
    public function getVerifiedEmailHtml()
    {
        if (!isset($this->verified_email)) return false;
        
        // Get checkbox
        $out = ajaxCheckboxDisplay($this->{$this->primaryKey}, $this->getTable(), 'verified_email', $this->verified_email);
        
        // Get all entity's data
        $entity = self::find($this->{$this->primaryKey});
        
        if (!empty($entity->email)) {
            if ($entity->verified_email != 1) {
            	// ToolTip
				$toolTip = (!empty($entity->email)) ? 'data-toggle="tooltip" title="' . trans('admin::messages.To') . ': ' . $entity->email . '"' : '';
	
				// Get entity's language (If exists)
				$localeQueryString = '';
				if (isset($entity->language_code)) {
					$locale = (array_key_exists($entity->language_code, LaravelLocalization::getSupportedLocales()))
						? $entity->language_code
						: config('app.locale');
					$localeQueryString = '?locale=' . $locale;
				}
				
                // Show re-send verification message link
                $entitySlug = ($this->getTable() == 'users') ? 'user' : 'post';
                $urlPath = 'verify/' . $entitySlug . '/' . $this->{$this->primaryKey} . '/resend/email' . $localeQueryString;
				$actionUrl = admin_url($urlPath);
    
				// HTML Link
                $out .= ' &nbsp;';
				$out .= '<a class="btn btn-default btn-xs" href="' . $actionUrl . '" ' . $toolTip . '>';
				$out .= '<i class="fa fa-send-o"></i> ';
				$out .= trans('admin::messages.Re-send link');
				$out .= '</a>';
            } else {
                // Get social icon (if exists)
                if ($this->getTable() == 'users') {
                    if (!empty($entity) && isset($entity->provider)) {
                        if (!empty($entity->provider)) {
                            if ($entity->provider == 'facebook') {
                                $toolTip = 'data-toggle="tooltip" title="' . trans('admin::messages.Registered with :provider', ['provider' => 'Facebook']) . '"';
                                $out .= ' &nbsp;<i class="admin-single-icon fa fa-facebook-square" style="color: #3b5998;" ' . $toolTip . '></i>';
                            }
							if ($entity->provider == 'linkedin') {
								$toolTip = 'data-toggle="tooltip" title="' . trans('admin::messages.Registered with :provider', ['provider' => 'LinkedIn']) . '"';
								$out .= ' &nbsp;<i class="admin-single-icon fa fa-linkedin-square" style="color: #4682b4;" ' . $toolTip . '></i>';
							}
							if ($entity->provider == 'twitter') {
								$toolTip = 'data-toggle="tooltip" title="' . trans('admin::messages.Registered with :provider', ['provider' => 'Twitter']) . '"';
								$out .= ' &nbsp;<i class="admin-single-icon fa fa-twitter-square" style="color: #0099d4;" ' . $toolTip . '></i>';
							}
                            if ($entity->provider == 'google') {
                                $toolTip = 'data-toggle="tooltip" title="' . trans('admin::messages.Registered with :provider', ['provider' => 'Google']) . '"';
                                $out .= ' &nbsp;<i class="admin-single-icon fa fa-google-plus-square" style="color: #d34836;" ' . $toolTip . '></i>';
                            }
                        }
                    }
                }
            }
        } else {
            $out = checkboxDisplay($this->verified_email);
        }
        
        return $out;
    }
    
    public function getVerifiedPhoneHtml()
    {
        if (!isset($this->verified_phone)) return false;
        
        // Get checkbox
        $out = ajaxCheckboxDisplay($this->{$this->primaryKey}, $this->getTable(), 'verified_phone', $this->verified_phone);
        
        // Get all entity's data
        $entity = self::find($this->{$this->primaryKey});
        
        if (!empty($entity->phone)) {
            if ($entity->verified_phone != 1) {
            	// ToolTip
				$toolTip = (!empty($entity->phone)) ? 'data-toggle="tooltip" title="' . trans('admin::messages.To') . ': ' . $entity->phone . '"' : '';
	
				// Get entity's language (If exists)
				$localeQueryString = '';
				if (isset($entity->language_code)) {
					$locale = (array_key_exists($entity->language_code, LaravelLocalization::getSupportedLocales()))
						? $entity->language_code
						: config('app.locale');
					$localeQueryString = '?locale=' . $locale;
				}
				
                // Show re-send verification message code
                $entitySlug = ($this->getTable() == 'users') ? 'user' : 'post';
                $urlPath = 'verify/' . $entitySlug . '/' . $this->{$this->primaryKey} . '/resend/sms' . $localeQueryString;
				$actionUrl = admin_url($urlPath);
                
                // HTML Link
                $out .= ' &nbsp;';
				$out .= '<a class="btn btn-default btn-xs" href="' . $actionUrl . '" ' . $toolTip . '>';
				$out .= '<i class="fa fa-mobile"></i> ';
				$out .= trans('admin::messages.Re-send code');
				$out .= '</a>';
            }
        } else {
            $out = checkboxDisplay($this->verified_phone);
        }
        
        return $out;
    }
}
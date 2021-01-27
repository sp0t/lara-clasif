<?php
/**
 * LaraClassified - Geo Classified Ads Software
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

use App\Helpers\UrlGen;

/**
 * @param string $path
 * @return string
 */
function admin_uri($path = '')
{
	$path = str_replace(url(config('larapen.admin.route_prefix', 'admin')), '', $path);
	$path = ltrim($path, '/');
	
	if (!empty($path)) {
		$path = config('larapen.admin.route_prefix', 'admin') . '/' . $path;
	} else {
		$path = config('larapen.admin.route_prefix', 'admin');
	}
	
	return $path;
}

/**
 * @param string $path
 * @return string
 */
function admin_url($path = '')
{
	return url(admin_uri($path));
}

/**
 * Default Admin translator (e.g. admin::messages.php)
 *
 * @param $string
 * @param array $params
 * @param string $file
 * @param null $locale
 * @return string|\Symfony\Component\Translation\TranslatorInterface
 */
function __t($string, $params = [], $file = 'admin::messages', $locale = null)
{
    if (is_null($locale)) {
        $locale = config('app.locale');
    }

    return trans($file . '.' . $string, $params, $locale);
}

/**
 * Checkbox Display
 *
 * @param $fieldValue
 * @return string
 */
function checkboxDisplay($fieldValue)
{
	// fa-square-o | fa-check-square-o
	// fa-toggle-off | fa-toggle-on
    if ($fieldValue == 1) {
        return '<i class="admin-single-icon fa fa-toggle-on" aria-hidden="true"></i>';
    } else {
        return '<i class="admin-single-icon fa fa-toggle-off" aria-hidden="true"></i>';
    }
}

/**
 * Ajax Checkbox Display
 *
 * @param $id
 * @param $table
 * @param $field
 * @param null $fieldValue
 * @return string
 */
function ajaxCheckboxDisplay($id, $table, $field, $fieldValue = null)
{
    $lineId = $field.$id;
    $lineId = str_replace('.', '', $lineId); // fix JS bug (in admin layout)
    $data = 'data-table="' . $table . '" 
			data-field="'.$field.'" 
			data-line-id="' . $lineId . '" 
			data-id="' . $id . '" 
			data-value="' . (isset($fieldValue) ? $fieldValue : 0) . '"';

    // Decoration
    if (isset($fieldValue) && $fieldValue == 1) {
        $html = '<i id="' . $lineId . '" class="admin-single-icon fa fa-toggle-on" aria-hidden="true"></i>';
    } else {
        $html = '<i id="' . $lineId . '" class="admin-single-icon fa fa-toggle-off" aria-hidden="true"></i>';
    }
    $html = '<a href="" class="ajax-request" ' . $data . '>' . $html . '</a>';

    return $html;
}

/**
 * Advanced Ajax Checkbox Display
 *
 * @param $id
 * @param $table
 * @param $field
 * @param null $fieldValue
 * @return string
 */
function installAjaxCheckboxDisplay($id, $table, $field, $fieldValue = null)
{
    $lineId = $field.$id;
    $lineId = str_replace('.', '', $lineId); // fix JS bug (in admin layout)
    $data = 'data-table="' . $table . '" 
			data-field="'.$field.'" 
			data-line-id="' . $lineId . '" 
			data-id="' . $id . '" 
			data-value="' . $fieldValue . '"';

    // Decoration
    if ($fieldValue == 1) {
        $html = '<i id="' . $lineId . '" class="admin-single-icon fa fa-toggle-on" aria-hidden="true"></i>';
    } else {
        $html = '<i id="' . $lineId . '" class="admin-single-icon fa fa-toggle-off" aria-hidden="true"></i>';
    }
    $html = '<a href="" class="ajax-request" ' . $data . '>' . $html . '</a>';

    // Install country's decoration
    $html .= ' &nbsp;';
    if ($fieldValue == 1) {
        $html .= '<a href="" id="install' . $id . '" class="ajax-request btn btn-xs btn-success" ' . $data . '>';
		$html .= '<i class="fa fa-download"></i> ' . trans('admin::messages.Installed');
		$html .= '</a>';
    } else {
        $html .= '<a href="" id="install' . $id . '" class="ajax-request btn btn-xs btn-default" ' . $data . '>';
		$html .= '<i class="fa fa-download"></i> ' . trans('admin::messages.Install');
		$html .= '</a>';
    }

    return $html;
}

/**
 * Generate the Post's link from the Admin panel
 *
 * @param $post
 * @return string
 */
function getPostUrl($post)
{
    $out = '';
	
	if (isset($post->latestPayment) && !empty($post->latestPayment)) {
		if (isset($post->latestPayment->package) && !empty($post->latestPayment->package)) {
			$info = '';
			if ($post->featured == 1) {
                $class = 'text-success';
            } else {
                $class = 'text-danger';
                $info = ' (' . trans('admin::messages.Expired') . ')';
            }
            $out = ' <i class="fa fa-check-circle ' . $class . ' tooltipHere"
                    title="" data-placement="bottom" data-toggle="tooltip"
                    type="button" data-original-title="' . $post->latestPayment->package->short_name . $info . '">
                </i>';
        }
    }
	
    // Get URL
    $url = localUrl($post->country_code, UrlGen::postPath($post));
    $out = '<a href="' . $url . '" target="_blank">' . \Illuminate\Support\Str::limit($post->title, 60) . '</a>' . $out;

    return $out;
}

/**
 * @param $entry
 * @param bool $withLink
 * @return string
 */
function getCountryFlag($entry, $withLink = false)
{
	$out = '';
	
	if (isset($entry->country_code)) {
		$countryName = (isset($entry->country) && isset($entry->country->asciiname)) ? $entry->country->asciiname : null;
		$countryName = (empty($countryName) && isset($entry->country) && isset($entry->country->name)) ? $entry->country->name : $countryName;
		$countryName = (!empty($countryName)) ? $countryName : $entry->country_code;
		
		$iconPath = 'images/flags/16/' . strtolower($entry->country_code) . '.png';
		if (file_exists(public_path($iconPath))) {
			$out = '';
			$out .= ($withLink) ? '<a href="' . localUrl($entry->country_code, '', true) . '" target="_blank">' : '';
			$out .= '<img src="' . url($iconPath) . getPictureVersion() . '" data-toggle="tooltip" title="' . $countryName . '">';
			$out .= ($withLink) ? '</a>' : '';
			$out .= ' ';
		} else {
			$out .= $entry->country_code . ' ';
		}
	}
	
	return $out;
}

/**
 * Check if the Post is verified
 *
 * @param $post
 * @return bool
 */
function isVerifiedPost($post)
{
    if (!isset($post->verified_email) || !isset($post->verified_phone) || !isset($post->reviewed)) {
        return false;
    }
    
    if (config('settings.single.posts_review_activation')) {
        $verified = ($post->verified_email == 1 && $post->verified_phone == 1 && $post->reviewed == 1) ? true : false;
    } else {
        $verified = ($post->verified_email == 1 && $post->verified_phone == 1) ? true : false;
    }
    
    return $verified;
}

/**
 * Check if the User is verified
 *
 * @param $user
 * @return bool
 */
function isVerifiedUser($user)
{
    if (!isset($user->verified_email) || !isset($user->verified_phone)) {
        return false;
    }
    
    return ($user->verified_email == 1 && $user->verified_phone == 1) ? true : false;
}

/**
 * @return bool
 */
function userHasSuperAdminPermissions()
{
	if (auth()->check()) {
		$permissions = \App\Models\Permission::getSuperAdminPermissions();
		
		// Remove the standard admin permission
		$permissions = collect($permissions)->reject(function ($value, $key) {
			return $value == 'access-dashboard';
		})->toArray();
		
		// Check if user has the super admin permissions
		if (auth()->user()->can($permissions)) {
			return true;
		}
	}
	
	return false;
}

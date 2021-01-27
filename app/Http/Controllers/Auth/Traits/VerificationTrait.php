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

use App\Helpers\UrlGen;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

trait VerificationTrait
{
	use EmailVerificationTrait, PhoneVerificationTrait, RecognizedUserActionsTrait;
	
	public $entitiesRefs = [
		'user' => [
			'slug'      => 'user',
			'namespace' => '\\App\Models\User',
			'name'      => 'name',
			'scopes'    => [
				\App\Models\Scopes\VerifiedScope::class,
			],
		],
		'post'   => [
			'slug'      => 'post',
			'namespace' => '\\App\Models\Post',
			'name'      => 'contact_name',
			'scopes'    => [
				\App\Models\Scopes\VerifiedScope::class,
				\App\Models\Scopes\ReviewedScope::class,
			],
		],
	];
	
	/**
	 * URL: Verify User's Email Address or Phone Number
	 *
	 * @param $field
	 * @param null $token
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
	 */
	public function verification($field, $token = null)
	{
		// Keep Success Message If exists
		if (session()->has('message')) {
			session()->keep(['message']);
		}
		
		// Get Entity
		$entityRef = $this->getEntityRef(getSegment(2));
		if (empty($entityRef)) {
			abort(404, t("Entity ID not found."));
		}
		
		// Get Field Label
		$fieldLabel = t('Email Address');
		if ($field == 'phone') {
			$fieldLabel = t('Phone Number');
		}
		
		// Show Token Form
		if (empty($token) && !request()->filled('_token')) {
			return view('token');
		}
		
		// Token Form Submission
		if (request()->filled('_token')) {
			// Form validation
			$validator = Validator::make(request()->all(), ['code' => 'required']);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}
			
			if (request()->filled('code')) {
				return redirect(config('app.locale') . '/verify/' . $entityRef['slug'] . '/' . $field . '/' . request()->get('code'));
			}
		}
		
		// Get Entity by Token
        $model = $entityRef['namespace'];
		$entity = $model::withoutGlobalScopes($entityRef['scopes'])->where($field . '_token', $token)->first();
		
		if (!empty($entity)) {
			if ($entity->{'verified_' . $field} != 1) {
				// Verified
				$entity->{'verified_' . $field} = 1;
				$entity->save();
				
				$message = t("Congratulation :name ! Your :field has been verified.", ['name' => $entity->{$entityRef['name']}, 'field' => $fieldLabel]);
				flash($message)->success();
				
				// Remove Notification Trigger
				if (session()->has('emailOrPhoneChanged')) {
					session()->forget('emailOrPhoneChanged');
				}
				if (session()->has('verificationEmailSent')) {
					session()->forget('verificationEmailSent');
				}
				if (session()->has('verificationSmsSent')) {
					session()->forget('verificationSmsSent');
				}
			} else {
				$message = t("Your :field is already verified.", ['field' => $fieldLabel]);
				flash($message)->error();
			}
			
			// Get Next URL
			// Get Default next URL
			$nextUrl = config('app.locale') . '/?from=verification';
			
			// Is User Entity
			if ($entityRef['slug'] == 'user') {
				// Match User's ads (posted as Guest)
				$this->findAndMatchPostsToUser($entity);
				
				// Get User creation next URL
				// Login the User
				if (Auth::loginUsingId($entity->id)) {
					$nextUrl = config('app.locale') . '/account';
				} else {
					if (session()->has('userNextUrl')) {
						$nextUrl = session('userNextUrl');
					} else {
						$nextUrl = config('app.locale') . '/' . trans('routes.login');
					}
				}
			}
			
			// Is Post Entity
			if ($entityRef['slug'] == 'post') {
				// Match User's Posts (posted as Guest) & User's data (if missed)
				$this->findAndMatchUserToPost($entity);
				
				// Get Post creation next URL
				if (session()->has('itemNextUrl')) {
					$nextUrl = session('itemNextUrl');
                    if (Str::contains($nextUrl, 'create') && !session()->has('tmpPostId')) {
                        $nextUrl = UrlGen::postUri($entity);
                    }
				} else {
					$nextUrl = UrlGen::postUri($entity);
				}
			}
			
			// Remove Next URL session
			if (session()->has('userNextUrl')) {
				session()->forget('userNextUrl');
			}
			if (session()->has('itemNextUrl')) {
				session()->forget('itemNextUrl');
			}
			
			// Redirection
			return redirect($nextUrl);
		} else {
			$message = t("Your :field verification has failed.", ['field' => $fieldLabel]);
			flash($message)->error();
			
			return view('token');
		}
	}
	
	/**
	 * @param null $entityRefId
	 * @return null
	 */
	public function getEntityRef($entityRefId = null)
	{
		if (empty($entityRefId)) {
			if (
				Str::contains(Route::currentRouteAction(), 'Auth\RegisterController') ||
				Str::contains(Route::currentRouteAction(), 'Account\EditController') ||
				Str::contains(Route::currentRouteAction(), 'Admin\UserController')
			) {
				$entityRefId = 'user';
			}
			
			if (
				Str::contains(Route::currentRouteAction(), 'Post\CreateOrEdit\MultiSteps\CreateController') ||
				Str::contains(Route::currentRouteAction(), 'Post\CreateOrEdit\MultiSteps\EditController') ||
				Str::contains(Route::currentRouteAction(), 'Post\CreateOrEdit\SingleStep\CreateController') ||
				Str::contains(Route::currentRouteAction(), 'Post\CreateOrEdit\SingleStep\EditController') ||
				Str::contains(Route::currentRouteAction(), 'Admin\PostController')
			) {
				$entityRefId = 'post';
			}
		}
		
		// Check if Entity exists
		if (!isset($this->entitiesRefs[$entityRefId])) {
			return null;
		}
		
		// Get Entity
		$entityRef = $this->entitiesRefs[$entityRefId];
		
		return $entityRef;
	}
}

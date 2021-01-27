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

use App\Events\PostWasVisited;
use App\Helpers\ArrayHelper;
use App\Helpers\UrlGen;
use App\Http\Requests\SendMessageRequest;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Message;
use App\Models\Package;
use App\Http\Controllers\FrontController;
use App\Models\Resume;
use App\Models\User;
use App\Models\Scopes\VerifiedScope;
use App\Models\Scopes\ReviewedScope;
use App\Notifications\EmployerContacted;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Event;
use Torann\LaravelMetaTags\Facades\MetaTag;
use App\Helpers\Localization\Helpers\Country as CountryLocalizationHelper;
use App\Helpers\Localization\Country as CountryLocalization;

class DetailsController extends FrontController
{
	/**
	 * Post expire time (in months)
	 *
	 * @var int
	 */
	public $expireTime = 24;
	
	/**
	 * DetailsController constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		// From Laravel 5.3.4 or above
		$this->middleware(function ($request, $next) {
			$this->commonQueries();
			
			return $next($request);
		});
	}
	
	/**
	 * Common Queries
	 */
	public function commonQueries()
	{
		// Check Country URL for SEO
		$countries = CountryLocalizationHelper::transAll(CountryLocalization::getCountries());
		view()->share('countries', $countries);
		
		// Count Packages
		$countPackages = Package::trans()->applyCurrency()->count();
		view()->share('countPackages', $countPackages);
		
		// Count Payment Methods
		view()->share('countPaymentMethods', $this->countPaymentMethods);
	}
	
	/**
	 * Show Post's Details.
	 *
	 * @param $postId
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function index($postId)
	{
		$data = [];
		
		// Get and Check the Controller's Method Parameters
		$parameters = request()->route()->parameters();
		
		// Show 404 error if the Post's ID is not numeric
		if (!isset($parameters['id']) || empty($parameters['id']) || !is_numeric($parameters['id'])) {
			abort(404);
		}
		
		// Set the Parameters
		$postId = $parameters['id'];
		if (isset($parameters['slug'])) {
			$slug = $parameters['slug'];
		}
		
		// GET POST'S DETAILS
		if (auth()->check()) {
			// Get post's details even if it's not activated and reviewed
			$cacheId = 'post.withoutGlobalScopes.with.city.pictures.' . $postId . '.' . config('app.locale');
			$post = Cache::remember($cacheId, $this->cacheExpiration, function () use ($postId) {
				return Post::withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
					->withCountryFix()
					->unarchived()
					->where('id', $postId)
					->with([
						'category' => function ($builder) { $builder->with(['parent']); },
						'city',
						'latestPayment' => function ($builder) { $builder->with(['package']); },
					])
					->first();
			});
			
			// If the logged user is not an admin user...
			if (!auth()->user()->can(Permission::getStaffPermissions())) {
				// Then don't get post that are not from the user
				if (!empty($post) && $post->user_id != auth()->user()->id) {
					$cacheId = 'post.with.city.pictures.' . $postId . '.' . config('app.locale');
					$post = Cache::remember($cacheId, $this->cacheExpiration, function () use ($postId) {
						return Post::withCountryFix()
							->unarchived()
							->where('id', $postId)
							->with([
								'category' => function ($builder) { $builder->with(['parent']); },
								'city',
								'latestPayment' => function ($builder) { $builder->with(['package']); },
							])
							->first();
					});
				}
			}
			
			// Get the User's Resumes
			$limit = config('larapen.core.selectResumeInto', 5);
			$cacheId = 'resumes.take.' . $limit . '.where.user.' . auth()->user()->id;
			$resumes = Cache::remember($cacheId, $this->cacheExpiration, function () use ($limit) {
				return Resume::where('user_id', auth()->user()->id)->take($limit)->orderByDesc('id')->get();
			});
			view()->share('resumes', $resumes);
			
			// Get the User's latest Resume
			if ($resumes->has(0)) {
				$lastResume = $resumes->get(0);
				view()->share('lastResume', $lastResume);
			}
		} else {
			$cacheId = 'post.with.city.pictures.' . $postId . '.' . config('app.locale');
			$post = Cache::remember($cacheId, $this->cacheExpiration, function () use ($postId) {
				return Post::withCountryFix()
					->unarchived()
					->where('id', $postId)
					->with([
						'category' => function ($builder) { $builder->with(['parent']); },
						'city',
						'latestPayment' => function ($builder) { $builder->with(['package']); },
					])
					->first();
			});
		}
		
		// Preview the Post after activation
		if (request()->filled('preview') && request()->get('preview') == 1) {
			// Get post's details even if it's not activated and reviewed
			$post = Post::withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
				->withCountryFix()
				->where('id', $postId)
				->with([
					'category' => function ($builder) { $builder->with(['parent']); },
					'city',
					'latestPayment' => function ($builder) { $builder->with(['package']); },
				])
				->first();
		}
		
		// Post not found
		if (empty($post) || empty($post->category) || empty($post->postType) || empty($post->city)) {
			abort(404, t('Post not found'));
		}
		
		// Share post's details
		view()->share('post', $post);
		
		// Get possible post's Author (User)
		$user = null;
		if (isset($post->user_id) && !empty($post->user_id)) {
			$user = User::find($post->user_id);
		}
		view()->share('user', $user);
		
		// Get ad's user decision about comments activation
		$commentsAreDisabledByUser = false;
		// Get possible ad's user
		if (isset($user) && !empty($user)) {
			if ($user->disable_comments == 1) {
				$commentsAreDisabledByUser = true;
			}
		}
		view()->share('commentsAreDisabledByUser', $commentsAreDisabledByUser);
		
		// Increment Post visits counter
		Event::dispatch(new PostWasVisited($post));
		
		// GET SIMILAR POSTS
		if (config('settings.single.similar_posts') == '1') {
			$cacheId = 'posts.similar.category.' . $post->category->tid . '.post.' . $post->id;
			$posts = Cache::remember($cacheId, $this->cacheExpiration, function () use ($post) {
				return $post->getSimilarByCategory();
			});
			
			// Featured Area Data
			$featured = [
				'title' => t('Similar Jobs'),
				'link'  => qsurl(trans('routes.v-search', ['countryCode' => config('country.icode')]), array_merge(request()->except('c'), ['c' => $post->category->tid])),
				'posts' => $posts,
			];
			$data['featured'] = (count($posts) > 0) ? ArrayHelper::toObject($featured) : null;
		} else if (config('settings.single.similar_posts') == '2') {
			$distance = 50; // km OR miles
			
			$cacheId = 'posts.similar.city.' . $post->city->id . '.post.' . $post->id;
			$posts = Cache::remember($cacheId, $this->cacheExpiration, function () use ($post, $distance) {
				return $post->getSimilarByLocation($distance);
			});
			
			// Featured Area Data
			$featured = [
				'title' => t('More jobs at :distance :unit around :city', [
					'distance' => $distance,
					'unit'     => getDistanceUnit(config('country.code')),
					'city'     => $post->city->name,
				]),
				'link'  => qsurl(trans('routes.v-search', ['countryCode' => config('country.icode')]), array_merge(request()->except(['l', 'location']), ['l' => $post->city->id])),
				'posts' => $posts,
			];
			$data['featured'] = (count($posts) > 0) ? ArrayHelper::toObject($featured) : null;
		}
		
		// SEO
		$title = $post->title . ', ' . $post->city->name;
		$description = Str::limit(str_strip(strip_tags($post->description)), 200);
		
		// Meta Tags
		MetaTag::set('title', $title);
		MetaTag::set('description', $description);
		if (!empty($post->tags)) {
			MetaTag::set('keywords', str_replace(',', ', ', $post->tags));
		}
		
		// Open Graph
		$this->og->title($title)
			->description($description)
			->type('article');
		if (isset($post->logo) && !empty($post->logo)) {
			if ($this->og->has('image')) {
				$this->og->forget('image')->forget('image:width')->forget('image:height');
			}
			$this->og->image(imgUrl($post->logo, 'big'), [
				'width'  => 600,
				'height' => 600,
			]);
		}
		view()->share('og', $this->og);
		
		/*
		// Expiration Info
		$today = Date::now(config('timezone.id'));
		if ($today->gt($post->created_at->addMonths($this->expireTime))) {
			flash(t("Warning! This ad has expired. The product or service is not more available (may be)"))->error();
		}
		*/
		
		// View
		return view('post.details', $data);
	}
	
	/**
	 * Contact the Post's Author
	 *
	 * @param $postId
	 * @param SendMessageRequest $request
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function sendMessage($postId, SendMessageRequest $request)
	{
		$this->middleware('auth', ['only' => ['sendMessage']]);
		
		// Get the Post
		$post = Post::unarchived()->findOrFail($postId);
		
		// Get or Create Resume
		if ($request->filled('resume_id') && !empty($request->input('resume_id'))) {
			// Get the User's Resume
			$resume = Resume::where('id', $request->input('resume_id'))->where('user_id', auth()->user()->id)->first();
		} else {
			// Get form Requests
			$resumeInfo = $request->input('resume');
			$resumeInfo += ['active' => 1];
			if (!isset($resumeInfo['filename'])) {
				$resumeInfo += ['filename' => null];
			}
			if (!isset($resumeInfo['country_code']) || empty($resumeInfo['country_code'])) {
				$resumeInfo += ['country_code' => config('country.code')];
			}
			
			// Logged Users
			if (auth()->check()) {
				if (!isset($resumeInfo['user_id']) || empty($resumeInfo['user_id'])) {
					$resumeInfo += ['user_id' => auth()->user()->id];
				}
				
				// Store the User's Resume
				$resume = new Resume($resumeInfo);
				$resume->save();
				
				// Save the Resume's file
				if ($request->hasFile('resume.filename')) {
					$resume->filename = $request->file('resume.filename');
					$resume->save();
				}
			} else {
				// Guest Users
				$resume = ArrayHelper::toObject($resumeInfo);
			}
		}
		
		// Return error if resume is not set
		if (empty($resume)) {
			flash(t("Please select a resume or 'New Resume' to add one."))->error();
			
			return back()->withInput($request->except('resume.filename'));
		}
		
		// New Message
		$message = new Message();
		$input = $request->only($message->getFillable());
		foreach ($input as $key => $value) {
			$message->{$key} = $value;
		}
		
		$message->post_id = $post->id;
		$message->from_user_id = auth()->check() ? auth()->user()->id : 0;
		$message->to_user_id = $post->user_id;
		$message->to_name = $post->contact_name;
		$message->to_email = $post->email;
		$message->to_phone = $post->phone;
		$message->subject = $post->title;
		
		$message->message = $request->input('message')
			. '<br><br>'
			. t('Related to the ad')
			. ': <a href="' . UrlGen::post($post) . '">' . t('Click here to see') . '</a>';
		
		$message->filename = $resume->filename;
		
		// Save
		$message->save();
		
		// Save the Resume file (for Guest Users)
		if (!auth()->check()) {
			if ($request->hasFile('resume.filename')) {
				$message->filename = $request->file('resume.filename');
				$message->save();
			}
		}
		
		// Send a message to publisher
		try {
			if (!isDemo()) {
				$post->notify(new EmployerContacted($post, $message));
			}
			
			$msg = t("Your message has sent successfully to :contact_name.", ['contact_name' => $post->contact_name]);
			flash($msg)->success();
		} catch (\Exception $e) {
			flash($e->getMessage())->error();
		}
		
		return redirect(UrlGen::postUri($post));
	}
}

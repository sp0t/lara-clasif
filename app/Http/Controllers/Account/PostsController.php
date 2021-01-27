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

namespace App\Http\Controllers\Account;

use App\Helpers\ArrayHelper;
use App\Helpers\UrlGen;
use App\Http\Controllers\Search\Traits\PreSearchTrait;
use App\Models\Post;
use App\Models\Category;
use App\Models\SavedPost;
use App\Models\SavedSearch;
use App\Models\Scopes\ReviewedScope;
use App\Models\Scopes\VerifiedScope;
use App\Notifications\PostArchived;
use App\Notifications\PostDeleted;
use App\Notifications\PostRepublished;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Jenssegers\Date\Date;
use Torann\LaravelMetaTags\Facades\MetaTag;

class PostsController extends AccountBaseController
{
	use PreSearchTrait;
	
	private $perPage = 12;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->perPage = (is_numeric(config('settings.listing.items_per_page'))) ? config('settings.listing.items_per_page') : $this->perPage;
	}
	
	/**
	 * @param $pagePath
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
	 */
	public function getPage($pagePath)
	{
		view()->share('pagePath', $pagePath);
		
		switch ($pagePath) {
			case 'my-posts':
				return $this->getMyPosts();
				break;
			case 'archived':
				return $this->getArchivedPosts($pagePath);
				break;
			case 'favourite':
				return $this->getFavouritePosts();
				break;
			case 'pending-approval':
				return $this->getPendingApprovalPosts();
				break;
			default:
				abort(404);
		}
	}
	
	/**
	 * @param null $postId
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
	 */
	public function getMyPosts($postId = null)
	{
		$pagePath = 'my-posts';
		
		// If "offline" button is clicked
		if (Str::contains(url()->current(), $pagePath . '/' . $postId . '/offline')) {
			$post = null;
			if (is_numeric($postId) && $postId > 0) {
				$post = Post::where('user_id', auth()->user()->id)->where('id', $postId)->first();
				if (empty($post)) {
					abort(404, t('Post not found'));
				}
				
				if ($post->archived != 1) {
					$post->archived          = 1;
					$post->archived_at       = Date::now();
					$post->archived_manually = 1;
					$post->save();
					
					if ($post->archived == 1) {
						$archivedPostsExpiration = config('settings.listing.manually_archived_posts_expiration', 180);
						
						$message = t('offline_putting_message', [
							'postId'  => $postId,
							'dateDel' => $post->archived_at
								->addDays($archivedPostsExpiration)
								->formatLocalized(config('settings.app.default_date_format')),
						]);
						
						flash($message)->success();
						
						// Send Confirmation Email or SMS
						if (config('settings.mail.confirmation') == 1) {
							try {
								$post->notify(new PostArchived($post, $archivedPostsExpiration));
							} catch (\Exception $e) {
								flash($e->getMessage())->error();
							}
						}
					} else {
						flash(t("The putting offline has failed. Please try again."))->error();
					}
				} else {
					flash(t("The ad is already offline."))->error();
				}
			} else {
				flash(t("The putting offline has failed. Please try again."))->error();
			}
			
			return back();
		}
		
		$data          = [];
		$data['posts'] = $this->myPosts->paginate($this->perPage);
		
		// Meta Tags
		MetaTag::set('title', t('My ads'));
		MetaTag::set('description', t('My ads on :app_name', ['app_name' => config('settings.app.app_name')]));
		
		view()->share('pagePath', $pagePath);
		
		return view('account.posts', $data);
	}
	
	/**
	 * @param null $postId
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
	 */
	public function getArchivedPosts($postId = null)
	{
		$pagePath = 'archived';
		
		// If "repost" button is clicked
		if (Str::contains(url()->current(), $pagePath . '/' . $postId . '/repost')) {
			$post = null;
			if (is_numeric($postId) && $postId > 0) {
				$post = Post::where('user_id', auth()->user()->id)->where('id', $postId)->first();
				if (empty($post)) {
					abort(404, t('Post not found'));
				}
				
				$postUrl = UrlGen::post($post);
				
				if ($post->archived != 0) {
					$post->archived              = 0;
					$post->archived_at           = null;
					$post->deletion_mail_sent_at = null;
					if ($post->archived_manually != 1) {
						$post->created_at        = Date::now();
						$post->archived_manually = 0;
					}
					$post->save();
					
					if ($post->archived == 0) {
						flash(t("The repost has done successfully."))->success();
						
						// Send Confirmation Email or SMS
						if (config('settings.mail.confirmation') == 1) {
							try {
								$post->notify(new PostRepublished($post));
							} catch (\Exception $e) {
								flash($e->getMessage())->error();
							}
						}
					} else {
						flash(t("The repost has failed. Please try again."))->error();
					}
				} else {
					flash(t("The ad is already online."))->error();
				}
				
				return redirect($postUrl);
			} else {
				flash(t("The repost has failed. Please try again."))->error();
			}
			
			return redirect(config('app.locale') . '/account/' . $pagePath);
		}
		
		$data          = [];
		$data['posts'] = $this->archivedPosts->paginate($this->perPage);
		
		// Meta Tags
		MetaTag::set('title', t('My archived ads'));
		MetaTag::set('description', t('My archived ads on :app_name', ['app_name' => config('settings.app.app_name')]));
		
		view()->share('pagePath', $pagePath);
		
		return view('account.posts', $data);
	}
	
	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function getFavouritePosts()
	{
		$data          = [];
		$data['posts'] = $this->favouritePosts->paginate($this->perPage);
		
		// Meta Tags
		MetaTag::set('title', t('My favourite jobs'));
		MetaTag::set('description', t('My favourite jobs on :app_name', ['app_name' => config('settings.app.app_name')]));
		
		return view('account.posts', $data);
	}
	
	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function getPendingApprovalPosts()
	{
		$data          = [];
		$data['posts'] = $this->pendingPosts->paginate($this->perPage);
		
		// Meta Tags
		MetaTag::set('title', t('My pending approval ads'));
		MetaTag::set('description', t('My pending approval ads on :app_name', ['app_name' => config('settings.app.app_name')]));
		
		return view('account.posts', $data);
	}
	
	/**
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function getSavedSearch(HttpRequest $request)
	{
		$data = [];
		
		// Get QueryString
		$tmp         = parse_url(url(request()->getRequestUri()));
		$queryString = (isset($tmp['query']) ? $tmp['query'] : 'false');
		$queryString = preg_replace('|\&pag[^=]*=[0-9]*|i', '', $queryString);
		
		// CATEGORIES COLLECTION
		$cats = Category::trans()->orderBy('lft')->get();
		$cats = collect($cats)->keyBy('translation_of');
		view()->share('cats', $cats);
		
		// Search
		$savedSearch = SavedSearch::currentCountry()
			->where('user_id', auth()->user()->id)
			->orderBy('created_at', 'DESC')
			->simplePaginate($this->perPage, ['*'], 'pag');
		
		if (collect($savedSearch->getCollection())->keyBy('query')->keys()->contains($queryString)) {
			parse_str($queryString, $queryArray);
			
			// QueryString vars
			$cityId    = isset($queryArray['l']) ? $queryArray['l'] : null;
			$location  = isset($queryArray['location']) ? $queryArray['location'] : null;
			$adminName = (isset($queryArray['r']) && !isset($queryArray['l'])) ? $queryArray['r'] : null;
			
			// Pre-Search
			$preSearch = [
				'city'  => $this->getCity($cityId, $location),
				'admin' => $this->getAdmin($adminName),
			];
			
			if ($savedSearch->getCollection()->count() > 0) {
				// Search
				$searchClass = config('larapen.core.searchClass');
				$search      = new $searchClass($preSearch);
				$data        = $search->fetch();
			}
		}
		$data['savedSearch'] = $savedSearch;
		
		// Meta Tags
		MetaTag::set('title', t('My saved search'));
		MetaTag::set('description', t('My saved search on :app_name', ['app_name' => config('settings.app.app_name')]));
		
		view()->share('pagePath', 'saved-search');
		
		return view('account.saved-search', $data);
	}
	
	/**
	 * @param $pagePath
	 * @param null $id
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function destroy($pagePath, $id = null)
	{
		// Get Entries ID
		$ids = [];
		if (request()->filled('entries')) {
			$ids = request()->input('entries');
		} else {
			if (!is_numeric($id) && $id <= 0) {
				$ids = [];
			} else {
				$ids[] = $id;
			}
		}
		
		// Delete
		$nb = 0;
		if ($pagePath == 'favourite') {
			$savedPosts = SavedPost::where('user_id', auth()->user()->id)->whereIn('post_id', $ids);
			if ($savedPosts->count() > 0) {
				$nb = $savedPosts->delete();
			}
		} else if ($pagePath == 'saved-search') {
			$nb = SavedSearch::destroy($ids);
		} else {
			foreach ($ids as $item) {
				$post = Post::withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
					->where('user_id', auth()->user()->id)
					->where('id', $item)
					->first();
				if (!empty($post)) {
					$tmpPost = ArrayHelper::toObject($post->toArray());
					
					// Delete Entry
					$nb = $post->delete();
					
					// Send an Email confirmation
					if (!empty($tmpPost->email)) {
						if (config('settings.mail.confirmation') == 1) {
							try {
								Notification::route('mail', $tmpPost->email)->notify(new PostDeleted($tmpPost));
							} catch (\Exception $e) {
								flash($e->getMessage())->error();
							}
						}
					}
				}
			}
		}
		
		// Confirmation
		if ($nb == 0) {
			flash(t("No deletion is done. Please try again."))->error();
		} else {
			$count = count($ids);
			if ($count > 1) {
				$message = t("x :entities has been deleted successfully.", ['entities' => t('ads'), 'count' => $count]);
			} else {
				$message = t("1 :entity has been deleted successfully.", ['entity' => t('ad')]);
			}
			flash($message)->success();
		}
		
		return redirect(config('app.locale') . '/account/' . $pagePath);
	}
}

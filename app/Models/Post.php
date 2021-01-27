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

namespace App\Models;

use App\Helpers\DBTool;
use App\Helpers\Files\Storage\StorageDisk;
use App\Helpers\RemoveFromString;
use App\Helpers\UrlGen;
use App\Models\Scopes\FromActivatedCategoryScope;
use App\Models\Scopes\LocalizedScope;
use App\Models\Scopes\VerifiedScope;
use App\Models\Scopes\ReviewedScope;
use App\Models\Traits\CountryTrait;
use App\Observer\PostObserver;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Jenssegers\Date\Date;
use Larapen\Admin\app\Models\Crud;
use Larapen\LaravelDistance\Distance;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;

class Post extends BaseModel implements Feedable
{
	use Crud, CountryTrait, Notifiable;
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'posts';
	
	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';
	protected $appends    = ['created_at_ta'];
	
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var boolean
	 */
	public $timestamps = true;
	
	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = ['id'];
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'country_code',
		'user_id',
		'company_id',
		'company_name',
		'logo',
		'company_description',
		'category_id',
		'post_type_id',
		'title',
		'description',
		'tags',
		'salary_min',
		'salary_max',
		'salary_type_id',
		'negotiable',
		'start_date',
		'application_url',
		'contact_name',
		'email',
		'phone',
		'phone_hidden',
		'city_id',
		'lat',
		'lon',
		'address',
		'ip_addr',
		'visits',
		'tmp_token',
		'email_token',
		'phone_token',
		'verified_email',
		'verified_phone',
		'reviewed',
		'featured',
		'archived',
		'archived_at',
		'deletion_mail_sent_at',
		'partner',
		'created_at',
	];
	
	/**
	 * The attributes that should be hidden for arrays
	 *
	 * @var array
	 */
	// protected $hidden = [];
	
	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['created_at', 'updated_at', 'deleted_at'];
	
	/*
	|--------------------------------------------------------------------------
	| FUNCTIONS
	|--------------------------------------------------------------------------
	*/
	protected static function boot()
	{
		parent::boot();
		
		Post::observe(PostObserver::class);
		
		static::addGlobalScope(new FromActivatedCategoryScope());
		static::addGlobalScope(new VerifiedScope());
		static::addGlobalScope(new ReviewedScope());
		static::addGlobalScope(new LocalizedScope());
	}
	
	public function routeNotificationForMail()
	{
		return $this->email;
	}
	
	public function routeNotificationForNexmo()
	{
		$phone = phoneFormatInt($this->phone, $this->country_code);
		$phone = setPhoneSign($phone, 'nexmo');
		
		return $phone;
	}
	
	public function routeNotificationForTwilio()
	{
		$phone = phoneFormatInt($this->phone, $this->country_code);
		$phone = setPhoneSign($phone, 'twilio');
		
		return $phone;
	}
	
	public static function getFeedItems()
	{
		$postsPerPage = (int)config('settings.listing.items_per_page', 50);
		
		$posts = Post::reviewed()->unarchived();
		
		if (request()->has('d') || config('plugins.domainmapping.installed')) {
			$countryCode = config('country.code');
			if (!config('plugins.domainmapping.installed')) {
				if (request()->has('d')) {
					$countryCode = request()->input('d');
				}
			}
			$posts = $posts->where('country_code', $countryCode);
		}
		
		$posts = $posts->take($postsPerPage)->orderByDesc('id')->get();
		
		return $posts;
	}
	
	public function toFeedItem()
	{
		$title = $this->title;
		$title .= (isset($this->city) && !empty($this->city)) ? ' - ' . $this->city->name : '';
		$title .= (isset($this->country) && !empty($this->country)) ? ', ' . $this->country->name : '';
		// $summary = str_limit(str_strip(strip_tags($this->description)), 5000);
		$summary = transformDescription($this->description);
		$link    = UrlGen::postUri($this, config('app.locale'), true);
		
		return FeedItem::create()
			->id($link)
			->title($title)
			->summary($summary)
			->updated($this->updated_at)
			->link($link)
			->author($this->contact_name);
	}
	
	public function getTitleHtml()
	{
		$post = self::find($this->id);
		
		return getPostUrl($post);
	}
	
	public function getLogoHtml()
	{
		$style = ' style="width:auto; max-height:90px;"';
		
		// Get logo
		$out = '<img src="' . imgUrl($this->logo, 'small') . '" data-toggle="tooltip" title="' . $this->title . '"' . $style . '>';
		
		// Add link to the Ad
		$url = localUrl($this->country_code, UrlGen::postPath($this));
		$out = '<a href="' . $url . '" target="_blank">' . $out . '</a>';
		
		return $out;
	}
	
	public function getPictureHtml()
	{
		// Get ad URL
		$url = url(UrlGen::postUri($this));
		
		$style = ' style="width:auto; max-height:90px;"';
		// Get first picture
		if ($this->pictures->count() > 0) {
			foreach ($this->pictures as $picture) {
				$url = localUrl($picture->post->country_code, UrlGen::postPath($this));
				$out = '<img src="' . imgUrl($picture->filename, 'small') . '" data-toggle="tooltip" title="' . $this->title . '"' . $style . '>';
				break;
			}
		} else {
			// Default picture
			$out = '<img src="' . imgUrl(config('larapen.core.picture.default'), 'small') . '" data-toggle="tooltip" title="' . $this->title . '"' . $style . '>';
		}
		
		// Add link to the Ad
		$out = '<a href="' . $url . '" target="_blank">' . $out . '</a>';
		
		return $out;
	}
	
	public function getCompanyNameHtml()
	{
		$out = '';
		
		// Company Name
		$out .= $this->company_name;
		
		// User Name
		$out .= '<br>';
		$out .= '<small>';
		$out .= trans('admin::messages.By:') . ' ';
		if (isset($this->user) and !empty($this->user)) {
			$url     = admin_url('users/' . $this->user->getKey() . '/edit');
			$tooltip = ' data-toggle="tooltip" title="' . $this->user->name . '"';
			
			$out .= '<a href="' . $url . '"' . $tooltip . '>';
			$out .= $this->contact_name;
			$out .= '</a>';
		} else {
			$out .= $this->contact_name;
		}
		$out .= '</small>';
		
		return $out;
	}
	
	public function getCityHtml()
	{
		if (isset($this->city) and !empty($this->city)) {
			return '<a href="' . UrlGen::city($this->city) . '" target="_blank">' . $this->city->name . '</a>';
		} else {
			return $this->city_id;
		}
	}
	
	public function getReviewedHtml()
	{
		return ajaxCheckboxDisplay($this->{$this->primaryKey}, $this->getTable(), 'reviewed', $this->reviewed);
	}
	
	/*
	|--------------------------------------------------------------------------
	| QUERIES
	|--------------------------------------------------------------------------
	*/
	/**
	 * Get Latest or Sponsored Posts
	 *
	 * @param int $limit
	 * @param string $type (latest OR featured)
	 * @return array
	 */
	public static function getLatestOrSponsored($limit = 20, $type = 'latest')
	{
		// Select fields
		$select = [
			'tPost.id',
			'tPost.country_code',
			'tPost.category_id',
			'tPost.post_type_id',
			'tPost.company_id',
			'tPost.company_name',
			'tPost.logo',
			'tPost.title',
			'tPost.description',
			'tPost.salary_min',
			'tPost.salary_max',
			'tPost.salary_type_id',
			'tPost.city_id',
			'tPost.featured',
			'tPost.created_at',
			'tPost.reviewed',
			'tPost.verified_email',
			'tPost.verified_phone',
			'tPayment.package_id',
			'tPackage.lft',
		];
		
		// GroupBy fields
		$groupBy = [
			'tPost.id',
		];
		
		// If the MySQL strict mode is activated, ...
		// Append all the non-calculated fields available in the 'SELECT' in 'GROUP BY' to prevent error related to 'only_full_group_by'
		if (env('DB_MODE_STRICT')) {
			$groupBy = $select;
		}
		
		$paymentJoin        = '';
		$sponsoredCondition = '';
		$sponsoredOrder     = '';
		if ($type == 'sponsored') {
			$paymentJoin        .= 'INNER JOIN ' . DBTool::table('payments') . ' AS tPayment ON tPayment.post_id=tPost.id AND tPayment.active=1' . "\n";
			$paymentJoin        .= 'INNER JOIN ' . DBTool::table('packages') . ' AS tPackage ON tPackage.id=tPayment.package_id' . "\n";
			$sponsoredCondition = ' AND tPost.featured = 1';
			$sponsoredOrder     = 'tPackage.lft DESC, ';
		} else {
			// $paymentJoin .= 'LEFT JOIN ' . DBTool::table('payments') . ' AS py ON tPayment.post_id=tPost.id AND tPayment.active=1' . "\n";
			$latestPayment = "(SELECT MAX(id) lid, post_id FROM " . DBTool::table('payments') . " WHERE active=1 GROUP BY post_id) latestPayment";
			$paymentJoin   .= 'LEFT JOIN ' . $latestPayment . ' ON latestPayment.post_id = tPost.id AND tPost.featured=1' . "\n";
			$paymentJoin   .= 'LEFT JOIN ' . DBTool::table('payments') . ' AS tPayment ON tPayment.id=latestPayment.lid' . "\n";
			$paymentJoin   .= 'LEFT JOIN ' . DBTool::table('packages') . ' AS tPackage ON tPackage.id=tPayment.package_id' . "\n";
		}
		$reviewedCondition = '';
		if (config('settings.single.posts_review_activation')) {
			$reviewedCondition = ' AND tPost.reviewed = 1';
		}
		
		$sql      = 'SELECT DISTINCT ' . implode(',', $select) . '
                FROM ' . DBTool::table('posts') . ' AS tPost
                INNER JOIN ' . DBTool::table('categories') . ' AS c ON c.id=tPost.category_id AND c.active=1
                ' . $paymentJoin . '
                WHERE tPost.country_code = :countryCode
                	AND (tPost.verified_email=1 AND tPost.verified_phone=1)
                	AND tPost.archived!=1 ' . $reviewedCondition . $sponsoredCondition . '
                GROUP BY ' . implode(',', $groupBy) . '
                ORDER BY ' . $sponsoredOrder . 'tPost.created_at DESC
                LIMIT 0,' . (int)$limit;
		$bindings = [
			'countryCode' => config('country.code'),
		];
		
		// Get Posts
		$posts = DB::select(DB::raw($sql), $bindings);
		
		// Transform the collection attributes
		$posts = collect($posts)->map(function ($post) {
			$post->title = mb_ucfirst($post->title);
			
			return $post;
		})->toArray();
		
		return $posts;
	}
	
	/**
	 * Get similar Posts (Posts in the same Category)
	 *
	 * @param int $limit
	 * @return array
	 */
	public function getSimilarByCategory($limit = 20)
	{
		$posts = [];
		
		// Get the sub-categories of the current ad parent's category
		$similarCatIds = [];
		if (!empty($this->category)) {
			if ($this->category->tid == $this->category->parent_id) {
				$similarCatIds[] = $this->category->tid;
			} else {
				if (!empty($this->category->parent_id)) {
					$similarCatIds   = Category::trans()->where('parent_id', $this->category->parent_id)->get()
						->keyBy('tid')
						->keys()
						->toArray();
					$similarCatIds[] = (int)$this->category->parent_id;
				} else {
					$similarCatIds[] = (int)$this->category->tid;
				}
			}
		}
		
		// Get ads from same category
		if (!empty($similarCatIds)) {
			if (count($similarCatIds) == 1) {
				$similarPostSql = 'AND tPost.category_id=' . ((isset($similarCatIds[0])) ? (int)$similarCatIds[0] : 0) . ' ';
			} else {
				$similarPostSql = 'AND tPost.category_id IN (' . implode(',', $similarCatIds) . ') ';
			}
			$reviewedPostSql = '';
			if (config('settings.single.posts_review_activation')) {
				$reviewedPostSql = ' AND tPost.reviewed = 1';
			}
			$sql      = 'SELECT DISTINCT tPost.* ' . '
				FROM ' . DBTool::table('posts') . ' AS tPost
				LEFT JOIN ' . DBTool::table('users') . ' AS tUser ON tUser.id = tPost.user_id
				WHERE tPost.country_code = :countryCode ' . $similarPostSql . '
					AND (tPost.verified_email=1 AND tPost.verified_phone=1)
					AND tPost.archived!=1
					AND tPost.deleted_at IS NULL ' . $reviewedPostSql . '
					AND tPost.id != :currentPostId
					AND tUser.blocked != 1
					AND tUser.closed != 1
				ORDER BY tPost.id DESC
				LIMIT 0,' . (int)$limit;
			$bindings = [
				'countryCode'   => config('country.code'),
				'currentPostId' => $this->id,
			];
			
			// Get Posts
			try {
				$posts = DB::select(DB::raw($sql), $bindings);
			} catch (\Exception $e) {
				return $posts;
			}
		}
		
		// Append the Posts 'uri' attribute
		$posts = collect($posts)->map(function ($post) {
			$post->title = mb_ucfirst($post->title);
			
			return $post;
		})->toArray();
		
		// Randomize the Posts
		$posts = collect($posts)->shuffle()->toArray();
		
		return $posts;
	}
	
	/**
	 * Get Posts in the same Location
	 *
	 * @param $distance
	 * @param int $limit
	 * @return array
	 */
	public function getSimilarByLocation($distance, $limit = 20)
	{
		$posts = [];
		
		if (empty($this->city)) {
			return $posts;
		}
		
		if (!is_numeric($distance) || $distance < 0) {
			$distance = 0;
		}
		
		$bindings = [];
		
		// Get ads from same location (with radius)
		$reviewedPostSql = '';
		if (config('settings.single.posts_review_activation')) {
			$reviewedPostSql = ' AND tPost.reviewed = 1';
		}
		
		// Use the Cities Extended Searches
		config()->set('distance.functions.default', config('settings.listing.distance_calculation_formula'));
		config()->set('distance.countryCode', config('country.code'));
		
		// Init. Distance SQL vars
		$distance       = 50; // km OR miles
		$distSelectSql  = Distance::select('tPost.lon', 'tPost.lat', ':longitude', ':latitude');
		$distWhereSql   = '';
		$distHavingSql  = '';
		$distOrderBySql = '';
		
		if ($distSelectSql) {
			$distHavingSql  = Distance::having($distance);
			$distOrderBySql = Distance::orderBy('ASC');
			
			$bindings['longitude'] = $this->city->longitude;
			$bindings['latitude']  = $this->city->latitude;
		} else {
			$distWhereSql = 'tPost.city_id = ' . $this->city->id;
		}
		
		if (!empty($distSelectSql)) {
			$distSelectSql = ', ' . $distSelectSql;
		}
		if (!empty($distWhereSql)) {
			$distWhereSql = ' AND ' . $distWhereSql;
		}
		if (!empty($distHavingSql)) {
			$distHavingSql = 'HAVING ' . $distHavingSql;
		}
		if (!empty($distOrderBySql)) {
			$distOrderBySql = $distOrderBySql . ', ';
		}
		
		// SQL Query
		$sql = 'SELECT DISTINCT tPost.*' . $distSelectSql . '
			FROM ' . DBTool::table('posts') . ' AS tPost
			INNER JOIN ' . DBTool::table('categories') . ' AS tCategory ON tCategory.id=tPost.category_id AND tCategory.active=1
			LEFT JOIN ' . DBTool::table('users') . ' AS tUser ON tUser.id = tPost.user_id
			WHERE tPost.country_code = :countryCode
				AND (tPost.verified_email=1 AND tPost.verified_phone=1)
				AND tPost.archived!=1
				AND tPost.deleted_at IS NULL ' . $reviewedPostSql . '
				AND tPost.id != :currentPostId
				AND tUser.blocked != 1
				AND tUser.closed != 1
				' . $distWhereSql . '
			' . $distHavingSql . '
			ORDER BY ' . $distOrderBySql . 'tPost.id DESC
			LIMIT 0,' . (int)$limit;
		
		$bindings['countryCode']   = config('country.code');
		$bindings['currentPostId'] = $this->id;
		
		// Get Posts
		try {
			$posts = DB::select(DB::raw($sql), $bindings);
		} catch (\Exception $e) {
			return $posts;
		}
		
		// Append the Posts 'uri' attribute
		$posts = collect($posts)->map(function ($post) {
			$post->title = mb_ucfirst($post->title);
			
			return $post;
		})->toArray();
		
		// Randomize the Posts
		$posts = collect($posts)->shuffle()->toArray();
		
		return $posts;
	}
	
	/**
	 * Count sub-categories posts
	 * NOTE: Please don't cache this query since posts can be published by non-admin users.
	 *
	 * @return array|\Illuminate\Support\Collection
	 */
	public static function countByCategories()
	{
		$sql      = 'SELECT sc.id, c.parent_id, count(*) as total' . '
            FROM ' . DBTool::table('posts') . ' as a
            INNER JOIN ' . DBTool::table('categories') . ' as sc ON sc.id=a.category_id AND sc.active=1
            INNER JOIN ' . DBTool::table('categories') . ' as c ON c.id=sc.parent_id AND c.active=1
            WHERE a.country_code = :countryCode AND (a.verified_email=1 AND a.verified_phone=1) AND a.archived!=1 AND a.deleted_at IS NULL
            GROUP BY sc.id';
		$bindings = [
			'countryCode' => config('country.code'),
		];
		$cats     = DB::select(DB::raw($sql), $bindings);
		$cats     = collect($cats)->keyBy('id');
		
		return $cats;
	}
	
	/**
	 * Count parent categories posts
	 * NOTE: Please don't cache this query since posts can be published by non-admin users.
	 *
	 * @return array|\Illuminate\Support\Collection
	 */
	public static function countByParentCategories()
	{
		$sql1 = 'SELECT c.id as id, count(*) as total' . '
            FROM ' . DBTool::table('posts') . ' as a
            INNER JOIN ' . DBTool::table('categories') . ' as c ON c.id=a.category_id AND c.active=1
            WHERE a.country_code = :countryCode AND (a.verified_email=1 AND a.verified_phone=1) AND a.archived!=1 AND a.deleted_at IS NULL
            GROUP BY c.id';
		
		$sql2 = 'SELECT c.id as id, count(*) as total' . '
            FROM ' . DBTool::table('posts') . ' as a
            INNER JOIN ' . DBTool::table('categories') . ' as sc ON sc.id=a.category_id AND sc.active=1
            INNER JOIN ' . DBTool::table('categories') . ' as c ON c.id=sc.parent_id AND c.active=1
            WHERE a.country_code = :countryCode AND (a.verified_email=1 AND a.verified_phone=1) AND a.archived!=1 AND a.deleted_at IS NULL
            GROUP BY c.id';
		
		$sql = 'SELECT cat.id, SUM(total) as total' . '
            FROM ((' . $sql1 . ') UNION ALL (' . $sql2 . ')) cat
            GROUP BY cat.id';
		
		$bindings = [
			'countryCode' => config('country.code'),
		];
		$cats     = DB::select(DB::raw($sql), $bindings);
		$cats     = collect($cats)->keyBy('id');
		
		return $cats;
	}
	
	/*
	|--------------------------------------------------------------------------
	| RELATIONS
	|--------------------------------------------------------------------------
	*/
	public function postType()
	{
		return $this->belongsTo(PostType::class, 'post_type_id', 'translation_of')->where('translation_lang', config('app.locale'));
	}
	
	public function category()
	{
		return $this->belongsTo(Category::class, 'category_id', 'translation_of')->where('translation_lang', config('app.locale'));
	}
	
	public function city()
	{
		return $this->belongsTo(City::class, 'city_id');
	}
	
	public function messages()
	{
		return $this->hasMany(Message::class, 'post_id');
	}
	
	public function latestPayment()
	{
		return $this->hasOne(Payment::class, 'post_id')->orderBy('id', 'DESC');
	}
	
	public function payments()
	{
		return $this->hasMany(Payment::class, 'post_id');
	}
	
	public function pictures()
	{
		return $this->hasMany(Picture::class, 'post_id')->orderBy('position')->orderBy('id', 'DESC');
	}
	
	public function savedByUsers()
	{
		return $this->hasMany(SavedPost::class, 'post_id');
	}
	
	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}
	
	public function company()
	{
		return $this->belongsTo(Company::class, 'company_id');
	}
	
	public function salaryType()
	{
		return $this->belongsTo(SalaryType::class, 'salary_type_id', 'translation_of')->where('translation_lang', config('app.locale'));
	}
	
	/*
	|--------------------------------------------------------------------------
	| SCOPES
	|--------------------------------------------------------------------------
	*/
	public function scopeVerified($builder)
	{
		$builder->where(function ($query) {
			$query->where('verified_email', 1)->where('verified_phone', 1);
		});
		
		if (config('settings.single.posts_review_activation')) {
			$builder->where('reviewed', 1);
		}
		
		return $builder;
	}
	
	public function scopeUnverified($builder)
	{
		$builder->where(function ($query) {
			$query->where('verified_email', 0)->orWhere('verified_phone', 0);
		});
		
		if (config('settings.single.posts_review_activation')) {
			$builder->orWhere('reviewed', 0);
		}
		
		return $builder;
	}
	
	public function scopeArchived($builder)
	{
		return $builder->where('archived', 1);
	}
	
	public function scopeUnarchived($builder)
	{
		return $builder->where('archived', 0);
	}
	
	public function scopeReviewed($builder)
	{
		if (config('settings.single.posts_review_activation')) {
			return $builder->where('reviewed', 1);
		} else {
			return $builder;
		}
	}
	
	public function scopeUnreviewed($builder)
	{
		if (config('settings.single.posts_review_activation')) {
			return $builder->where('reviewed', 0);
		} else {
			return $builder;
		}
	}
	
	public function scopeWithCountryFix($builder)
	{
		// Check the Domain Mapping plugin
		if (config('plugins.domainmapping.installed')) {
			return $builder->where('country_code', config('country.code'));
		} else {
			return $builder;
		}
	}
	
	/*
	|--------------------------------------------------------------------------
	| ACCESSORS
	|--------------------------------------------------------------------------
	*/
	public function getCreatedAtAttribute($value)
	{
		$value = new Date($value);
		if (config('timezone.id')) {
			$value->timezone(config('timezone.id'));
		}
		// echo $value->format('l d F Y H:i:s').'<hr>'; exit();
		// echo $value->formatLocalized('%A %d %B %Y %H:%M').'<hr>'; exit(); // Multi-language
		
		return $value;
	}
	
	public function getUpdatedAtAttribute($value)
	{
		$value = new Date($value);
		if (config('timezone.id')) {
			$value->timezone(config('timezone.id'));
		}
		
		return $value;
	}
	
	public function getDeletedAtAttribute($value)
	{
		$value = new Date($value);
		if (config('timezone.id')) {
			$value->timezone(config('timezone.id'));
		}
		
		return $value;
	}
	
	public function getCreatedAtTaAttribute($value)
	{
		$value = new Date($this->attributes['created_at']);
		if (config('timezone.id')) {
			$value->timezone(config('timezone.id'));
		}
		$value = $value->ago();
		
		return $value;
	}
	
	public function getArchivedAtAttribute($value)
	{
		$value = (is_null($value)) ? $this->updated_at : $value;
		
		$value = new Date($value);
		if (config('timezone.id')) {
			$value->timezone(config('timezone.id'));
		}
		
		return $value;
	}
	
	public function getDeletionMailSentAtAttribute($value)
	{
		$value = (is_null($value)) ? $this->updated_at : $value;
		
		$value = new Date($value);
		if (config('timezone.id')) {
			$value->timezone(config('timezone.id'));
		}
		
		return $value;
	}
	
	public function getEmailAttribute($value)
	{
		if (isFromAdminPanel() || (!isFromAdminPanel() && in_array(request()->method(), ['GET']))) {
			if (
				isDemo() &&
				request()->segment(2) != 'password'
			) {
				if (auth()->check()) {
					if (auth()->user()->id != 1) {
						$value = hidePartOfEmail($value);
					}
				}
			}
		}
		
		return $value;
	}
	
	public function getPhoneAttribute($value)
	{
		$countryCode = config('country.code');
		if (isset($this->country_code) && !empty($this->country_code)) {
			$countryCode = $this->country_code;
		}
		
		$value = phoneFormatInt($value, $countryCode);
		
		return $value;
	}
	
	public function getTitleAttribute($value)
	{
		$value = mb_ucfirst($value);
		
		if (!isFromAdminPanel()) {
			if (!empty($this->user)) {
				if (!$this->user->hasAllPermissions(Permission::getStaffPermissions())) {
					$value = RemoveFromString::contactInfo($value, false, true);
				}
			} else {
				$value = RemoveFromString::contactInfo($value, false, true);
			}
		}
		
		return $value;
	}
	
	public function getContactNameAttribute($value)
	{
		$value = mb_ucwords($value);
		
		return $value;
	}
	
	public function getCompanyNameAttribute($value)
	{
		$value = mb_ucwords($value);
		
		return $value;
	}
	
	public function getCompanyDescriptionAttribute($value)
	{
		if (!isFromAdminPanel()) {
			if (!empty($this->user)) {
				if (!$this->user->hasAllPermissions(Permission::getStaffPermissions())) {
					$value = RemoveFromString::contactInfo($value, false, true);
				}
			} else {
				$value = RemoveFromString::contactInfo($value, false, true);
			}
		}
		
		return $value;
	}
	
	public function getDescriptionAttribute($value)
	{
		if (!isFromAdminPanel()) {
			if (!empty($this->user)) {
				if (!$this->user->hasAllPermissions(Permission::getStaffPermissions())) {
					$value = RemoveFromString::contactInfo($value, false, true);
				}
			} else {
				$value = RemoveFromString::contactInfo($value, false, true);
			}
		}
		
		return $value;
	}
	
	public function getLogoFromOldPath()
	{
		if (!isset($this->attributes) || !isset($this->attributes['logo'])) {
			return null;
		}
		
		$value = $this->attributes['logo'];
		
		// Fix path
		$value = str_replace('uploads/pictures/', '', $value);
		$value = str_replace('pictures/', '', $value);
		$value = 'pictures/' . $value;
		
		$disk = StorageDisk::getDisk();
		
		if (!$disk->exists($value)) {
			$value = null;
		}
		
		return $value;
	}
	
	public function getLogoAttribute()
	{
		// OLD PATH
		$value = $this->getLogoFromOldPath();
		if (!empty($value)) {
			return $value;
		}
		
		// NEW PATH
		if (!isset($this->attributes) || !isset($this->attributes['logo'])) {
			$value = config('larapen.core.picture.default');
			return $value;
		}
		
		$value = $this->attributes['logo'];
		
		$disk = StorageDisk::getDisk();
		
		if (!$disk->exists($value)) {
			$value = config('larapen.core.picture.default');
		}
		
		return $value;
	}
	
	public static function getLogo($value)
	{
		$disk = StorageDisk::getDisk();
		
		// OLD PATH
		$value = str_replace('uploads/pictures/', '', $value);
		$value = str_replace('pictures/', '', $value);
		$value = 'pictures/' . $value;
		if ($disk->exists($value) && substr($value, -1) != '/') {
			return $value;
		}
		
		// NEW PATH
		$value = str_replace('pictures/', '', $value);
		if (!$disk->exists($value) && substr($value, -1) != '/') {
			$value = config('larapen.core.picture.default');
		}
		
		return $value;
	}
	
	/*
	|--------------------------------------------------------------------------
	| MUTATORS
	|--------------------------------------------------------------------------
	*/
	public function setLogoAttribute($value)
	{
		$disk           = StorageDisk::getDisk();
		$attribute_name = 'logo';
		
		// Don't make an upload for Post->logo for logged users
		if (!Str::contains(Route::currentRouteAction(), 'Admin\PostController')) {
			if (auth()->check()) {
				$this->attributes[$attribute_name] = $value;
				
				return $this->attributes[$attribute_name];
			}
		}
		
		if (!isset($this->country_code) || !isset($this->id)) {
			$this->attributes[$attribute_name] = null;
			
			return false;
		}
		
		// Path
		$destination_path = 'files/' . strtolower($this->country_code) . '/' . $this->id;
		
		// If the image was erased
		if (empty($value)) {
			// delete the image from disk
			if (!Str::contains($this->{$attribute_name}, config('larapen.core.picture.default'))) {
				$disk->delete($this->{$attribute_name});
			}
			
			// set null in the database column
			$this->attributes[$attribute_name] = null;
			
			return false;
		}
		
		// Check the image file
		if ($value == url('/')) {
			$this->attributes[$attribute_name] = null;
			
			return false;
		}
		
		// If laravel request->file('filename') resource OR base64 was sent, store it in the db
		try {
			if (fileIsUploaded($value)) {
				// Get file extension
				$extension = getUploadedFileExtension($value);
				if (empty($extension)) {
					$extension = 'jpg';
				}
				
				// Image quality
				$imageQuality = config('settings.upload.image_quality', 90);
				
				// Image default sizes
				$width  = (int)config('settings.upload.img_resize_width', 1000);
				$height = (int)config('settings.upload.img_resize_height', 1000);
				
				// Other parameters
				$ratio  = config('settings.upload.img_resize_ratio', '1');
				$upSize = config('settings.upload.img_resize_upsize', '1');
				
				// Make the image (Size: 454x454)
				$image = Image::make($value)->resize($width, $height, function ($constraint) use ($ratio, $upSize) {
					if ($ratio == '1') {
						$constraint->aspectRatio();
					}
					if ($upSize == '1') {
						$constraint->upsize();
					}
				})->encode($extension, $imageQuality);
				
				// Generate a filename.
				$filename = md5($value . time()) . '.' . $extension;
				
				// Store the image on disk.
				$disk->put($destination_path . '/' . $filename, $image->stream()->__toString());
				
				// Save the path to the database
				$this->attributes[$attribute_name] = $destination_path . '/' . $filename;
				
				return $this->attributes[$attribute_name];
			} else {
				// Retrieve current value without upload a new file.
				if (Str::startsWith($value, config('larapen.core.logo'))) {
					$value = null;
				} else {
					// Extract the value's country code
					$tmp = [];
					preg_match('#files/([A-Za-z]{2})/[\d]+#i', $value, $tmp);
					$valueCountryCode = (isset($tmp[1]) && !empty($tmp[1])) ? $tmp[1] : null;
					
					// Extract the value's ID
					$tmp = [];
					preg_match('#files/[A-Za-z]{2}/([\d]+)#i', $value, $tmp);
					$valueId = (isset($tmp[1]) && !empty($tmp[1])) ? $tmp[1] : null;
					
					// Extract the value's filename
					$tmp = [];
					preg_match('#files/[A-Za-z]{2}/[\d]+/(.+)#i', $value, $tmp);
					$valueFilename = (isset($tmp[1]) && !empty($tmp[1])) ? $tmp[1] : null;
					
					if (!empty($valueCountryCode) && !empty($valueId) && !empty($valueFilename)) {
						// Value's Path
						$valueDestinationPath = 'files/' . strtolower($valueCountryCode) . '/' . $valueId;
						if ($valueDestinationPath != $destination_path) {
							$oldFilePath = $valueDestinationPath . '/' . $valueFilename;
							$newFilePath = $destination_path . '/' . $valueFilename;
							
							// Copy the file
							$disk->copy($oldFilePath, $newFilePath);
							
							$this->attributes[$attribute_name] = $newFilePath;
							
							return $this->attributes[$attribute_name];
						}
					}
					
					if (!Str::startsWith($value, 'files/')) {
						$value = $destination_path . last(explode($destination_path, $value));
					}
				}
				$this->attributes[$attribute_name] = $value;
				
				return $this->attributes[$attribute_name];
			}
		} catch (\Exception $e) {
			flash($e->getMessage())->error();
			$this->attributes[$attribute_name] = null;
			
			return false;
		}
	}
	
	public function setTagsAttribute($value)
	{
		$this->attributes['tags'] = (!empty($value)) ? mb_strtolower($value) : $value;
	}
	
	public function setApplicationUrlAttribute($value)
	{
		$this->attributes['application_url'] = (!empty($value)) ? strtolower($value) : $value;
	}
}

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

use App\Http\Controllers\Auth\Traits\VerificationTrait;
use App\Models\PostType;
use App\Models\Category;
use App\Models\SalaryType;
use Larapen\Admin\app\Http\Controllers\PanelController;
use App\Http\Requests\Admin\PostRequest as StoreRequest;
use App\Http\Requests\Admin\PostRequest as UpdateRequest;

class PostController extends PanelController
{
	use VerificationTrait;
	
	public function setup()
	{
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel('App\Models\Post');
		$this->xPanel->with(['user', 'city', 'latestPayment' => function ($builder) { $builder->with(['package']); }]);
		$this->xPanel->setRoute(admin_uri('posts'));
		$this->xPanel->setEntityNameStrings(trans('admin::messages.ad'), trans('admin::messages.ads'));
		$this->xPanel->denyAccess(['create']);
		if (!request()->input('order')) {
			if (config('settings.single.posts_review_activation')) {
				$this->xPanel->orderBy('reviewed', 'ASC');
			}
			$this->xPanel->orderBy('created_at', 'DESC');
		}
		
		$this->xPanel->addButtonFromModelFunction('top', 'bulk_delete_btn', 'bulkDeleteBtn', 'end');
		
		// Hard Filters
		if (request()->filled('active')) {
			if (request()->get('active') == 0) {
				$this->xPanel->addClause('where', 'verified_email', '=', 0);
				$this->xPanel->addClause('orWhere', 'verified_phone', '=', 0);
				if (config('settings.single.posts_review_activation')) {
					$this->xPanel->addClause('orWhere', 'reviewed', '=', 0);
				}
			}
			if (request()->get('active') == 1) {
				$this->xPanel->addClause('where', 'verified_email', '=', 1);
				$this->xPanel->addClause('where', 'verified_phone', '=', 1);
				if (config('settings.single.posts_review_activation')) {
					$this->xPanel->addClause('where', 'reviewed', '=', 1);
				}
			}
		}
		
		// Filters
		// -----------------------
		$this->xPanel->addFilter([
			'name'  => 'id',
			'type'  => 'text',
			'label' => 'ID',
		],
		false,
		function ($value) {
			$this->xPanel->addClause('where', 'id', '=', $value);
		});
		// -----------------------
		$this->xPanel->addFilter([
			'name'  => 'from_to',
			'type'  => 'date_range',
			'label' => trans('admin::messages.Date range'),
		],
		false,
		function ($value) {
			$dates = json_decode($value);
			$this->xPanel->addClause('where', 'created_at', '>=', $dates->from);
			$this->xPanel->addClause('where', 'created_at', '<=', $dates->to);
		});
		// -----------------------
		$this->xPanel->addFilter([
			'name'  => 'title',
			'type'  => 'text',
			'label' => trans('admin::messages.Title'),
		],
		false,
		function ($value) {
			$this->xPanel->addClause('where', 'title', 'LIKE', "%$value%");
		});
		// -----------------------
		$this->xPanel->addFilter([
			'name'  => 'country',
			'type'  => 'select2',
			'label' => trans('admin::messages.Country'),
		],
		getCountries(),
		function ($value) {
			$this->xPanel->addClause('where', 'country_code', '=', $value);
		});
		// -----------------------
		$this->xPanel->addFilter([
			'name'  => 'city',
			'type'  => 'text',
			'label' => trans('admin::messages.City'),
		],
		false,
		function ($value) {
			$this->xPanel->query = $this->xPanel->query->whereHas('city', function ($query) use ($value) {
				$query->where('name', 'LIKE', "%$value%");
			});
		});
		// -----------------------
		$this->xPanel->addFilter([
			'name'  => 'status',
			'type'  => 'dropdown',
			'label' => trans('admin::messages.Status'),
		], [
			1 => trans('admin::messages.Unactivated'),
			2 => trans('admin::messages.Activated'),
		], function ($value) {
			if ($value == 1) {
				$this->xPanel->addClause('where', 'verified_email', '=', 0);
				$this->xPanel->addClause('orWhere', 'verified_phone', '=', 0);
				if (config('settings.single.posts_review_activation')) {
					$this->xPanel->addClause('orWhere', 'reviewed', '=', 0);
				}
			}
			if ($value == 2) {
				$this->xPanel->addClause('where', 'verified_email', '=', 1);
				$this->xPanel->addClause('where', 'verified_phone', '=', 1);
				if (config('settings.single.posts_review_activation')) {
					$this->xPanel->addClause('where', 'reviewed', '=', 1);
				}
			}
		});
		
		
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
			'name'  => 'created_at',
			'label' => trans("admin::messages.Date"),
			'type'  => 'datetime',
		]);
		$this->xPanel->addColumn([
			'name'          => 'title',
			'label'         => trans('admin::messages.Title'),
			'type'          => 'model_function',
			'function_name' => 'getTitleHtml',
		]);
		$this->xPanel->addColumn([
			'name'          => 'logo', // Put unused field column
			'label'         => trans("admin::messages.Logo"),
			'type'          => 'model_function',
			'function_name' => 'getLogoHtml',
		]);
		$this->xPanel->addColumn([
			'name'  		=> 'company_name',
			'label' 		=> trans("admin::messages.Company Name"),
			'type'          => 'model_function',
			'function_name' => 'getCompanyNameHtml',
		]);
		$this->xPanel->addColumn([
			'name'          => 'city_id',
			'label'         => trans("admin::messages.City"),
			'type'          => 'model_function',
			'function_name' => 'getCityHtml',
		]);
		$this->xPanel->addColumn([
			'name'          => 'country_code',
			'label'         => trans("admin::messages.Country"),
			'type'          => 'model_function',
			'function_name' => 'getCountryHtml',
		]);
		$this->xPanel->addColumn([
			'name'          => 'verified_email',
			'label'         => trans("admin::messages.Verified Email"),
			'type'          => 'model_function',
			'function_name' => 'getVerifiedEmailHtml',
		]);
		if (config('settings.sms.phone_verification')) {
			$this->xPanel->addColumn([
				'name'          => 'verified_phone',
				'label'         => trans("admin::messages.Verified Phone"),
				'type'          => 'model_function',
				'function_name' => 'getVerifiedPhoneHtml',
			]);
		}
		if (config('settings.single.posts_review_activation')) {
			$this->xPanel->addColumn([
				'name'          => 'reviewed',
				'label'         => trans("admin::messages.Reviewed"),
				'type'          => "model_function",
				'function_name' => 'getReviewedHtml',
			]);
		}
		
		// FIELDS
		$this->xPanel->addField([
			'label'       => trans("admin::messages.Category"),
			'name'        => 'category_id',
			'type'        => 'select2_from_array',
			'options'     => $this->categories(),
			'allows_null' => false,
		]);
		$this->xPanel->addField([
			'name'       => 'company_name',
			'label'      => trans('admin::messages.Company Name'),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans('admin::messages.Company Name'),
			],
		]);
		$this->xPanel->addField([
			'name'   => 'logo',
			'label'  => trans('admin::messages.Logo') . ' (Supported file extensions: jpg, jpeg, png, gif)',
			'type'   => 'image',
			'upload' => true,
			'disk'   => 'public',
		]);
		$this->xPanel->addField([
			'name'       => 'company_description',
			'label'      => trans("admin::messages.Company Description"),
			'type'       => 'textarea',
			'attributes' => [
				'placeholder' => trans("admin::messages.Company Description"),
				'rows'        => 10,
			],
		]);
		$this->xPanel->addField([
			'name'       => 'title',
			'label'      => trans('admin::messages.Title'),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans('admin::messages.Title'),
			],
		]);
		$this->xPanel->addField([
			'name'       => 'description',
			'label'      => trans("admin::messages.Description"),
			'type'       => (config('settings.single.simditor_wysiwyg'))
				? 'simditor'
				: 'textarea',
			'attributes' => [
				'placeholder' => trans("admin::messages.Description"),
				'id'          => 'description',
				'rows'        => 10,
			],
		]);
		$this->xPanel->addField([
			'name'              => 'salary_min',
			'label'             => trans("admin::messages.Salary (min)"),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans("admin::messages.Salary (min)"),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'salary_max',
			'label'             => trans("admin::messages.Salary (max)"),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans("admin::messages.Salary (max)"),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'label'             => trans("admin::messages.Salary Type"),
			'name'              => 'salary_type_id',
			'type'              => 'select2_from_array',
			'options'           => $this->salaryType(),
			'allows_null'       => false,
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'negotiable',
			'label'             => trans("admin::messages.Negotiable Salary"),
			'type'              => 'checkbox',
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
				'style' => 'margin-top: 20px;',
			],
		]);
		
		$this->xPanel->addField([
			'name'              => 'contact_name',
			'label'             => trans('admin::messages.User Name'),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.User Name'),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'email',
			'label'             => trans('admin::messages.User Email'),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.User Email'),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'phone',
			'label'             => trans('admin::messages.User Phone'),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans('admin::messages.User Phone'),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'phone_hidden',
			'label'             => trans("admin::messages.Hide contact phone"),
			'type'              => 'checkbox',
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
				'style' => 'margin-top: 20px;',
			],
		]);
		$this->xPanel->addField([
			'label'             => trans("admin::messages.Post Type"),
			'name'              => 'post_type_id',
			'type'              => 'select2_from_array',
			'options'           => $this->postType(),
			'allows_null'       => false,
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'tags',
			'label'             => trans("admin::messages.Tags"),
			'type'              => 'text',
			'attributes'        => [
				'placeholder' => trans("admin::messages.Tags"),
			],
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'verified_email',
			'label'             => trans("admin::messages.Verified Email"),
			'type'              => 'checkbox',
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
				'style' => 'margin-top: 20px;',
			],
		]);
		$this->xPanel->addField([
			'name'              => 'verified_phone',
			'label'             => trans("admin::messages.Verified Phone"),
			'type'              => 'checkbox',
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
				'style' => 'margin-top: 20px;',
			],
		]);
		if (config('settings.single.posts_review_activation')) {
			$this->xPanel->addField([
				'name'              => 'reviewed',
				'label'             => trans("admin::messages.Reviewed"),
				'type'              => 'checkbox',
				'wrapperAttributes' => [
					'class' => 'form-group col-md-6',
					'style' => 'margin-top: 20px;',
				],
			]);
		}
		$this->xPanel->addField([
			'name'              => 'archived',
			'label'             => trans("admin::messages.Archived"),
			'type'              => 'checkbox',
			'wrapperAttributes' => [
				'class' => 'form-group col-md-6',
				'style' => 'margin-top: 20px;',
			],
		]);
		$entity = $this->xPanel->getModel()->find(request()->segment(3));
		if (!empty($entity)) {
			$ipLink = config('larapen.core.ipLinkBase') . $entity->ip_addr;
			$this->xPanel->addField([
				'name'  => 'ip_addr',
				'type'  => 'custom_html',
				'value' => '<h5><strong>IP:</strong> <a href="' . $ipLink . '" target="_blank">' . $entity->ip_addr . '</a></h5>',
			], 'update');
			if (!empty($entity->email)) {
				$btnUrl = admin_url('blacklists/add') . '?email=' . $entity->email;
				
				$cMsg = trans('admin::messages.confirm_this_action');
				$cLink = "window.location.replace('" . $btnUrl . "'); window.location.href = '" . $btnUrl . "';";
				$cHref = "javascript: if (confirm('" . addcslashes($cMsg, "'") . "')) { " . $cLink . " } else { void('') }; void('')";
				
				$btnText = trans("admin::messages.ban_the_user");
				$btnHint = trans("admin::messages.ban_the_user_email", ['email' => $entity->email]);
				$tooltip = ' data-toggle="tooltip" title="' . $btnHint . '"';
				
				$btnLink = '<a href="' . $cHref . '" class="btn btn-danger"' . $tooltip . '>' . $btnText . '</a>';
				$this->xPanel->addField([
					'name'              => 'ban_button',
					'type'              => 'custom_html',
					'value'             => $btnLink,
					'wrapperAttributes' => [
						'style' => 'text-align:center;',
					],
				], 'update');
			}
		}
	}
	
	public function store(StoreRequest $request)
	{
		return parent::storeCrud();
	}
	
	public function update(UpdateRequest $request)
	{
		return parent::updateCrud();
	}
	
	public function postType()
	{
		$entries = PostType::trans()->get();
		
		return $this->getTranslatedArray($entries);
	}
	
	public function categories()
	{
		$entries = Category::trans()->where('parent_id', 0)->orderBy('lft')->get();
		if ($entries->count() <= 0) {
			return [];
		}
		
		$tab = [];
		foreach ($entries as $entry) {
			$tab[$entry->tid] = $entry->name;
			
			$subEntries = Category::trans()->where('parent_id', $entry->id)->orderBy('lft')->get();
			if (!empty($subEntries)) {
				foreach ($subEntries as $subEntrie) {
					$tab[$subEntrie->tid] = "---| " . $subEntrie->name;
				}
			}
		}
		
		return $tab;
	}
	
	public function salaryType()
	{
		$entries = SalaryType::trans()->get();
		
		return $this->getTranslatedArray($entries);
	}
}

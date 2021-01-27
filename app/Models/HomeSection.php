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

use App\Helpers\Files\Storage\StorageDisk;
use App\Models\Scopes\ActiveScope;
use App\Observer\HomeSectionObserver;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Larapen\Admin\app\Models\Crud;
use Prologue\Alerts\Facades\Alert;

class HomeSection extends BaseModel
{
	use Crud;
	
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'home_sections';
    
    protected $fakeColumns = ['value'];
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = false;
    
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
    protected $fillable = ['method', 'name', 'value', 'view', 'field', 'parent_id', 'lft', 'rgt', 'depth', 'active'];
    
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
    // protected $dates = [];
    
    protected $casts = [
        'value' => 'array',
    ];
    
    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();
	
		HomeSection::observe(HomeSectionObserver::class);
		
        static::addGlobalScope(new ActiveScope());
    }
	
	public function resetHomepageReOrderBtn($xPanel = false)
	{
		$url = admin_url('homepage/reset_reorder');
		
		$msg = trans('admin::messages.Reset the homepage sections reorder.');
		$tooltip = ' data-toggle="tooltip" title="' . $msg . '"';
		
		// Button
		$out = '';
		$out .= '<a class="btn btn-warning" href="' . $url . '"' . $tooltip . '>';
		$out .= '<i class="fa fa-sort-amount-asc"></i> ';
		$out .= trans('admin::messages.Reset sections reorganization');
		$out .= '</a>';
		
		return $out;
	}
	
	public function resetHomepageSettingsBtn($xPanel = false)
	{
		$url = admin_url('homepage/reset_settings');
		
		$msg = trans('admin::messages.Reset all the homepage settings.');
		$tooltip = ' data-toggle="tooltip" title="' . $msg . '"';
		
		// Button
		$out = '';
		$out .= '<a class="btn btn-danger" href="' . $url . '"' . $tooltip . '>';
		$out .= '<i class="fa fa-industry"></i> ';
		$out .= trans('admin::messages.Return to factory settings');
		$out .= '</a>';
		
		return $out;
	}
    
    public function getNameHtml()
    {
		$currentUrl = preg_replace('#/(search)$#', '', url()->current());
		$url = $currentUrl . '/' . $this->getKey() . '/edit';
		
        $out = '<a href="' . $url . '">' . $this->name . '</a>';
        
        return $out;
    }
	
	public function configureBtn($xPanel = false)
	{
		$url = admin_url('homepage/' . $this->id . '/edit');
		
		$msg = trans('admin::messages.Configure :entity', ['entity' => $this->name]);
		$tooltip = ' data-toggle="tooltip" title="' . $msg . '"';
		
		$out = '';
		$out .= '<a class="btn btn-xs btn-primary" href="' . $url . '"' . $tooltip . '>';
		$out .= '<i class="fa fa-cog"></i> ';
		$out .= mb_ucfirst(trans('admin::messages.Configure'));
		$out .= '</a>';
		
		return $out;
	}
    
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    
    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    
    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */
	public function getValueAttribute($value)
	{
		// Get 'options' field value
		$value = jsonToArray($value);
		
		// Handle 'options' field value
		if (is_array($value) && count($value) > 0) {
			// Get Entered values (Or Default values if the Entry doesn't exist)
			if ($this->method == 'getSearchForm') {
				if (!isset($value['enable_form_area_customization'])) {
					$value['enable_form_area_customization'] = '1';
				}
				if (!isset($value['background_image'])) {
					$value['background_image'] = null;
				}
			}
			
			if ($this->method == 'getLocations') {
				if (!isset($value['show_cities'])) {
					$value['show_cities'] = '1';
				}
				if (!isset($value['max_items'])) {
					$value['max_items'] = '14';
				}
				if (!isset($value['show_post_btn'])) {
					$value['show_post_btn'] = '1';
				}
				if (!isset($value['show_map'])) {
					$value['show_map'] = '1';
				}
				if (!isset($value['map_width'])) {
					$value['map_width'] = '300px';
				}
				if (!isset($value['map_height'])) {
					$value['map_height'] = '300px';
				}
			}
			
			if ($this->method == 'getSponsoredPosts') {
				if (!isset($value['max_items'])) {
					$value['max_items'] = '20';
				}
				if (!isset($value['autoplay'])) {
					$value['autoplay'] = '1';
				}
			}
			
			if ($this->method == 'getLatestPosts') {
				if (!isset($value['max_items'])) {
					$value['max_items'] = '8';
				}
				if (!isset($value['show_view_more_btn'])) {
					$value['show_view_more_btn'] = '1';
				}
			}
			
			if ($this->method == 'getCategories') {
				if (!isset($value['type_of_display'])) {
					$value['type_of_display'] = 'c_check_list';
				}
				if (!isset($value['show_icon'])) {
					$value['show_icon'] = '0';
				}
				if (!isset($value['max_sub_cats'])) {
					$value['max_sub_cats'] = '3';
				}
			}
			
			if ($this->method == 'getFeaturedPostsCompanies') {
				if (!isset($value['max_items'])) {
					$value['max_items'] = '12';
				}
			}
		} else {
			if (isset($this->method)) {
				// Get Default values
				$value = [];
				if ($this->method == 'getSearchForm') {
					$value['enable_form_area_customization'] = '1';
					$value['background_image'] = null;
				}
				if ($this->method == 'getLocations') {
					$value['show_cities'] = '1';
					$value['max_items'] = '14';
					$value['show_post_btn'] = '1';
					$value['show_map'] = '1';
					$value['map_width'] = '300px';
					$value['map_height'] = '300px';
				}
				if ($this->method == 'getSponsoredPosts') {
					$value['max_items'] = '20';
					$value['autoplay'] = '1';
				}
				if ($this->method == 'getLatestPosts') {
					$value['max_items'] = '8';
					$value['show_view_more_btn'] = '1';
				}
				if ($this->method == 'getCategories') {
					$value['type_of_display'] = 'c_check_list';
					$value['show_icon'] = '0';
					$value['max_sub_cats'] = '3';
				}
				if ($this->method == 'getFeaturedPostsCompanies') {
					$value['max_items'] = '12';
				}
			}
		}
		
		return $value;
	}
    
    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
	public function setValueAttribute($value)
	{
		$value = jsonToArray($value);
		
		// Background Image
		if (isset($value['background_image'])) {
			$backgroundImage = [
				'attribute' => 'background_image',
				'path'      => 'app/logo',
				'default'   => null,
				'width'     => 2000,
				'height'    => 1000,
				'upsize'    => false,
				'quality'   => 100,
				'filename'  => 'header-',
				'orientate' => true,
			];
			$value = $this->upload($value, $backgroundImage);
		}
		
		$this->attributes['value'] = json_encode($value);
	}
	
	// Set Upload
	public function upload($value, $params)
	{
		$disk = StorageDisk::getDisk();
		$attribute_name = $params['attribute'];
		$destination_path = $params['path'];
		
		// If 'background_image' option doesn't exist, don't make the upload and save data
		if (!isset($value[$attribute_name])) {
			return $value;
		}
		
		// If the image was erased
		if ($value[$attribute_name] == null) {
			// Delete the image from disk
			if (isset($this->options) && isset($this->options[$attribute_name])) {
				if (!empty($params['default'])) {
					if (!Str::contains($this->options[$attribute_name], $params['default'])) {
						$disk->delete($this->options[$attribute_name]);
					}
				} else {
					$disk->delete($this->options[$attribute_name]);
				}
			}
			
			// Set null in the database column
			$value[$attribute_name] = null;
			
			return $value;
		}
		
		// If laravel request->file('filename') resource OR base64 was sent, store it in the db
		try {
			if (fileIsUploaded($value[$attribute_name])) {
				// Get file extension
				$extension = getUploadedFileExtension($value[$attribute_name]);
				if (empty($extension)) {
					$extension = 'jpg';
				}
				
				// Check if 'Auto Orientate' is enabled
				$autoOrientateIsEnabled = false;
				if (isset($params['orientate']) && $params['orientate']) {
					$autoOrientateIsEnabled = exifExtIsEnabled();
				}
				
				// Make the Image
				if ($autoOrientateIsEnabled) {
					$image = Image::make($value[$attribute_name])->orientate()->resize($params['width'], $params['height'], function ($constraint) use ($params) {
						$constraint->aspectRatio();
						if ($params['upsize']) {
							$constraint->upsize();
						}
					})->encode($extension, $params['quality']);
				} else {
					$image = Image::make($value[$attribute_name])->resize($params['width'], $params['height'], function ($constraint) use ($params) {
						$constraint->aspectRatio();
						if ($params['upsize']) {
							$constraint->upsize();
						}
					})->encode($extension, $params['quality']);
				}
				
				// Generate a filename.
				$filename = uniqid($params['filename']) . '.' . $extension;
				
				// Store the image on disk.
				$disk->put($destination_path . '/' . $filename, $image->stream()->__toString());
				
				// Save the path to the database
				$value[$attribute_name] = $destination_path . '/' . $filename;
			} else {
				// Retrieve current value without upload a new file.
				if (!empty($params['default'])) {
					if (Str::contains($value[$attribute_name], $params['default'])) {
						$value[$attribute_name] = null;
					} else {
						if (!Str::startsWith($value[$attribute_name], $destination_path)) {
							$value[$attribute_name] = $destination_path . last(explode($destination_path, $value[$attribute_name]));
						}
					}
				} else {
					if ($value[$attribute_name] == url('/')) {
						$value[$attribute_name] = null;
					} else {
						if (!Str::startsWith($value[$attribute_name], $destination_path)) {
							$value[$attribute_name] = $destination_path . last(explode($destination_path, $value[$attribute_name]));
						}
					}
				}
			}
			
			return $value;
		} catch (\Exception $e) {
			Alert::error($e->getMessage())->flash();
			
			$value[$attribute_name] = null;
			
			return $value;
		}
	}
	
	public function getFieldAttribute($value)
	{
		$diskName = StorageDisk::getDiskName();
		
		$breadcrumb = trans('admin::messages.Admin panel') . ' &rarr; '
			. mb_ucwords(trans('admin::messages.setup')) . ' &rarr; '
			. mb_ucwords(trans('admin::messages.homepage')) . ' &rarr; ';
		
		$formTitle = '{"name":"group_name","type":"custom_html","value":"<h2 class=\"setting-group-name\">' . $this->name . '</h2>","disableTrans":"true"},
	{"name":"group_breadcrumb","type":"custom_html","value":"<p class=\"setting-group-breadcrumb\">' . $breadcrumb . $this->name . '</p>","disableTrans":"true"},';
		
		
		if ($this->method == 'getSearchForm') {
			$value = '';
			$value .= '{"name":"enable_form_area_customization","label":"Enable search form area customization","type":"checkbox","hint":"NOTE: The options below require to enable the search form area customization."},
{"name":"separator_1","type":"custom_html","value":"<h3>Background</h3>"},
{"name":"background_color","label":"Background Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#444"},"hint":"Enter a RGB color code."},
{"name":"background_image","label":"Background Image","type":"image","upload":true,"disk":"' . $diskName . '","hint":"getSearchForm_background_image_hint"},
{"name":"height","label":"Height","attributes":{"placeholder":"450px"},"hint":"Enter a value greater than 50px. (Example: 400px)","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"parallax","label":"Enable Parallax Effect","type":"checkbox","wrapperAttributes":{"class":"form-group col-md-6","style":"margin-top: 20px;"}},
{"name":"separator_2","type":"custom_html","value":"<h3>Search Form</h3>"},
{"name":"hide_form","label":"Hide the Form","type":"checkbox"},
{"name":"form_border_color","label":"Form Border Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#7324bc"},"hint":"Enter a RGB color code.","wrapperAttributes":{"class":"form-group col-md-3"}},
{"name":"form_border_width","label":"Form Border Width","attributes":{"placeholder":"5px"},"hint":"Enter a number with unit (eg. 5px)","wrapperAttributes":{"class":"form-group col-md-3"}},
{"name":"form_btn_background_color","label":"Form Button Background Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#7324bc"},"hint":"Enter a RGB color code.","wrapperAttributes":{"class":"form-group col-md-3"}},
{"name":"form_btn_text_color","label":"Form Button Text Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#FFF"},"hint":"Enter a RGB color code.","wrapperAttributes":{"class":"form-group col-md-3"}},
{"name":"separator_3","type":"custom_html","value":"<h3>Titles</h3>"},
{"name":"hide_titles","label":"Hide Titles","type":"checkbox"},
{"name":"separator_3_1","type":"custom_html","value":"<h4>Titles Content</h4>"},
{"name":"separator_3_2","type":"custom_html","value":"NOTE: You can use dynamic variables such as <strong>{app_name}</strong>, <strong>{country}</strong>, <strong>{count_ads}</strong> and <strong>{count_users}</strong>."},';
			
			$languages = Language::active()->get();
			if ($languages->count() > 0) {
				foreach ($languages as $language) {
					$value .= '{"name":"title_' . $language->abbr . '","label":"' . trans('admin::messages.Title') . ' (' . $language->name . ')","attributes":{"placeholder":"' . t('Find a job near you', [], 'global', $language->abbr) . '"},"wrapperAttributes":{"class":"form-group col-md-6"},"disableTrans":"true"},
{"name":"sub_title_' . $language->abbr . '","label":"' . trans('admin::messages.Sub Title') . ' (' . $language->name . ')","attributes":{"placeholder":"' . t('Simple, fast and efficient', [], 'global', $language->abbr) . '"},"wrapperAttributes":{"class":"form-group col-md-6"},"disableTrans":"true"},';
				}
			}
			
			$value .= '{"name":"separator_3_3","type":"custom_html","value":"<h4>Titles Color</h4>"},
{"name":"big_title_color","label":"Big Title Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#FFF"},"hint":"Enter a RGB color code.","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"sub_title_color","label":"Sub Title Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#FFF"},"hint":"Enter a RGB color code.","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"separator_last","type":"custom_html","value":"<hr>"},
{"name":"active","label":"Active","type":"checkbox"}';
		}
		
		
		if ($this->method == 'getSponsoredPosts') {
			$value = '{"name":"max_items","label":"Max Items","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"order_by","label":"Order By","type":"select2_from_array","options":{"date":"Date","random":"Random"},"allows_null":false,"wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"autoplay","label":"Carousel\'s Autoplay","type":"checkbox"},
{"name":"autoplay_timeout","label":"Carousel\'s Autoplay Timeout","attributes":{"placeholder":1500},"wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"cache_expiration","label":"Cache Expiration Time for this section","attributes":{"placeholder":"0"},"hint":"home_cache_expiration_hint","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"separator_last","type":"custom_html","value":"<hr>"},
{"name":"active","label":"Active","type":"checkbox"}';
		}
		
		
		if ($this->method == 'getLatestPosts') {
			$value = '{"name":"max_items","label":"Max Items","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"order_by","label":"Order By","type":"select2_from_array","options":{"date":"Date","random":"Random"},"allows_null":false,"wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"cache_expiration","label":"Cache Expiration Time for this section","attributes":{"placeholder":"0"},"hint":"home_cache_expiration_hint","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"separator_last","type":"custom_html","value":"<hr>"},
{"name":"active","label":"Active","type":"checkbox"}';
		}
		
		
		if ($this->method == 'getCategories') {
			$value = '{"name":"type_of_display","label":"Type of display","type":"select2_from_array","options":{"c_normal_list":"Normal List","c_circle_list":"Circle List","c_check_list":"Check List","c_border_list":"Border List","c_picture_icon":"Picture as Icon","cc_normal_list":"Normal List (Categories + Children)","cc_normal_list_s":"Normal List Styled (Categories + Children)"},"allows_null":false,"wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"max_items","label":"max_categories_label","hint":"max_categories_hint","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"show_icon","label":"Show the categories icons","type":"checkbox","hint":"NOTE: This will be applied for all of \"Types of display\", except \"Picture as Icon\".","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"count_categories_posts","label":"count_categories_posts_label","type":"checkbox","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"separator_clear_1","type":"custom_html","value":"<div style=\"clear: both;\"></div>"},
{"name":"max_sub_cats","label":"Max subcategories displayed by default","hint":"NOTE: This will be applied for only the \"Categories + Children\" type of display.","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"cache_expiration","label":"Cache Expiration Time for this section","attributes":{"placeholder":"0"},"hint":"home_cache_expiration_hint","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"separator_last","type":"custom_html","value":"<hr>"},
{"name":"active","label":"Active","type":"checkbox"}';
		}
		
		
		if ($this->method == 'getLocations') {
			$value = '{"name":"separator_4","type":"custom_html","value":"<h3>Locations</h3>"},
{"name":"show_cities","label":"Show the Country Cities","type":"checkbox","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"show_post_btn","label":"Show the bottom button","type":"checkbox","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"background_color","label":"Background Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#c7c5c1"},"wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"border_width","label":"Border Width","attributes":{"placeholder":"1px"},"wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"border_color","label":"Border Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#c7c5c1"},"wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"text_color","label":"Text Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#c7c5c1"},"wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"link_color","label":"Links Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#c7c5c1"},"wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"link_color_hover","label":"Links Color (Hover)","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#c7c5c1"},"wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"max_items","label":"max_cities_label","attributes":{"placeholder":12},"hint":"max_cities_hint","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"items_cols","label":"Cities Columns","type":"select2_from_array","options":{"3":"3","2":"2","1":"1"},"hint":"This option is applied only when the map is displayed.","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"count_cities_posts","label":"count_cities_posts_label","type":"checkbox","hint":"count_cities_posts_hint","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"cache_expiration","label":"Cache Expiration Time for this section","attributes":{"placeholder":"0"},"hint":"home_cache_expiration_hint","wrapperAttributes":{"class":"form-group col-md-6"}},

{"name":"separator_4_1","type":"custom_html","value":"<h3>Country Map</h3>"},
{"name":"show_map","label":"Show the Country Map","type":"checkbox"},
{"name":"map_background_color","label":"Map\'s Background Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"transparent"},"hint":"Enter a RGB color code or the word \'transparent\'.","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"map_border","label":"Map\'s Border","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#c7c5c1"},"hint":"<br>","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"map_hover_border","label":"Map\'s Hover Border","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#c7c5c1"},"wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"map_border_width","label":"Map\'s Border Width","attributes":{"placeholder":4},"wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"map_color","label":"Map\'s Color","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#f2f0eb"},"wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"map_hover","label":"Map\'s Hover","type":"color_picker","colorpicker_options":{"customClass":"custom-class"},"attributes":{"placeholder":"#4682B4"},"wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"map_width","label":"Map\'s Width","attributes":{"placeholder":"300px"},"wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"map_height","label":"Map\'s Height","attributes":{"placeholder":"300px"},"wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"separator_last","type":"custom_html","value":"<hr>"},
{"name":"active","label":"Active","type":"checkbox"}';
		}
		
		
		if ($this->method == 'getFeaturedPostsCompanies') {
			$value = '{"name":"max_items","label":"Max Items","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"order_by","label":"Order By","type":"select2_from_array","options":{"date":"Date","random":"Random"},"allows_null":false,"wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"cache_expiration","label":"Cache Expiration Time for this section","attributes":{"placeholder":"0"},"hint":"home_cache_expiration_hint","wrapperAttributes":{"class":"form-group col-md-6"}},
{"name":"separator_last","type":"custom_html","value":"<hr>"},
{"name":"active","label":"Active","type":"checkbox"}';
		}
		
		
		if ($this->method == 'getStats') {
			$value = '{"name":"active","label":"Active","type":"checkbox"}';
		}
		
		
		if ($this->method == 'getTopAdvertising') {
			$value = '{"name":"active","label":"Active","type":"checkbox","hint":"getTopAdvertising_active_hint"}';
		}
		
		
		if ($this->method == 'getBottomAdvertising') {
			$value = '{"name":"active","label":"Active","type":"checkbox","hint":"getBottomAdvertising_active_hint"}';
		}
		
		$value = '[' . $formTitle . $value . ']';
		
		return $value;
	}
}

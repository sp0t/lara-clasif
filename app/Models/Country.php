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
use App\Models\Scopes\LocalizedScope;
use App\Observer\CountryObserver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Larapen\Admin\app\Models\Crud;
use Prologue\Alerts\Facades\Alert;

class Country extends BaseModel
{
	use Crud;
	
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'countries';
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $appends = ['icode'];
    protected $visible = [
    	'code',
		'name',
		'asciiname',
		'icode',
		'currency_code',
		'phone',
		'languages',
		'currency',
		'background_image',
		'admin_type',
		'admin_field_active'
	];
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    // public $timestamps = false;
    
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
        'code',
        'name',
        'asciiname',
        'capital',
        'continent_code',
        'tld',
        'currency_code',
        'phone',
        'languages',
		'background_image',
        'admin_type',
        'admin_field_active',
        'active'
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
    protected $dates = ['created_at', 'created_at'];
    
    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();
	
		Country::observe(CountryObserver::class);
		
        static::addGlobalScope(new ActiveScope());
		static::addGlobalScope(new LocalizedScope());
    }
    
    public function getNameHtml()
    {
		$currentUrl = preg_replace('#/(search)$#', '', url()->current());
		$url = $currentUrl . '/' . $this->getKey() . '/edit';
		
		$out = '<a href="' . $url . '">' . $this->asciiname . '</a>';
        
        return $out;
    }

	public function getActiveHtml()
	{
		if (!isset($this->active)) return false;

        return installAjaxCheckboxDisplay($this->{$this->primaryKey}, $this->getTable(), 'active', $this->active);
	}
	
	public function adminDivisionsBtn($xPanel = false)
	{
		$url = admin_url('countries/' . $this->id . '/admins1');
		
		$msg = trans('admin::messages.Admin. Divisions 1 of :country', ['country' => $this->asciiname]);
		$tooltip = ' data-toggle="tooltip" title="' . $msg . '"';
		
		$out = '';
		$out .= '<a class="btn btn-xs btn-default" href="' . $url . '"' . $tooltip . '>';
		$out .= '<i class="fa fa-eye"></i> ';
		$out .= mb_ucfirst(trans('admin::messages.admin. divisions 1'));
		$out .= '</a>';
		
		return $out;
	}
	
	public function citiesBtn($xPanel = false)
	{
		$url = admin_url('countries/' . $this->id . '/cities');
		
		$msg = trans('admin::messages.Cities of :country', ['country' => $this->asciiname]);
		$tooltip = ' data-toggle="tooltip" title="' . $msg . '"';
		
		$out = '';
		$out .= '<a class="btn btn-xs btn-default" href="' . $url . '"' . $tooltip . '>';
		$out .= '<i class="fa fa-eye"></i> ';
		$out .= mb_ucfirst(trans('admin::messages.cities'));
		$out .= '</a>';
		
		return $out;
	}
    
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }
    public function continent()
    {
        return $this->belongsTo(Continent::class, 'continent_code', 'code');
    }
	public function posts()
	{
		return $this->hasMany(Post::class, 'country_code')->orderBy('id', 'DESC');
	}
	public function users()
	{
		return $this->hasMany(User::class, 'country_code')->orderBy('id', 'DESC');
	}
	
	public function timeZones()
	{
		return $this->hasMany(TimeZone::class, 'country_code')->orderBy('time_zone_id', 'ASC');
	}
    
    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    public function scopeActive($query)
    {
        if (request()->segment(1) == admin_uri()) {
			if (Str::contains(Route::currentRouteAction(), 'Admin\CountryController')) {
				return $query;
			}
        }
        
        return $query->where('active', 1);
    }
    
    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */
    public function getIcodeAttribute()
    {
        return strtolower($this->attributes['code']);
    }
    
    public function getIdAttribute($value)
    {
        return $this->attributes['code'];
    }
    
    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
	public function setBackgroundImageAttribute($value)
	{
		$disk = StorageDisk::getDisk();
		$attribute_name = 'background_image';
		$destination_path = 'app/logo';
		
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
		
		// If laravel request->file('filename') resource OR base64 was sent, store it in the db
		try {
			if (fileIsUploaded($value)) {
				// Get file extension
				$extension = getUploadedFileExtension($value);
				if (empty($extension)) {
					$extension = 'jpg';
				}
				
				// Make the image (Size: 2000x1000)
				if (exifExtIsEnabled()) {
					$image = Image::make($value)->orientate()->resize(2000, 1000, function ($constraint) {
						$constraint->aspectRatio();
					})->encode($extension, 100);
				} else {
					$image = Image::make($value)->resize(2000, 1000, function ($constraint) {
						$constraint->aspectRatio();
					})->encode($extension, 100);
				}
				
				// Save the file on server
				$filename = uniqid('header-') . '.' . $extension;
				$disk->put($destination_path . '/' . $filename, $image->stream()->__toString());
				
				// Save the path to the database
				$this->attributes[$attribute_name] = $destination_path . '/' . $filename;
			} else {
				// Retrieve current value without upload a new file.
				if (!Str::startsWith($value, $destination_path)) {
					$value = $destination_path . last(explode($destination_path, $value));
				}
				$this->attributes[$attribute_name] = $value;
			}
		} catch (\Exception $e) {
			Alert::error($e->getMessage())->flash();
			$this->attributes[$attribute_name] = null;
			
			return false;
		}
	}
}

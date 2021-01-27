<?php
/**
 * LaraClassified - Geo Classified Ads CMS
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
use App\Helpers\UrlGen;
use App\Models\Scopes\ActiveScope;
use App\Models\Traits\TranslatedTrait;
use App\Observer\PageObserver;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Larapen\Admin\app\Models\Crud;
use Prologue\Alerts\Facades\Alert;

class Page extends BaseModel
{
    use Crud, Sluggable, SluggableScopeHelpers, TranslatedTrait;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pages';
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    // protected $primaryKey = 'id';
    protected $appends = ['tid'];
    
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
        'parent_id',
        'type',
        'name',
        'slug',
        'picture',
        'title',
        'content',
		'external_link',
        'name_color',
        'title_color',
		'target_blank',
        'excluded_from_footer',
        'active',
        'lft',
        'rgt',
        'depth',
        'translation_lang',
        'translation_of'
    ];
    public $translatable = ['name', 'slug', 'title', 'content'];
    
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
    protected $dates = ['created_at', 'updated_at'];
    
    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();
	
		Page::observe(PageObserver::class);
		
        static::addGlobalScope(new ActiveScope());
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'slug_or_name',
            ],
        ];
    }

    public function getNameHtml()
    {
        return '<a href="' . UrlGen::page($this) . '" target="_blank">' . $this->name . '</a>';
    }
    
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function parent()
    {
        return $this->belongsTo(Page::class, 'parent_id', 'translation_of')->where('translation_lang', config('app.locale'));
    }
    
    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    public function scopeType($builder, $type)
    {
        return $builder->where('type', $type)->orderBy('id', 'DESC');
    }
    
    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */
    // The slug is created automatically from the "name" field if no slug exists.
    public function getSlugOrNameAttribute()
    {
        if ($this->slug != '') {
            return $this->slug;
        }
        return $this->name;
    }

    public function getPictureAttribute()
    {
        if (!isset($this->attributes) || !isset($this->attributes['picture'])) {
            return null;
        }

        $value = $this->attributes['picture'];
	
		$disk = StorageDisk::getDisk();

        if (!$disk->exists($value)) {
            $value = null;
        }

        return $value;
    }
    
    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
	public function setTitleAttribute($value)
	{
		if (empty($value)) {
			$this->attributes['title'] = $this->name;
		} else {
			$this->attributes['title'] = $value;
		}
	}
	
    public function setPictureAttribute($value)
    {
		$disk = StorageDisk::getDisk();
        $attribute_name = 'picture';
        $destination_path = 'app/page';

        // If the image was erased
        if (empty($value)) {
            // delete the image from disk
			$disk->delete($this->picture);

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
				
				// Make the image
				if (exifExtIsEnabled()) {
					$image = Image::make($value)->orientate()->resize(1280, 1280, function ($constraint) {
						$constraint->aspectRatio();
					});
				} else {
					$image = Image::make($value)->resize(1280, 1280, function ($constraint) {
						$constraint->aspectRatio();
					});
				}
				
				// Generate a filename.
				$filename = md5($value . time()) . '.' . $extension;
	
				// Store the image on disk.
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

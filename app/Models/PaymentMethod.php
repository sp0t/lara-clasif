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

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\CompatibleApiScope;
use App\Observer\PaymentMethodObserver;
use Illuminate\Support\Str;
use Larapen\Admin\app\Models\Crud;

class PaymentMethod extends BaseModel
{
	use Crud;
	
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_methods';
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    // protected $primaryKey = 'id';
    
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
    protected $fillable = [
    	'id',
		'name',
		'display_name',
		'description',
		'has_ccbox',
		'is_compatible_api',
		'countries',
		'active',
		'lft',
		'rgt',
		'depth',
		'parent_id',
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
    // protected $dates = [];
    
    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();
	
		PaymentMethod::observe(PaymentMethodObserver::class);
		
        static::addGlobalScope(new ActiveScope());
		static::addGlobalScope(new CompatibleApiScope());
    }
    
    public function getCountriesHtml()
    {
        $out = strtoupper(trans('admin::messages.All'));
        if (isset($this->countries) && !empty($this->countries)) {
            $countriesCropped = Str::limit($this->countries, 50, ' [...]');
            $out = '<div title="' . $this->countries . '">' . $countriesCropped . '</div>';
        }
        
        return $out;
    }
    
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function payment()
    {
        return $this->hasMany(Payment::class, 'payment_method_id');
    }
    
    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    public function scopeActive($builder)
    {
        return $builder->where('active', 1);
    }
    
    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */
	public function getDescriptionAttribute($value)
	{
		if (isset($this->name) && $this->name == 'offlinepayment') {
			if (mb_strlen(trans('offlinepayment::messages.payment_method_description')) > 0) {
				$value = trans('offlinepayment::messages.payment_method_description');
			}
		}
		
		return $value;
	}
	
    public function getCountriesAttribute($value)
    {
        // Get a custom display value
        $value = str_replace(',', ', ', strtoupper($value));
        $value = strtoupper($value);
        
        return $value;
    }
    
    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    public function setCountriesAttribute($value)
    {
        // Get the MySQL right value
        $value = preg_replace('/(,|;)\s*/', ',', $value);
        $value = strtolower($value);
        
        // Check if the entry is removed
        if (empty($value) || $value == strtolower(trans('admin::messages.All'))) {
            $value = null;
        }
        
        $this->attributes['countries'] = $value;
        
        return $value;
    }
}

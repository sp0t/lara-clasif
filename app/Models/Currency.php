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


use App\Observer\CurrencyObserver;
use Larapen\Admin\app\Models\Crud;

class Currency extends BaseModel
{
	use Crud;
	
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'currencies';
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $appends = ['symbol'];
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    //public $timestamps = false;
    
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
        'html_entity',
        'font_arial',
        'font_code2000',
        'unicode_decimal',
        'unicode_hex',
        'in_left',
        'decimal_places',
        'decimal_separator',
        'thousand_separator'
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
		
		Currency::observe(CurrencyObserver::class);
	}
	
	public function getNameHtml()
	{
		$currentUrl = preg_replace('#/(search)$#', '', url()->current());
		$url = $currentUrl . '/' . $this->getKey() . '/edit';
		
		$out = '<a href="' . $url . '">' . $this->name . '</a>';
		
		return $out;
	}
	
	public function getPositionHtml()
	{
		if ($this->in_left == 1) {
			return '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
		} else {
			return '<i class="fa fa-square-o" aria-hidden="true"></i>';
		}
	}
    
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function countries()
    {
        return $this->hasMany(Country::class, 'currency_code', 'code');
    }
    
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
    public function getIdAttribute($value)
    {
        return $this->attributes['code'];
    }
    
    public function getSymbolAttribute()
    {
        $symbol = $this->attributes['html_entity'];
        if (trim($symbol) == '') {
            $symbol = $this->attributes['font_arial'];
        }
        if (trim($symbol) == '') {
            $symbol = $this->attributes['font_code2000'];
        }
        if (trim($symbol) == '') {
            $symbol = $this->attributes['code'];
        }
        
        return $symbol;
    }
    
    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    public function setSymbolAttribute($value)
    {
        $this->attributes['font_arial'] = $value;
        $this->attributes['font_code2000'] = $value;
    }
}

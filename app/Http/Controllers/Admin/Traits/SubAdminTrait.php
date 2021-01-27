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

namespace App\Http\Controllers\Admin\Traits;


use App\Models\Scopes\ActiveScope;
use Illuminate\Support\Str;

trait SubAdminTrait
{
    /**
     * Increment new Entries Codes
     *
     * @param $prefix
     * @return int|string
     */
    public function autoIncrementCode($prefix)
    {
        // Init.
        $startAt = 0;
        $customPrefix = config('larapen.core.locationCodePrefix', 'Z');
        $zeroLead = 3;
        
        // Get the latest Entry
        $latestAddedEntry = $this->xPanel->model->withoutGlobalScope(ActiveScope::class)
            ->where('country_code', $this->countryCode)
            ->where('code', 'LIKE', $prefix . $customPrefix . '%')
            ->orderBy('code', 'DESC')
            ->first();
        
        if (!empty($latestAddedEntry)) {
            $codeTab = explode($prefix, $latestAddedEntry->code);
            $latestAddedId = (isset($codeTab[1])) ? $codeTab[1] : null;
            if (!empty($latestAddedId)) {
                if (is_numeric($latestAddedId)) {
                    $newId = $latestAddedId + 1;
                } else {
                    $newId = $this->alphanumericToUniqueIncrementation($latestAddedId, $startAt, $zeroLead, $customPrefix);
                }
            } else {
                $newId = $customPrefix . zeroLead($startAt + 1, $zeroLead);
            }
        } else {
            $newId = $customPrefix . zeroLead($startAt + 1, $zeroLead);
        }
        
        // Full new ID
        $newId = $prefix . $newId;
        
        return $newId;
    }
    
    /**
     * Increment existing alphanumeric value by Transforming the given value
     * e.g. AB => ZZ001 => ZZ002 => ZZ003 ...
     *
     * @param $value
     * @param $startAt
     * @param $zeroLead
     * @param $customPrefix
     * @return string
     */
    private function alphanumericToUniqueIncrementation($value, $startAt, $zeroLead, $customPrefix)
    {
        if (!empty($value)) {
            // Numeric value
            if (is_numeric($value)) {
                
                $value = $customPrefix . zeroLead($value + 1);
                
            } // NOT numeric value
            else {
                
                // Value contains the Custom Prefix
                if (Str::startsWith($value, $customPrefix)) {
                    
                    $prefixLoop = '';
                    $partOfValue = '';
                    
                    $tmp = explode($customPrefix, $value);
                    if (count($tmp) > 0) {
                        foreach ($tmp as $item) {
                            if (!empty($item)) {
                                $partOfValue = $item;
                                break;
                            } else {
                                $prefixLoop .= $customPrefix;
                            }
                        }
                    }
                    
                    if (!empty($partOfValue)) {
                        if (is_numeric($partOfValue)) {
                            $tmpValue = zeroLead($partOfValue + 1, $zeroLead);
                        } else {
                            // If the part of the value is not numeric, Get a (sub-)new unique code
                            $tmpValue = $this->alphanumericToUniqueIncrementation($partOfValue, $startAt, $zeroLead, $customPrefix);
                        }
                    } else {
                        $tmpValue = zeroLead($startAt + 1, $zeroLead);
                    }
                    
                    $value = $prefixLoop . $tmpValue;
                    
                } // Value NOT contains the Custom Prefix
                else {
                    $value = $customPrefix . zeroLead($startAt + 1, $zeroLead);
                }
            }
            
        } else {
            $value = $customPrefix . zeroLead($startAt + 1, $zeroLead);
        }
        
        return $value;
    }
}

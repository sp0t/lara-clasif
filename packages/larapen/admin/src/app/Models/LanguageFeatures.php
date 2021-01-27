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

namespace Larapen\Admin\app\Models;

use Illuminate\Support\Facades\Cache;

trait LanguageFeatures
{
    public static function getActiveLanguagesArray()
    {
        $cacheExpiration = config('settings.optimization.cache_expiration', 86400);
        $activeLanguages = Cache::remember('languages.active.array', $cacheExpiration, function () {
            $activeLanguages = self::where('active', 1)->get()->toArray();
            return $activeLanguages;
        });
        
        $localizableLanguagesArray = [];

        if (count($activeLanguages) > 0) {
            foreach ($activeLanguages as $key => $lang) {
                $localizableLanguagesArray[$lang['abbr']] = $lang;
            }

            return $localizableLanguagesArray;
        }

        return config('laravellocalization.supportedLocales');
    }

    public static function findByAbbr($abbr = false)
    {
        return self::where('abbr', $abbr)->first();
    }
}

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

namespace Larapen\TextToImage\Libraries;

class Settings
{
    public $color = '#000000';
    public $backgroundColor = '#FFFFFF';
    public $fontFamily = null;
    public $fontSize = 12;
    public $padding = 5;
    public $quality = 90;
    public $format = IMAGETYPE_JPEG;
    public $blur = 0;
    public $pixelate = 0;
    
    public static function createFromIni($iniFile)
    {
        $settings = new Settings();
        
        // Cannot find settings file
        if (!realpath($iniFile)) {
            return $settings;
        }
        
        // Parse config file
        $properties = @parse_ini_file($iniFile);
        if (empty($properties)) {
            return $settings;
        }
        
        $settings->assignProperties($properties);
        
        return $settings;
    }
    
    public function assignProperties($properties)
    {
        if (empty($properties) || !is_array($properties)) {
            return;
        }
        
        foreach ($properties as $name => $value) {
            if (!property_exists($this, $name)) {
                continue;
            }
            
            $this->$name = $value;
        }
    }
}

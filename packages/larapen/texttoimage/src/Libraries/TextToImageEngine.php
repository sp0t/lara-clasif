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

use Larapen\TextToImage\Libraries\Intervention\Image\Image;

class TextToImageEngine
{
    /** @var Settings */
    protected $settings;
    
    /** @var Image */
    protected $image;
    
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }
    
    public function setText($string)
    {
        $padding = $this->settings->padding;
        $fontSize = $this->settings->fontSize;
        $color = $this->settings->color;
        $fontFamily = $this->settings->fontFamily;
        
        $bounds = $this->getTextBounds($string);
        
        $this->image = Image::canvas($bounds->width, $bounds->height, $this->settings->backgroundColor);
        $this->image->text($string, $padding, $fontSize + $padding, $fontSize, $color, 0, $fontFamily);
        
        if ((float)$this->settings->blur > 0) {
            $this->image->blur($this->settings->blur);
        }
        
        if ((float)$this->settings->pixelate > 0) {
            $this->image->pixelate($this->settings->pixelate);
        }
    }
    
    /**
     * Get the physical size of text with a given string and font settings
     *
     * @param $string
     *
     * @return BoundingBox
     */
    protected function getTextBounds($string)
    {
        $fontSize = $this->settings->fontSize;
        $fontFile = $this->settings->fontFamily;
        
        list($llx, $lly, $lrx, $lry, $urx, $ury, $ulx, $uly) = imagettfbbox($fontSize, 0, $fontFile, $string);
        $width = abs($urx - $llx) + ($this->settings->padding * 2);
        $height = abs($ury - $lly) + ($this->settings->padding * 2);
        
        return new BoundingBox($width, $height, $this->settings->padding);
    }
    
    /**
     * @return string
     */
    public function getEncodedImage()
    {
        return $this->image->encode($this->settings->format, $this->settings->quality);
    }
    
    /**
     * Get image as base64 string
     */
    public function getEmbeddedImage()
    {
        $format = "<img src=\"data:%s;charset=utf-8;base64,%s\"/>";
        $mimeType = Mimetype::getMimetype($this->settings->format);
        if (empty($mimeType)) {
            trigger_error('Invalid filetype: ' . $this->settings->format, E_USER_WARNING);
        }
        
        $encoded = $this->getEncodedImage();
        $encoded = base64_encode($encoded);
        
        return sprintf($format, $mimeType, $encoded);
    }
}

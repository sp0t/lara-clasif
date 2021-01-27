<?php
/**
 * Ok, glad you are here
 * first we get a config instance, and set the settings
 * $config = HTMLPurifier_Config::createDefault();
 * $config->set('Core.Encoding', $this->config->get('purifier.encoding'));
 * $config->set('Cache.SerializerPath', $this->config->get('purifier.cachePath'));
 * if ( ! $this->config->get('purifier.finalize')) {
 *     $config->autoFinalize = false;
 * }
 * $config->loadArray($this->getConfig());
 *
 * You must NOT delete the default settings
 * anything in settings should be compacted with params that needed to instance HTMLPurifier_Config.
 *
 * @link http://htmlpurifier.org/live/configdoc/plain.html
 */

return [
    'encoding'      => 'UTF-8',
    'finalize'      => true,
    'cachePath'     => storage_path('app/purifier'),
    'cacheFileMode' => 0755,
    'settings'      => [
        'default' => [
            'HTML.Doctype'             => 'HTML 4.01 Transitional', // 'XHTML 1.0 Strict' (NOT supports a[target] attr.)
			'Attr.AllowedFrameTargets' => '_blank',
			'Attr.AllowedRel'          => 'nofollow',
            'HTML.Allowed'             => 'div,b,strong,i,em,ul,ol,li,p[style],br,span[style],blockquote[cite],
                a[href|title|target|rel|style],
                img[width|height|alt|src],
                table[summary],
                table[class],
                table[id],
                table[border],
                table[cellpadding],
                table[cellspacing],
                table[style],
                table[width],
                colgroup,
                col[width]
                td[abbr],
                td[align],
                td[class],
                td[id],
                td[colspan],
                td[rowspan],
                td[style],
                td[valign],
                tr[align],
                tr[class],
                tr[id],
                tr[style],
                tr[valign],
                th[abbr],
                th[align],
                th[class],
                th[id],
                th[colspan],
                th[rowspan],
                th[style],
                th[valign],
                h1[style],
                h2[style],
                h3[style],
                h4[style],
                h5[style],
                h6[style],',
            'CSS.AllowedProperties'    => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align',
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty'   => true,
        ],
        'test'    => [
            'Attr.EnableID' => true
        ],
        "youtube" => [
            "HTML.SafeIframe"      => 'true',
            "URI.SafeIframeRegexp" => "%^(http://|https://|//)(www.youtube.com/embed/|player.vimeo.com/video/)%",
        ],
    ],

];

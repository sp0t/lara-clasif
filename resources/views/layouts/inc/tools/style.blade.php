<?php
// Get values from Settings
$skin = config('settings.style.app_skin');

// CSS Buffer
$cssBuffer = '' . "\n";

/* === CSS Version === */
$cssBuffer .= '/* === v' . config('app.version') . ' === */' . "\n";

/* === Body === */
$cssBuffer .= '/* === Body === */' . "\n";
if (!empty(config('settings.style.page_width'))) {
	$pageWidth = strToInt(config('settings.style.page_width')) . 'px';
	$cssBuffer .= '@media (min-width: 1200px) {';
	$cssBuffer .= '.container { max-width: ' . $pageWidth . '; }';
	$cssBuffer .= '}';
}
if (!empty(config('settings.style.body_background_color')) or !empty(config('settings.style.body_text_color')) or !empty(config('settings.style.body_background_image'))) {
	$cssBuffer .= 'body {';
	if (!empty(config('settings.style.body_background_color'))) {
		$cssBuffer .= 'background-color: ' . config('settings.style.body_background_color') . ';';
	}
	if (!empty(config('settings.style.body_text_color'))) {
		$cssBuffer .= 'color: ' . config('settings.style.body_text_color') . ';';
	}
	if (!empty(config('settings.style.body_background_image'))) {
		$cssBuffer .= 'background-image: url(' . imgUrl(config('settings.style.body_background_image'), 'css') . ');';
		$cssBuffer .= 'background-repeat: repeat;';
		if (!empty(config('settings.style.body_background_image_fixed'))) {
			$cssBuffer .= 'background-attachment: fixed;';
		}
	}
	$cssBuffer .= '}';
}
if (!empty(config('settings.style.body_background_color')) or !empty(config('settings.style.body_background_image'))) {
	$cssBuffer .= '#wrapper { background-color: rgba(0, 0, 0, 0); }';
}
if (!empty(config('settings.style.title_color'))) {
	$cssBuffer .= '.' . $skin . ' h1,';
	$cssBuffer .= '.' . $skin . ' h2,';
	$cssBuffer .= '.' . $skin . ' h3,';
	$cssBuffer .= '.' . $skin . ' h4,';
	$cssBuffer .= '.' . $skin . ' h5,';
	$cssBuffer .= '.' . $skin . ' h6 {';
	$cssBuffer .= 'color: ' . config('settings.style.title_color') . ';';
	$cssBuffer .= '}';
}
if (!empty(config('settings.style.link_color'))) {
	$cssBuffer .= '.' . $skin . ' a,';
	$cssBuffer .= '.' . $skin . ' .link-color {';
	$cssBuffer .= 'color: ' . config('settings.style.link_color') . ';';
	$cssBuffer .= '}';
}
if (!empty(config('settings.style.link_color_hover'))) {
	$cssBuffer .= '.' . $skin . ' a:hover,';
	$cssBuffer .= '.' . $skin . ' a:focus {';
	$cssBuffer .= 'color: ' . config('settings.style.link_color_hover') . ';';
	$cssBuffer .= '}';
}
if (!empty(config('settings.style.progress_background_color'))) {
	$cssBuffer .= '.' . $skin . ' .pace .pace-progress { background: ' . config('settings.style.progress_background_color') . ' none repeat scroll 0 0; }';
}

/* === Header === */
$cssBuffer .= "\n" . '/* === Header === */' . "\n";
if (!empty(config('settings.style.header_sticky'))) {
	$cssBuffer .= '.navbar.navbar-site { position: fixed !important; }';
} else {
	$cssBuffer .= '.navbar.navbar-site { position: absolute !important; }';
}
if (!empty(config('settings.style.header_height'))) {
	// Default values
	$defaultHeight = 80;
	$defaultPadding = 20;
	$defaultMargin = 0;
	
	// Get known value from Settings
	$headerHeight = strToInt(config('settings.style.header_height'));
	
	$headerBottomBorderSize = 0;
	if (!empty(config('settings.style.header_bottom_border_width'))) {
		$headerBottomBorderSize = strToInt(config('settings.style.header_bottom_border_width'));
	}
	$wrapperPaddingTop = $headerHeight + $headerBottomBorderSize;
	
	// Calculate unknown values
	$padding = floor(($headerHeight * $defaultPadding) / $defaultHeight);
	$margin = floor(($headerHeight * $defaultMargin) / $defaultHeight);
	$padding = abs(($padding - ($defaultPadding / 2)) * 2);
	$margin = abs(($margin - ($defaultMargin / 2)) * 2);
	
	// $wrapperPaddingTop + 4 for default margin/padding values
	$cssBuffer .= '#wrapper { padding-top: ' . ($wrapperPaddingTop + 4) . 'px; }';
	
	$cssBuffer .= '.navbar.navbar-site .navbar-identity .navbar-brand {';
	$cssBuffer .= 'height: ' . $headerHeight . 'px;';
	$cssBuffer .= 'padding-top: ' . $padding . 'px;';
	$cssBuffer .= 'padding-bottom: ' . $padding . 'px;';
	$cssBuffer .= '}';
	
	$cssBuffer .= '@media (max-width: 767px) {';
	$cssBuffer .= '#wrapper { padding-top: ' . $wrapperPaddingTop . 'px; }';
	$cssBuffer .= '.navbar-site.navbar .navbar-identity { height: ' . $headerHeight . 'px; }';
	$cssBuffer .= '.navbar-site.navbar .navbar-identity .btn,';
	$cssBuffer .= '.navbar-site.navbar .navbar-identity .navbar-toggler { margin-top: ' . $padding . 'px; }';
	$cssBuffer .= '}';
	
	$cssBuffer .= '@media (max-width: 479px) {';
	$cssBuffer .= '#wrapper { padding-top: ' . $wrapperPaddingTop . 'px; }';
	$cssBuffer .= '.navbar-site.navbar .navbar-identity { height: ' . $headerHeight . 'px; }';
	$cssBuffer .= '}';
	
	$cssBuffer .= '@media (min-width: 768px) and (max-width: 992px) {';
	$cssBuffer .= '.navbar.navbar-site .navbar-identity a.logo { height: ' . $headerHeight . 'px; }';
	$cssBuffer .= '.navbar.navbar-site .navbar-identity a.logo-title { padding-top: ' . $padding . 'px; }';
	$cssBuffer .= '}';
	
	$cssBuffer .= '@media (min-width: 768px) {';
	// $cssBuffer .= '.navbar.navbar-site .navbar-identity a.logo { height: ' . $headerHeight . 'px; }';
	// $cssBuffer .= '.navbar.navbar-site .navbar-identity a.logo-title { padding-top: ' . $padding . 'px; }';
	$cssBuffer .= '.navbar.navbar-site .navbar-identity { margin-top: ' . $margin . 'px; }';
	$cssBuffer .= '.navbar.navbar-site .navbar-collapse { margin-top: ' . $margin . 'px; }';
	$cssBuffer .= '}';
}
if (!empty(config('settings.style.header_background_color'))) {
	$cssBuffer .= '.navbar.navbar-site { background-color: ' . config('settings.style.header_background_color') . ' !important; }';
}
if (!empty(config('settings.style.header_bottom_border_width'))) {
	$headerBottomBorderSize = strToInt(config('settings.style.header_bottom_border_width')) . 'px';
	$cssBuffer .= '.navbar.navbar-site {';
	$cssBuffer .= 'border-bottom-width: ' . $headerBottomBorderSize . ' !important;';
	$cssBuffer .= 'border-bottom-style: solid !important;';
	$cssBuffer .= '}';
}
if (!empty(config('settings.style.header_bottom_border_color'))) {
	$cssBuffer .= '.navbar.navbar-site { border-bottom-color: ' . config('settings.style.header_bottom_border_color') . ' !important; }';
}
if (!empty(config('settings.style.header_link_color'))) {
	$cssBuffer .= '@media (min-width: 768px) {';
	$cssBuffer .= '.navbar.navbar-site ul.navbar-nav > li > a {';
	$cssBuffer .= 'color: ' . config('settings.style.header_link_color') . ' !important;';
	$cssBuffer .= '}';
	$cssBuffer .= '}';
	
	$cssBuffer .= '.navbar.navbar-site ul.navbar-nav > .open > a,';
	$cssBuffer .= '.navbar.navbar-site ul.navbar-nav > .open > a:focus,';
	$cssBuffer .= '.navbar.navbar-site ul.navbar-nav > .open > a:hover {';
	$cssBuffer .= 'color: ' . config('settings.style.header_link_color') . ' !important;';
	$cssBuffer .= '}';
}
if (!empty(config('settings.style.header_link_color_hover'))) {
	$cssBuffer .= '@media (min-width: 768px) {';
	$cssBuffer .= '.navbar.navbar-site ul.navbar-nav > li > a:hover,';
	$cssBuffer .= '.navbar.navbar-site ul.navbar-nav > li > a:focus {';
	$cssBuffer .= 'color: ' . config('settings.style.header_link_color_hover') . ' !important;';
	$cssBuffer .= '}';
	$cssBuffer .= '}';
}

/* === Footer === */
$cssBuffer .= "\n" . '/* === Footer === */' . "\n";
if (!empty(config('settings.style.footer_background_color'))) {
	$cssBuffer .= '.footer-content { background: ' . config('settings.style.footer_background_color') . '; }';
}
if (!empty(config('settings.style.footer_text_color'))) {
	$cssBuffer .= '.footer-content { color: ' . config('settings.style.footer_text_color') . '; }';
}
if (!empty(config('settings.style.footer_title_color'))) {
	$cssBuffer .= '.footer-title { color: ' . config('settings.style.footer_title_color') . '; }';
}
if (!empty(config('settings.style.footer_link_color'))) {
	$cssBuffer .= '.footer-nav li a,';
	$cssBuffer .= '.copy-info a {';
	$cssBuffer .= 'color: ' . config('settings.style.footer_link_color') . ';';
	$cssBuffer .= '}';
}
if (!empty(config('settings.style.footer_link_color_hover'))) {
	$cssBuffer .= '.' . $skin . ' .footer-nav li a:hover,';
	$cssBuffer .= '.' . $skin . ' .footer-nav li a:focus,';
	$cssBuffer .= '.copy-info a:focus,';
	$cssBuffer .= '.copy-info a:hover {';
	$cssBuffer .= 'color: ' . config('settings.style.footer_link_color_hover') . ';';
	$cssBuffer .= '}';
}
if (!empty(config('settings.style.payment_icon_top_border_width'))) {
	$paymentIconTopBorderSize = strToInt(config('settings.style.payment_icon_top_border_width')) . 'px';
	$cssBuffer .= '.paymanet-method-logo { border-top-width: ' . $paymentIconTopBorderSize . '; }';
	$cssBuffer .= '.footer-content hr { border-top-width: ' . $paymentIconTopBorderSize . '; }';
}
if (!empty(config('settings.style.payment_icon_top_border_color'))) {
	$cssBuffer .= '.paymanet-method-logo { border-top-color: ' . config('settings.style.payment_icon_top_border_color') . '; }';
	$cssBuffer .= '.footer-content hr { border-top-color: ' . config('settings.style.payment_icon_top_border_color') . '; }';
}
if (!empty(config('settings.style.payment_icon_bottom_border_width'))) {
	$paymentIconBottomBorderSize = strToInt(config('settings.style.payment_icon_bottom_border_width')) . 'px';
	$cssBuffer .= '.paymanet-method-logo { border-bottom-width: ' . $paymentIconBottomBorderSize . '; }';
}
if (!empty(config('settings.style.payment_icon_bottom_border_color'))) {
	$cssBuffer .= '.paymanet-method-logo { border-bottom-color: ' . config('settings.style.payment_icon_bottom_border_color') . '; }';
}

/* === Button: Add Listing === */
$cssBuffer .= "\n" . '/* === Button: Add Listing === */' . "\n";
if (!empty(config('settings.style.btn_post_bg_top_color')) || !empty(config('settings.style.btn_post_bg_bottom_color'))) {
	$btnBackgroundTopColor = '#ffeb43';
	$btnBackgroundBottomColor = '#fcde11';
	if (!empty(config('settings.style.btn_post_bg_top_color'))) {
		$btnBackgroundTopColor = config('settings.style.btn_post_bg_top_color');
	}
	if (!empty(config('settings.style.btn_post_bg_bottom_color'))) {
		$btnBackgroundBottomColor = config('settings.style.btn_post_bg_bottom_color');
	}
	$cssBuffer .= 'a.btn-add-listing,';
	$cssBuffer .= 'button.btn-add-listing,';
	$cssBuffer .= '.navbar.navbar-site ul.navbar-nav > li.postadd > a.btn-add-listing,';
	$cssBuffer .= '#homepage a.btn-add-listing {';
	$cssBuffer .= 'background-image: linear-gradient(to bottom, ' . $btnBackgroundTopColor . ' 0,' . $btnBackgroundBottomColor . ' 100%);';
	$cssBuffer .= '}';
}
if (!empty(config('settings.style.btn_post_border_color'))) {
	$cssBuffer .= 'a.btn-add-listing,';
	$cssBuffer .= 'button.btn-add-listing,';
	$cssBuffer .= '.navbar.navbar-site ul.navbar-nav > li.postadd > a.btn-add-listing,';
	$cssBuffer .= '#homepage a.btn-add-listing {';
	$cssBuffer .= 'border-color: ' . config('settings.style.btn_post_border_color') . ';';
	$cssBuffer .= '}';
}
if (!empty(config('settings.style.btn_post_text_color'))) {
	$cssBuffer .= 'a.btn-add-listing,';
	$cssBuffer .= 'button.btn-add-listing,';
	$cssBuffer .= '.navbar.navbar-site ul.navbar-nav > li.postadd > a.btn-add-listing,';
	$cssBuffer .= '#homepage a.btn-add-listing {';
	$cssBuffer .= 'color: ' . config('settings.style.btn_post_text_color') . ' !important;';
	$cssBuffer .= '}';
}
if (!empty(config('settings.style.btn_post_bg_top_color_hover')) || !empty(config('settings.style.btn_post_bg_bottom_color_hover'))) {
	$btnBackgroundTopColorHover = '#fff860';
	$btnBackgroundBottomColorHover = '#ffeb43';
	if (!empty(config('settings.style.btn_post_bg_top_color'))) {
		$btnBackgroundTopColorHover = config('settings.style.btn_post_bg_top_color');
	}
	if (!empty(config('settings.style.btn_post_bg_bottom_color'))) {
		$btnBackgroundBottomColorHover = config('settings.style.btn_post_bg_bottom_color');
	}
	$cssBuffer .= 'a.btn-add-listing:hover,';
	$cssBuffer .= 'a.btn-add-listing:focus,';
	$cssBuffer .= 'button.btn-add-listing:hover,';
	$cssBuffer .= 'button.btn-add-listing:focus,';
	$cssBuffer .= 'li.postadd > a.btn-add-listing:hover,';
	$cssBuffer .= 'li.postadd > a.btn-add-listing:focus,';
	$cssBuffer .= '#homepage a.btn-add-listing:hover,';
	$cssBuffer .= '#homepage a.btn-add-listing:focus {';
	$cssBuffer .= 'background-image: linear-gradient(to bottom, ' . $btnBackgroundTopColorHover . ' 0,' . $btnBackgroundBottomColorHover . ' 100%);';
	$cssBuffer .= '}';
}
if (!empty(config('settings.style.btn_post_border_color_hover'))) {
	$cssBuffer .= 'a.btn-add-listing:hover,';
	$cssBuffer .= 'a.btn-add-listing:focus,';
	$cssBuffer .= 'button.btn-add-listing:hover,';
	$cssBuffer .= 'button.btn-add-listing:focus,';
	$cssBuffer .= '.navbar.navbar-site ul.navbar-nav > li.postadd > a.btn-add-listing:hover,';
	$cssBuffer .= '.navbar.navbar-site ul.navbar-nav > li.postadd > a.btn-add-listing:focus,';
	$cssBuffer .= '#homepage a.btn-add-listing:hover,';
	$cssBuffer .= '#homepage a.btn-add-listing:focus {';
	$cssBuffer .= 'border-color: ' . config('settings.style.btn_post_border_color_hover') . ' !important;';
	$cssBuffer .= '}';
}
if (!empty(config('settings.style.btn_post_text_color_hover'))) {
	$cssBuffer .= 'a.btn-add-listing:hover,';
	$cssBuffer .= 'a.btn-add-listing:focus,';
	$cssBuffer .= 'button.btn-add-listing:hover,';
	$cssBuffer .= 'button.btn-add-listing:focus,';
	$cssBuffer .= '.navbar.navbar-site ul.navbar-nav > li.postadd > a.btn-add-listing:hover,';
	$cssBuffer .= '.navbar.navbar-site ul.navbar-nav > li.postadd > a.btn-add-listing:focus,';
	$cssBuffer .= '#homepage a.btn-add-listing:hover,';
	$cssBuffer .= '#homepage a.btn-add-listing:focus {';
	$cssBuffer .= 'color: ' . config('settings.style.btn_post_text_color_hover') . ' !important;';
	$cssBuffer .= '}';
}

/* === Other: Grid View Columns === */
$cssBuffer .= "\n" . '/* === Other: Grid View Columns === */' . "\n";
if (!empty(config('settings.listing.grid_view_cols'))) {
	$gridViewCols = config('settings.listing.grid_view_cols');
	$gridWidth = round_val(100 / $gridViewCols, 2);
	
	/* Ribbons */
	$gridRibbonLeft = round_val((($gridViewCols * 30) / 4) - (4 - $gridViewCols), 2); 		   /* 30px => 4 items */
	$noSidebarGridRibbonLeft = round_val((($gridViewCols * 22) / 4) - (4 - $gridViewCols), 2); /* 22px => 4 items */
	
	/* Ribbons: Media Screen - Dynamic */
	$gridRibbonLeft992 = round_val((($gridViewCols * 36) / 4) - (4 - $gridViewCols), 2); 		  /* 36px => 4 items */
	$noSidebarGridRibbonLeft992 = round_val((($gridViewCols * 26) / 4) - (4 - $gridViewCols), 2); /* 26px => 4 items */
	$gridRibbonLeft768 = round_val((($gridViewCols * 35) / 4) - (4 - $gridViewCols), 2); 		  /* 35px => 4 items */
	$noSidebarGridRibbonLeft768 = round_val((($gridViewCols * 25) / 4) - (4 - $gridViewCols), 2); /* 25px => 4 items */
	
	if (config('lang.direction') == 'rtl') {
		$cssBuffer .= '.item-list.make-grid { width: ' . $gridWidth . '% !important; }';
		$cssBuffer .= '@media (max-width: 767px) {';
		$cssBuffer .= '.make-grid .item-list { width: 50% !important; }';
		$cssBuffer .= '}';
		
		/* Ribbons */
		$cssBuffer .= '.make-grid .item-list .cornerRibbons { right: -' . $gridRibbonLeft . '%; top: 8%; }';
		$cssBuffer .= '.make-grid.noSideBar .item-list .cornerRibbons { right: -' . $noSidebarGridRibbonLeft . '%; top: 8%; }';
		
		/* Ribbons: Media Screen - Dynamic */
		$cssBuffer .= '@media (min-width: 992px) and (max-width: 1119px) {';
		$cssBuffer .= '.make-grid .item-list .cornerRibbons { right: -' . $gridRibbonLeft992 . '%; top: 8%; }';
		$cssBuffer .= '.make-grid.noSideBar .item-list .cornerRibbons { right: -' . $noSidebarGridRibbonLeft992 . '%; top: 8%; }';
		$cssBuffer .= '}';
		$cssBuffer .= '@media (min-width: 768px) and (max-width: 991px) {';
		$cssBuffer .= '.make-grid .item-list .cornerRibbons { right: -' . $gridRibbonLeft768 . '%; top: 8%; }';
		$cssBuffer .= '.make-grid.noSideBar .item-list .cornerRibbons { right: -' . $noSidebarGridRibbonLeft768 . '%; top: 8%; }';
		$cssBuffer .= '}';
		/* Ribbons: Media Screen - Fix */
		$cssBuffer .= '@media (max-width: 767px) {';
		$cssBuffer .= '.make-grid .item-list { width: 50%; }';
		$cssBuffer .= '}';
		$cssBuffer .= '@media (max-width: 767px) {';
		$cssBuffer .= '.make-grid .item-list .cornerRibbons, .make-grid.noSideBar .item-list .cornerRibbons { right: -10%; top: 8%; }';
		$cssBuffer .= '}';
		$cssBuffer .= '@media (max-width: 736px) {';
		$cssBuffer .= '.make-grid .item-list .cornerRibbons, .make-grid.noSideBar .item-list .cornerRibbons { right: -12%; top: 8%; }';
		$cssBuffer .= '}';
		$cssBuffer .= '@media (max-width: 667px) {';
		$cssBuffer .= '.make-grid .item-list .cornerRibbons, .make-grid.noSideBar .item-list .cornerRibbons { right: -13%; top: 8%; }';
		$cssBuffer .= '}';
		$cssBuffer .= '@media (max-width: 568px) {';
		$cssBuffer .= '.make-grid .item-list .cornerRibbons, .make-grid.noSideBar .item-list .cornerRibbons { right: -14%; top: 8%; }';
		$cssBuffer .= '}';
		$cssBuffer .= '@media (max-width: 480px) {';
		$cssBuffer .= '.make-grid .item-list .cornerRibbons, .make-grid.noSideBar .item-list .cornerRibbons { right: -22%; top: 8%; }';
		$cssBuffer .= '}';
		
		/* Item Border */
		$cssBuffer .= '.adds-wrapper.make-grid .item-list:nth-child(4n+4),';
		$cssBuffer .= '.category-list.make-grid .item-list:nth-child(4n+4) {';
		$cssBuffer .= 'border-left: solid 1px #ddd;';
		$cssBuffer .= '}';
		
		$cssBuffer .= '.adds-wrapper.make-grid .item-list:nth-child(3n+3),';
		$cssBuffer .= '.category-list.make-grid .item-list:nth-child(3n+3) {';
		$cssBuffer .= 'border-left: solid 1px #ddd;';
		$cssBuffer .= '}';
		
		$cssBuffer .= '.adds-wrapper.make-grid .item-list:nth-child(' . $gridViewCols . 'n+' . $gridViewCols . '),';
		$cssBuffer .= '.category-list.make-grid .item-list:nth-child(' . $gridViewCols . 'n+' . $gridViewCols . ') {';
		$cssBuffer .= 'border-left: none;';
		$cssBuffer .= '}';
		
		$cssBuffer .= '@media (max-width: 991px) {';
		$cssBuffer .= '.adds-wrapper.make-grid .item-list:nth-child(' . $gridViewCols . 'n+' . $gridViewCols . '),';
		$cssBuffer .= '.category-list.make-grid .item-list:nth-child(' . $gridViewCols . 'n+' . $gridViewCols . ') {';
		$cssBuffer .= 'border-left-style: solid;';
		$cssBuffer .= 'border-left-width: 1px;';
		$cssBuffer .= 'border-left-color: #ddd;';
		$cssBuffer .= '}';
		$cssBuffer .= '}';
	} else {
		$cssBuffer .= '.make-grid .item-list { width: ' . $gridWidth . '% !important; }';
		$cssBuffer .= '@media (max-width: 767px) {';
		$cssBuffer .= '.make-grid .item-list { width: 50% !important; }';
		$cssBuffer .= '}';
		
		/* Ribbons */
		$cssBuffer .= '.make-grid .item-list .cornerRibbons { left: -' . $gridRibbonLeft . '%; top: 8%; }';
		$cssBuffer .= '.make-grid.noSideBar .item-list .cornerRibbons { left: -' . $noSidebarGridRibbonLeft . '%; top: 8%; }';
		
		/* Ribbons: Media Screen - Dynamic */
		$cssBuffer .= '@media (min-width: 992px) and (max-width: 1119px) {';
		$cssBuffer .= '.make-grid .item-list .cornerRibbons { left: -' . $gridRibbonLeft992 . '%; top: 8%; }';
		$cssBuffer .= '.make-grid.noSideBar .item-list .cornerRibbons { left: -' . $noSidebarGridRibbonLeft992 . '%; top: 8%; }';
		$cssBuffer .= '}';
		$cssBuffer .= '@media (min-width: 768px) and (max-width: 991px) {';
		$cssBuffer .= '.make-grid .item-list .cornerRibbons { left: -' . $gridRibbonLeft768 . '%; top: 8%; }';
		$cssBuffer .= '.make-grid.noSideBar .item-list .cornerRibbons { left: -' . $noSidebarGridRibbonLeft768 . '%; top: 8%; }';
		$cssBuffer .= '}';
		/* Ribbons: Media Screen - Fix */
		$cssBuffer .= '@media (max-width: 767px) {';
		$cssBuffer .= '.make-grid .item-list { width: 50%; }';
		$cssBuffer .= '}';
		$cssBuffer .= '@media (max-width: 767px) {';
		$cssBuffer .= '.make-grid .item-list .cornerRibbons, .make-grid.noSideBar .item-list .cornerRibbons { left: -10%; top: 8%; }';
		$cssBuffer .= '}';
		$cssBuffer .= '@media (max-width: 736px) {';
		$cssBuffer .= '.make-grid .item-list .cornerRibbons, .make-grid.noSideBar .item-list .cornerRibbons { left: -12%; top: 8%; }';
		$cssBuffer .= '}';
		$cssBuffer .= '@media (max-width: 667px) {';
		$cssBuffer .= '.make-grid .item-list .cornerRibbons, .make-grid.noSideBar .item-list .cornerRibbons { left: -13%; top: 8%; }';
		$cssBuffer .= '}';
		$cssBuffer .= '@media (max-width: 568px) {';
		$cssBuffer .= '.make-grid .item-list .cornerRibbons, .make-grid.noSideBar .item-list .cornerRibbons { left: -14%; top: 8%; }';
		$cssBuffer .= '}';
		$cssBuffer .= '@media (max-width: 480px) {';
		$cssBuffer .= '.make-grid .item-list .cornerRibbons, .make-grid.noSideBar .item-list .cornerRibbons { left: -22%; top: 8%; }';
		$cssBuffer .= '}';
		
		/* Item Border */
		$cssBuffer .= '.adds-wrapper.make-grid .item-list:nth-child(4n+4),';
		$cssBuffer .= '.category-list.make-grid .item-list:nth-child(4n+4) {';
		$cssBuffer .= 'border-right: solid 1px #ddd;';
		$cssBuffer .= '}';
		
		$cssBuffer .= '.adds-wrapper.make-grid .item-list:nth-child(3n+3),';
		$cssBuffer .= '.category-list.make-grid .item-list:nth-child(3n+3) {';
		$cssBuffer .= 'border-right: solid 1px #ddd;';
		$cssBuffer .= '}';
		
		$cssBuffer .= '.adds-wrapper.make-grid .item-list:nth-child(' . $gridViewCols . 'n+' . $gridViewCols . '),';
		$cssBuffer .= '.category-list.make-grid .item-list:nth-child(' . $gridViewCols . 'n+' . $gridViewCols . ') {';
		$cssBuffer .= 'border-right: none;';
		$cssBuffer .= '}';
		
		$cssBuffer .= '@media (max-width: 991px) {';
		$cssBuffer .= '.adds-wrapper.make-grid .item-list:nth-child(' . $gridViewCols . 'n+' . $gridViewCols . '),';
		$cssBuffer .= '.category-list.make-grid .item-list:nth-child(' . $gridViewCols . 'n+' . $gridViewCols . ') {';
		$cssBuffer .= 'border-right-style: solid;';
		$cssBuffer .= 'border-right-width: 1px;';
		$cssBuffer .= 'border-right-color: #ddd;';
		$cssBuffer .= '}';
		$cssBuffer .= '}';
	}
}
$cssBuffer .= '';


/* === Homepage: Search Form Area === */
if (isset($searchFormOptions['height']) and !empty($searchFormOptions['height'])) {
	$searchFormOptions['height'] = strToInt($searchFormOptions['height']) . 'px';
	$cssBuffer .= '#homepage .wide-intro {';
	$cssBuffer .= 'height: ' . $searchFormOptions['height'] . ';';
	$cssBuffer .= 'max-height: ' . $searchFormOptions['height'] . ';';
	$cssBuffer .= '}';
}
if (isset($searchFormOptions['background_color']) and !empty($searchFormOptions['background_color'])) {
	$cssBuffer .= '#homepage .wide-intro { background: ' . $searchFormOptions['background_color'] . '; }';
}
$bgImgFound = false;
if (!empty(config('country.background_image'))) {
	if (isset($disk) && $disk->exists(config('country.background_image'))) {
		$cssBuffer .= '#homepage .wide-intro {';
		$cssBuffer .= 'background-image: url(' . imgUrl(config('country.background_image'), 'css') . ');';
		$cssBuffer .= 'background-size: cover;';
		$cssBuffer .= '}';
		$bgImgFound = true;
	}
}
if (!$bgImgFound) {
	if (isset($searchFormOptions['background_image']) and !empty($searchFormOptions['background_image'])) {
		$cssBuffer .= '#homepage .wide-intro {';
		$cssBuffer .= 'background-image: url(' . imgUrl($searchFormOptions['background_image'], 'css') . ');';
		$cssBuffer .= 'background-size: cover;';
		$cssBuffer .= '}';
	}
}
if (isset($searchFormOptions['big_title_color']) and !empty($searchFormOptions['big_title_color'])) {
	$cssBuffer .= '#homepage .wide-intro h1 { color: ' . $searchFormOptions['big_title_color'] . '; }';
}
if (isset($searchFormOptions['sub_title_color']) and !empty($searchFormOptions['sub_title_color'])) {
	$cssBuffer .= '#homepage .wide-intro p { color: ' . $searchFormOptions['sub_title_color'] . '; }';
}
if (isset($searchFormOptions['form_border_width']) and !empty($searchFormOptions['form_border_width'])) {
	$searchFormOptions['form_border_width'] = strToInt($searchFormOptions['form_border_width']) . 'px';
	$cssBuffer .= '#homepage .wide-intro .search-row { padding: ' . $searchFormOptions['form_border_width'] . '; }';
}
if (isset($searchFormOptions['form_border_color']) and !empty($searchFormOptions['form_border_color'])) {
	$cssBuffer .= '.' . $skin . ' #homepage .wide-intro .search-row { background-color: ' . $searchFormOptions['form_border_color'] . '; }';
}
if (isset($searchFormOptions['form_btn_background_color']) and !empty($searchFormOptions['form_btn_background_color'])) {
	$cssBuffer .= '.' . $skin . ' #homepage button.btn-search {';
	$cssBuffer .= 'background-color: ' . $searchFormOptions['form_btn_background_color'] . ';';
	$cssBuffer .= 'border-color: ' . $searchFormOptions['form_btn_background_color'] . ';';
	$cssBuffer .= '}';
}
if (isset($searchFormOptions['form_btn_text_color']) and !empty($searchFormOptions['form_btn_text_color'])) {
	$cssBuffer .= '.' . $skin . ' #homepage button.btn-search { color: ' . $searchFormOptions['form_btn_text_color'] . '; }';
}

/* === Homepage: Locations & Country Map === */
if (isset($citiesOptions['background_color']) and !empty($citiesOptions['background_color'])) {
	$cssBuffer .= '#homepage .inner-box { background: ' . $citiesOptions['background_color'] . '; }';
}
if (isset($citiesOptions['border_width']) and !empty($citiesOptions['border_width'])) {
	$citiesOptions['border_width'] = strToInt($citiesOptions['border_width']) . 'px';
	$cssBuffer .= '#homepage .inner-box { border-width: ' . $citiesOptions['border_width'] . '; }';
}
if (isset($citiesOptions['border_color']) and !empty($citiesOptions['border_color'])) {
	$cssBuffer .= '#homepage .inner-box { border-color: ' . $citiesOptions['border_color'] . '; }';
}
if (isset($citiesOptions['text_color']) and !empty($citiesOptions['text_color'])) {
	$cssBuffer .= '#homepage .inner-box,';
	$cssBuffer .= '#homepage .inner-box p,';
	$cssBuffer .= '#homepage .inner-box h1,';
	$cssBuffer .= '#homepage .inner-box h2,';
	$cssBuffer .= '#homepage .inner-box h3,';
	$cssBuffer .= '#homepage .inner-box h4,';
	$cssBuffer .= '#homepage .inner-box h5 {';
	$cssBuffer .= 'color: ' . $citiesOptions['text_color'] . ';';
	$cssBuffer .= '}';
}
if (isset($citiesOptions['link_color']) and !empty($citiesOptions['link_color'])) {
	$cssBuffer .= '#homepage .inner-box a { color: ' . $citiesOptions['link_color'] . '; }';
}
if (isset($citiesOptions['link_color_hover']) and !empty($citiesOptions['link_color_hover'])) {
	$cssBuffer .= '#homepage .inner-box a:hover,';
	$cssBuffer .= '#homepage .inner-box a:focus {';
	$cssBuffer .= 'color: ' . $citiesOptions['link_color_hover'] . ';';
	$cssBuffer .= '}';
}
$cssBuffer .= '';

?>
<style type="text/css">
	
	{!! $cssBuffer !!}
	
	/* === CSS Fix === */
	.f-category h6 {
		color: #333;
	}
	.photo-count {
		color: #292b2c;
	}
	.page-info-lite h5 {
		color: #999999;
	}
	h4.item-price {
		color: #292b2c;
	}
	.skin-blue .pricetag {
		color: #fff;
	}
	h5.company-title a {
		color: #999;
	}

</style>

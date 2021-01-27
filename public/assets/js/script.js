
/* Prevent errors, If these variables are missing. */
/* Carousel Parameters */
if (typeof carouselItems === 'undefined') {
	var carouselItems = 0;
}
if (typeof carouselAutoplay === 'undefined') {
	var carouselAutoplay = false;
}
if (typeof carouselAutoplayTimeout === 'undefined') {
	var carouselAutoplayTimeout = 1000;
}
if (typeof carouselLang === 'undefined') {
	var carouselLang = {'navText': {'prev': "prev", 'next': "next"}};
}

/* Categories Parameters */
if (typeof maxSubCats === 'undefined') {
	var maxSubCats = 3;
}


var headerHeight = $('.navbar-site').height();
var wrapper = $('#wrapper');

// Modernizr touch event detect
function is_touch_device() {
	return 'ontouchstart' in window;
}
var isTouchDevice = is_touch_device();

/* console.log('is touch device : ',isTouchDevice); */


$(document).ready(function ()
{
	/* Change a tooltip size in Bootstrap 4.x */
	$('#locSearch').on('mouseover mouseenter mouseleave mousemove', function () {
		$('.tooltip-inner').css({"width":"300px","max-width":"300px"});
	});
	
	
	var navbarSite = $('.navbar-site');
	
	
	/* Get Page Direction */
	var rtlIsEnabled = false;
	var dir = $('html').attr('dir');
	if (dir === 'rtl') {
		rtlIsEnabled = true;
	}
	
	
	/* SET HEADER HEIGHT AS PADDING-TOP to WRAPPER */
	
	function setWrapperHeight() {
		wrapper.css('padding-top', headerHeight + 'px');
	}
	
	setWrapperHeight();
	
	/* ON SCROLL FADE OUT */
	
	function fadeOnScroll(target) {
		var target = $('' + target + ''),
			targetHeight = target.outerHeight();
		$(document).scroll(function () {
			var scrollPercent = (targetHeight - window.scrollY) / targetHeight;
			scrollPercent >= 0 && (target.css("background-color", "rgba(0,0,0," + (1.1 - scrollPercent) + ")"))
		});
	}
	
	if (!isTouchDevice) {
		fadeOnScroll('.layer-bg');
	}
	
	
	/*==================================
	 Carousel
	 ==================================*/
	
	/* Featured Listings Carousel */
	var carouselObject = $('.featured-list-slider');
	var responsiveObject = {
		0:{
			items: 1,
			nav: true
		},
		576:{
			items: 2,
			nav: false
		},
		768:{
			items: 3,
			nav: false
		},
		992:{
			items: 5,
			nav: false,
			loop: (carouselItems > 5) ? true : false
		}
	};
	carouselObject.owlCarousel({
		rtl: rtlIsEnabled,
		nav: false,
		navText: [carouselLang.navText.prev, carouselLang.navText.next],
		loop: true,
		responsiveClass: true,
		responsive: responsiveObject,
		autoWidth: true,
		autoplay: carouselAutoplay,
		autoplayTimeout: carouselAutoplayTimeout,
		autoplayHoverPause: true
	});


	/*==================================
	 Ajax Tab || CATEGORY PAGE
	 ==================================*/
	
	$(".nav-tabs li > a").click(function () {
		$(this).closest('ul').find('li').removeClass('active');
		$(this).parent('li').addClass('active');
	});

	$("#ajaxTabs li > a").click(function () {

		$("#allAds").empty().append("<div id='loading text-center'> <br> <img class='center-block' src='images/loading.gif' alt='Loading' /> <br> </div>");
		$("#ajaxTabs li").removeClass('active');
		$(this).parent('li').addClass('active');
		$.ajax({
			url: this.href, success: function (html) {
				$("#allAds").empty().append(html);
				$('.tooltipHere').tooltip('hide');
			}
		});
		return false;
	});

	urls = $('#ajaxTabs li:first-child a').attr("href");

	$("#allAds").empty().append("<div id='loading text-center'> <br> <img class='center-block' src='images/loading.gif' alt='Loading' /> <br>  </div>");
	$.ajax({
		url: urls, success: function (html) {
			$("#allAds").empty().append(html);
			$('.tooltipHere').tooltip('hide');

			/* default grid view class invoke into ajax content (product item) */
			$(function () {
				$('.hasGridView .item-list').addClass('make-grid');
				$('.hasGridView .item-list').matchHeight();
				$.fn.matchHeight._apply('.hasGridView .item-list');
			});
		}
	});


	/*==================================
	 List view clickable || CATEGORY 
	 ==================================*/
	
	// List view, Grid view and compact view
	
	// var selector doesn't work on ajax tab category.hhml. This variables elements disable for V1.6
	// var listItem = $('.item-list');
	// var addDescBox = $('.item-list .add-desc-box');
	// var addsWrapper = $('.adds-wrapper');

	/* Default view */
	var listingDisplayMode = readCookie('listing_display_mode');
	if (listingDisplayMode) {
		if (listingDisplayMode == '.grid-view') {
			gridView('.grid-view');
		} else if (listingDisplayMode == '.list-view') {
			listView('.list-view');
		} else if (listingDisplayMode == '.compact-view') {
			compactView('.compact-view');
		} else {
			listView('.list-view');
		}
	} else {
		createCookie('listing_display_mode', '.list-view', 7);
	}
	
	/* List view, Grid view  and compact view */
	
	$('.list-view,#ajaxTabs li a').click(function (e) { /* use a class, since your ID gets mangled */
		e.preventDefault();
		listView('.list-view');
		createCookie('listing_display_mode', '.list-view', 7);
	});
	
	$('.grid-view').click(function (e) { /* use a class, since your ID gets mangled */
		e.preventDefault();
		gridView(this);
		createCookie('listing_display_mode', '.grid-view', 7);
	});
	
	$('.compact-view').click(function (e) { /* use a class, since your ID gets mangled */
		e.preventDefault();
		compactView(this);
		createCookie('listing_display_mode', '.compact-view', 7);
	});
	
	$('.e-grid-view').click(function (e) {
		$(this).addClass("active");
		$('.event-category-list').removeClass('has-list-view');
		$('.e-list-view').removeClass("active");
	});
	
	$('.e-list-view').click(function (e) {
		$(this).addClass("active");
		$('.event-category-list').addClass('has-list-view');
		$('.e-grid-view').removeClass("active");
	});
	
	
	if ($(this).width() < 767) {
		$( ".event-category-list .event-item-col" ).each( function( index, element ){
			var eventFooter =  $(this).find('.card-footer');
			var eventInfo =  $(this).find('.card-event-info');
			//  $(this).find('.card-body').append(footer);
			$(this).find('.badge.price-tag').clone().insertAfter(eventInfo);
			eventFooter.clone().insertAfter(eventInfo);
		});
	}
	
	/*
	$(function () {
		$('.row-featured .f-category').matchHeight();
		$.fn.matchHeight._apply('.row-featured .f-category');
	});

	$(function () {
		$('.has-equal-div > div').matchHeight();
		$.fn.matchHeight._apply('.row-featured .f-category');
	});
	*/
	

	/*==================================
	 Global Plugins ||
	 ==================================*/

	$('.long-list').hideMaxListItems({
		'max': 8,
		'speed': 500,
        'moreText': langLayout.hideMaxListItems.moreText + ' ([COUNT])',
        'lessText': langLayout.hideMaxListItems.lessText
	});

	$('.long-list-user').hideMaxListItems({
		'max': 12,
		'speed': 500,
        'moreText': langLayout.hideMaxListItems.moreText + ' ([COUNT])',
        'lessText': langLayout.hideMaxListItems.lessText
	});

	$('.long-list-home').hideMaxListItems({
		'max': maxSubCats,
		'speed': 500,
		'moreText': langLayout.hideMaxListItems.moreText + ' ([COUNT])',
		'lessText': langLayout.hideMaxListItems.lessText
	});
	
	/* Bootstrap Collapse + jQuery hideMaxListItem fix on mobile */
	$('.btn-cat-collapsed').click(function () {
		var targetSelector = $(this).data('target');
		var isExpanded = $(this).attr('aria-expanded');
		
		/*
		console.log(targetSelector);
		console.log(isExpanded);
		*/
		
		if (typeof isExpanded === 'undefined') {
			return false;
		}
		
		$(targetSelector).toggle('slow');
		
		if (isExpanded == 'true') {
			$('.cat-list ' + targetSelector).next('.maxlist-more').hide();
		} else {
			$('.cat-list ' + targetSelector).next('.maxlist-more').show();
		}
	});


	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	});
	
	// $("select.selecter").niceSelect({ /* custom scroll select plugin */ });
	
	$(".niceselecter").niceSelect({ /* category list Short by */
		// customClass: "select-sort-by"
	});
	
	$(".scrollbar").niceScroll();  /* customs scroll plugin */
	
	// smooth scroll to the ID
	$(document).on('click', 'a.scrollto', function (event) {
		event.preventDefault();
		$('html, body').animate({
			scrollTop: $($.attr(this, 'href')).offset().top
		}, 500);
	});


	/*=======================================================================================
	 cat-collapse Hmepage Category Responsive view
	 =======================================================================================*/
	
	var catCollapse = $('.cat-collapse');
	
	$(window).bind('resize load', function () {
		
		if ($(this).width() < 767) {
			catCollapse.collapse('hide');
			catCollapse.on('show.bs.collapse', function () {
				$(this).prev('.cat-title').find('.icon-down-open-big').addClass("active-panel");
			});
			
			catCollapse.on('hide.bs.collapse', function () {
				$(this).prev('.cat-title').find('.icon-down-open-big').removeClass("active-panel");
			})
			
		} else {
			$('#bd-docs-nav').collapse('show');
			catCollapse.collapse('show');
		}
		
	});

	/* DEMO PREVIEW */

	$(".tbtn").click(function () {
		$('.themeControll').toggleClass('active')
	});

	/* Jobs */
	
	$("input:radio").click(function () {
		if ($('input:radio#job-seeker:checked').length > 0) {
			$('.forJobSeeker').removeClass('hide');
			$('.forJobFinder').addClass('hide');
		} else {
			$('.forJobFinder').removeClass('hide');
			$('.forJobSeeker').addClass('hide')
		}
	});
	
	/* Change Direction based on template dir="RTL"  or dir="LTR" */
	
	var sidebarDirection = {};
	var sidebarDirectionClose = {};
	
	if (rtlIsEnabled) {
		sidebarDirection = {right: '-251px'};
		sidebarDirectionClose = {right: '0'};
	}
	else {
		sidebarDirection = {left: '-251px'};
		sidebarDirectionClose = {left: '0'};
	}
	
	$(".filter-toggle").click(function () {
		$('.mobile-filter-sidebar')
			.prepend("<div class='closeFilter'>X</div>")
			.animate(sidebarDirectionClose, 250, "linear", function () {
			});
		$('.menu-overly-mask').addClass('is-visible');
	});
	
	$(".menu-overly-mask").click(function () {
		$(".mobile-filter-sidebar").animate(sidebarDirection, 250, "linear", function () {
		});
		$('.menu-overly-mask').removeClass('is-visible');
	});
	
	
	$(document).on('click', '.closeFilter', function () {
		$(".mobile-filter-sidebar").animate(sidebarDirection, 250, "linear", function () {
		});
		$('.menu-overly-mask').removeClass('is-visible');
	});
	
	/*
	$(".filter-toggle").click(function () {
		$('.mobile-filter-sidebar').prepend("<div class='closeFilter'>X</div>");
		if (rtlIsEnabled) {
			$(".mobile-filter-sidebar").animate({"right": "0"}, 250, "linear", function () {
			});
		} else {
			$(".mobile-filter-sidebar").animate({"left": "0"}, 250, "linear", function () {
			});
		}
		$('.menu-overly-mask').addClass('is-visible');
	});

	$(".menu-overly-mask").click(function () {
		if (rtlIsEnabled) {
			$(".mobile-filter-sidebar").animate({"right": "-251px"}, 250, "linear", function () {
			});
		} else {
			$(".mobile-filter-sidebar").animate({"left": "-251px"}, 250, "linear", function () {
			});
		}
		$('.menu-overly-mask').removeClass('is-visible');
	});


	$(document).on('click', '.closeFilter', function () {
		if (rtlIsEnabled) {
			$(".mobile-filter-sidebar").animate({"right": "-251px"}, 250, "linear", function () {
			});
		} else {
			$(".mobile-filter-sidebar").animate({"left": "-251px"}, 250, "linear", function () {
			});
		}
		$('.menu-overly-mask').removeClass('is-visible');
	});
	*/


	/* cityName will replace with selected location/area from location modal */
	
	$('#browseAdminCities').on('shown.bs.modal', function (e) {
		/* alert('Modal is successfully shown!'); */
		$("ul.list-link li a").click(function () {
			$('ul.list-link li a').removeClass('active');
			$(this).addClass('active');
			$(".cityName").text($(this).text());
			$('#browseAdminCities').modal('hide');
		});
	});
	
	$("#checkAll").click(function () {
		$('.add-img-selector input:checkbox').not(this).prop('checked', this.checked);
	});
	
	
	var stickyScroller = function () {
		var intialscroll = 0;
		$(window).scroll(function (event) {
			var windowScroll = $(this).scrollTop();
			if (windowScroll > intialscroll) {
				/* downward-scrolling */
				navbarSite.addClass('stuck');
			} else {
				/* upward-scrolling */
				navbarSite.removeClass('stuck');
			}
			if (windowScroll < 450) {
				/* downward-scrolling */
				navbarSite.removeClass('stuck');
			}
			intialscroll = windowScroll;
		});
	};
	
	if (!isTouchDevice) {
		stickyScroller();
	}
	
	$('.dropdown-clear-filter').click(function(e) {
		$(this).closest('.dropdown-menu').find('input[type="radio"]').prop('checked', false);
		$(this).closest('.dropdown-menu').find('input[type="checkbox"]').prop('checked',false);
		e.stopPropagation();
	});
	
	$('.dropdown-menu.stay').click(function(e) {
		e.stopPropagation();
	});
	
	
	/* INBOX MESSAGE */
	
	$('ul.dropdown-menu-sort li').click(function(e) {
		$('ul.dropdown-menu-sort li').removeClass('active');
		$(this).addClass('active');
		var selectedText = $(this).find('a').text();
		$('.dropdown-menu-sort-selected').text(selectedText);
	});
	
	$('.markAsRead').click(function(e) {
		e.stopPropagation();
		var isSeen = $(this).closest('.list-group-item').hasClass('seen');
		var titleIs = isSeen ? "Mark as read" : "Mark as unread";
		$(this).find('i').toggleClass('fa-envelope-open fa-envelope');
		$(this).closest('.list-group-item').toggleClass('seen');
		$(this).attr('title',titleIs);
		$(this).attr('data-original-title',titleIs);
	});
	
	var markAsAll = function(asSeen) {
		$(".message-list .list-group-item" ).each( function(){
			var itemIs =  $(this).find('.markAsRead');
			var isSeen = asSeen === "seen";
			var titleIs = isSeen ? "Mark as read" : "Mark as unread";
			if (isSeen) {
				itemIs.closest('.list-group-item').addClass('seen');
				itemIs.find('i').addClass('fa-envelope-open').removeClass('fa-envelope');
			} else  {
				itemIs.closest('.list-group-item').removeClass('seen');
				itemIs.find('i').addClass('fa-envelope').removeClass('fa-envelope-open');
			}
			itemIs.attr('title',titleIs);
			itemIs.attr('data-original-title',titleIs);
		});
	};
	
	$('.markAllAsUnRead').click(function() {
		markAsAll("seen");
	});
	
	$('.markAllAsRead').click(function() {
		markAsAll("notseen");
	});
	
	$('.markAsStar').click(function(e) {
		e.stopPropagation();
		$(this).find('i').toggleClass('fas far');
	});
	
	$('#form-check-all').click(function(e) {
		e.stopPropagation();
		$('.message-list input:checkbox').not(this).prop('checked', this.checked);
	});
	
	
	/* Check New Messages */
	if (typeof timerNewMessagesChecking !== 'undefined') {
		checkNewMessages();
		if (timerNewMessagesChecking > 0) {
			setInterval(function() {
				checkNewMessages();
				/* 60000 = 60 seconds (Timer) */
			}, timerNewMessagesChecking);
		}
	}
	
	/* Show/hide password in forms */
	$('.eyeOfPwd').click(function(e) {
		showPwd();
	});
});

jQuery.event.special.touchstart = {
	setup: function( _, ns, handle ){
		if ( ns.includes("noPreventDefault") ) {
			this.addEventListener("touchstart", handle, { passive: false });
		} else {
			this.addEventListener("touchstart", handle, { passive: true });
		}
	}
};

function listView(selecter) {
	$('.grid-view,.compact-view').removeClass("active");
	$(selecter).addClass("active");
	
	/* $('.item-list').addClass("make-list").removeClass("make-grid make-compact"); */
	$('.category-list').addClass("make-list").removeClass("make-grid make-compact");
	
	if ($('.adds-wrapper').hasClass('property-list')) {
		$('.item-list .add-desc-box').removeClass("col-md-9").addClass("col-md-6");
	} else {
		/* $('.item-list .add-desc-box').removeClass("col-sm-9").addClass("col-sm-7"); */
		$('.item-list .add-desc-box').removeClass("col-md-9").addClass("col-md-7");
	}
	
	$(function () {
		$('.item-list').matchHeight('remove');
	});
}

function gridView(selecter) {
	$('.list-view,.compact-view').removeClass("active");
	$(selecter).addClass("active");
	/* $('.item-list').addClass("make-grid").removeClass("make-list make-compact"); */
	$('.category-list').addClass("make-grid").removeClass("make-list make-compact");
	
	$(function () {
		$('.item-list').matchHeight();
		$.fn.matchHeight._apply('.item-list');
	});
	
	if ($('.adds-wrapper').hasClass('property-list')) {
		// keep it for future
	} else {
		//
	}
}

function compactView(selecter) {
	$('.list-view,.grid-view').removeClass("active");
	$(selecter).addClass("active");
	/* $('.item-list').addClass("make-compact").removeClass("make-list make-grid"); */
	$('.category-list').addClass("make-compact").removeClass("make-list make-grid");
	
	if ($('.adds-wrapper').hasClass('property-list')) {
		$('.item-list .add-desc-box').addClass("col-md-9").removeClass('col-md-6');
	} else {
		/* $('.item-list .add-desc-box').toggleClass("col-sm-9 col-sm-7"); */
		$('.item-list .add-desc-box').addClass("col-md-9").removeClass('col-md-7');
	}
	
	$(function () {
		$('.adds-wrapper .item-list').matchHeight('remove');
	});
}

/**
 * Create cookie
 * @param name
 * @param value
 * @param days
 */
function createCookie(name, value, days) {
	var expires;

	if (days) {
		var date = new Date();
		date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
		expires = "; expires=" + date.toGMTString();
	} else {
		expires = "";
	}
	document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
}

/**
 * Read cookie
 * @param name
 * @returns {*}
 */
function readCookie(name) {
	var nameEQ = encodeURIComponent(name) + "=";
	var ca = document.cookie.split(';');
	for (var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) === ' ') c = c.substring(1, c.length);
		if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
	}
	return null;
}

/**
 * Delete cookie
 * @param name
 */
function eraseCookie(name) {
	createCookie(name, "", -1);
}

/**
 * Set Country Phone Code
 * @param countryCode
 * @param countries
 * @returns {boolean}
 */
function setCountryPhoneCode(countryCode, countries)
{
	if (typeof countryCode === "undefined" || typeof countries === "undefined") return false;
	if (typeof countries[countryCode] === "undefined") return false;
	
	$('#phoneCountry').html(countries[countryCode]['phone']);
}

/**
 * Set user type
 * @param userTypeId
 * @returns {boolean}
 */
function setUserType(userTypeId)
{
	$('#companyBloc').hide();
	$('#resumeBloc').hide();
	
	if (typeof userTypeId === "undefined") {
		return false;
	}
	
	/* Company */
	if (userTypeId == 1) {
		$('#companyBloc').show();
	}
	
	/* Candidate */
	if (userTypeId == 2) {
		$('#resumeBloc').show();
	}
}

/**
 * Google Maps Generation
 * @param key
 * @param address
 * @param language
 */
function getGoogleMaps(key, address, language) {
	var q = '';
	if (typeof address === 'undefined') {
		q = encodeURIComponent($('#address').text());
	} else {
		q = encodeURIComponent(address);
	}
	if (typeof language === 'undefined') {
		language = 'en';
	}
	var googleMapsUrl = 'https://www.google.com/maps/embed/v1/place?key=' + key + '&q=' + q + '&language=' + language;
	
	$('#googleMaps').attr('src', googleMapsUrl);
}

/**
 * Show price & Payment Methods
 * @param packagePrice
 * @param packageCurrencySymbol
 * @param packageCurrencyInLeft
 */
function showAmount(packagePrice, packageCurrencySymbol, packageCurrencyInLeft)
{
	/* Show Amount */
	$('.payable-amount').html(packagePrice);
	
	/* Show Amount Currency */
	$('.amount-currency').html(packageCurrencySymbol);
	if (packageCurrencyInLeft == 1) {
		$('.amount-currency.currency-in-left').show();
		$('.amount-currency.currency-in-right').hide();
	} else {
		$('.amount-currency.currency-in-left').hide();
		$('.amount-currency.currency-in-right').show();
	}
	
	/* If price <= 0 hide the Payment Method selection */
	if (packagePrice <= 0) {
		$('#packagesTable tbody tr:last').hide();
	} else {
		$('#packagesTable tbody tr:last').show();
	}
}

/**
 * Get the Selected Package Price
 * @param selectedPackage
 * @returns {*|jQuery}
 */
function getPackagePrice(selectedPackage)
{
	var price = $('#price-' + selectedPackage + ' .price-int').html();
	price = parseFloat(price);
	
	return price;
}

/**
 * Redirect URL
 * @param url
 */
function redirect(url) {
	window.location.replace(url);
	window.location.href = url;
}

/**
 * Raw URL encode
 * @param str
 * @returns {string}
 */
function rawurlencode(str) {
	str = (str + '').toString();
	return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A');
}

/**
 * Check if a string is empty or null
 * @param str
 * @returns {boolean}
 */
function isEmptyValue(str) {
	return (!str || 0 === str.length);
}

/**
 * Check if a string is blank or null
 * @param str
 * @returns {boolean}
 */
function isBlankValue(str) {
	return (!str || /^\s*$/.test(str));
}

/**
 * Get Recruiter's Company details
 * @param companyId
 */
function getCompany(companyId) {
	if (companyId == 0) {
		$('#logoField').hide();
		$('#logoFieldValue').html('');
		
		$('#companyFields').show();
	} else {
		$('#companyFields').hide();
		
		var selectedCompanyLogo = $('#companyId').find('option:selected').data('logo');
		$('#logoFieldValue').html('<img src="' + selectedCompanyLogo + '">');
		
		var companyFormLink = $('#companyFormLink').attr('href');
		companyFormLink = companyFormLink.replace(/companies\/([0-9]+)\/edit/, 'companies/' + companyId + '/edit');
		$('#companyFormLink').attr('href', companyFormLink);
		
		$('#logoField').show();
	}
}

/**
 * Get Candidate's Resume details
 * @param resumeId
 */
function getResume(resumeId) {
	if (resumeId == 0) {
		$('#resumeFields').show();
	} else {
		$('#resumeFields').hide();
	}
}

/**
 * Check New Messages
 */
function checkNewMessages() {
	var oldValue = $('.dropdown-toggle .count-conversations-with-new-messages').html();
	if (typeof oldValue === 'undefined') {
		return false;
	}
	
	/* Make ajax call */
	$.ajax({
		method: 'POST',
		url: siteUrl + '/ajax/messages/check',
		data: {
			'languageCode': languageCode,
			'oldValue': oldValue,
			'_token': $('input[name=_token]').val()
		}
	}).done(function(data) {
		if (typeof data.logged === 'undefined') {
			return false;
		}
		
		/* Guest Users - Need to Log In */
		if (data.logged == '0') {
			return false;
		}
		
		/* Logged Users - Notification */
		if (data.countConversationsWithNewMessages > 0) {
			if (data.countConversationsWithNewMessages >= data.countLimit) {
				$('.count-conversations-with-new-messages').html(data.countConversationsWithNewMessages + '+');
			} else {
				$('.count-conversations-with-new-messages').html(data.countConversationsWithNewMessages);
			}
			$('.count-conversations-with-new-messages').show();
			
			if (data.oldValue > 0 && document.getElementById('reloadBtn')) {
				if (data.countConversationsWithNewMessages != data.oldValue) {
					$('#reloadBtn').show();
				}
			}
		} else {
			$('.count-conversations-with-new-messages').html('0');
			$('.count-conversations-with-new-messages').hide();
		}
		
		return false;
	});
}

/**
 * Show/Hide password in forms
 */
function showPwd()
{
	var elmt = $('.show-pwd-group #mPassword, .show-pwd-group #password');
	
	if (elmt.attr('type') === 'password') {
		elmt.prop('type', 'text');
		$(this).find('.eyeOfPwd').html('<i class="far fa-eye"></i>');
	} else {
		elmt.prop('type', 'password');
		$(this).find('.eyeOfPwd').html('<i class="far fa-eye-slash"></i>');
	}
}

/*
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

/* Prevent errors, If these variables are missing. */
if (typeof adminType === 'undefined') {
	var adminType = 0;
}
if (typeof selectedAdminCode === 'undefined') {
	var selectedAdminCode = 0;
}
if (typeof cityId === 'undefined') {
	var cityId = 0;
}
var select2Language = languageCode;
if (typeof langLayout !== 'undefined' && typeof langLayout.select2 !== 'undefined') {
	select2Language = langLayout.select2;
}

$(document).ready(function() {
	
	/* CSRF Protection */
	var token = $('meta[name="csrf-token"]').attr('content');
	if (token) {
		$.ajaxSetup({
			headers: {'X-CSRF-TOKEN': token},
			async: true,
			cache: false
		});
	}
    
    /* Get and Bind administrative divisions */
	getAdminDivisions(countryCode, adminType, selectedAdminCode);
	$('#countryCode').bind('click, change', function () {
		countryCode = $(this).val();
		getAdminDivisions(countryCode, adminType, 0);
	});
    
    /* Get and Bind the selected city */
	if (adminType == 0) {
		getSelectedCity(countryCode, cityId);
	}
    
    /* Get and Bind cities */
	$('#cityId').select2({
		language: select2Language,
		ajax: {
			url: function () {
				/* Get the current country code */
				var selectedCountryCode = $('#countryCode').val();
				if (typeof selectedCountryCode !== "undefined") {
					countryCode = selectedCountryCode;
				}

				/* Get the current admin code */
				var selectedAdminCode = $('#adminCode').val();
				if (typeof selectedAdminCode === "undefined") {
					selectedAdminCode = 0;
				}
				return siteUrl + '/ajax/countries/' + countryCode + '/admins/' + adminType + '/' + selectedAdminCode + '/cities';
			},
			dataType: 'json',
			delay: 50,
			data: function (params) {
				var query = {
					languageCode: languageCode,
					q: params.term, // search term
					page: params.page
				};

				return query;
			},
			processResults: function (data, params) {
				/*
				// parse the results into the format expected by Select2
				// since we are using custom formatting functions we do not need to
				// alter the remote JSON data, except to indicate that infinite
				// scrolling can be used
				*/
				params.page = params.page || 1;

				return {
					results: data.items,
					pagination: {
						more: (params.page * 10) < data.totalEntries
					}
				};
			},
			cache: true
		},
		escapeMarkup: function (markup) { return markup; }, /* let our custom formatter work */
		minimumInputLength: 2,
		templateResult: function (data) {
			return data.text;
		},
		templateSelection: function (data, container) {
			return data.text;
		}
	});
});

/**
 * Get and Bind Administrative Divisions
 *
 * @param countryCode
 * @param adminType
 * @param selectedAdminCode
 * @returns {*}
 */
function getAdminDivisions(countryCode, adminType, selectedAdminCode)
{
    if (countryCode == 0 || countryCode == '') return false;
	
	/* Make ajax call */
	$.ajax({
		method: 'GET',
		url: siteUrl + '/ajax/countries/' + countryCode + '/admins/' + adminType + '?languageCode=' + languageCode
	}).done(function(obj)
	{
		/* Init. */
		$('#adminCode').empty().append('<option value="0">' + lang.select.admin + '</option>').val('0').trigger('change');
		$('#cityId').empty().append('<option value="0">' + lang.select.city + '</option>').val('0').trigger('change');
		
		/* Bind data into Select list */
		if (typeof obj.error !== 'undefined') {
			$('#adminCode').find('option').remove().end().append('<option value="0"> '+ obj.error.message +' </option>');
			$('#adminCode').closest('.form-group').addClass('has-error');
			return false;
		} else {
			$('#adminCode').closest('.form-group').removeClass('has-error');
		}
		
		if (typeof obj.data === 'undefined') {
			return false;
		}
		$.each(obj.data, function (key, item) {
			if (selectedAdminCode == item.code) {
				$('#adminCode').append('<option value="' + item.code + '" selected="selected">' + item.name + '</option>');
			} else {
				$('#adminCode').append('<option value="' + item.code + '">' + item.name + '</option>');
			}
		});
		
		/* Get and Bind the selected city */
		getSelectedCity(countryCode, cityId);
	});
    
    return selectedAdminCode;
}

/**
 * Get and Bind (Selected) City by ID
 *
 * @param countryCode
 * @param cityId
 * @returns {number}
 */
function getSelectedCity(countryCode, cityId)
{
	/* Clear by administrative divisions selection */
	$('#adminCode').bind('click, change', function() {
		$('#cityId').empty().append('<option value="0">' + lang.select.city + '</option>').val('0').trigger('change');
	});
	
	/* Make ajax call */
	$.ajax({
		method: 'GET',
		url: siteUrl + '/ajax/countries/' + countryCode + '/cities/' + cityId + '?languageCode=' + languageCode
	}).done(function(data)
	{
		$('#cityId').empty().append('<option value="' + data.id + '">' + data.text + '</option>').val(data.id).trigger('change');
		return data.id;
	}).fail(function()
	{
		$('#cityId').empty().append('<option value="0">' + lang.select.city + '</option>').val('0').trigger('change');
		return 0;
	});
	
	return 0;
}

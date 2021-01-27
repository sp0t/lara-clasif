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
if (typeof category === 'undefined') {
	var category = 0;
}
if (typeof subCategory === 'undefined') {
	var subCategory = 0;
}
if (typeof packageIsEnabled === 'undefined') {
	var packageIsEnabled = false;
}

$(document).ready(function () {
	
	/* CSRF Protection */
	var token = $('meta[name="csrf-token"]').attr('content');
	if (token) {
		$.ajaxSetup({
			headers: {'X-CSRF-TOKEN': token},
			async: true,
			cache: false
		});
	}
	
	/* On load */
	$('#subCatBloc').hide();
	var catObj = getSubCategories(siteUrl, languageCode, category, subCategory);
	
	/* On category selected */
	$('#parentId').bind('click, change', function () {
		var category = $(this).val();
		var selectedCat = $(this).find('option:selected');
		var selectedCatType = selectedCat.data('type');
		
		/* Get sub-categories */
		catObj = getSubCategories(siteUrl, languageCode, category, 0);
		
		/* Update 'parent_type' field */
		$('input[name=parent_type]').val(selectedCatType);
		
		/* Check resume file field */
		/* ... */
	});
	
});

function getSubCategories(siteUrl, languageCode, catId, selectedSubCatId) {
	/* Check Bugs */
	if (typeof languageCode === 'undefined' || typeof catId === 'undefined') {
		return false;
	}
	
	/* Don't make ajax request if any category has selected. */
	if (catId === 0 || catId === '') {
		/* Remove all entries from subcategory field. */
		$('#categoryId').empty().append('<option value="0">' + lang.select.subCategory + '</option>').val('0').trigger('change');
		return false;
	}
	
	/* Default number of sub-categories */
	var countSubCats = 0;
	
	/* Make ajax call */
	$.ajax({
		method: 'POST',
		url: siteUrl + '/ajax/category/sub-categories',
		data: {
			'_token': $('input[name=_token]').val(),
			'catId': catId,
			'selectedSubCatId': selectedSubCatId,
			'languageCode': languageCode
		}
	}).done(function (obj) {
		/* init. */
		$('#categoryId').empty();
		$('#categoryId').append('<option value="0">' + lang.select.subCategory + '</option>');
		$('#categoryId').val('0');
		$('#categoryId').trigger('change');
		
		/* error */
		if (typeof obj.error !== "undefined") {
			$('#categoryId').find('option').remove().end().append('<option value="0"> ' + obj.error.message + ' </option>');
			$('#categoryId').closest('.form-group').addClass('has-error');
			return false;
		} else {
			/* $('#categoryId').closest('.form-group').removeClass('has-error'); */
		}
		
		if (typeof obj.subCats === "undefined" || typeof obj.countSubCats === "undefined") {
			return false;
		}
		
		/* Bind data into Select list */
		if (obj.countSubCats == 1) {
			$('#subCatBloc').hide();
			
			$('#categoryId').empty();
			$('#categoryId').append('<option value="' + obj.subCats[0].tid + '">' + obj.subCats[0].name + '</option>');
			$('#categoryId').val(obj.subCats[0].tid);
			$('#categoryId').trigger('change');
		} else {
			$('#subCatBloc').show();
			
			$.each(obj.subCats, function (key, subCat) {
				if (selectedSubCatId == subCat.tid) {
					$('#categoryId').append('<option value="' + subCat.tid + '" selected="selected">' + subCat.name + '</option>');
				} else {
					$('#categoryId').append('<option value="' + subCat.tid + '">' + subCat.name + '</option>');
				}
			});
		}
		
		/* Get number of sub-categories */
		countSubCats = obj.countSubCats;
	});
	
	/* Get result */
	return {
		'catId': catId,
		'countSubCats': countSubCats
	};
}

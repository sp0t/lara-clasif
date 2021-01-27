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

$(document).ready(function()
{
	/* CSRF Protection */
	var token = $('meta[name="csrf-token"]').attr('content');
	if (token) {
		$.ajaxSetup({
			headers: {'X-CSRF-TOKEN': token}
		});
	}

    /* Search Left Sidebar : Categories & Sub-categories */
    $('#subCatsList h5 a').click(function()
    {
        $('#subCatsList').hide();
        $('#catsList').show();
        return false;
    });

    /* Save the Post */
    $('.make-favorite, .save-job, a.saved-job').click(function(){
        savePost(this);
    });

    /* Save the Search */
    $('#saveSearch').click(function(){
        saveSearch(this);
    });

});

/**
 * Save Ad
 * @param elmt
 * @returns {boolean}
 */
function savePost(elmt)
{
    var postId = $(elmt).closest('li').attr('id');

    $.ajax({
        method: 'POST',
        url: siteUrl + '/ajax/save/post',
        data: {
            'postId': postId,
            '_token': $('input[name=_token]').val()
        }
    }).done(function(data) {
        if (typeof data.logged == "undefined") {
            return false;
        }
	
		/* Guest Users - Need to Log In */
        if (data.logged == '0') {
			$('#quickLogin').modal();
            return false;
        }
	
		/* Logged Users - Notification */
        if (data.status == 1) {
            if ($(elmt).hasClass('btn')) {
                $('#' + data.postId).removeClass('saved-job').addClass('saved-job');
                $('#' + data.postId + ' a').removeClass('save-job').addClass('saved-job');
            } else {
                $(elmt).html('<span class="fa fa-heart"></span> ' + lang.labelSavePostRemove);
            }
            alert(lang.confirmationSavePost);
        } else {
            if ($(elmt).hasClass('btn')) {
                $('#' + data.postId).removeClass('save-job').addClass('save-job');
                $('#' + data.postId + ' a').removeClass('saved-job').addClass('save-job');
            } else {
                $(elmt).html('<span class="far fa-heart"></span> ' + lang.labelSavePostSave);
            }
            alert(lang.confirmationRemoveSavePost);
        }
        
        return false;
    });
    
    return false;
}

/**
 * Save Search
 * @param elmt
 * @returns {boolean}
 */
function saveSearch(elmt)
{
    var url = $(elmt).attr('name');
    var countPosts = $(elmt).attr('count');

    $.ajax({
        method: 'POST',
        url: siteUrl + '/ajax/save/search',
        data: {
            'url': url,
            'countPosts': countPosts,
            '_token': $('input[name=_token]').val()
        }
    }).done(function(data) {
        if (typeof data.logged == "undefined") {
            return false;
        }
	
		/* Guest Users - Need to Log In */
        if (data.logged == '0') {
			$('#quickLogin').modal();
            return false;
        }
	
		/* Logged Users - Notification */
        if (data.status == 1) {
            alert(lang.confirmationSaveSearch);
        } else {
            alert(lang.confirmationRemoveSaveSearch);
        }
        
        return false;
    });
    
    return false;
}
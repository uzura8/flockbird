$(function() {
	$('body').tooltip({
		selector: 'a[data-toggle=tooltip]'
	});

	var listType = $('#article_list').data('type');
	if (!listType || listType != 'ajax') {
		postLoadTimeline();
	}

	$(document).on('click','.link_show_comment', function(){
		var id = $(this).data('id') ? $(this).data('id') : 0;
		var blockSelector = '#comment_list_' + id;
		var getUri = $(blockSelector).data('get_uri') ? $(blockSelector).data('get_uri') : '';
		var postUri = $(blockSelector).data('post_uri') ? $(blockSelector).data('post_uri') : '';

		if (is_sp()) loadTlComment(getUri, blockSelector, id);
		showCommentInput(id, 'form_comment_' + id, postUri, getUri, true);
		$(this).parent('small').html('<span>' + get_term('comment') + '</span>');
		return false;
	});

	$(document).on('click','.link_show_comment_form', function(){
		var id = parseInt($(this).data('id'));
		var targetBlockName = $(this).data('block');

		var blockSelector = '#comment_list_' + id;
		var getUri = $(blockSelector).data('get_uri') ? $(blockSelector).data('get_uri') : '';
		var postUri = $(blockSelector).data('post_uri') ? $(blockSelector).data('post_uri') : '';

		showCommentInput(id, targetBlockName, postUri, getUri, true);
		$('#link_show_comment_' + id).parent('small').html('<span>' + get_term('comment') + '</span>');
		return false;
	});

	$(document).on('click','.js-ajax-Load_timeline', function(){
		var getData = $(this).data('get_data') ? $(this).data('get_data') : {};
		loadTimeline(getData, this, true, true);
		return false;
	});
})

function showLinkCommentBlocks() {
	if (is_sp()) return;
	if (!get_uid()) return;
	$('.link_show_comment_form').each(function() {
		var id = parseInt($(this).data('id'));
		var parentDomElement = $('#timelineBox_' + id);
		var comment_cont = parseInt(parentDomElement.data('comment_count'));
		if (comment_cont) $(this).removeClass('hidden');
	});
}

function loadTimeline() {
	var getData         = (arguments.length > 0) ? arguments[0] : {};
	var trigerSelector  = (arguments.length > 1) ? arguments[1] : '';
	var isAddHisttory   = (arguments.length > 2) ? Boolean(arguments[2]) : false;
	var isRemoveTrigger = (arguments.length > 3) ? Boolean(arguments[3]) : false;

	var getUri             = 'timeline/api/list.html';
	var parentListSelector = '#article_list';

	var pushStateInfo = {};
	if (isAddHisttory) {
		var trigerObj = trigerSelector ? $(trigerSelector) : null;
		if (trigerObj && trigerObj.attr('href') && trigerObj.attr('href') != '#') {
			pushStateInfo['url'] = trigerObj.attr('href');
		} else {
			pushStateInfo['keys'] = ['max_id'];
		}
	}

	loadList(
		getUri,
		parentListSelector,
		trigerSelector,
		'prepend',
		getData,
		pushStateInfo,
		null,
		null,
		[postLoadTimeline],
		isRemoveTrigger
	);
}

function postLoadTimeline() {
	removeCaretFromPublicFlagDropdown();
	changeLikeView();
	loadTlCommentAll();
	showLinkCommentBlocks();
	setCommentCount();
	setLikeCount();
	renderSiteSummary();
	displayShareButton();
}

function displayShareButton() {
	if (get_config('isEnabledShareGoogle') && !empty(gapi)) gapi.plusone.go('#article_list');
}

function loadTlComment(getUri, parentListSelector, id) {
	loadList(getUri, parentListSelector, '', 'replace', {latest: 1, image_size: 'S'}, null, '#comment-template', '#comment_count_' + id, [renderSiteSummary]);
	$(parentListSelector).removeClass('unloade_comments');
}

function removeCaretFromPublicFlagDropdown() {
	$('.check_require_caret').each(function() {
		var uid = parseInt($(this).data('uid'));
		if (!get_uid() || uid != get_uid()) {
			$(this).children('span.caret').remove();
		}
		$(this).removeClass('check_require_caret');
	});
}

function loadTlCommentAll() {
	if (is_sp()) return;
	$('.unloade_comments').each(function() {
		var id = parseInt($(this).data('id'));
		var comment_cont = parseInt($('#timelineBox_' + id).data('comment_count'));
		if (comment_cont > 0) {
			var getUri = $(this).data('get_uri');
			loadTlComment(getUri, this, id);
		}
	});
}

function setCommentCount() {
	$('.unset_comment_count').each(function() {
		var id = parseInt($(this).data('id'));
		var count = $('#timelineBox_' + id).data('comment_count');
		if (count !== null) $(this).html(count);
		$(this).removeClass('unset_comment_count');
	});
}

function setLikeCount() {
	$('.unset_like_count').each(function() {
		var id = parseInt($(this).data('id'));
		var count = $('#timelineBox_' + id).data('like_count');
		if (count !== null) $(this).html(count);
		$(this).removeClass('unset_like_count');
	});
}

function changeLikeView() {
	if (get_uid()) {
		changeLikeStatus();
	} else {
		$('.timeline_link_like').each(function() {
			var id = parseInt($(this).data('id'));
			var targetSelector = '#timeline_link_like_' + id;
			$(targetSelector).remove();
		});
	}
}

function changeLikeStatus() {
	var likedTimelineObj = $('#liked_timeline_ids');
	if (get_uid() && likedTimelineObj.size()) {
		var timelineIds = $.parseJSON(likedTimelineObj.val());
		$.each(timelineIds, function(i, id) {
			$('#timeline_link_like_' + id).html(get_term('undo_like'));
		});
	}
	if (likedTimelineObj.size()) likedTimelineObj.remove();
}

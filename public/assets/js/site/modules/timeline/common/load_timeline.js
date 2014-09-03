$(function() {
	$('body').tooltip({
		selector: 'a[data-toggle=tooltip]'
	});

	var listType = $('#article_list').data('type');
	if (!listType || listType != 'ajax') {
		postLoadTimeline();
	}

	$(document).on('click', '.js-dropdown_tl_menu', function(){
		var detail_uri = $(this).data('detail_uri') ? $(this).data('detail_uri') : '';
		var delete_uri = $(this).data('delete_uri') ? $(this).data('delete_uri') : '';
		var parent = $(this).data('parent') ? $(this).data('parent') : '';
		var member_id = $(this).data('member_id') ? parseInt($(this).data('member_id')) : 0;

		var targetBlock = $(this).next('ul');
		if (targetBlock.html().length) return false;

		var source   = $("#tl_dropdown_menu-tpl").html();
		var template = Handlebars.compile(source);
		var val = {};
		val['detail_uri'] = detail_uri? get_url(detail_uri) : '';
		if (member_id == get_uid()) {
			if (delete_uri) {
				val['delete_uri'] = delete_uri;
				val['parent_id'] = parent;
			}
		}
		targetBlock.html(template(val));
		return false;
	});

	$(document).on('click','.link_show_comment', function(){
		var id = $(this).data('id') ? $(this).data('id') : 0;
		var blockSelector = '#comment_list_' + id;
		var getUri = $(blockSelector).data('get_uri') ? $(blockSelector).data('get_uri') : '';
		var postUri = $(blockSelector).data('post_uri') ? $(blockSelector).data('post_uri') : '';

		if (is_sp()) loadTlComment(getUri, blockSelector);
		showCommentInput(id, 'form_comment_' + id, postUri, getUri, false);
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
		loadTimeline(getData, this, true);
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
	var getData        = (arguments.length > 0) ? arguments[0] : {};
	var trigerSelector = (arguments.length > 1) ? arguments[1] : '';
	var isAddHisttory  = (arguments.length > 2) ? Boolean(arguments[2]) : false;

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
		postLoadTimeline
	);
}

function postLoadTimeline() {
	loadTlCommentAll();
	showLinkCommentBlocks();
	removeCaretFromPublicFlagDropdown();
	setCommentCount();
	setLikeCount();
}

function loadTlComment(getUri, parentListSelector) {
	loadList(getUri, parentListSelector, '', 'replace', {'latest': 1});
	$(parentListSelector).removeClass('unloade_comments');
}

function showCommentInput(id, targetBlockName, postUri, getUri, isFocus) {
	if (!get_uid() || !id || !targetBlockName.length) return;
	if ($('#link_show_comment_form_' + id).size()) $('#link_show_comment_form_' + id).hide();
	var textareaSelector = '#textarea_comment_' + id;
	if ($(textareaSelector).size() == 0) {
		var source   = $("#comment_form-template").html();
		var template = Handlebars.compile(source);
		var val = {
			'id' : id,
			'postUri' : postUri,
			'getUri' : getUri,
			'listSelector' : '#comment_list_' + id,
			'counterSelector' : '#comment_count_' + id
		};
		$('#' + targetBlockName).html(template(val));
	}
	if (isFocus) $(textareaSelector).focus();
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
			loadTlComment(getUri, this);
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

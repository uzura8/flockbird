$(function(){
	$('textarea.autogrow').autogrow();

	if (checkIsRenderSiterSummary()) {
		renderSiteSummary();
	}
});

$(document).on('keyup', '.js-keyup', function(event){
	var targetBtn = $(this).data('btn') ? $(this).data('btn') : '';
	if (targetBtn.length && event.keyCode == 13) {
		$(targetBtn).click();
	}
});

$(document).on('change', '.js-file_input', function(){
	var type = $(this).data('type') ? $(this).data('type') : 'image';
	var inputSelector = $(this).data('input') ? $(this).data('input') : '#form_' + type;
	$(inputSelector).val($(this).val());
});

$(document).on('click', '.js-like', function(){
	if (GL.execute_flg) return false;
	update_like_status(this);
	return false;
});

$(document).on('click', '.js-simplePost', function(){
	var postData = $(this).data('post_data') ? $(this).data('post_data') : {};
	close_dropdown_menu(this);
	post_submit(this, postData);
	return false;
});

$(document).on('click', '.js-simpleLink', function(){
	var confirm_msg = $(this).data('msg') ? $(this).data('msg') : '',
			getUri = $(this).data('uri') ? $(this).data('uri') : '',
			href = $(this).attr('href'),
			location_to;

	if (href && href == '#') href = '';
	if (!href && !getUri) return false;
	location_to = href ? href : get_url(getUri);
	close_dropdown_menu(this);

	if (confirm_msg.length > 0) {
		apprise(confirm_msg, {'confirm':true}, function(r) {
			if (r == true) location.href = location_to;
		});
		return false;
	}
	location.href = location_to;
	return false;
});

$(document).on('click', '.js-ajax-delete', function(){
	close_dropdown_menu(this);
	execute_simple_delete(this);
	return false;
});

$(document).on('click', '.js-update_toggle', function(){
	var confirmMsg = $(this).data('msg') ? $(this).data('msg') : '';
	if (GL.execute_flg) return false;
	if (confirmMsg.length > 0) {
		apprise(confirmMsg, {'confirm':true}, function(r) {
			if (r == true) updateToggle(this);
		});
	} else {
		updateToggle(this);
	}
	return false;
});

if (!is_sp()) {
	$(document).on({
		mouseenter:function() {
			if (check_editable_content(this)) $('#' + $(this).data('hidden_btn')).fadeIn('fast');
		},
		mouseleave:function() {
			var targetSelector = '#' + $(this).data('hidden_btn');
			if (check_editable_content(this)) $(targetSelector).hide();
			$(targetSelector).removeClass('open');
		}
	},'.js-hide-btn');
}

if (!is_sp()) {
	$(document).on({
		mouseenter:function() {
			if (check_editable_content(this)) $('.hidden_btn', this).fadeIn('fast');
		},
		mouseleave:function() {
			var $targetObj = $('.hidden_btn', this);
			if (check_editable_content(this)) $targetObj.hide();
			$targetObj.removeClass('open');
		}
	},'.js-hide-btn-simple');
}

$(document).on('click','.js-ajax-postComment', function(){
	var postUri = $(this).data('post_uri') ? $(this).data('post_uri') : '';
	var textareaSelector = $(this).data('textarea') ? $(this).data('textarea') : '';
	var getUri = $(this).data('get_uri') ? $(this).data('get_uri') : '';
	var listSelector = $(this).data('list') ? $(this).data('list') : '';
	var getData = $(this).data('get_data') ? $(this).data('get_data') : {};
	var counterSelector = $(this).data('counter') ? $(this).data('counter') : '';
	var templateSelector = $(this).data('template') ? $(this).data('template') : '';
	var isLatest = $(this).data('latest') ? Boolean($(this).data('latest')) : false;
	var isDesc = $(this).data('desc') ? Boolean($(this).data('desc')) : false;
	var isRenderSiteSummary = $(this).data('render_site_summary') ? Boolean($(this).data('render_site_summary')) : get_config('is_render_site_summary_at_client_side');

	var loadCallbacks = isRenderSiteSummary ? [renderSiteSummary] : [];
	var listSelectorGetData = $(listSelector).data('get_data');

	var position = isDesc ? 'prepend' : 'append';
	var sinceId = getSinceId(listSelector, position);
	if (sinceId) getData['since_id'] = sinceId;
	if (isLatest) getData['latest'] = 1;
	if (isDesc) getData['desc'] = 1;
	
	if (!empty(listSelectorGetData)) {
		if (!empty(listSelectorGetData.image_size)) getData.image_size = listSelectorGetData.image_size;
	}

	postComment(
		postUri,
		textareaSelector,
		getUri,
		listSelector,
		position,
		getData,
		this,
		counterSelector,
		templateSelector,
		[],
		loadCallbacks
	);
	return false;
});

$(document).on('click','.js-ajax-updatePublicFlag', function(){
	close_dropdown_menu(this);
	if (GL.execute_flg) return false;
	update_public_flag(this);
	return false;
});

$(document).on('click','.js-ajax-loadList', function(){
	var getUri = $(this).data('uri') ? $(this).data('uri') : '';
	var listSelector = $(this).data('list') ? $(this).data('list') : '';
	var getData = $(this).data('get_data') ? $(this).data('get_data') : {};
	var position = $(this).data('position') ? $(this).data('position') : 'replace';// params: replace / append / prepend
	var historyKeys = $(this).data('history_keys') ? $(this).data('history_keys') : {};
	var templateSelector = $(this).data('template') ? $(this).data('template') : '';
	var counterSelecor = $(this).data('counter') ? $(this).data('counter') : '';
	var inputs = $(this).data('inputs') ? $(this).data('inputs') : {};
	var triggerType = $(this).data('type') ? $(this).data('type') : 'list';// params: list / button

	if (GL.execute_flg) return false;
	if (!getUri) return false;

	if (inputs.length) {
		$.each(inputs, function(i, val) {
			getData[val] = $.trim($('[name=' + val + ']').val());
		});
	}

	var pushStateInfo = {};
	if (historyKeys) {
		var trigerObj = $(this);
		if (trigerObj && trigerObj.attr('href') && trigerObj.attr('href') != '#') {
			pushStateInfo['url'] = trigerObj.attr('href');
		} else {
			pushStateInfo.keys = $.isArray(historyKeys) ? historyKeys : [historyKeys];
		}
	}

	loadList(
		getUri,
		listSelector,
		this,
		position,
		getData,
		pushStateInfo,
		templateSelector,
		counterSelecor,
		null,
		(triggerType == 'list') ? true : false
	);

	return false;
});

$(document).on('click', '.js-exec_unauth', function(){
	var uid = $(this).data('uid') ? parseInt($(this).data('uid')) : 0;
	var func = $(this).data('func') ? $(this).data('func') : null;
	if (!uid || !func) return false;
	if (uid == get_uid()) return false;
	func = eval(func);
	func(this);
	return false;
});

$(document).on('click', '.js-display_parts', function(){
	var targetId = $(this).data('target_id') ? $(this).data('target_id') : '';
	var hideSelector = $(this).data('hide_selector') ? $(this).data('hide_selector') : '';
	var focusSelector = $(this).data('focus_selector') ? $(this).data('focus_selector') : '';

	$('#' + targetId).removeClass('hidden');
	if (hideSelector) $(hideSelector).addClass('hidden');
	if (focusSelector) $(focusSelector).focus();
	return false;
});

$(document).on('click', '.js-insert_text', function(){
	var inputSelector = $(this).data('input') ? $(this).data('input') : '',
			openSelector  = $(this).data('open')  ? $(this).data('open') : '',
			hideSelector  = $(this).data('hide')  ? $(this).data('hide') : '',
			insertText    = $(this).data('text')  ? $(this).data('text') : '',
			parentId      = $(this).data('parent_id') ? parseInt($(this).data('parent_id')) : 0,
			inputVal,
			blockSelector,
			getUri,
			postUri;
	// For rendering input form by template.
	if (empty($('#commentPostBox_' + parentId).html())) {
		parentId = parseInt($(this).parents('.comment_list').data('id'));
		inputSelector = '#textarea_comment_' + parentId;
		blockSelector = '#comment_list_' + parentId;
		getUri = $(blockSelector).data('get_uri') ? $(blockSelector).data('get_uri') : '';
		postUri = $(blockSelector).data('post_uri') ? $(blockSelector).data('post_uri') : '';
		showCommentInput(parentId, 'form_comment_' + parentId, postUri, getUri, true);
		$('#link_show_comment_' + parentId).parent('small').html('<span>' + get_term('comment') + '</span>');

	// For rendering input form hidden element.
	} else {
		if ($(openSelector).hasClass('hidden')) $(openSelector).removeClass('hidden');
		if (!$(hideSelector).hasClass('hidden')) $(hideSelector).addClass('hidden');
		$(inputSelector).focus();
	}
	inputVal = $(inputSelector).val();
	if (insertText.length) {
		if (inputVal.length) inputVal += ' ';
		$(inputSelector).val(inputVal + insertText + ' ');
	}
	if (is_sp()) scroll('#commentPostBox_' + parentId);
	return false;
});

$(document).on('click', '.js-insert_img', function(){
	var selfSelector = $(this).attr('id') ? $(this).attr('id') : this;
	var btn = document.getElementById(selfSelector);
	var targetBodySelector = btn.getAttribute("data-body");
	var isAddElement = (targetBodySelector == '.note-editable');
	if (!targetBodySelector) return false;
	var fileId = $(this).data('id') ? parseInt($(this).data('id')) : 0;
	var size = fileId ? $('#select_size_' + fileId).val() : 'raw';
	var imgNamePrefix = $(this).data('file_name_prefix') ? $(this).data('file_name_prefix') : '';
	var imgName = $(this).data('file_name') ? $(this).data('file_name') : '';
	if (!imgNamePrefix || !imgName) return false;

	var imgUri = getImgUri(imgNamePrefix + imgName, size);
	if (!imgUri) return false;
	var textAreaSelector = fileId ? '#image_description_' + fileId : '';
	var textAreaVal = textAreaSelector ? $(textAreaSelector).val() : '';
	var src = ' src="' + get_url(imgUri, true, false) + '"';
	var alt = textAreaVal ? ' alt="' + textAreaVal + '"' : '';
	var addValue = '<img' + src + alt + '>';
	var result;
	if (isAddElement) {
		result = insertHtmlAtCaret(addValue, targetBodySelector);
	} else {
		result = insertTextAtCaret(targetBodySelector, addValue);
	}
	var message = get_term('add_picture');
	message += result ? 'しました。' : '出来ませんでした。';
	showMessage(message);
	return false;
});

$(document).on('click', '.js-dropdown_content_menu', function(){
	var getUri = $(this).data('uri') ? $(this).data('uri') : '';
	var getData = $(this).data('get_data') ? $(this).data('get_data') : {};
	var memberId = $(this).data('member_id') ? parseInt($(this).data('member_id')) : 0;
	var targetBlock = $(this).data('menu') ? $($(this).data('menu')) : $(this).next('ul');

	if (is_site() && !get_uid()) return false;

	var selfObj = $(this);
	$.ajax({
		url : get_url(getUri),
		type : 'GET',
		dataType : 'text',
		data : getData,
		timeout: get_config('default_ajax_timeout'),
		beforeSend: function(xhr, settings) {
			GL.execute_flg = true;
			setLoading(targetBlock);
		},
		complete: function(xhr, textStatus) {
			GL.execute_flg = false;
			removeLoading(targetBlock);
		},
		success: function(response, status) {
			if (status != 'nocontent' && response.length) {
				targetBlock.find('li.ajax_loaded').remove();
				targetBlock.append(response);
			}
		},
		error: function(result, status) {
			showErrorMessage(result);
		}
	});
	return false;
});

$(document).on('click', '.js-popover', function(){
	var contentSelector = $(this).data('content_id');

	if ($(contentSelector).size() && $(contentSelector).html().length) {
		$(this).popover('hide');
	} else {
		$(this).popover('show', {html: true});

		var getUri = $(this).data('uri');
		var getUri = $(this).data('uri');
		var templateSelector = $(this).data('tmpl') ? $(this).data('tmpl') : '';
		var template = templateSelector ? Handlebars.compile($(templateSelector).html()) : null;

		loadList(
			getUri,
			contentSelector,
			'',
			'replace',
			getData,
			'',
			templateSelector
		);
	}
	return false;
});

$(document).on('click', '.js-modal', function(){
	var target = $(this).data('target');
	var getUri = $(this).data('uri');
	var getData = $(this).data('get_data') ? $(this).data('get_data') : {};
	var isList = $(this).data('is_list') ? Boolean($(this).data('is_list')) : false;
	var templateSelector = $(this).data('tmpl') ? $(this).data('tmpl') : '';
	var template = templateSelector ? Handlebars.compile($(templateSelector).html()) : null;

	if (target == '#modal_album_slide')
	{
		GL.currentScrollY = $(window).scrollTop();
	}

	var option = {};
	if (isList) {
		loadList(
			getUri,
			target + ' .modal-body',
			'',
			'replace',
			getData,
			'',
			templateSelector
		);
	} else {
		if (getUri) {
			option['remote'] = get_url(getUri);
		}
	}
	$(target).modal(option);
	return false;
});

$(document).on('click', '.js-facebook_feed', function(){
	var options = $(this).data('options');
	popupFacebookShareDialog(options);
	return false;
});

function checkIsRenderSiterSummary()
{
	var divArticleElement,
			divCommentElement;

	if (!get_config('is_render_site_summary_at_client_side')) return false;
	if (!empty($('#main_container').data('not_render_site_summary'))) return false;
	if (!empty($('#article_list').data('not_render_site_summary'))) return false;

	divArticleElement = document.getElementById('article_list');
	if (divArticleElement != null && divArticleElement.hasChildNodes()) return true;
	divCommentElement = document.getElementById('comment_list');
	if (divCommentElement != null && divCommentElement.hasChildNodes()) return true;

	return false;
}

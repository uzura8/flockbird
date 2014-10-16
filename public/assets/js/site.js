$('textarea.autogrow').autogrow();

$(document).on('click', '.btn_follow', function(){
	if (GL.execute_flg) return false;
	update_follow_status(this);
	return false;
});

$(document).on('click', '.js-like', function(){
	if (GL.execute_flg) return false;
	update_like_status(this);
	return false;
});

$(document).on('click', '.js-simplePost', function(){
	close_dropdown_menu(this);
	post_submit(this);
	return false;
});

$(document).on('click', '.js-simpleLink', function(){
	var getUri = $(this).data('uri') ? $(this).data('uri') : '';
	if (!getUri.length) return false;
	location.href = get_url(getUri);
	return false;
});

$(document).on('click', '.js-ajax-delete', function(){
	close_dropdown_menu(this);
	execute_simple_delete(this);
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

	var position = isDesc ? 'prepend' : 'append';
	var nextSelector = getNextSelector(listSelector, position);
	if (nextSelector) getData['since_id'] = parseInt($(nextSelector).data('id'));
	if (isLatest) getData['latest'] = 1;
	if (isDesc) getData['desc'] = 1;

	postComment(
		postUri,
		textareaSelector,
		getUri,
		listSelector,
		position,
		getData,
		this,
		counterSelector,
		templateSelector
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
	var historyKey = $(this).data('history_key') ? $(this).data('history_key') : '';
	var templateSelector = $(this).data('template') ? $(this).data('template') : '';
	var counterSelecor = $(this).data('counter') ? $(this).data('counter') : '';

	if (GL.execute_flg) return false;
	if (!getUri) return false;

	var pushStateInfo = {};
	if (historyKey) {
		var trigerObj = $(this);
		if (trigerObj && trigerObj.attr('href') && trigerObj.attr('href') != '#') {
			pushStateInfo['url'] = trigerObj.attr('href');
		} else {
			pushStateInfo['keys'] = [historyKey];
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
		counterSelecor
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

$(document).on('click', '.js-popover', function(){
	var contentSelector = $(this).data('content_id');

	if ($(contentSelector).size() && $(contentSelector).html().length) {
		$(this).popover('hide');
	} else {
		$(this).popover('show', {html: true});

		var getUri = $(this).data('uri');
		var templateSelector = $(this).data('tmpl');
		var source = $(templateSelector).html();
		var template = Handlebars.compile(source);

		$.ajax({
			type: 'GET',
			url: get_url(getUri),
			dataType: 'text',
			beforeSend: function(xhr, settings) {
				GL.execute_flg = true;
				$("#article").append(get_loading_image_tag('loading_article'));
			},
			complete: function(xhr, textStatus) {
				GL.execute_flg = false;
				$("#loading_article").remove();
			},
			success: function(response, status){
				var obj = $.parseJSON(response);
				if (obj.list) {
					$.each(obj.list, function(i, val) {
						var html = template(val);
						$(contentSelector).append(html);
					});
				} else {
					var html = 'ありません。';
					$(contentSelector).append(html);
				}
			},
			error: function(result) {
				showMessage(get_error_message(result['status'], '読み込みに失敗しました。'));
			}
		});
	}
	return false;
});

$(document).on('click', '.js-modal', function(){
	var target = $(this).data('target');
	var getUri = $(this).data('uri');
	var getData = $(this).data('get_data') ? $(this).data('get_data') : {};
	var templateSelector = $(this).data('tmpl') ? $(this).data('tmpl') : '';
	var template = templateSelector ? Handlebars.compile($(templateSelector).html()) : null;

	var option = {};
	if (getUri) {
		$.ajax({
			type: 'GET',
			url: get_url(getUri),
			data : getData,
			dataType : templateSelector ? 'json' : 'text',
			beforeSend: function(xhr, settings) {
				GL.execute_flg = true;
				$("#article").append(get_loading_image_tag('loading_article'));
			},
			complete: function(xhr, textStatus) {
				GL.execute_flg = false;
				$("#loading_article").remove();
			},
			success: function(result, status){
				var html = templateSelector ? template(result) : result;
				$(target + ' .modal-body').html(html);
				$(target).modal(option);
			},
			error: function(result) {
				showMessage(get_error_message(result['status'], '読み込みに失敗しました。'));
			}
		});
	} else {
		option['remote'] = get_url(getUri);
		$(target).modal(option);
	}
	return false;
});

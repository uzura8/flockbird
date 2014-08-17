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
	var isInsertBefore = $(this).data('is_before') ? Boolean($(this).data('is_before')) : false;
	var getData = $(this).data('get_data') ? $(this).data('get_data') : {};
	var counterSelector = $(this).data('counter') ? $(this).data('counter') : '';

	var nextSelector = getNextSelector(listSelector, isInsertBefore);
	postComment(
		postUri,
		textareaSelector,
		getUri,
		listSelector,
		nextSelector,
		isInsertBefore,
		this,
		counterSelector
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
	var limit = $(this).data('limit') ? $(this).data('limit') : 0;
	var isInsertBefore = $(this).data('is_before') ? Boolean($(this).data('is_before')) : false;
	var getData = $(this).data('get_data') ? $(this).data('get_data') : {};
	var lastId = $(this).data('last_id') ? parseInt($(this).data('last_id')) : 0;

	if (GL.execute_flg) return false;
	if (!getUri || !listSelector) return false;

	var limitId = 0;
	if (isInsertBefore) {
		getData['is_before'] = 1;
		var nextSelector = '#' + $(this).next().attr('id');
		if ($(this).prev().size()) limitId = parseInt($(this).prev().data('id'));
	} else {
		var nextSelector = '#' + $(this).prev().attr('id');
		if ($(this).next().size()) limitId = parseInt($(this).next().data('id'));
	}
	if (limitId) getData['limit_id'] = limitId;

	loadList(
		getUri,
		listSelector,
		limit,
		nextSelector,
		isInsertBefore,
		this,
		getData,
		lastId
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

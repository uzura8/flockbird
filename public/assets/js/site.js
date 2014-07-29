$('textarea.autogrow').autogrow();

$(document).on('click', '.btn_follow', function(){
	if (GL.execute_flg) return false;
	update_follow_status(this);
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
			if (check_editable_content(this)) $('#' + $(this).data('hidden_btn')).hide();
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

	if (isInsertBefore) {
		getData['is_before'] = 1;
		var nextSelector = '#' + $(this).next().attr('id');
		if ($(this).prev().size()) lastId = parseInt($(this).prev().data('id'));
	} else {
		var nextSelector = '#' + $(this).prev().attr('id');
		if ($(this).next().size()) lastId = parseInt($(this).next().data('id'));
	}

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

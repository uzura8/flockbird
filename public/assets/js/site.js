$('textarea.autogrow').autogrow();

$(document).on('click', '.btn_follow', function(){
	if (GL.execute_flg) return false;
	update_follow_status(this);
	return false;
});

$(document).on('click', '.js-simplePost', function(){
	$(this).parent('li').parent('ul.dropdown-menu').parent('div.btn-group').removeClass('open');
	post_submit(this);
	return false;
});

$(document).on('click', '.js-ajax-delete', function(){
	$(this).parent('li').parent('ul.dropdown-menu').parent('div.btn-group').removeClass('open');
	execute_simple_delete(this);
	return false;
});

if (!is_sp()) {
	$(document).on({
		mouseenter:function() {$('#' + $(this).data('hidden_btn')).fadeIn('fast')},
		mouseleave:function() {$('#' + $(this).data('hidden_btn')).hide()}
	},'.js-hide-btn');
}

$(document).on('click','.js-ajax-postComment', function(){
	var postUri = $(this).data('post_uri') ? $(this).data('post_uri') : '';
	var textareaSelector = $(this).data('textarea') ? $(this).data('textarea') : '';
	var getUri = $(this).data('get_uri') ? $(this).data('get_uri') : '';
	var listSelector = $(this).data('list') ? $(this).data('list') : '';
	var isInsertBefore = $(this).data('is_before') ? Boolean($(this).data('is_before')) : false;
	var getData = $(this).data('get_data') ? $(this).data('get_data') : {};

	var nextElement = '';
	if ($(listSelector).html().replace(/[\r\n\s]+/, '')) {
		var position = isInsertBefore ? 'first' : 'last';
		nextElement = $(listSelector + ' > div:' + position);
	}
	var nextSelector = nextElement ? '#' + nextElement.attr('id') : '';

	postComment(
		postUri,
		textareaSelector,
		getUri,
		listSelector,
		nextSelector,
		isInsertBefore,
		this
	);
	return false;
});

$(document).on('click','.js-ajax-loadList', function(){
	var getUri = $(this).data('uri') ? $(this).data('uri') : '';
	var listSelector = $(this).data('list') ? $(this).data('list') : '';
	var limit = $(this).data('limit') ? $(this).data('limit') : 0;
	var isInsertBefore = $(this).data('is_before') ? Boolean($(this).data('is_before')) : false;
	var getData = $(this).data('get_data') ? $(this).data('get_data') : {};

	if (GL.execute_flg) return false;
	if (!getUri || !listSelector) return false;

	var limitId = 0;
	if (isInsertBefore) {
		var nextSelector = '#' + $(this).next().attr('id');
		getData['is_before'] = 1;
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
		getData
	);

	return false;
});

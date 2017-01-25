$(document).on('click', '.js-notice-read_all', function(){
	var type = !empty($(this).data('type')) ? $(this).data('type') : 'notice';

	if (!inArray(type, ['notice', 'message'])) return false;
	if (!get_uid()) return false;

	var postUri = type + '/api/read_all';
	var $selfObj = $(this);
	$.ajax({
		url : get_url(postUri),
		type : 'POST',
		dataType : 'json',
		data : set_token(),
		timeout: get_config('default_ajax_timeout'),
		beforeSend: function(xhr, settings) {
			GL.execute_flg = true;
			$selfObj.attr('disabled', true);
			//$selfObj.html(get_loading_image_tag());
		},
		complete: function(xhr, textStatus) {
			GL.execute_flg = false;
			$selfObj.attr('disabled', false);
		},
		success: function(result){
			if (result.updated_count > 0) {
				var messageDefault = __('notice_update_read_already');
				showMessage(result.message, messageDefault);
				changeViewToRead(type);
			}
		},
		error: function(result){
			showErrorMessage(result, __('message_change_failed'));
		}
	});
	return false;
});

function changeViewToRead(type) {
	var changedReadState = get_term('already_read');
	$('#badge_' + type).remove();
	$('.' + type + '_list').removeClass('simpleList-item-warning');
	$('.' + type + '_read_state').html(changedReadState);
}

function loadNotice() {
	var getData        = (arguments.length > 0) ? arguments[0] : {};
	var trigerSelector = (arguments.length > 1) ? arguments[1] : '';
	var isAddHisttory  = (arguments.length > 2) ? Boolean(arguments[2]) : false;

	var getUri             = 'notice/api/list.json';
	var parentListSelector = '#article_list';

	loadList(
		getUri,
		parentListSelector,
		trigerSelector,
		'append',
		getData,
		'',
		'#notices-template'
	);
}

Handlebars.registerHelper('getNoticeInfo', function(foreign_table, type, members, members_count) {
	var outputMemberCount = members_count - members.length;
	if (outputMemberCount < 0) outputMemberCount = 0;
	var subject = '';
	var count = members.length;
	$.each(members, function(i, val) {
		if (empty(val)) return;
		if (subject.length) {
			if (i == count - 1 && empty(outputMemberCount)) {
				subject += get_term('delimitter_last');
			} else {
				subject += get_term('delimitter_normal');
			}
		}
		subject += val.name;
	});
	if (!subject.length) subject = get_term('left_member');
	if (outputMemberCount) subject += get_term('delimitter_last') + get_term('other_members_count', {num: outputMemberCount});
	return convertNoticeAction(foreign_table, type, subject);
});

Handlebars.registerHelper('getImgUri', function(fileName, size) {
	if (empty(fileName)) fileName = 'm';
	return get_url(getImgUri(fileName, size), true, false);
});

Handlebars.registerHelper('getNoticeContentUrl', function(foreign_table, foreign_id, parent_table, parent_id) {
	if (!parent_table) parent_table = foreign_table;
	if (!parent_id) parent_id = foreign_id;
	return get_url(getNoticeContentUriMiddlePath(parent_table) + '/' + parent_id);
});

function getNoticeContentUriMiddlePath(foreign_table)
{
	switch (foreign_table)
	{
		case 'timeline':
		case 'thread':
		case 'note':
		case 'album':
		case 'member':
			return foreign_table;
		case 'album_image':
			return 'album/image';
	}
	return '';
}

function convertNoticeAction(foreign_table, type, subject)
{
	var label;
	switch (type)
	{
		case '3':
			return __('message_comment_from_to', {'subject': subject, 'object': get_term('you')});

		case '4':
			return __('message_like_from_to', {'subject': subject, 'object': get_term('you')});

		case '5':
			if (foreign_table == 'album') {
				return __('message_add_for_from_to', {'subject': subject, 'object': get_term('album'), 'label': get_term('picture')});
			}
			return __('message_add_from_to', {'subject': subject, 'object': get_term('picture')});

		case '6':
			return __('message_post_for_from_to', {'subject': subject, 'object': get_term('you'), label: convertNoticeForeignTable(foreign_table)});

		case '7':
			label = convertNoticeForeignTable(foreign_table) + get_term('delimitter_words') + get_term('comment');
			return __('message_post_for_from_to', {'subject': subject, 'object': get_term('you'), 'label': label});

		case '8':
			return __('member_message_follow_from_to', {'subject': subject, 'object': get_term('you')});
	}
}

function convertNoticeForeignTable(foreign_table)
{
	var isComment = false;
	var matches = foreign_table.match(/^([A-Za-z0-9_]+)_comment$/);
	if (matches) {
		isComment = true;
		foreign_table = matches[1];
	}
	switch (foreign_table)
	{
		case 'timeline':
		case 'thread':
		case 'note':
		case 'album':
		case 'album_image':
			var suffix = (isComment) ? get_term('delimitter_words') + get_term('comment') : '';
			return get_term(foreign_table) + suffix;
	}
	return '';
}


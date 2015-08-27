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
	var output = '';
	$.each(members, function(i, val) {
		if (output.length) output += ' と ';
		if (empty(val)) return;
		output += val.name;
	});
	if (!output.length) output = get_term('left_member');
	if (outputMemberCount) output += ' 他' + outputMemberCount + '人';
	output += ' が';
	output += convertNoticeAction(foreign_table, type);
	return output;
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
			return foreign_table;
		case 'album_image':
			return 'album/image';
	}
	return '';
}

function convertNoticeAction(foreign_table, type)
{
	switch (type)
	{
		case '6':
			return 'あなた宛に' + convertNoticeForeignTable(foreign_table) + 'を投稿しました。';
		case '7':
			return 'あなた宛に' + convertNoticeForeignTable(foreign_table) + get_term('comment') + 'を投稿しました。';
	}
	return convertNoticeForeignTable(foreign_table) + 'に' + convertNoticeType(foreign_table, type) + 'しました。';
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
			var suffix = (isComment) ? get_term('comment') : '';
			return get_term(foreign_table) + suffix;
	}
	return '';
}

function convertNoticeType(foreign_table, type)
{
	switch (type)
	{
		case '3':
			return get_term('comment');
		case '4':
			return get_term('like');
		case '5':
			if (foreign_table == 'album') return get_term('add_picture');
			return '追加';
	}
	return '';
}

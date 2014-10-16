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
		output += val.name;
	});
	if (outputMemberCount) output += ' 他' + outputMemberCount + '人';
	output += ' が';
	output += convertNoticeAction(foreign_table, type);
	return output;
});

Handlebars.registerHelper('getNoticeContentUrl', function(foreign_table, foreign_id) {
	return get_url(getNoticeContentUriMiddlePath(foreign_table) + '/' + foreign_id);
});

function getNoticeContentUriMiddlePath(foreign_table)
{
	switch (foreign_table)
	{
		case 'timeline':
			return foreign_table;
	}
	return '';
}

function convertNoticeAction(foreign_table, type)
{
	return convertNoticeForeignTable(foreign_table) + 'に' + convertNoticeType(type) + 'しました。';
}

function convertNoticeForeignTable(foreign_table)
{
	switch (foreign_table)
	{
		case 'timeline':
			return get_term(foreign_table);
	}
	return '';
}

function convertNoticeType(type)
{
	switch (type)
	{
		case '3':
			return get_term('comment');
	}
	return '';
}

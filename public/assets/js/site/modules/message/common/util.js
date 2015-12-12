function loadMessage() {
	var getData        = (arguments.length > 0) ? arguments[0] : {};
	var trigerSelector = (arguments.length > 1) ? arguments[1] : '';
	var isAddHisttory  = (arguments.length > 2) ? Boolean(arguments[2]) : false;

	var getUri             = 'message/api/list.json';
	var parentListSelector = '#article_list';

	loadList(
		getUri,
		parentListSelector,
		trigerSelector,
		'append',
		getData,
		'',
		'#messages-template'
	);
}

Handlebars.registerHelper('getMessageInfo', function(member_name, type, subject) {
	member_name = escapeHtml(member_name);
	subject = escapeHtml(subject);
	switch (type)
	{
		case '1':
			return '<h5>' + member_name + ' から' + get_term('messageTypeMember') + 'が届きました。</h5>';
		case '2':
			return '<h5>' + member_name + ' から' + get_term('messageTypeGroup') + 'が届きました。</h5>';
		case '7':
			return '<h4>' + subject + ' <small>' + get_term('messageTypeSiteInfoAll') + '</small></h4>';
		case '8':
			return '<h4>' + subject + ' <small>' + get_term('messageTypeSiteInfoAll') + '</small></h4>';
		case '9':
			return '<h4>' + subject + ' <small>' + get_term('messageTypeSystemInfo') + '</small></h4>';
	}
});

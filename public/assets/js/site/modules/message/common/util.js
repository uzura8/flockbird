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

Handlebars.registerHelper('getMessageInfo', function(member_name, type) {
	return member_name + ' から' + get_term('message') + 'が届きました。';
});

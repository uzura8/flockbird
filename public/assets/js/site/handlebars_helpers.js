Handlebars.registerHelper('site_url', function(uri) {
	var result = get_url(Handlebars.Utils.escapeExpression(uri));
	return new Handlebars.SafeString(result);
});

Handlebars.registerHelper('member_link', function(object) {
	object.member_id = Handlebars.Utils.escapeExpression(object.id);
	object.member_name = Handlebars.Utils.escapeExpression(object.name);
	var result = '<a href="' + get_url('member/' + object.member_id) + '">' + object.member_name + '</a>';
	return new Handlebars.SafeString(result);
});

Handlebars.registerHelper('member_url', function(member_id) {
	var member_id = Handlebars.Utils.escapeExpression(member_id);
	var result = get_url('member/' + member_id);
	return new Handlebars.SafeString(result);
});

Handlebars.registerHelper('img_url', function(filepath, filename) {
	var size = (arguments.length > 2) ? arguments[2] : '50x50xc';
	if (!filepath) filepath = 'm/all';
	if (!filename) filename = 'noimage.gif';
	var result = get_url('media/img/' + size + '/' + filepath + filename);
	return new Handlebars.SafeString(result);
});

Handlebars.registerHelper('conv2objStr', function(key1, value1, key2, value2) {
	var items = [];
	if (value1) items.push('&quot;' + key1 + '&quot;:' + value1);
	if (value2) items.push('&quot;' + key2 + '&quot;:' + value2);
	if (items.length == 0) return '';

	var objStr = '{' + items.join() + '}';
	return new Handlebars.SafeString(objStr);
});

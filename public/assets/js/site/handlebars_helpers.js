Handlebars.registerHelper('site_url', function(uri) {
	return get_url(Handlebars.Utils.escapeExpression(uri));
});

Handlebars.registerHelper('member_screen_name', function(member_name) {
	if (member_name) return member_name;
	return get_term('member_left');
});

Handlebars.registerHelper('member_link', function(object) {
	object.member_id = Handlebars.Utils.escapeExpression(object.id);
	object.member_name = Handlebars.Utils.escapeExpression(object.name);
	var result = '<a href="' + get_url('member/' + object.member_id) + '">' + object.member_name + '</a>';
	return new Handlebars.SafeString(result);
});

Handlebars.registerHelper('member_url', function(member_id) {
	var member_id = Handlebars.Utils.escapeExpression(member_id);
	return get_url('member/' + member_id);
});

Handlebars.registerHelper('img_url', function(filename) {
	var size = (arguments.length > 1) ? arguments[1] : '50x50xc';
	var imgUri = getImgUri(filename, size, false)
	if (!imgUri) imgUri = 'media/img/' + size + '/m/all/noimage.gif';
	return get_url(imgUri, true, false);
});

Handlebars.registerHelper('conv2objStr', function(key1, value1, key2, value2, key3, value3) {
	var items = [];
	if (value1) items.push('&quot;' + key1 + '&quot;:&quot;' + value1 + '&quot;');
	if (value2) items.push('&quot;' + key2 + '&quot;:&quot;' + value2 + '&quot;');
	if (value3) items.push('&quot;' + key3 + '&quot;:&quot;' + value3 + '&quot;');
	if (items.length == 0) return '';

	var objStr = '{' + items.join() + '}';
	return new Handlebars.SafeString(objStr);
});

Handlebars.registerHelper('getTerm', function(key) {
	return get_term(key);
});

Handlebars.registerHelper('strimwidth', function(str, width) {
	return strimwidth(str, width)
});

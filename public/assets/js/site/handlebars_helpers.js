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

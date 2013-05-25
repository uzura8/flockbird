$('textarea.autogrow').autogrow();

function get_id_from_url()
{
	var is_parseInt = (arguments.length > 1) ? arguments[1] : true;

	var id = url('-1');
	if (is_parseInt) id = parseInt(id);

	return id;
}

function get_id_num(id_string)
{
	var matches = id_string.match(/^[a-z0-9_]+_(\d+)$/i);
	if (matches) return matches[1];

	return false;
}

function get_url(uri)
{
	return get_baseUrl() + uri;
}

function set_token(obj)
{
	var token_key = get_token_key();
	obj[token_key] = get_token();

	return obj;
}

function get_error_message(status)
{
	var default_message = (arguments.length > 2) ? arguments[2] : '';

	switch (status)
	{
		case 401:
			return '認証情報の取得に失敗しました。ログイン後、再度実行してください。';
		default :
			return default_message;
	}
}

function show_list(uri, list_attribute) {
	var is_fadein = (arguments.length > 3) ? arguments[3] : true;

	var baseUrl = get_baseUrl();
	var get_url = baseUrl + uri;
	$(list_attribute).html('<div class="loading_image"><img src="' + baseUrl + 'assets/img/loading.gif"></div>');
	$.get(get_url, {'nochache':(new Date()).getTime()}, function(data) {
		if (data.length > 0) {
			if (is_fadein) $(list_attribute).fadeOut('fast');
			$(list_attribute).html(data).fadeIn('fast');
		}
	});
}

function delete_item(post_uri, id, target_attribute_prefix)
{
	var item_term = (arguments.length > 4) ? arguments[4] : '';

	jConfirm('削除しますか?', '削除確認', function(r) {
		if (r == true) delete_item_execute(post_uri, id, target_attribute_prefix, item_term);
	});
}

function delete_item_execute(post_uri, id, target_attribute_prefix, item_term)
{
	var baseUrl = get_baseUrl();

	var token_key = get_token_key();
	var post_data = {};
	post_data['id'] = id;
	post_data[token_key] = get_token();

	var msg_prefix = '';
	if (item_term.length > 0) msg_prefix = item_term + 'を';

	$.ajax({
		url : baseUrl + post_uri,
		dataType : "text",
		data : post_data,
		type : 'POST',
		success: function(data){
			$(target_attribute_prefix + '_' + id).fadeOut();
			$.jGrowl(msg_prefix + '削除しました。');
		},
		error: function(data){
			$.jGrowl(get_error_message(data['status'], msg_prefix + '削除できませんでした。'));
		}
	});
}

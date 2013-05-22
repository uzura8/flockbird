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

function set_token(obj)
{
	var token_key = get_token_key();
	obj[token_key] = get_token();

	return obj;
}

function get_error_message(status, default_message = '')
{
	switch (status)
	{
		case 401:
			return '認証情報の取得に失敗しました。ログイン後、再度実行してください。';
		default :
			return default_message;
	}
}

function show_list(uri, list_attribute) {
	var is_fadein = (arguments.length > 2) ? arguments[2] : true;
	var loading_image_attribute = (arguments.length > 3) ? arguments[3] : '#loading_list';

	var baseUrl = get_baseUrl();
	var get_url = baseUrl + uri;
	$(loading_image_attribute).html('<img src="' + baseUrl + 'assets/img/loading.gif">');
	$.get(get_url, {'nochache':(new Date()).getTime()}, function(data) {
		if (data.length > 0) {
			if (is_fadein) $(list_attribute).fadeOut('fast');
			$(list_attribute).html(data).fadeIn('fast');
		}
	});
	$(loading_image_attribute).remove();
}

function create_comment(textarea_attribute, parent_id, post_uri, get_uri, list_attribute)
{
	var list_fadein = (arguments.length > 5) ? arguments[5] : true;
	var textarea_height  = (arguments.length > 6) ? arguments[6] : '33px';

	var body = $(textarea_attribute).val().trim();
	if (body.length <= 0) return;

	var data = {'id':parent_id, 'body':body};
	data = set_token(data);

	$.ajax({
		url : get_baseUrl() + post_uri,
		dataType : 'text',
		data : data,
		type : 'POST',
		success: function(result){
			$.jGrowl('コメントを投稿しました。');
			show_list(get_uri, list_attribute, list_fadein);
			$(textarea_attribute).val('');
			$('textarea'.textarea_attribute).css('height', textarea_height);
		},
		error: function(data){
			$.jGrowl(get_error_message(data['status'], 'コメントを投稿できませんでした。'));
		}
	});
}

function delete_comment(post_uri, id, target_attribute_prefix)
{
	jConfirm('削除しますか?', '削除確認', function(r) {
		if (r == true) delete_comment_execute(post_uri, id, target_attribute_prefix);
	});
}

function delete_comment_execute(post_uri, id, target_attribute_prefix)
{
	var baseUrl = get_baseUrl();
	var token_key = get_token_key();
	var post_data = {};
	post_data['id'] = id;
	post_data[token_key] = get_token();
	$.ajax({
		url : baseUrl + post_uri,
		dataType : "text",
		data : post_data,
		type : 'POST',
		success: function(data){
			$(target_attribute_prefix + '_' + id).fadeOut();
			$.jGrowl('コメントを削除しました。');
		},
		error: function(data){
			$.jGrowl(get_error_message(data['status'], 'コメントを削除できませんでした。'));
		}
	});
}

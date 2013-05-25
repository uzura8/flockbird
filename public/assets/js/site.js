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

function load_masonry_item(container_attribute, item_attribute, item_name)
{
	var loading_image_url = (arguments.length > 4) ? arguments[4] : get_url('assets/img/site/loading_l.gif');

	var $container = $(container_attribute);
	$container.imagesLoaded(function(){
		$container.masonry({
			itemSelector : item_attribute
		});

		$container.infinitescroll({
			navSelector  : '#page-nav',   // ページのナビゲーションを選択
			nextSelector : '#page-nav a', // 次ページへのリンク
			itemSelector : '.main_item',    // 持ってくる要素のclass
			loading: {
				finishedMsg: item_name + 'がありません。', //次のページがない場合に表示するテキスト
				img: loading_image_url //ローディング画像のパス
			}
		},
		function( newElements ) {
			var $newElems = $( newElements ).css({ opacity: 0 });
			$newElems.imagesLoaded(function(){
				$newElems.animate({ opacity: 1 });
				$container.masonry( 'appended', $newElems, true );
			});
		});
	});
}

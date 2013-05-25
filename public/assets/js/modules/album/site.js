function set_cover(album_image_id) {
	var post_data = {'id':album_image_id};
	post_data     = set_token(post_data);

	$.ajax({
		url : get_baseUrl() + 'album/image/api/set_cover.json',
		dataType : 'text',
		data : post_data,
		type : 'POST',
		success: function(status_after){
			$.jGrowl('カバー写真を設定しました。');
		},
		error: function(){
			$.jGrowl('カバー写真の設定に失敗しました。');
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

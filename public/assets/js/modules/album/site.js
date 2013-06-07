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

function load_masonry_item(container_attribute, item_attribute)
{
	var finished_msg = (arguments.length > 3) ? arguments[3] : '';
	var loading_image_url = (arguments.length > 4) ? arguments[4] : get_url('assets/img/site/loading_l.gif');

	var $container = $(container_attribute);
	$container.imagesLoaded(function(){
		$container.masonry({
			itemSelector : item_attribute
		});
	});
	$container.infinitescroll({
		navSelector  : '#page-nav',   // ページのナビゲーションを選択
		nextSelector : '#page-nav a', // 次ページへのリンク
		itemSelector : '.main_item',    // 持ってくる要素のclass
		loading: {
				finishedMsg: finished_msg, //次のページがない場合に表示するテキスト
				img: loading_image_url //ローディング画像のパス
			}
		},
		// trigger Masonry as a callback
		function( newElements ) {
			var $newElems = $( newElements ).css({ opacity: 0 });
			$newElems.imagesLoaded(function(){
				$newElems.animate({ opacity: 1 });
				$container.masonry( 'appended', $newElems, true );
			});
		}
	);
}

$('body').tooltip({
	selector: 'a[data-toggle=tooltip]'
});
$(function(){
	$(document).on('click','.update_public_flag', function(){
		if (GL.execute_flg) return false;
		update_public_flag(this);
		return false;
	});
});

function set_cover(selfDomElement) {
	var is_disabled_after_execute = (arguments.length > 1) ? arguments[1] : false;

	var album_image_id = get_id_num($(selfDomElement).attr('id'));
	var parentElement = $(selfDomElement).parent('li');
	var text = $(selfDomElement).html();

	var post_data = {'id':album_image_id};
	post_data = set_token(post_data);
	$.ajax({
		url : get_baseUrl() + 'album/image/api/set_cover.json',
		type : 'POST',
		dataType : 'text',
		data : post_data,
		beforeSend: function(xhr, settings) {
			GL.execute_flg = true;
			$(selfDomElement).remove();
			$(parentElement).html('<span>' + get_loading_image_tag( + '</span>'));
		},
		complete: function(xhr, textStatus) {
			GL.execute_flg = false;
		},
		success: function(result){
			if (is_disabled_after_execute) {
				$(parentElement).html('<span class="disabled">' + text + '済</span>');
			} else {
				$(parentElement).html(selfDomElement);
			}
			$.jGrowl('カバー写真を設定しました。');
		},
		error: function(result){
			$(parentElement).html(selfDomElement);
			var resData = $.parseJSON(result.responseText);
			var message = resData.message ? resData.message : 'カバー写真の設定に失敗しました。';
			$.jGrowl(get_error_message(resData.status, resData.message));
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
			itemSelector : item_attribute,
			isFitWidth: true,
			isAnimated: true,
			animationOptions: {
					duration: 400
			}
		});
	});
	$container.infinitescroll({
		navSelector  : '#page-nav',   // ページのナビゲーションを選択
		nextSelector : '#page-nav a', // 次ページへのリンク
		itemSelector : item_attribute,    // 持ってくる要素のclass
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

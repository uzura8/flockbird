<script type="text/javascript">
$('.carousel').carousel({
	interval: false
});

$(function(){
	var baseUrl = '<?php echo Uri::base(false); ?>';

	var basePath = '/upload/img/album/original/'; // 画像のベースパスを指定
	var images = []; // 画像ファイル名格納用配列
	var image_ids = []; // 画像id格納用配列
	var slideNumber = 0;

	$.get('/album/api/detail.json', {id:<?php echo $id; ?>}, function(json){
			$.each(json, function(i, data){
					images.push(basePath + data.image);
					image_ids.push(data.id);
			});
			
			if ( images.length < 2 ) {
					return;
			}

			// 最初の画像の設定
			var position = {
					prev : images.length - 1,
					now  : 0,
					next : 1
			};

			var html = "";
			$.each(position, function(i, v){
					if ( i == 'now' ) {
							html += "<img class='item active' src='"+ images[v]+"' id='image_" + image_ids[v] + "'>";
					}
					else {
							html += "<img class='item' src='"+ images[v]+"' id='image_" + image_ids[v] + "'>";
					}
			});

			if ( html == "" ) {
					return;
			}
			
			// 画像のDOMの追加
			$('#myCarousel > .carousel-inner').html(html);
			$('#slideNumber').html('現在のスライド番号:' + slideNumber + ' / 画像ID: ' + image_ids[slideNumber]);
			$('#link2detail').html('<a href="' + baseUrl + 'album/image/detail/' + image_ids[slideNumber] + '">詳細</a>');

			show_list(baseUrl, image_ids[slideNumber], true);
	},'json');
	
	var next = function(){
			// 次のスライドへ移動
			slideNumber++;
			if (slideNumber > images.length - 1) {
					slideNumber = 0;
			}

			nextSlideNumber = slideNumber + 1;
			if (nextSlideNumber > images.length - 1) {
					nextSlideNumber = 0;
			}

			$('#myCarousel > .carousel-inner > img:first').empty();
			$('#myCarousel > .carousel-inner').append('<img class="item" src="'+ images[nextSlideNumber]+'" id="image_'+ image_ids[nextSlideNumber] +'">');
			$('#myCarousel').carousel('next');
			show_list(baseUrl, image_ids[slideNumber], true);
	}

	var prev = function(){
			// 前のスライドに移動
			slideNumber--;
			if (slideNumber < 0) {
					slideNumber = images.length - 1;
			}
			prevSlideNumber = slideNumber - 1;
			if (prevSlideNumber < 0) {
					prevSlideNumber = images.length - 1;
			}
			$('#myCarousel > .carousel-inner > img:last').empty();
			$('#myCarousel > .carousel-inner').prepend('<img class="item" src="'+ images[prevSlideNumber]+'" id="image_'+ image_ids[prevSlideNumber] +'>');
			$('#myCarousel').carousel('prev');
			show_list(baseUrl, image_ids[slideNumber], true);
	}

	var slide = function(type) {
			// スライドの実行
			if ( type == 'next' || type == 39 ) {
					next();
			}
			else if ( type == 'prev' || type == 37 ) {
					prev();
			}
			$('#slideNumber').html('現在のスライド番号:' + slideNumber + ' / 画像ID: ' + image_ids[slideNumber]);
			$('#link2detail').html('<a href="' + baseUrl + 'album/image/detail/' + image_ids[slideNumber] + '">詳細</a>');
	}

	$('.carousel-control').click(function(event) {
			// 横ボタン動作
			if ( images.length < 2 ) {
					return;
			}
			slide($(this).attr('data-action'));
	});

	$("body").keydown(function(event){
			// キーボード操作によるスライドの移動
			slide(event.keyCode);
	});

	$('#btn_album_image_comment_create').live("click", function(){
		//$("#input_album_image_comment").val('hoge');
		var body = $("#input_album_image_comment").val().trim();
console.log(body);
		if (body.length > 0) {
			$.post(
				baseUrl + 'album/image/comment/create_ajax/' + image_ids[slideNumber],
				{
					'body': body,
					'<?php echo Config::get('security.csrf_token_key'); ?>': '<?php echo Security::fetch_token(); ?>',
				},
				function(data){
					$.jGrowl('コメントを投稿しました。');
					show_list(baseUrl, image_ids[slideNumber]);
					$("#input_album_image_comment").val('');
					$("textarea#input_album_image_comment").css("height", "33px");;
				}
			);
		}
	});

	$('.btn_album_image_comment_delete').live("click", function(){
		var id_value = $(this).attr("id");
		var id = id_value.replace(/btn_album_image_comment_delete_/g, "");
		jConfirm('削除しますか?', '削除確認', function(r) {
			if (r == true) {
				$.ajax({
					url : baseUrl + 'album/image/comment/delete_ajax/' + id,
					dataType : "text",
					data : {'id': id, '<?php echo Config::get('security.csrf_token_key'); ?>': '<?php echo Security::fetch_token(); ?>'},
					type : 'POST',
					success: function(status_after){
						$('#commentBox_' + id).fadeOut();
						$.jGrowl('No.' + id + 'のコメントを削除しました。');
					},
					error: function(){
						$.jGrowl('No.' + id + 'のコメントを削除できませんでした。');
					}
				});
			}
		});
	});

});


function show_list(base_url, album_id, is_fadein) {
	var url = base_url + 'album/image/comment/list/' + album_id;

	//$.ajaxSetup({cache : false});
	$("#loading_list").html('<img src="' + base_url + 'assets/img/loading.gif">');

	$.get(url, {'nochache':(new Date()).getTime()}, function(data) {
		if (data.length > 0) {
			if (is_fadein) {
				$("#comment_list").fadeOut('fast');
			}
			$("#comment_list").html(data).fadeIn('slow');
		}
	});
	$("#loading_list").remove();
}
</script>

get_tmp_images($('#tmp_hash').val());

var $modal = $('#upload_images');
var $btn_elment = $('#upload_images_btn');
var btn_elment_html = $btn_elment.html();

$('#upload_images_btn').on('click', function(){
	var tmp_hash = $('#tmp_hash').val();

	// create the backdrop and wait for next modal to be triggered
	//$('body').modalmanager('loading');

	$(this).attr('disabled', true);
	$(this).html(get_loading_image_tag());

	setTimeout(function(){
		$modal.load(get_baseUrl() + 'album/image/upload/note?tmp_hash=' + tmp_hash, '', function(){
			$modal.modal();
			$btn_elment.attr('disabled', false);
			$btn_elment.html(btn_elment_html);
		});
	}, 1000);
});

$modal.on('hidden', function () {
	var tmp_hash = $('#tmp_hash').val();
	get_tmp_images(tmp_hash);

	// 念のためボタン有効化処理を追加
	$btn_elment.attr('disabled', false);
	$btn_elment.html(btn_elment_html);
});

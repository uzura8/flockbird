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

function delte_tmp_image(id) {
	delete_item_execute_ajax('site/api/delte_tmp_image', id, '#note_image', false);
	return false;
}

function get_tmp_images(tmp_hash)
{
	var targetDomElement = $('#uploaded_images');
	var get_data = {};
	get_data['nochache'] = (new Date()).getTime();
	get_data['tmp_hash'] = tmp_hash;

	$.ajax({
		url : get_baseUrl() + 'site/api/tmp_images/note.html',
		type : 'GET',
		dataType : 'text',
		data : get_data,
		timeout: 10000,
		beforeSend: function(xhr, settings) {
			GL.execute_flg = true;
			if (targetDomElement) {
				$(targetDomElement).html(get_loading_image_tag(true));
			}
		},
		complete: function(xhr, textStatus) {
			GL.execute_flg = false;
		},
		success: function(result){
			$('.loading_image').remove();
			$(targetDomElement).html(result);
		},
		error: function(result) {
			$('.loading_image').remove();
			$.jGrowl(get_error_message(result['status'], '読み込みに失敗しました。'));
		}
	});
}

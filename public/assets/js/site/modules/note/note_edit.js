var id = get_id_from_url(true);

var album_image_name_uploaded_posted = {};
$('.album_image_name_uploaded_posted').each(function() {
	var album_image_id = get_id_num($(this).attr('id'));
	var key = 'album_image_' + album_image_id;
	album_image_name_uploaded_posted[key] = $(this).val();
});


get_uploaded_images(id, album_image_name_uploaded_posted);
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

isPopover = false;
setup_simple_validation_required_popover('#form_title');
setup_simple_validation_required_popover('#form_body');
$('html').click(function(e) {
	if (isPopover) {
		$('#form_title').popover('hide');
		$('#form_body').popover('hide');
		isPopover = false;
	}
});

$('#form_button').click(function() {
	if (!simple_validation_required('#form_title')) return false;
	if (!simple_validation_required('#form_body')) return false;

	var original_public_flag = $('#form_original_public_flag').val();
	var changed_public_flag  = $('input[name="public_flag"]:checked').val();
	if (is_expanded_public_range(original_public_flag, changed_public_flag)) {
		apprise('公開範囲が広がります。送信しますか？', {'confirm':true}, function(r) {
			if (r == true) $('#form_note_edit').submit();
		});
	} else {
		$('#form_note_edit').submit();
	}
});

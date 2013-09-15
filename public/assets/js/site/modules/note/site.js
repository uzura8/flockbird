$(function(){
	$(document).on('click','.update_public_flag', function(){
		if (GL.execute_flg) return false;
		update_public_flag(this);
		return false;
	});
});

function delte_image(id) {
	var is_tmp = (arguments.length > 1) ? arguments[1] : false;
	var post_uri = is_tmp ? 'site/api/delte_tmp_image' : 'album/image/api/delete';
	var target_attribute_prefix = is_tmp ? '#note_image_tmp' : '#note_image_uploaded';

	if (is_tmp) {
		delete_item_execute_ajax(post_uri, id, target_attribute_prefix, false);
	} else {
		apprise('削除した写真は元に戻せません。削除しますか？', {'confirm':true}, function(r) {
			if (r == true) delete_item_execute_ajax(post_uri, id, target_attribute_prefix, false);
		});
	}

	return false;
}

function get_uploaded_images(content_id)
{
	var get_data_added = (arguments.length > 1) ? arguments[1] : {};
console.log(get_data_added);
	get_upload_images('#uploaded_images', 'album/image/api/uploaded_images/note.html', content_id, '', get_data_added);
}

function get_tmp_images(tmp_hash)
{
	get_upload_images('#tmp_images', 'site/api/tmp_images/note.html', 0, tmp_hash);
}

function get_upload_images(target_atter, get_uri)
{
	var content_id = (arguments.length > 2) ? arguments[2] : 0;
	var tmp_hash   = (arguments.length > 3) ? arguments[3] : '';
	var get_data_added = (arguments.length > 4) ? arguments[4] : {};

	var targetDomElement = $(target_atter);

	var get_data = {};
	get_data['nochache'] = (new Date()).getTime();
	if (content_id > 0) get_data['content_id'] = content_id;
	if (tmp_hash.length > 0) get_data['tmp_hash'] = tmp_hash;
	if (get_data_added) {
		get_data = $.extend(get_data, get_data_added);
	}

	$.ajax({
		url : get_baseUrl() + get_uri,
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

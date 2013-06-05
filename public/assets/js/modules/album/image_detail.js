$(function(){
	var parent_id = get_id_from_url();
	//show_list('album/image/comment/api/list/' + parent_id + '.html', '#comment_list', 5);

	var execute_flg = false;
	$('#btn_album_image_comment_create').click(function(){
		if (execute_flg) return false;
		var textarea_attribute = '#input_album_image_comment';
		var textarea_height  = '33px';
		var body = $(textarea_attribute).val().trim();
		if (body.length <= 0) return false;

		var data = {'id':parent_id, 'body':body};
		data = set_token(data);
		$.ajax({
			url : get_baseUrl() + 'album/image/comment/api/create.json',
			type : 'POST',
			dataType : 'text',
			data : data,
			timeout: 10000,
			beforeSend: function(xhr, settings) {
				execute_flg = true;
				$(this).attr('disabled', true);
			},
			complete: function(xhr, textStatus) {
				execute_flg = false;
				$(this).attr('disabled', false);
			},
			success: function(result, textStatus, xhr) {
				$.jGrowl('コメントを投稿しました。');
				show_list('album/image/comment/api/list/' + parent_id + '.html', '#comment_list', 0, $('.commentBox:last').attr('id'));
				$(textarea_attribute).val('');
				$('textarea' + textarea_attribute).css('height', textarea_height);
			},
			error: function(data){
				$.jGrowl(get_error_message(data['status'], 'コメントを投稿できませんでした。'));
			}
		});
	});

	$('#listMoreBox_comment').click(function(){
		show_list('album/image/comment/api/list/' + parent_id + '.html', '#comment_list', 0, $('.commentBox:first').attr('id'), true, '#' + $(this).attr('id'));
		return false;
	});

	$(document).on('click','.btn_album_image_comment_delete', function(){
		delete_item('album/image/comment/api/delete.json', get_id_num($(this).attr('id')), '#commentBox');
		return false;
	});

	if (!is_sp()) {
		$(document).on({
			mouseenter:function() {$('#btn_album_image_comment_delete_' + get_id_num($(this).attr('id'))).fadeIn('fast')},
			mouseleave:function() {$('#btn_album_image_comment_delete_' + get_id_num($(this).attr('id'))).hide()}
		},'#comment_list .commentBox');
	}
});

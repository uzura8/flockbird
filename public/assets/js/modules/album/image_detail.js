$(function(){
	var parent_id = get_id_from_url();
	//show_list('album/image/comment/api/list/' + parent_id + '.html', '#comment_list', 5);

	$('#btn_album_image_comment_create').click(function(){
		create_comment(
			'#input_album_image_comment',
			parent_id,
			'album/image/comment/api/create.json',
			'album/image/comment/api/list/' + parent_id + '.html',
			'#comment_list',
			$('.commentBox').last().attr('id'),
			this
		);

		return false;
	});

	$('#listMoreBox_comment').click(function(){
		show_list(
			'album/image/comment/api/list/' + parent_id + '.html',
			'#comment_list',
			0,
			$('.commentBox').first().attr('id'),
			true,
			this
		);

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

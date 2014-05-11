$(function(){
	var parent_id = get_id_from_url();
	//show_list('album/image/comment/api/list/' + parent_id + '.html', '#comment_list', 5);

	$(document).on('click','.link_album_image_set_cover', function(){
		if (GL.execute_flg) return false;
		set_cover(this, true);
		return false;
	});

	$('#btn_comment').click(function(){
		if (GL.execute_flg) return false;
		create_comment(
			parent_id,
			'album/image/comment/api/create.json',
			'album/image/comment/api/list/' + parent_id + '.html',
			$('.commentBox').last().attr('id'),
			this
		);

		return false;
	});

	$(document).on('click','#listMoreBox_comment', function(){
		if (GL.execute_flg) return false;
		show_list(
			'album/image/comment/api/list/' + parent_id + '.html',
			'#comment_list',
			get_config('default_detail_comment_limit_max'),
			$('.commentBox').first().attr('id'),
			true,
			this
		);

		return false;
	});

	if (!is_sp()) {
		$(document).on({
			mouseenter:function() {$('#btn_comment_delete_' + get_id_num($(this).attr('id'))).fadeIn('fast')},
			mouseleave:function() {$('#btn_comment_delete_' + get_id_num($(this).attr('id'))).hide()}
		},'#comment_list .commentBox');
	}

	if (url('?write_comment')) $('#textarea_comment').focus();
});

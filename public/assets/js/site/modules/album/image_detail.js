$(function(){
	var parent_id = get_id_from_url();

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

	if (url('?write_comment')) $('#textarea_comment').focus();
});

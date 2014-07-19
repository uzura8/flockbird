$(function(){
	$(document).on('click','.link_album_image_set_cover', function(){
		if (GL.execute_flg) return false;
		set_cover(this, true);
		return false;
	});

	if (url('?write_comment')) $('#textarea_comment').focus();
});

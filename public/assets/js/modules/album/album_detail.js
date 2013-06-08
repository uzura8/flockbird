$(function(){
	load_masonry_item('#main_container', '.main_item');
});

$(document).on('click','.link_album_image_set_cover', function(){
	if (GL.execute_flg) return false;
	set_cover(this);
	return false;
});

$('.btn_album_image_comment_delete').click(function(){
	delete_item('album/image/comment/api/delete.json', get_id_num($(this).attr('id')), '#commentBox');
	return false;
});

if (!is_sp()) {
	$('.commentBox').on({
		mouseenter:function() {$('#btn_album_image_comment_delete_' + get_id_num($(this).attr('id'))).fadeIn('fast')},
		mouseleave:function() {$('#btn_album_image_comment_delete_' + get_id_num($(this).attr('id'))).hide()}
	});
	$('.imgBox').on({
		mouseenter:function() {$('#btn_album_image_edit_' + get_id_num($(this).attr('id'))).fadeIn('fast')},
		mouseleave:function() {$('#btn_album_image_edit_' + get_id_num($(this).attr('id'))).hide()}
	});
}

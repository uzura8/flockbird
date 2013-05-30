$('.carousel').carousel({
	interval: false
})

$(function(){
	load_masonry_item(
		'#main_container',
		'.main_item',
		get_term('album_image') + 'がありません。'
	);
});

$('.link_album_image_delete').on('click', function(){
	delete_item('album/image/api/delete.json', get_id_num($(this).attr('id')), '#main_item');
	return false;
});
$('.link_album_image_set_cover').on('click', function(){
	set_cover(get_id_num($(this).attr('id')));
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

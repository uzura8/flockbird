$(function(){
	var parent_id = get_id_from_url();

	$('#btn_album_image_comment_create').click(function(){
		create_comment(
			'#input_album_image_comment',
			parent_id,
			'album/image/comment/api/create.json',
			'album/image/comment/api/list/' + parent_id + '.html',
			'#comment_list',
			$('.commentBox:last').attr("id")
		)
		return false;
	});

	$('#listMoreBox_comment').click(function(){
		show_list('album/image/comment/api/list/' + parent_id + '.html', '#comment_list', 0, $('.commentBox:first').attr("id"), true, '#' + $(this).attr("id"));
		return false;
	});

	$('.btn_album_image_comment_delete').live("click", function(){
		delete_item('album/image/comment/api/delete.json', get_id_num(($(this).attr("id"))), '#commentBox');
		return false;
	});

	if (is_sp() == false) {
		$('.commentBox').live({
			mouseenter:function() {
				var id = $(this).attr('id').replace($(this).attr('class') + '_', '');
				var btn = '#btn_album_image_comment_delete_' + id;
				$(btn).fadeIn('fast');
			},
			mouseleave:function() {
				var id = $(this).attr('id').replace($(this).attr('class') + '_', '');
				var btn = '#btn_album_image_comment_delete_' + id;
				$(btn).hide();
			}
		});
	}
});

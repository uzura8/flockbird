<script type="text/javascript">
$(function(){
	var parent_id = get_id_from_url();
	show_list('album/image/comment/list/' + parent_id, '#comment_list', false);

	$('#btn_album_image_comment_create').click(function(){
		create_comment('#input_album_image_comment', parent_id, 'album/image/comment/api/create.json', 'album/image/comment/list/' + parent_id, '#comment_list')
	});

	$('.btn_album_image_comment_delete').live("click", function(){
		delete_comment('album/image/comment/api/delete.json', get_id_num(($(this).attr("id"))), '#commentBox');
	});
});
</script>

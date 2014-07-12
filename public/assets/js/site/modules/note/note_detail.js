$(function(){
	var parent_id = get_id_from_url();

	$(document).on('click','#link_publish', function(){
		post_submit(this);
		return false;
	});

	$('#btn_comment').click(function(){
		if (GL.execute_flg) return false;
		create_comment(
			parent_id,
			'note/comment/api/create.json',
			'note/comment/api/list/' + parent_id + '.html',
			$('.commentBox').last().attr('id'),
			this
		);

		return false;
	});

	if (url('?write_comment')) $('#textarea_comment').focus();
});

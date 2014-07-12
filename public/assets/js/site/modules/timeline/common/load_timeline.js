$(function() {
	$(document).on('click','.update_public_flag', function(){
		$(this).parent('li').parent('ul.dropdown-menu').parent('div.btn-group').removeClass('open');
		if (GL.execute_flg) return false;
		update_public_flag(this);
		return false;
	});

	$(document).on('click','.link_comment', function(){
		if ($(this).hasClass('hide-after_click')) $(this).hide();
		$('#commentPostBox_' + $(this).data('id')).show();
		$('#textarea_comment_' + $(this).data('id')).focus();
		return false;
	});

	$(document).on('click','.btn_comment', function(){
		if (GL.execute_flg) return false;
		var parent_id = $(this).data('parent_id');
		var post_parent_id = $(this).data('post_parent_id') ? $(this).data('post_parent_id') : parent_id;
		var post_uri = $(this).data('post_uri') ? $(this).data('post_uri') : 'timeline/comment/api/create.json';
		var get_uri = $(this).data('get_uri') ? $(this).data('get_uri') : 'timeline/comment/api/list/' + post_parent_id + '.html';

		create_comment(
			post_parent_id,
			post_uri,
			get_uri,
			$('.commentBox_' + parent_id).last().attr('id'),
			this,
			1,
			'#textarea_comment_' + parent_id,
			'#comment_list_' + parent_id,
			{},
			{class_id: parent_id}
		);

		return false;
	});

	$('body').tooltip({
		selector: 'a[data-toggle=tooltip]'
	});
})

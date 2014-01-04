$(function() {
	$(document).on('click','.load_more_timeline', function(){
		load_more_timeline(this);
	});

	$(document).on('click','.update_public_flag', function(){
		if (GL.execute_flg) return false;
		update_public_flag(this);
		return false;
	});

	if (!is_sp()) {
		$(document).on({
			mouseenter:function() {$('#btn_timeline_delete_' + get_id_num($(this).attr('id'))).fadeIn('fast')},
			mouseleave:function() {$('#btn_timeline_delete_' + get_id_num($(this).attr('id'))).hide()}
		},'.timelineBox');
	}

	$(document).on('click','.btn_timeline_delete', function(){
		var post_uri = $(this).data('uri') ? $(this).data('uri') : 'timeline/api/delete.json';
		var post_id  = $(this).data('post_id') ? $(this).data('post_id') : '';
		var list_id  = get_id_num($(this).attr('id'));

		if (!post_id) return false;
		delete_item(post_uri, post_id, '', '#timelineBox_' + list_id);

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
			'#comment_list_' + parent_id
		);

		return false;
	});

	$(document).on('click','.listMoreBox', function(){
		if (GL.execute_flg) return false;
		var parent_id = $(this).data('parent_id');
		var get_uri   = $(this).data('get_uri') ? $(this).data('get_uri') : '';
		if (get_uri.length == 0) get_uri = 'timeline/comment/api/list/' + parent_id + '.html';

		show_list(
			get_uri,
			'#comment_list_' + parent_id,
			get_config('timeline_list_comment_limit_max'),
			$('.commentBox_' + parent_id).first().attr('id'),
			true,
			this
		);

		return false;
	});

	if (!is_sp()) {
		$(document).on({
			mouseenter:function() {$('#btn_comment_delete_' + get_id_num($(this).attr('id'))).fadeIn('fast')},
			mouseleave:function() {$('#btn_comment_delete_' + get_id_num($(this).attr('id'))).hide()}
		},'.commentBox');
	}

	$(document).on('click','.btn_comment_delete', function(){
		delete_item('timeline/comment/api/delete.json', get_id_num($(this).attr('id')), '#commentBox');
		return false;
	});

	$('body').tooltip({
		selector: 'a[data-toggle=tooltip]'
	});
})

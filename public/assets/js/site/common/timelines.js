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
		var delete_uri = $(this).data('uri') ? $(this).data('uri') : '';
		if (delete_uri) {
			delete_item(delete_uri);
		} else {
			delete_item('timeline/api/delete.json', $(this).data('id'), '#timelineBox');
		}
		return false;
	});

	$(document).on('click','.link_comment', function(){
		$('#commentPostBox_' + $(this).data('id')).show();
		$('#textarea_comment_' + $(this).data('id')).focus();
		return false;
	});

	$(document).on('click','.btn_comment', function(){
		if (GL.execute_flg) return false;
		var parent_id = $(this).data('parent_id');
		create_comment(
			parent_id,
			'timeline/comment/api/create.json',
			'timeline/comment/api/list/' + parent_id + '.html',
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
		show_list(
			'timeline/comment/api/list/' + parent_id + '.html',
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
})

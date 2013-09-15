$(function() {
 $('textarea.autogrow').css('height', '50px');
	var uid = get_uid();
	$('#btn_comment').click(function(){
		if (GL.execute_flg) return false;
		create_comment(
			0,
			'timeline/api/create.json',
			'//timeline/api/list.html?is_over=1&last_id=' + $($('.timelineBox').first()).data('id'),
			$('.timelineBox').first().attr('id'),
			this,
			1,
			'#textarea_comment',
			'#article_list',
			'50px',
			true,
			get_term('timeline')
		);

		return false;
	});

	$(document).on('click','.load_more_timeline', function(){
		var last_id   = $(this).data('last_id') ?   parseInt($(this).data('last_id')) : 0;
		var member_id = $(this).data('member_id') ? parseInt($(this).data('member'))  : 0;

		var get_uri = 'timeline/api/list.html?limit=3&last_id=' + last_id;
		if (member_id > 0) get_uri += '&member_id=' + member_id;
		
		show_list(get_uri, '#article_list', 3, $('.timelineBox').last().attr('id'), false, this);
		return false;
	});

	$(document).on('click','.btn_timeline_delete', function(){
		delete_item('timeline/api/delete.json', $(this).data('id'), '#timelineBox');
		return false;
	});

	if (!is_sp()) {
		$(document).on({
			mouseenter:function() {$('#btn_timeline_delete_' + get_id_num($(this).attr('id'))).fadeIn('fast')},
			mouseleave:function() {$('#btn_timeline_delete_' + get_id_num($(this).attr('id'))).hide()}
		},'#article_list .timelineBox');
	}

	$('#listMoreBox_comment').click(function(){
		if (GL.execute_flg) return false;
		show_list(
			'note/comment/api/list/' + parent_id + '.html',
			'#comment_list',
			0,
			$('.commentBox').first().attr('id'),
			true,
			this
		);

		return false;
	});

	$(document).on('click','.btn_comment_delete', function(){
		delete_item('note/comment/api/delete.json', get_id_num($(this).attr('id')), '#commentBox');
		return false;
	});
})

$(function() {
	$('textarea.input_timeline').css('height', '50px');
	$('#form_public_flag').val(get_config('site_public_flag_default'));

	var uid = get_uid();
	$('#btn_timeline').click(function(){
		if (GL.execute_flg) return false;
		create_comment(
			0,
			'timeline/api/create.json',
			'timeline/api/list.html?is_over=1&last_id=' + $($('.timelineBox').first()).data('id'),
			$('.timelineBox').first().attr('id'),
			this,
			$('#form_public_flag').val(),
			'#textarea_comment',
			'#article_list',
			'50px',
			true,
			get_term('timeline')
		);

		return false;
	});

	$(document).on('click','.select_public_flag', function(){
		var selected_public_flag = $(this).data('public_flag');
		$('#form_public_flag').val(selected_public_flag);

		var buttonDomElement = $('#public_flag_selector').parent('.btn-group');
		var buttonHtml = get_public_flag_select_button_html(selected_public_flag, false, true);
		$(buttonDomElement).html(buttonHtml);

		return false;
	});

	$(document).on('click','.load_more_timeline', function(){
		var last_id   = $(this).data('last_id') ?   parseInt($(this).data('last_id')) : 0;
		var member_id = $(this).data('member_id') ? parseInt($(this).data('member'))  : 0;

		var limit = get_config('timeline_articles_limit');
		var get_uri = 'timeline/api/list.html?limit=' + limit + '&last_id=' + last_id;
		if (member_id > 0) get_uri += '&member_id=' + member_id;
		
		show_list(get_uri, '#article_list', limit, $('.timelineBox').last().attr('id'), false, this);
		return false;
	});

	$(document).on('click','.link_comment', function(){
		$('#commentPostBox_' + $(this).data('id')).show();
		$('#textarea_comment_' + $(this).data('id')).focus();
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
			0,
			$('.commentBox_' + parent_id).first().attr('id'),
			true,
			this
		);

		return false;
	});

	$(document).on('click','.update_public_flag', function(){
		if (GL.execute_flg) return false;
		update_public_flag(this);
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

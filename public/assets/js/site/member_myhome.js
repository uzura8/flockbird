$(function() {
	$('textarea.input_timeline').css('height', '50px');
	$('#form_public_flag').val($('#public_flag_selector').data('public_flag'));

	$(document).on('click','.select_public_flag', function(){
		if (GL.execute_flg) return false;
		update_public_flag(this);
		$('#form_public_flag').val($(this).data('public_flag'));
		return false;
	});

	var uid = get_uid();
	$('#btn_timeline').click(function(){
		if (GL.execute_flg) return false;
		create_comment(
			0,
			'timeline/api/create.json',
			'timeline/api/list.html?mytimeline=1&is_over=1',
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
})

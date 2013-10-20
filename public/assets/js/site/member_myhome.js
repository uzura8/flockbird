$(function() {
	$('textarea.input_timeline').css('height', '50px');
	$('#form_public_flag').val(get_config('site_public_flag_default'));

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

	$(document).on('click','.select_public_flag', function(){
		var selected_public_flag = $(this).data('public_flag');
		$('#form_public_flag').val(selected_public_flag);

		var buttonDomElement = $('#public_flag_selector').parent('.btn-group');
		var buttonHtml = get_public_flag_select_button_html(selected_public_flag, false, true);
		$(buttonDomElement).html(buttonHtml);

		return false;
	});
})

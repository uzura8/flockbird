$(function() {
	$('textarea.input_timeline').css('height', '50px');
	$('#form_public_flag').val($('#public_flag_selector').data('public_flag'));

	$(document).on('click','.display_upload_form', function(){
		$('.upload').removeClass('hidden');
		$(this).addClass('hidden');
		return false;
	});

	$(document).on('click','.select_public_flag', function(){
		if (GL.execute_flg) return false;
		update_public_flag(this);
		$('#form_public_flag').val($(this).data('public_flag'));
		return false;
	});

	var uid = get_uid();
	$('#btn_timeline').click(function(){
		if (GL.execute_flg) return false;

		var body = $('#textarea_comment').val().trim();
		var post_data_additional = {};
		$('input[name^="file_tmp"]').each(function(){
				post_data_additional[this.name] = this.value;
		});
		if (body.length == 0 && post_data_additional.length == 0) return;

		post_data_additional[album_id] = $('#album_id').val();
		create_comment(
			0,
			'timeline/api/create.json',
			'timeline/api/list.html?mytimeline=1&is_over=1',
			$('.timelineBox').first().attr('id'),
			this,
			$('#form_public_flag').val(),
			'#textarea_comment',
			'#article_list',
			post_data_additional,
			{},
			false,
			'50px',
			true,
			get_term('timeline')
		);

		$('.upload').addClass('hidden');
		$('.display_upload_form').removeClass('hidden');
		$('#files').html('');
		$('#album_id').val('0')
		$('#progress .progress-bar').css('width', 0);
	});
})

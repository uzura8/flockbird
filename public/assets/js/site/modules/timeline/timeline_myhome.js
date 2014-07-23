$(function() {
	$('textarea.input_timeline').css('height', '50px');
	$('#form_public_flag').val($('#public_flag_selector').data('public_flag'));

	load_default_timeline();

	$(document).on('click','.display_upload_form', function(){
		$('.upload').removeClass('hidden');
		$(this).addClass('hidden');

		var url = get_url('album/api/albums.json');
		var get_data = {};
		get_data['nochache']  = (new Date()).getTime();
		$.ajax({
			url : url,
			type : 'GET',
			dataType : 'json',
			data : get_data,
			timeout: 10000,
			beforeSend: function(xhr, settings) {
				//GL.execute_flg = true;
				$('#album_id').attr('disabled', 'disabled');
			},
			complete: function(xhr, textStatus) {
				//GL.execute_flg = false;
				$('#album_id').removeAttr('disabled');
			},
			success: function(result) {
				$.each(result, function(i, val) {
					$('#album_id').append('<option value="' + val.id + '">' + val.name + '</option>');
				});
			},
			error: function(result) {
			}
		});

		return false;
	});

	$(document).on('click','.select_public_flag', function(){
		if (GL.execute_flg) return false;
		update_public_flag(this);
		$('#form_public_flag').val($(this).data('public_flag'));
		return false;
	});

	$('#btn_timeline').click(function(){
		if (GL.execute_flg) return false;

		var body = $('#textarea_comment').val().trim();
		var postData = {};
		$('input[name^="image_tmp"]').each(function(){
				postData[this.name] = this.value;
		});
		if (body.length == 0 && Object.keys(postData).length == 0) return;

		var listSelector = '#article_list';
		var isInsertBefore = true;
		var nextSelector = getNextSelector(listSelector, isInsertBefore);
		postData['album_id'] = $('#album_id').val();
		postData['public_flag'] = $('#form_public_flag').val();
		var getData = {
			'mytimeline' : 1,
			'is_before' : 1,
			'desc' : 1
		};

		postComment(
			'timeline/api/create.json',
			'#textarea_comment',
			'timeline/api/list.html',
			listSelector,
			nextSelector,
			isInsertBefore,
			this,
			'',
			postData,
			false,
			get_term('timeline'),
			getData,
			'50px'
		);

		$('.upload').addClass('hidden');
		$('.display_upload_form').removeClass('hidden');
		$('#files').html('');
		$('#album_id').val('0')
		$('#progress .progress-bar').css('width', 0);
		$('#album_id').html('<option selected="selected" value="0">' + get_term('timeline') + '用' + get_term('album') + '</option>');
	});

	$(document).on('click','.timeline_viewType', function(){
		var member_id = $(this).data('member_id') ? parseInt($(this).data('member_id')) : 0;
		var value = $(this).data('value') ? parseInt($(this).data('value')) : 0;

		var text = $(this).html();
		var parentElement = $(this).parent('li');
		var buttonElement = $(parentElement).parents('div.btn-group');

		var post_data = {
			'id'    : member_id,
			'value' : value,
		};
		uri = 'member/api/update_config/timeline_viewType.html';
		post_data = set_token(post_data);
		$.ajax({
			url : get_url(uri),
			type : 'POST',
			dataType : 'text',
			data : post_data,
			beforeSend: function(xhr, settings) {
				GL.execute_flg = true;
				$(this).remove();
				$(parentElement).html('<span>' + get_loading_image_tag( + '</span>'));
			},
			complete: function(xhr, textStatus) {
				GL.execute_flg = false;
			},
			success: function(result, status, xhr){
				$(buttonElement).html(result);
				$(buttonElement).removeClass('open');
				$.jGrowl('表示設定を変更しました。');
			},
			error: function(result){
				$(parentElement).html(this);
				$.jGrowl('表示設定の変更に失敗しました。');
			}
		});

		$('#article_list').empty();
		load_default_timeline(true);

		return false;
	});
})

function load_default_timeline() {
	var getData = {'mytimeline' : 1, 'desc' : 1};
	loadList('timeline/api/list.html', '#article_list', get_config('timeline_list_limit'), '', false, '', getData);
}

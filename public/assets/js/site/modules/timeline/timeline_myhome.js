$(function() {
	$('textarea.input_timeline').css('height', '50px');
	$('#form_public_flag').val($('#public_flag_selector').data('public_flag'));

	loadTimelineDefault();

	$(document).on('click','.display_upload_form', function(){
		$('.upload').removeClass('hidden');
		$(this).addClass('hidden');

		var get_data = {};
		$.ajax({
			url : get_url('album/api/albums.json'),
			type : 'GET',
			dataType : 'json',
			data : get_data,
			timeout: get_config('default_ajax_timeout'),
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

		//var body = $('#textarea_comment').val().trim();
		var body = $.trim($('#textarea_comment').val());// for legacy IE.
		var postData = {};
		$('input[name^="image_tmp"]').each(function(){
			postData[this.name] = this.value;
		});
		if (body.length == 0 && Object.keys(postData).length == 0) return;

		postData['album_id'] = $('#album_id').val();
		postData['public_flag'] = $('#form_public_flag').val();

		var listSelector = '#article_list';
		var position = 'prepend';
		var getData = {
			mytimeline: 1,
			latest: 1,
			desc: 1
		};

		// 「最新をみる」リンクがあれば削除する
		linkElement = $(listSelector + ' > a.listMoreBox:first');
		if (!empty(linkElement.data('type')) && linkElement.data('type') == 'see_latest') {
			linkElement.remove();
		}

		var nextSelector = getNextSelector(listSelector, position);
		if (nextSelector) getData['since_id'] = parseInt($(nextSelector).data('list_id'));

		postComment(
			'timeline/api/create.json',
			'#textarea_comment',
			'timeline/api/list.html',
			listSelector,
			position,
			getData,
			this,
			'',
			'',
			[resetInputs],
			[postLoadTimeline],
			postData,
			false,
			get_term('timeline'),
			'50px'
		);
		return false;
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
		post_data = set_token(post_data);
		$.ajax({
			url : get_url('member/api/update_config/timeline_viewType.html'),
			type : 'POST',
			dataType : 'text',
			data : post_data,
			timeout: get_config('default_ajax_timeout'),
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
		loadTimelineDefault();

		return false;
	});
})

function resetInputs() {
	$('.upload').addClass('hidden');
	$('.display_upload_form').removeClass('hidden');
	$('#files_img').html('');
	$('#album_id').val('0')
	$('#progress_img .progress-bar').css('width', 0);
	$('#album_id').html('<option selected="selected" value="0">' + get_term('timeline') + '用' + get_term('album') + '</option>');
	scroll(is_sp() ? '#main_post_box' : 0, 'swing');
}

function loadTimelineDefault() {
	var getData = {'mytimeline': 1};
	var max_id = url('?max_id');
	if (max_id) {
		getData['max_id'] = max_id;
		getData['before_link'] = 1;
	}
	loadTimeline(getData);
}

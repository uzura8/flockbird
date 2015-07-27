$(function() {
	$('textarea.input_timeline').css('height', '50px');
	$('#form_public_flag').val($('#public_flag_selector').data('public_flag'));

	loadTimelineDefault();

	$(document).on('click','.display_upload_form', function(){
		$('.upload').removeClass('hidden');
		$(this).addClass('hidden');

		var getData = {
			limit: 100,
			cols : ['id', 'name'],
			no_relateds: 1
		};
		var member_id = get_uid();
		if (!member_id) return false;

		$.ajax({
			url : get_url('album/api/member/' + member_id + '.json'),
			type : 'GET',
			dataType : 'json',
			data : getData,
			timeout: get_config('default_ajax_timeout'),
			beforeSend: function(xhr, settings) {
				$('#album_id').attr('disabled', 'disabled');
			},
			complete: function(xhr, textStatus) {
				$('#album_id').removeAttr('disabled');
			},
			success: function(result) {
				$.each(result.list, function(i, val) {
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

		var sinceId = getSinceId(listSelector, position, 'list_id');
		if (sinceId) getData['since_id'] = sinceId;

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
		var viewType = $(this).data('value') ? parseInt($(this).data('value')) : 0;
		var trigerSelectorHtml = $(this).html();
		var parentElement = $(this).parent('li');
		var buttonElement = $(parentElement).parents('div.btn-group');
		var postData = {
			name  : 'timeline_viewType',
			value : viewType
		};

		$.ajax({
			url : get_url('member/setting/api/config.json'),
			type : 'POST',
			dataType : 'json',
			data : set_token(postData),
			timeout: get_config('default_ajax_timeout'),
			beforeSend: function(xhr, settings) {
				GL.execute_flg = true;
				setLoading(null, this, 'btn_loading_image');
			},
			complete: function(xhr, textStatus) {
				GL.execute_flg = false;
				removeLoading(null, this, 'btn_loading_image');
				$(this).html(trigerSelectorHtml);
			},
			success: function(result, status, xhr){
				$(buttonElement).html(result.html);
				$(buttonElement).removeClass('open');
				showMessage(result.message);
				$('#article_list').empty();
				loadTimelineDefault();
			},
			error: function(result){
				GL.execute_flg = false;
				$(parentElement).html(this);
				showErrorMessage(result);
			}
		});

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

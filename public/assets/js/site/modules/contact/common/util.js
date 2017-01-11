$(document).on('click', '.js-post_report', function(){
	var postUri = $(this).data('uri') ? $(this).data('uri') : '';
	var postData = $(this).data('post_data') ? $(this).data('post_data') : {};
	var valCategory = $('#form_category').val();
	var selfObj = $(this);
	var trigerSelectorHtml = $(selfObj).html();

	if (empty(valCategory)) {
		showMessage(__('report_error_required_report_about'));
		return false;
	}
	postData['category'] = valCategory;
	postData['body'] = $('#form_body').val();

	$.ajax({
		url : get_url(postUri),
		type : 'POST',
		dataType : 'json',
		data : set_token(postData),
		timeout: get_config('default_ajax_timeout'),
		beforeSend: function(xhr, settings) {
			GL.execute_flg = true;
			setLoading('', selfObj);
		},
		complete: function(xhr, textStatus) {
			GL.execute_flg = false;
			removeLoading('', selfObj);
			$(selfObj).html(trigerSelectorHtml);
		},
		success: function(response, status) {
			$('#modal_report').modal('hide');
			$('#form_category').val('');
			$('#form_body').val('');

	if (empty(valCategory)) {
		showMessage(__('report_error_required_report_about'));
		return false;
	}
	postData['category'] = valCategory;
	postData['body'] = $('#form_body').val();
			showMessage(response.message);
		},
		error: function(result, status) {
			showErrorMessage(result);
		}
	});
	return false;
});

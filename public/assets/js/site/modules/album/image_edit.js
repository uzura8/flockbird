$('#form_button').click(function() {
	var original_public_flag = $('#original_public_flag').val();
	var changed_public_flag  = $('input[name="public_flag"]:checked').val();
	if (is_expanded_public_range(original_public_flag, changed_public_flag)) {
		apprise(__('public_flag_expand_confirm'), {'confirm':true}, function(r) {
			if (r == true) $('form').submit();
		});
	} else {
		$('form').submit();
	}
});

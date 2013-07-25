$('#form_button').click(function() {
	var original_public_flag = $('#original_public_flag').val();
	var changed_public_flag  = $('input[name="public_flag"]:checked').val();
	if (is_expanded_public_range(original_public_flag, changed_public_flag)) {
		apprise('公開範囲が広がります。送信しますか？', {'confirm':true}, function(r) {
			if (r == true) $('form').submit();
		});
	} else {
		$('form').submit();
	}
});

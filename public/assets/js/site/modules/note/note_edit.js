isPopover = false;
setup_simple_validation_required_popover('#form_title');
setup_simple_validation_required_popover('#form_body');
$('html').click(function(e) {
	if (isPopover) {
		$('#form_title').popover('hide');
		$('#form_body').popover('hide');
		isPopover = false;
	}
});

$('#form_button').click(function() {
	if (!simple_validation_required('#form_title')) return false;
	if (!simple_validation_required('#form_body')) return false;

	var original_public_flag = $('#form_original_public_flag').val();
	var changed_public_flag  = $('input[name="public_flag"]:checked').val();
	if (is_expanded_public_range(original_public_flag, changed_public_flag)) {
		apprise(__('public_flag_expand_confirm'), {'confirm':true}, function(r) {
			if (r == true) $('#form_note_edit').submit();
		});
	} else {
		$('#form_note_edit').submit();
	}
});

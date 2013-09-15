isPopover = false;
setup_simple_validation_required_popover('#form_name');
setup_simple_validation_required_popover('#form_body');
$('html').click(function(e) {
	if (isPopover) {
		$('#form_name').popover('hide');
		$('#form_body').popover('hide');
		isPopover = false;
	}
});

$('#form_button').click(function() {
	if (!simple_validation_required('#form_name')) return false;
	if (!simple_validation_required('#form_body')) return false;

	var original_public_flag = $('#original_public_flag').val();
	var changed_public_flag  = $('input[name="public_flag"]:checked').val();

	var is_submit = false;
	if (is_expanded_public_range(original_public_flag, changed_public_flag)) {
		apprise('公開範囲が広がります。送信しますか？', {'confirm':true}, function(r) {
			if (r == true) submit_album_edit();
		});
	} else {
		submit_album_edit();
	}
});

function submit_album_edit() {
	apprise(get_term('album_image') + 'の' + get_term('public_flag') + 'も変更しますか？', {'verify':true}, function(r) {
		if (r == true) {
			$('#is_update_children_public_flag').val(1);
			$('form').submit();
		} else {
			$('form').submit();
		}
	});
}

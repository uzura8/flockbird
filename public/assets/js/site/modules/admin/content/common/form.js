function checkInput() {
	if ($('#form_title').val().length > 0) return true;
	if ($('#form_body').val().length > 0) return true;
	if ($('.note-editable').size() > 0 && $('.note-editable').html().replace(/^<br>\s*/, '').size() > 0) return true;
	if ($('#form_slug').val().length > 0) return true;
	return false;
}


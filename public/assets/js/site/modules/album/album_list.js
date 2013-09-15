if (!is_sp()) {
	$('.main_item').on({
		mouseenter:function() {$('#btn_album_edit_' + get_id_num($(this).attr('id'))).fadeIn('fast')},
		mouseleave:function() {$('#btn_album_edit_' + get_id_num($(this).attr('id'))).hide()}
	});
}

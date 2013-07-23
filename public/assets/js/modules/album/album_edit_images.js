$('#submit_delete').click(function() {
	submit_after_confirm('delete');
});
$('#submit_post').click(function() {
	submit_after_confirm('post');
});
function submit_after_confirm(action) {
	$('#clicked_btn').val(action);

	if (check_is_checked()) {
		var action_name = '編集';
		if (action == 'delete') action_name = '削除';

		var name        = $('#form_name').val().trim();
		var public_flag = $('input[name="public_flag"]:checked').val();
		var shot_at     = $('#form_shot_at').val().trim();
		if (action == 'post' && name.length == 0 && public_flag == 99 && shot_at.length == 0) {
			apprise('入力してください');
			return false;
		}

		apprise('一括' + action_name + 'しますか？', {'confirm':true}, function(r) {
			if (r == true) $("form#form_edit_images").submit();
		});
	} else {
		apprise('実施対象が選択されていません');
		return false;
	}
}

function check_is_checked() {
	var is_checked = false;
	$('.album_image_ids').each(function() {
		if ($(this).is(':checked')) is_checked = true;
	});

	return is_checked;
}

$('table#album_image_list td:not(.image)').click(function() {
	var c = $(this).parent('tr').children('td').children('input[type=checkbox]');
	if (c.prop('checked')) {
		c.prop('checked', '');
	} else {
		c.prop('checked', 'checked');
	}
});
$('table#album_image_list td input[type=checkbox]').click(function(){
	if($(this).attr('checked')) {
		$(this).attr('checked', false);
	}else{
		$(this).attr('checked', true);
	}
});

$('input.album_image_all').click(function() {
	if (this.checked) {
		$('input.album_image_ids').attr('checked', 'checked');
		$('input.album_image_all').attr('checked', 'checked');
	} else {
		$('input.album_image_ids').removeAttr('checked');
		$('input.album_image_all').removeAttr('checked');
	}
});

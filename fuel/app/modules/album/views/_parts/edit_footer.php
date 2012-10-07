<?php echo Asset::js('jquery-ui-1.8.24.custom.min.js');?>
<?php echo Asset::js('jquery-ui-timepicker-addon.js');?>
<?php echo Asset::js('jquery-ui-sliderAccess.js');?>

<script type="text/javascript">
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

		if (action == 'post' && $('#form_name').val().length == 0 && $('#form_shot_at').val().length == 0) {
			jAlert('入力してください');
			return false;
		} else {
			jConfirm('一括' + action_name + 'しますか？', action_name + '確認', function(result) {
				if (result) $("form#form_edit_images").submit();
			});
		}
	} else {
		jAlert('実施対象が選択されていません');
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

$('#form_shot_at').datetimepicker({
	dateFormat: 'yy-mm-dd',
	changeYear: true,
	changeMonth: true,
	prevText: '&#x3c;前',
	nextText: '次&#x3e;',
	timeFormat: 'hh:mm',
	hourGrid: 6,
	minuteGrid: 15,
	addSliderAccess: true,
	sliderAccessArgs: { touchonly: false }
});
</script>

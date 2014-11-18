<?php if (Config::get('news.form.isEnabledWysiwygEditor')): ?>
<?php echo render('_parts/form/summernote/footer'); ?>
<?php echo render('_parts/form/summernote/moderator_setting'); ?>
<?php endif; ?>
<script>
$(function(){
	$(window).on('beforeunload', function() {
		if (checkInput()) return '投稿が完了していません。このまま移動しますか？';
	});
	$("button[type=submit]").click(function() {
		$(window).off('beforeunload');
	});
	$("#btn_delete").click(function() {
		$(window).off('beforeunload');
	});
});
function checkInput() {
	if ($('#form_title').val().length > 0) return true;
	if ($('.note-editable').size() > 0 && $('.note-editable').html().replace(/^<br>\s*/, '')) return true;
	if ($('.image_tmp').size() > 0 && $('.image_tmp').length) return true;
	if ($('.file_tmp').size() > 0 && $('.file_tmp').length) return true;
	if ($('.link_uri').size() > 0 && $('.link_uri').length) return true;
	if ($('.link_label').size() > 0 && $('.link_label').length) return true;
	if ($('#form_published_at_time').val()) return true;
	return false;
}
</script>

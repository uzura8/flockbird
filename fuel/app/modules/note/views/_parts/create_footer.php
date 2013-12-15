<?php echo render('_parts/date_timepicker_footer', array('attr' => '#form_published_at_time')); ?>
<?php echo render('filetmp/_parts/upload_footer'); ?>
<script>
$('.display_fileinput-button').click(function() {
	$('.display_fileinput-button').remove();
	$('.fileinput').attr('class', 'fileinput');
});
$('#form_button').click(function() {
	$('#form_note_create').submit();
});
</script>

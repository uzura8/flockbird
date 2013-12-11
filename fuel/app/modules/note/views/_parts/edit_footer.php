<?php echo render('_parts/date_timepicker_footer', array('attr' => '#form_published_at_time')); ?>
<?php echo render('filetmp/_parts/upload_footer'); ?>
<script>
$('#form_button').click(function() {
	$('#form_note_create').submit();
});
</script>

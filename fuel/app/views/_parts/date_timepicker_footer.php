<?php echo Asset::js('jquery-ui-1.10.3.custom.min.js');?>
<?php echo Asset::js('i18n/jquery.ui.datepicker-ja.js');?>
<?php echo Asset::js('jquery-ui-timepicker-addon.js');?>
<?php echo Asset::js('jquery-ui-sliderAccess.js');?>
<script>
$(function(){
	set_datetimepicker('<?php echo isset($attr) ? $attr : '#form_date'; ?>'<?php if (!empty($is_accept_futer)): ?>, true<?php endif; ?>);
});
</script>

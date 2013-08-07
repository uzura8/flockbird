<?php echo Asset::js('jquery-ui-1.10.3.custom.min.js');?>
<?php echo Asset::js('i18n/jquery.ui.datepicker-ja.js');?>
<?php echo Asset::js('jquery-ui-timepicker-addon.js');?>
<?php echo Asset::js('jquery-ui-sliderAccess.js');?>
<script type="text/javascript">
$(function(){
	set_datetimepicker('<?php echo isset($attr_shot_at) ? $attr_shot_at : '#form_shot_at'; ?>');
});
</script>

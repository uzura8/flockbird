<?php echo Asset::js('jquery-ui-1.8.24.custom.min.js');?>
<?php echo Asset::js('jquery-ui-timepicker-addon.js');?>
<?php echo Asset::js('jquery-ui-sliderAccess.js');?>

<script type="text/javascript">
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

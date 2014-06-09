<?php //echo Asset::js('bootstrap-datetimepicker.min.js');?>
<?php echo Asset::js('bootstrap-datetimepicker.js');?>
<?php echo Asset::js('locales/bootstrap-datetimepicker.ja.js');?>
<script>
$(function () {
	$('<?php echo isset($attr) ? $attr : '#form_date'; ?>').datetimepicker({
		useCurrent: true,               //when true, picker will set the value to the current date/time     
		//minuteStepping:1,               //set the minute stepping
		minDate:'1/1/1900',               //set a minimum date
<?php if (isset($max_date) && $max_date = 'now'): ?>
		maxDate: moment(),     //set a maximum date (defaults to today +100 years)
<?php endif; ?>
		language:'ja',                  //sets language locale
		//defaultDate:"",                 //sets a default date, accepts js dates, strings and moment objects
		//disabledDates:[],               //an array of dates that cannot be selected
		//enabledDates:[],                //an array of dates that can be selected
		//useStrict: true,               //use "strict" when validating dates  
		//sideBySide: true,              //show the date and time picker side by side
		//daysOfWeekDisabled:[]          //for example use daysOfWeekDisabled: [0,6] to disable weekends 
	});
});
</script>

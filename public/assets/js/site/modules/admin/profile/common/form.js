var target_values = ['input', 'textarea'];
var disp_target_forms = ['value_regexp', 'value_type', 'value_min_max', 'is_unique', 'placeholder'];
display_form4value($('#form_form_type').val(), target_values, disp_target_forms);

var target_values_regexp = ['regexp'];
var disp_target_forms_regexp = ['value_regexp'];
display_form4value($('#form_value_type').val(), target_values_regexp, disp_target_forms_regexp);

$('#form_form_type').change(function() {
	display_form4value($(this).val(), target_values, disp_target_forms);
	if ($.inArray($(this).val(), target_values) != -1) {
		display_form4value($('#form_value_type').val(), target_values_regexp, disp_target_forms_regexp);
	}
});

$('#form_value_type').change(function() {
	display_form4value($(this).val(), target_values_regexp, disp_target_forms_regexp);
});

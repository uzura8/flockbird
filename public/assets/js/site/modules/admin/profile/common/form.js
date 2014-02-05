display_form4form_type_value($('#form_form_type').val());

$('#form_form_type').change(function() {
	display_form4form_type_value($(this).val());
});

function display_form4form_type_value(value) {
	var targets = ['value_type', 'value_regexp', 'value_min_max'];

	if (value == 'input' || value == 'textarea') {
		targets.forEach(function(target) {
			var block = $('#form_' + target + '_block');
			if (block.hasClass('hidden') == true) block.removeClass('hidden');
		});
	} else {
		targets.forEach(function(target) {
			var block = $('#form_' + target + '_block');
			if (block.hasClass('hidden') == false) block.addClass('hidden');
		});
	}
}

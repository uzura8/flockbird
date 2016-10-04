<?php if (empty($label_col_sm_size)) $label_col_sm_size = 3; ?>
<?php echo form_open(true); ?>
	<?php echo form_input_multi($val, array(
		'last_name' => array(
			'col_sm_size' => 6,
			'value' => isset($member_address) ? $member_address->last_name : null,
		),
		'first_name' => array(
			'col_sm_size' => 6,
			'value' => isset($member_address) ? $member_address->first_name : null,
		),
	), t('member.address.full_name'), $label_col_sm_size); ?>
	<?php echo form_input($val, 'company_name', isset($member_address) ? $member_address->company_name : null, 12, $label_col_sm_size); ?>
<?php if (conf('address.country.isEnabled', 'member')): ?>
<?php
				if (isset($member_address)) $value = $member_address->country;
				if (! $value) $value = $u->country ?: null;
				echo form_select($val, 'country', $value, 6, $label_col_sm_size, false, false, null, null, null, true);
?>
<?php endif; ?>
	<?php echo form_input($val, 'postal_code', isset($member_address) ? $member_address->postal_code : null, 4, $label_col_sm_size); ?>
	<?php echo form_input($val, 'address01', isset($member_address) ? $member_address->address01 : null, 12, $label_col_sm_size); ?>
	<?php echo form_input($val, 'address02', isset($member_address) ? $member_address->address02 : null, 12, $label_col_sm_size); ?>
	<?php echo form_input($val, 'phone01', isset($member_address) ? $member_address->phone01 : null, 6, $label_col_sm_size); ?>
	<?php echo form_button('form.do_edit', null, null, null, $label_col_sm_size); ?>
<?php echo form_close(); ?>


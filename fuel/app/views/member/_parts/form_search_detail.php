<?php $label_size = 3; ?>
<?php echo form_open(false, false, null, null, null, true); ?>
	<?php echo render('member/profile/_parts/form/search_items', array(
		'label_size' => $label_size,
		'val' => $val,
		'inputs' => $inputs,
		'profiles' => $profiles,
	)); ?>
	<?php echo form_button('form.do_search', 'submit', null, array(), $label_size, null, null, true); ?>
<?php echo form_close(); ?>


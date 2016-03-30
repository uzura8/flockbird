<?php
$col_sm_size = 12;
$label_col_sm_size = 3;
?>
<div class="well">
<?php echo form_open(true, false, array('action' => 'contact/confirm')); ?>
<?php
if ($confs = conf('contact.fields.pre', 'contact'))
{
	foreach ($confs as $name => $props)
	{
		$method = 'form_'.$props['attr']['type'];
		echo $method($val, $name, null, $col_sm_size, $label_col_sm_size);
	}
}
?>

	<?php //echo form_input($val, 'subject', null, $col_sm_size, $label_col_sm_size); ?>
	<?php echo form_textarea($val, 'body', null, $label_col_sm_size); ?>

<?php
if ($confs = conf('contact.fields.post', 'contact'))
{
	foreach ($confs as $name => $props)
	{
		$method = 'form_'.$props['attr']['type'];
		echo $method($val, $name, null, $col_sm_size, $label_col_sm_size);
	}
}
?>

	<?php echo form_button('form.do_confirm', 'submit', '', array(), $label_col_sm_size); ?>
<?php echo form_close(); ?>
</div><!-- well -->


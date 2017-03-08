<?php
$label_col_sm_size = 3;
$posted = Arr::filter_keys($val->validated(), array(conf('csrf_token_key', 'security')), true);
?>
<p><?php echo __('message_send_confirm_following_contents'); ?></p>

<div class="well">
<?php echo form_open(false, false, array('action' => 'contact/send'), $posted); ?>
<?php
if ($confs = conf('contact.fields.pre', 'contact'))
{
	foreach ($confs as $name => $props)
	{
		$value = $name == 'category' ? t('contact.fields.pre.category.options.'.$posted[$name]) : $posted[$name];
		echo form_text($value, Site_Form::get_label($val, $name), $label_col_sm_size);
	}
}
?>

	<?php echo form_text($posted['body'], Site_Form::get_label($val, 'body'), $label_col_sm_size, false, null, true); ?>

<?php
if ($confs = conf('contact.fields.post', 'contact'))
{
	foreach ($confs as $name => $props)
	{
		echo form_text($posted[$name], Site_Form::get_label($val, $name), $label_col_sm_size);
	}
}
?>
	<?php echo form_button('form.do_send', 'submit', 'submit', array('class' => 'btn btn-default btn-primary'), $label_col_sm_size); ?>
<?php echo form_close(); ?>

<?php echo form_open(false, false, array('action' => 'contact'), $posted); ?>
	<?php //echo Form::hidden('password', $input['password'], array('dont_prep' => true)); ?>
	<?php echo form_button('form.back', 'submit', 'submit_back', array('class' => 'btn btn-default'), $label_col_sm_size); ?>
<?php echo form_close(); ?>
</div><!-- well -->


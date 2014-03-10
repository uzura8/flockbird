<div class="well">
<?php $label_size = 4; ?>
<?php echo form_open(); ?>
<?php foreach ($site_configs_form_config as $name => $form_config): ?>
<?php if ($form_config['form'] == 'radio'): ?>
	<?php echo form_radio($val, $name, $site_configs[$name], $label_size, 'inline'); ?>
<?php elseif ($form_config['form'] == 'select'): ?>
	<?php echo form_select($val, $name, $site_configs[$name], 6, $label_size); ?>
<?php elseif ($form_config['form'] == 'public_flag'): ?>
	<?php echo form_public_flag($val, $site_configs[$name], false, $label_size, false, $name); ?>
<?php endif; ?>
<?php endforeach; ?>
	<?php echo form_button('編集する', 'submit', 'submit', array(), $label_size); ?>
<?php echo form_close(); ?>
</div><!-- well -->

<?php
$label_size = 3;
$back_uri = 'member/setting/email';
if (!empty($is_regist_mode)) $back_uri .= '/regist';
?>
<div class="well">
<?php echo form_open(true); ?>
<?php echo form_text($email, term('site.email'), $label_size); ?>
<?php echo form_input($val, 'code', '', 6, $label_size); ?>
<?php echo form_button($is_registerd ? 'form.do_update' : 'site.register', 'submit', null, null, $label_size); ?>
<?php echo form_button('form.back', 'button', 'button_back', array(
	'class' => 'btn btn-default js-simpleLink',
	'data-uri' => $back_uri,
), $label_size); ?>
<?php echo form_close(); ?>
</div><!-- well -->

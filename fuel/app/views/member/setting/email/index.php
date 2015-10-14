<?php
$label_size = 3;
$btn_label = sprintf('%s用%sを%sする', term('form.confirm'), term('site.mail'), term('form.send'));
?>
<div class="well">
<?php echo form_open(true); ?>
<?php echo form_input($val, 'email', '', 7, $label_size); ?>
<?php echo form_input($val, 'email_confirm', '', 7, $label_size); ?>
<?php echo form_button($btn_label, 'submit', null, null, $label_size, null, 'form.do_send'); ?>
<?php echo form_close(); ?>
</div><!-- well -->

<?php if (empty($is_regist_mode)): ?>
<?php echo render('member/setting/_parts/footer_navi'); ?>
<?php endif; ?>


<?php echo alert(__('message_please_input_for', array('label' => t('site.password')))); ?>
<div class="well">
<?php echo render('_parts/form/description', array('exists_required_fields' => true)); ?>
<?php echo $html_form; ?>
</div>
<?php echo render('member/setting/_parts/footer_navi'); ?>

<?php echo render('member/setting/_parts/navi', array('active_action' => 'email')); ?>
<div class="well">
<?php echo render('_parts/form/description', array('exists_required_fields' => true)); ?>
<?php echo $html_form; ?>
</div>

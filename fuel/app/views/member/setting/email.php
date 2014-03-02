<ul class="nav nav-tabs">
	<li class="active"><?php echo Html::anchor('member/setting/email', term('member.email').'変更'); ?></li>
	<li><?php echo Html::anchor('member/setting/password', term('member.password').'変更'); ?></li>
	<li><?php echo Html::anchor('member/leave', Config::get('term.member_leave')); ?></li>
</ul>
<div class="well">
<?php echo render('_parts/form/description', array('exists_required_fields' => true)); ?>
<?php echo $html_form; ?>
</div>

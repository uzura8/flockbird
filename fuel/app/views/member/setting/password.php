<ul class="nav nav-tabs">
	<li><?php echo Html::anchor('member/setting/email', 'メールアドレス変更'); ?></li>
	<li class="active"><?php echo Html::anchor('member/setting/password', 'パスワード変更'); ?></li>
	<li><?php echo Html::anchor('member/leave', Config::get('term.member_leave')); ?></li>
</ul>
<div class="well">
<?php echo render('_parts/form/description', array('exists_required_fields' => true)); ?>
<?php echo $html_form; ?>
</div>

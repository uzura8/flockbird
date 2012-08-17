<ul class="nav nav-tabs">
	<li class="active"><?php echo Html::anchor('member/setting/email', 'メールアドレス変更'); ?></li>
	<li><?php echo Html::anchor('member/setting/password', 'パスワード変更'); ?></li>
	<li><?php echo Html::anchor('member/leave', Config::get('site.term.member_leave')); ?></li>
</ul>
<div class="well">
	<h3>メールアドレス変更</h3>
<?php echo $html_form; ?>
</div>

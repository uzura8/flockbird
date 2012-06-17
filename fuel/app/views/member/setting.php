<ul class="nav nav-tabs">
	<li><?php echo Html::anchor('member/setting/email', 'メールアドレス変更'); ?></li>
	<li><?php echo Html::anchor('member/setting/password', 'パスワード変更'); ?></li>
	<li><?php echo Html::anchor('member/leave', Config::get('site.term.member_leave')); ?></li>
</ul>
<div class="well">
	<p>設定変更ページです</p>
</div>

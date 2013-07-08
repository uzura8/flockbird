<?php $is_api_request = Site_Util::check_is_api_request(); ?>
<?php if ($is_api_request): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>

<?php if (!$is_api_request): ?><div class="well"><?php endif; ?>
<?php echo $html_form; ?>

<div class="control-group">
	<div class="controls">
	<small><?php echo Html::anchor('member/resend_password', 'パスワードを忘れた場合はこちら'); ?></small>
	</div>
</div>

<?php if (PRJ_FACEBOOK_APP_ID): ?>
<div class="control-group">
	<div class="controls facebook_login">
	<?php echo Html::anchor('facebook/login', 'facebookでログイン', array('class' => 'btn btn-primary')); ?>
	</div>
</div>
<?php endif; ?>

<div class="control-group">
	<div class="controls signup">
	<?php echo Html::anchor('member/signup', '新規登録', array('class' => 'btn btn-warning')); ?>
	</div>
</div>
<?php if (!$is_api_request): ?></div><?php endif; ?>

<?php if ($is_api_request): ?></body></html><?php endif; ?>

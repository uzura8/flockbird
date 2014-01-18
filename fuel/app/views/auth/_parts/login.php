<?php $is_api_request = Site_Util::check_is_api_request(); ?>
<?php if ($is_api_request): ?><?php echo Html::doctype('html5'); ?><body><?php endif; ?>

<?php if (!$is_api_request): ?><div class="well"><?php endif; ?>
<?php
$form_attributes = array('action' => Config::get('site.login_uri.site'));
if ($is_api_request) $form_attributes['class'] = '';
$col_sm_size = $is_api_request ? 12 : 7;
$label_col_sm_size = $is_api_request ? 12 : 3;
?>
<?php echo form_open(false, false, $form_attributes, array('destination' => $destination)); ?>
	<?php echo form_input($val, 'email', '', $col_sm_size, $label_col_sm_size); ?>
	<?php echo form_input($val, 'password', '', $col_sm_size, $label_col_sm_size); ?>

	<div class="form-group">
		<div class="col-sm-9 col-sm-offset-3">
			<div class="checkbox">
			<?php echo Form::checkbox(
				'rememberme[]',
				'1',
				in_array('1', Input::post('rememberme', array())) ? array('checked' => 'checked', 'id' => 'form_rememberme_1') : array('id' => 'form_rememberme_1')
			); ?>
			<?php echo Form::label('次回から自動的にログイン', 'rememberme_1'); ?>
			</div>
		</div>
	</div>

	<?php echo form_anchor('member/resend_password', 'パスワードを忘れた場合はこちら', array(), 3, null, true); ?>
	<?php echo form_button('ログイン', 'submit', null, null, 3); ?>

<?php if (PRJ_FACEBOOK_APP_ID): ?>
	<?php echo form_anchor(Config::get('site.login_uri.site').'/facebook', '<i class="ls-icon-facebook"></i> facebookでログイン', array('class' => 'btn btn-default'), 3); ?>
<?php endif; ?>
<?php if (PRJ_TWITTER_APP_ID): ?>
	<?php echo form_anchor(Config::get('site.login_uri.site').'/twitter', '<i class="ls-icon-twitter"></i> twitterでログイン', array('class' => 'btn btn-default'), 3); ?>
<?php endif; ?>
<?php if (PRJ_GOOGLE_APP_ID): ?>
	<?php echo form_anchor(Config::get('site.login_uri.site').'/google', '<i class="ls-icon-google"></i> googleでログイン', array('class' => 'btn btn-default'), 3); ?>
<?php endif; ?>
	<?php echo form_anchor('member/signup', '新規登録', array('class' => 'btn btn-default btn-warning'), 3); ?>

<?php echo form_close(); ?>
<?php if (!$is_api_request): ?></div><?php endif; ?>
<?php if ($is_api_request): ?></body></html><?php endif; ?>

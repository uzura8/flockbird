<?php
$lang = 'ja';
$file = 'template_content_'.$lang;

$config = array(
	'mail' => array(
		'site' => array(
			'signature' => array(
				'format' =>'twig',
				'body' => array(
					'default' => array(
						'file' => 'mail/'.$lang.'/signature',
					),
				),
			),
			'memberSignup' => array(
				'format' =>'twig',
				'title' => '仮登録完了のお知らせ',
				'body' => array(
					'default' => array(
						'file' => 'mail/'.$lang.'/member_signup',
					),
				),
			),
			'memberRegister' => array(
				'format' =>'twig',
				'title' => 'メンバー登録完了のお知らせ',
				'body' => array(
					'default' => array(
						'file' => 'mail/'.$lang.'/member_register',
					),
				),
			),
			'memberLeave' => array(
				'format' =>'twig',
				'title' => 'メンバー退会完了のお知らせ',
				'body' => array(
					'default' => array(
						'file' => 'mail/'.$lang.'/member_leave',
					),
				),
			),
			'memberSettingPassword' => array(
				'format' =>'twig',
				'title' => 'パスワード変更完了のお知らせ',
				'body' => array(
					'default' => array(
						'file' => 'mail/'.$lang.'/member_setting_password',
					),
				),
			),
			'memberRegisterEmailConfirm' => array(
				'format' =>'twig',
				'title' => 'メールアドレス登録確認',
				'body' => array(
					'default' => array(
						'file' => 'mail/'.$lang.'/member_register_email_confirm',
					),
				),
			),
			'memberChangeEmail' => array(
				'format' =>'twig',
				'title' => 'メールアドレス変更完了のお知らせ',
				'body' => array(
					'default' => array(
						'file' => 'mail/'.$lang.'/member_change_email',
					),
				),
			),
			'memberResendPassword' => array(
				'format' =>'twig',
				'title' => 'パスワードの再登録確認',
				'body' => array(
					'default' => array(
						'file' => 'mail/'.$lang.'/member_resend_password',
					),
				),
			),
			'memberResetPassword' => array(
				'format' =>'twig',
				'title' => 'パスワードの再登録完了のお知らせ',
				'body' => array(
					'default' => array(
						'file' => 'mail/'.$lang.'/member_reset_password',
					),
				),
			),
			'memberInvite' => array(
				'format' =>'twig',
				'title' => '{{ invite_member_name }} から {{ site_name }} の招待状が届いています',
				'body' => array(
					'default' => array(
						'file' => 'mail/'.$lang.'/member_invite',
					),
				),
			),
			'notice' => array(
				'format' =>'twig',
				'title' => '【{{ site_name }}】新着お知らせメール',
				'body' => array(
					'default' => array(
						'file' => 'notice::mail/'.$lang.'/notice',
					),
				),
			),
			'message' => array(
				'format' =>'twig',
				'title' => '{% if subject %}{{ subject }}{% else %}【{{ site_name }}】新着メッセージお知らせメール{% endif %}',
				'body' => array(
					'default' => array(
						'file' => 'message::mail/'.$lang.'/notice',
					),
				),
			),
		),
	),
);
$config = Site_Config::merge_module_configs($config, $file);

try
{
	$config = Site_Config::setup_configs_template($config, $lang);
}
catch(Database_Exception $e)
{
	// Task DbSetter 実行時にDBが存在しない場合があるので、スルーする
	if (! IS_TASK) throw $e;
}

return $config;

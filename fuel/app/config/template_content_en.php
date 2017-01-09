<?php
$lang = 'en';
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
				'title' => 'Temporary registration is completed',
				'body' => array(
					'default' => array(
						'file' => 'mail/'.$lang.'/member_signup',
					),
				),
			),
			'memberRegister' => array(
				'format' =>'twig',
				'title' => 'Member registration is completed',
				'body' => array(
					'default' => array(
						'file' => 'mail/'.$lang.'/member_register',
					),
				),
			),
			'memberLeave' => array(
				'format' =>'twig',
				'title' => 'The leaving procedure is completed',
				'body' => array(
					'default' => array(
						'file' => 'mail/'.$lang.'/member_leave',
					),
				),
			),
			'memberSettingPassword' => array(
				'format' =>'twig',
				'title' => 'Changed password',
				'body' => array(
					'default' => array(
						'file' => 'mail/'.$lang.'/member_setting_password',
					),
				),
			),
			'memberRegisterEmailConfirm' => array(
				'format' =>'twig',
				'title' => 'Mail address registration confirmation',
				'body' => array(
					'default' => array(
						'file' => 'mail/'.$lang.'/member_register_email_confirm',
					),
				),
			),
			'memberChangeEmail' => array(
				'format' =>'twig',
				'title' => 'Mail address registration is completed',
				'body' => array(
					'default' => array(
						'file' => 'mail/'.$lang.'/member_change_email',
					),
				),
			),
			'memberResendPassword' => array(
				'format' =>'twig',
				'title' => 'Confirmation password re-registration',
				'body' => array(
					'default' => array(
						'file' => 'mail/'.$lang.'/member_resend_password',
					),
				),
			),
			'memberResetPassword' => array(
				'format' =>'twig',
				'title' => 'Password re-registration is completed',
				'body' => array(
					'default' => array(
						'file' => 'mail/'.$lang.'/member_reset_password',
					),
				),
			),
			'memberInvite' => array(
				'format' =>'twig',
				'title' => 'Invitation {{ site_name }} has arrived from {{ invite_member_name }}',
				'body' => array(
					'default' => array(
						'file' => 'mail/'.$lang.'/member_invite',
					),
				),
			),
			'notice' => array(
				'format' =>'twig',
				'title' => '[{{ site_name }}] Notification mail',
				'body' => array(
					'default' => array(
						'file' => 'notice::mail/'.$lang.'/notice',
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

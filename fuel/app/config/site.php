<?php

$config = array(
	'login_uri' => array(
		'site'  => 'site/login',
		'admin' => 'admin/login',
	),
	'record_limit' => array(
		'default' => array(
			'comment' => array(
				'l' => 10,
				'm' => 5,
				's' => 3,
			),
		),
	),
	'upload_files' => array(
		'img' => array(
			// member profile image
			'default_size' => '50x50',
			'accept_format' => array(
				'gif',
				'jpg',
				'png',
			),
			'type' => array(
				'm' => array(
					'sizes' => array(
						'RAW' => 'raw',
						'SS' => '20x20',
						'S' => '30x30',
						'M' => '50x50',
						'L' => '180x180',
					),
					'max_size' => '600x600',
					'resize_type' => 'crop',
				),
			),
		),
	),
	'accepted_upload_filesize_type' => array(
		'small' => array(
			'limit_size' => '256M',
		),
	),
	'posted_value_rule_default' => array(
		'time' => array(
			'min' => strtotime('- 120 years'),
			'max' => strtotime('+ 50 years'),
		),
	),
	'member_setting_common' => array(
		'from_name' => PRJ_SITE_NAME.' '.PRJ_ADMIN_NAME,
		'from_mail_address' => PRJ_ADMIN_MAIL,
	),
	'member_confirm_register_mail' => array(
		'subject'           => 'メンバー仮登録完了のお知らせ',
	),
	'member_register_mail' => array(
		'subject'           => 'メンバー登録完了のお知らせ',
	),
	'member_leave_mail' => array(
		'from_name' => PRJ_SITE_NAME.' '.PRJ_ADMIN_NAME,
		'from_mail_address' => PRJ_ADMIN_MAIL,
		'subject'           => 'メンバー退会完了のお知らせ',
	),
	'member_setting_password' => array(
		'subject'           => 'パスワード変更完了のお知らせ',
	),
	'member_confirm_change_email' => array(
		'subject'           => 'メールアドレス変更確認',
	),
	'member_change_email' => array(
		'subject'           => 'メールアドレス変更完了のお知らせ',
	),
	'member_resend_password' => array(
		'subject'           => 'パスワードの再登録確認',
	),
	'member_reset_password' => array(
		'subject'           => 'パスワードの再登録完了のお知らせ',
	),
);

return Site_Util::merge_module_configs($config, 'site');

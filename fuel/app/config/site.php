<?php

return array(
	'term' => array(
		'toppage' => 'トップ',
		'myhome' => 'マイホーム',
		'signup' => 'メンバー登録',
		'member_leave'  => '退会',
		'guest' => 'ゲスト',
		'note' => '日記',
	),
	'image' => array(
		'member' => array(
			'original' => array(
				'path' => PRJ_UPLOAD_DIR.'/img/member/original',
			),
			'x-small' => array(
				'path' => PRJ_UPLOAD_DIR.'/img/member/x-small',
				'width'  => 30,
				'height' => 30,
			),
			'small' => array(
				'path' => PRJ_UPLOAD_DIR.'/img/member/small',
				'width'  => 50,
				'height' => 50,
			),
			'medium' => array(
				'path' => PRJ_UPLOAD_DIR.'/img/member/medium',
				'width'  => 100,
				'height' => 100,
			),
			'lerge' => array(
				'path' => PRJ_UPLOAD_DIR.'/img/member/lerge',
				'width'  => 600,
				'height' => 600,
			),
		),
	),
	'member_register_mail' => array(
		'from_name' => PRJ_SITE_NAME.' '.PRJ_ADMIN_NAME,
		'from_mail_address' => PRJ_ADMIN_MAIL,
		'subject'           => 'メンバー登録完了のお知らせ',
	),
	'member_leave_mail' => array(
		'from_name' => PRJ_SITE_NAME.' '.PRJ_ADMIN_NAME,
		'from_mail_address' => PRJ_ADMIN_MAIL,
		'subject'           => 'メンバー退会完了のお知らせ',
	),
	'member_setting_common' => array(
		'from_name' => PRJ_SITE_NAME.' '.PRJ_ADMIN_NAME,
		'from_mail_address' => PRJ_ADMIN_MAIL,
	),
	'member_setting_password' => array(
		'subject'           => 'パスワード変更完了のお知らせ',
	),
	'member_setting_email' => array(
		'subject'           => 'メールアドレス変更完了のお知らせ',
	),
);

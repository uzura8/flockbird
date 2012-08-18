<?php

return array(
	'term' => array(
		'toppage' => 'Top',
		'myhome'  => 'Home',
		'profile' => 'Profile',
		'signup'  => 'Sign Up',
		'member_leave'  => '退会',
		'guest'   => 'Guest',
		'note'    => 'Note',
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
				'width'  => 180,
				'height' => 180,
			),
			'lerge' => array(
				'path' => PRJ_UPLOAD_DIR.'/img/member/lerge',
				'width'  => 600,
				'height' => 600,
			),
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

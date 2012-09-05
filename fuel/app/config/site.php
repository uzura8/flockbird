<?php

$config = array(
	'term' => array(
		'toppage' => 'Top',
		'myhome'  => 'Home',
		'profile' => 'Profile',
		'signup'  => 'Sign Up',
		'member_leave'  => '退会',
		'guest'   => 'Guest',
		'note'    => 'Note',
	),
	'upload_files' => array(
		'img' => array(
			// member profile image
			'm' => array(
				'sizes' => array(
					'raw',
					'20x20',
					'30x30',
					'50x50',
					'180x180',
				),
				'max_size' => '600x600',
				'resize_type' => 'crop',
			),
		),
	),
	'accepted_upload_filesize_type' => array(
		'small' => array(
			'limit_size' => '256M',
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


$modules = Module::loaded();
foreach ($modules as $module => $path)
{
	Config::load($module.'::site', $module.'_site');
	$module_site = Config::get($module.'_site');
	if (!empty($module_site)) $config = array_merge_recursive($config, $module_site);
}

return $config;

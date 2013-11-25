<?php

$config = array(
	'login_uri' => array(
		'site'  => 'auth/login',
		'admin' => 'admin/login',
	),
	'upload' => array(
		'num_of_split_dirs' => 10,
		'check_and_make_dir_level' => 7,
		'accepted_filesize' => array(
			'small' => array(
				'limit' => '256M',
			),
		),
		'tmp_file' => array(
			'accepted_contents' => array(
					'note',
				),
			'lifetime' => 60 * 60 * 24,
			'lifetime_extra_when_posted' => 60 * 60 * 1,
			'is_delete_olds_when_display' => true,
			'delete_record_limit' => 100,
		),
		'types' => array(
			'img' => array(
				'root_path' => array(
					'cache_dir' => PRJ_UPLOAD_DIRNAME.'/img/',
					'raw_dir' => PRJ_UPLOAD_DIRNAME.'/img/raw/',
				),
				//'raw_file_path' => APPPATH.'cache/media/img/raw/',// raw ファイルを非公開領域に置く場合
				'raw_file_path' => PRJ_PUBLIC_DIR.PRJ_UPLOAD_DIRNAME.'/img/raw/',
				'tmp' => array(
					'root_path' => array(
						'cache_dir' => PRJ_UPLOAD_DIRNAME.'/img_tmp/',
						'raw_dir' => PRJ_UPLOAD_DIRNAME.'/img_tmp/raw/',
					),
					//'raw_file_path' => APPPATH.'cache/media/img_tmp/raw/',// raw ファイルを非公開領域に置く場合
					'raw_file_path' => PRJ_PUBLIC_DIR.PRJ_UPLOAD_DIRNAME.'/img_tmp/raw/',
					'sizes' => array(
						'thumbnail' => '320x320',
					),
				),
				// member profile image
				'noimage_filename' => 'noimage.gif',
				'accept_format' => array(
					'gif' => 'image/gif',
					'jpg' => 'image/jpeg',
					'jpeg'=> 'image/jpeg',
					'png' => 'image/png',
				),
				'defaults' => array(
					'default_size' => '50x50xc',
					'max_size' => '600x600',
				),
				'types' => array(
					'm' => array(
						// サイズが小さい順に定義する
						'sizes' => array(
							'SS' => '20x20xc',
							'S' => '30x30xc',
							'M' => '50x50xc',
							'L' => '180x180xc',
							'LL' => '600x600',
						),
						'default_size' => '50x50xc',
						'max_size' => '600x600',
						'save_as_album_image' => true,
					),
				),
			),
		),
	),
	'view_params_default' => array(
		'list' => array(
			'limit' => 5,
			'limit_max' => 50,
			'trim_width' => array(
				'title' => 88,
				'body'  => 500,
			),
			'truncate_lines' => array(
				'body'  => 5,
			),
			'comment' => array(
				'limit' => 5,
				'limit_max' => 20,
				'trim_width' => 200,
			),
		),
		'detail' => array(
			'comment' => array(
				'limit' => 10,
				'limit_max' => 30,
			),
		),
	),
	'posted_value_rule_default' => array(
		'time' => array(
			'min' => '- 120 years',
			'max' => '+ 50 years',
		),
	),
	'public_flag' => array(
		'default' => PRJ_PUBLIC_FLAG_ALL,
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

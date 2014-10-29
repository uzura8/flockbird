<?php

$config = array(
	'default' => array(
		'token_lifetime' => '1 day',// user for function.strtodate. if false, not check lifetime.
		'ajax_timeout' => 10000,
	),
	'login_uri' => array(
		'site' => 'auth/login',
	),
	'original_user_id' => array(
		'site'  => 1,
	),
	'ssl_required' => array(
		'modules' => array(
			'admin',
		),
		'actions' => array(
			'auth/login',
			'auth/callback',
			'member/setting',
			'member/setting/index',
			'member/setting/password',
			'member/setting/change_password',
			'member/setting/email',
			'member/setting/confirm_change_email',
			'member/leave',
			'member/leave/index',
			'member/leave/confirm',
			'member/leave/delete',
			'member/profile',
			'member/profile/index',
			'member/profile/edit',
			'member/recover/resend_password',
			'member/recover/send_reset_password_mail',
			'member/recover/reset_password',
			'member/register',
			'member/register/index',
			'member/register/signup',
			'member/register/confirm_signup',
		),
	),
	'memberRelation' => array(
		'follow' => array(
			'isEnabled'  => 1,
		),
	),
	'member' => array(
		'register' => array(
			'token_lifetime' => '1 day',// user for function.strtodate. if false, not check lifetime.
			'email' => array(
				'hideUniqueCheck' => true,
			),
		),
		'recover' => array(
			'password' => array(
				'token_lifetime' => '1 day',// user for function.strtodate. if false, not check lifetime.
			),
		),
		'setting' => array(
			'email' => array(
				'token_lifetime' => '1 day',// user for function.strtodate. if false, not check lifetime.
				'hideUniqueCheck' => true,
			),
		),
		'profile' => array(
			'display_type' => array(
				'detail' => '0',
				'summery' => '1',
				//'always' => '2',// (未実装のため無効)
			),
			'birthday' => array(
				'year_from' => -100,// years
				'year_to' => 0,// years
				'use_generation_view' => false,// 「年代」表示の使用するかどうか(未実装のため無効)
			),
		),
		'view_params' => array(
			'list' => array(
				'limit' => 3,
				'limit_max' => 5,
			),
		),
	),
	'upload' => array(
		'isSaveDb' => true,
		'num_of_split_dirs' => 10,
		'check_and_make_dir_level' => 7,
		'mkdir_mode' => 0755,
		'accepted_filesize' => array(
			'small' => array(
				'limit' => '256M',
			),
		),
		'tmp_file' => array(
			'lifetime' => 60 * 60 * 24,
			'delete_record_limit' => 100,
		),
		'types' => array(
			'img' => array(
				'accepted_max_size' => array(
					'default' => '600x600',
				),
				'exif' => array(
					'is_use' => true,
					'is_remove' => true,
				),
				'root_path' => array(
					'cache_dir' => PRJ_UPLOAD_DIRNAME.'/img/',
					'raw_dir' => PRJ_UPLOAD_DIRNAME.'/img/raw/',
				),
				'raw_file_path' => APPPATH.'media/img/raw/',// raw ファイルを非公開領域に置く場合
				//'raw_file_path' => PRJ_PUBLIC_DIR.PRJ_UPLOAD_DIRNAME.'/img/raw/',
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
					// profile
					'm' => array(
						// サイズが小さい順に定義する
						'sizes' => array(
							'SS' => '20x20xc',
							'S' => '30x30xc',
							'M' => '50x50xc',
							'ML' => '120x120xc',
							'L' => '180x180xc',
							'LL' => '600x600',
						),
						'default_size' => '50x50xc',
						'save_as_album_image' => false,
					),
				),
			),
			'file' => array(
				'root_path' => array(
					'cache_dir' => PRJ_UPLOAD_DIRNAME.'/file/',
					'raw_dir' => PRJ_UPLOAD_DIRNAME.'/file/raw/',
				),
				//'raw_file_path' => APPPATH.'cache/media/file/raw/',// raw ファイルを非公開領域に置く場合
				'raw_file_path' => PRJ_PUBLIC_DIR.PRJ_UPLOAD_DIRNAME.'/file/raw/',
				'tmp' => array(
					'root_path' => array(
						'cache_dir' => PRJ_UPLOAD_DIRNAME.'/file_tmp/',
						'raw_dir' => PRJ_UPLOAD_DIRNAME.'/file_tmp/raw/',
					),
					//'raw_file_path' => APPPATH.'cache/media/file_tmp/raw/',// raw ファイルを非公開領域に置く場合
					'raw_file_path' => PRJ_PUBLIC_DIR.PRJ_UPLOAD_DIRNAME.'/file_tmp/raw/',
				),
				'accept_format' => array(
					'jpg' => 'image/jpeg',
					'jpeg'=> 'image/jpeg',
					'png' => 'image/png',
					'bmp' => 'image/bmp',
					'txt' => 'text/plain',
					'csv' => 'text/csv',
					'htm' => 'text/html',
					'html' => 'text/html',
					'css' => 'text/css',
					'js' => 'text/javascript',
					'tsv' => 'text/tab-separated-values',
					'pdf' => 'application/pdf',
					'doc' => 'application/msword',
					'docx' => 'application/msword',
					'xls' => 'application/vnd.ms-excel',
					'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
					'ppt' => 'application/vnd.ms-powerpoint',
					'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
					'ai' => 'application/postscript',
					'zip' => 'application/zip',
					'lha' => 'application/x-lzh',
					'lzh' => 'application/x-lzh',
					'tar' => 'application/x-tar',
					'tgz' => 'application/x-tar',
					'mp3' => 'audio/mpeg',
					'm4a' => 'audio/mp4',
					'wav' => 'audio/x-wav',
					'mid' => 'audio/midi',
					'midi' => 'audio/midi',
					'mmf' => 'application/x-smaf',
					'mpeg' => 'video/mpeg',
					'mpg' => 'video/mpeg',
					'wmv' => 'video/x-ms-wmv',
					'swf' => 'application/x-shockwave-flash',
					'3g2' => 'video/3gpp2',
				),
			),
		),
	),
	'view_params_default' => array(
		'list' => array(
			'limit' => 2,
			'limit_max' => 3,
			'trim_width' => array(
				'title' => 88,
				'body'  => 500,
			),
			'truncate_lines' => array(
				'body'  => 5,
			),
			'comment' => array(
				'limit' => 2,
				'limit_max' => 3,
				'trim_width' => 200,
			),
		),
		'detail' => array(
			'comment' => array(
				'limit' => 2,
				'limit_max' => 3,
			),
		),
		'like' => array(
			'members' => array(
				'popover' => array(
					'limit' => 2,
					'limit_max' => 3,
				),
			),
		),
		'form' => array(
			'comment' => array(
				'textarea' => array(
					'height'  => '33px',
				),
			),
		),
	),
	'posted_value_rule_default' => array(
		'time' => array(
			'min' => '- 120 years',
			'max' => '+ 50 years',
		),
	),
	'sort_order' => array(
		'interval' => 10,
	),
	'public_flag' => array(
		'default' => PRJ_PUBLIC_FLAG_ALL,
	),
	'like' => array(
		'isEnabled'  => true,
	),
	'member_config_default' => array(
	),
	// site_config default
	'profile' => array(
		'name' => array(
			'isDispConfig' => 1,
			'isDispSearch' => 0,
		),
		'sex' => array(
			'isEnable' => 1,
			'isDispRegist' => 1,
			'isDispConfig' => 1,
			'isDispSearch' => 0,
			'displayType' => 0,
			'isRequired' => 0,
			'publicFlag' => array(
				'isEdit' => 1,
				'default' => 1,
			),
		),
		'birthday' => array(
			'isEnable' => 1,
			'isDispRegist' => 1,
			'isDispConfig' => 1,
			'isDispSearch' => 0,
			'birthyear' => array(
				'viewType' => 0,// 0:生年 1:年齢
				'displayType' => 0,
				'isRequired' => 0,
				'publicFlag' => array(
					'isEdit' => 1,
					'default' => 1,
				),
			),
			'birthday' => array(
				'displayType' => 0,
				'isRequired' => 0,
				'publicFlag' => array(
					'isEdit' => 1,
					'default' => 1,
				),
			),
		),
	),
);
$config = Site_Util::merge_module_configs($config, 'site');
try
{
	$config = Site_Util::merge_db_configs($config, 'site_config');
}
catch(Database_Exception $e)
{
	// Task DbSetter 実行時にDBが存在しない場合があるので、スルーする
}


return $config;

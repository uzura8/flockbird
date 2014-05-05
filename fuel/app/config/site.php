<?php

$config = array(
	'default' => array(
		'token_lifetime' => '1 day',// user for function.strtodate. if false, not check lifetime.
	),
	'login_uri' => array(
		'site'  => 'auth/login',
	),
	'original_user_id' => array(
		'site'  => 1,
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
				'sort' => array(
					'property' => 'created_at',
					'direction' => 'desc',
				),
				'limit' => 3,
				'limit_max' => 5,
			),
		),
	),
	'upload' => array(
		'remove_exif_data' => true,
		'num_of_split_dirs' => 10,
		'check_and_make_dir_level' => 5,
		'mkdir_mode' => 0755,
		'accepted_filesize' => array(
			'small' => array(
				'limit' => '256M',
			),
		),
		'accepted_max_size' => array(
			'default' => '600x600',
			),
		'tmp_file' => array(
			'lifetime' => 60 * 60 * 24,
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
	'sort_order' => array(
		'interval' => 10,
	),
	'public_flag' => array(
		'default' => PRJ_PUBLIC_FLAG_ALL,
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
$config = Site_Util::merge_db_configs($config, 'site_config');

return $config;

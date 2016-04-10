<?php
$config = array(
	'legacyBrowserSupport' => array(
		'isEnabled' => true,
		'legacyIECriteriaVersion' => 8,
	),
	'library' => array(
		'jqueryVersion' => array(
			'latest' => '2.1.3',
			'legacy' => '1.11.2',
		),
		'angularJs' => array(
			'isEnabled' => false,
			'versions' => array(
				'latest' => '1.3.15',
				//'legacy' => '1.2.9',
			),
			//'legacyIECriteriaVersion' => 8,
		),
		//'goutte' => array(
		//	'isEnabled'  => true,
		//),
		//'PEAR_I18N_UnicodeNormalizer' => array(
		//  'isEnabled' => false, // if set this true, you have to install PEAR/I18N_UnicodeNormalizer.
		//),
	),
	'default' => array(
		'token_lifetime' => '1 day',// user for function.strtodate. if false, not check lifetime.
		'ajax_timeout' => 10000,
		'dateFormat' => 'Y/m/d',
	),
	'login_uri' => array(
		'site' => 'auth/login',
	),
	'auth' => array(
		'isEnabled' => true,
		'headerLoginForm' => array(
			'type' => 'popover',// popover / modal / link
		),
		'oauth' => array(
			'forceSetRememberMe' => false,
			'log' => array(
				'isOutputErrorLog' => array(
					'provider_signup' => true,
				),
			),
			'saveTermsUnAgreement' => true,
		),
	),
	'original_user_id' => array(
		'site'  => 1,
	),
	'batch' => array(
		'default' => array(
			'limit' => array(
				'model' => array(
					'delete' => array(
						'normal' => 100,
						'file' => 10,
					),
				),
			),
		),
	),
	'mail' => array(
		'site' => array(
			'from_name' => FBD_SITE_NAME.' '.FBD_ADMIN_NAME,
			'from_email' => FBD_ADMIN_MAIL,
		),
		'log' => array(
			'develop' => array(
				'isEnabled' => false,
				'file_path' => APPPATH.'logs/development/mail.log',
			),
		),
	),
	'navbar' => array(
		'largeLogo' => array(
			'isEnabled' => false,
		),
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
		'accessBlock' => array(
			'isEnabled'  => 0,
		),
	),
	'member' => array(
		'name' => array(
			'validation' => array(
				'length' => array(
					'min' => 2,
					'max' => 20,// 20 or less.
				),
				'match_patterns' => array(
					'basic' => '0-9A-Za-z０-９Ａ-Ｚａ-ｚ_〃々ぁ-ゖ゛-ゞァ-ヺーヽヾ一-龥',
					'register' => '[0-9A-Za-z０-９Ａ-Ｚａ-ｚ_〃々ぁ-ゖ゛-ゞァ-ヺーヽヾ一-龥]*[A-Za-zＡ-Ｚａ-ｚ_〃々ぁ-ゖ゛-ゞァ-ヺーヽヾ一-龥]+[0-9A-Za-z０-９Ａ-Ｚａ-ｚ_〃々ぁ-ゖ゛-ゞァ-ヺーヽヾ一-龥]*',
				),
				'blacklist' => array(
					'method' => array('\Site_Member', 'get_prohibited_words_for_name'),
				),
			),
		),
		'register' => array(
			'token_lifetime' => '1 day',// user for function.strtodate. if false, not check lifetime.
			'email' => array(
				'hideUniqueCheck' => true,
			),
		),
		'leave' => array(
			'isRemoveOnBatch' => false,// TODO: batch is not implemented
		),
		'recover' => array(
			'password' => array(
				'token_lifetime' => '1 day',// user for function.strtodate. if false, not check lifetime.
			),
		),
		'setting' => array(
			'email' => array(
				'token_lifetime' => '1 day',// user for function.strtodate. if false, not check lifetime.
				'codeLifetime' => '1 hour',// user for function.strtodate. if false, not check lifetime.
				'hideUniqueCheck' => true,
				'codeLength' => 6,// set under 24
				'forceRegister' => array(
					'isEnabled' => false,
					'accessableUri' => array(
						'member/setting/email/regist',
						'member/setting/email/register_confirm/regist',
						'member/setting/email/register/regist',
					),
				),
			),
		),
		'profile' => array(
			'forceRegisterRequired' => array(
				'isEnabled' => false,
				'accessableUri' => array(
					'member/profile/edit/regist',
					'site/term',
					'member/setting/email/regist',
					'member/setting/email/register_confirm/regist',
					'member/setting/email/register/regist'
				),
			),
			'display_type' => array(
				'detail' => '0',
				'summary' => '1',
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
				'limit' => 10,
				'limit_max' => 12,
			),
		),
	),
	'upload' => array(
		'storageType' => 'db', // normal (local disk) / db / S3 (if multipule server env, chose db or S3)
		'isRemoveOnBatch' => false,// TODO: batch is not implemented
		'file_category_max_length' => 3,
		'num_of_split_dirs' => 10,
		'check_and_make_dir_level' => 7,
		'mkdir_mode' => 0755,
		'isOutputLogSaveError' => true,
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
					'default' => '1800x1800',
				),
				'thumbnailsDeleteType' => 'sameTime',// sameTime: raw ファイル削除時に同時に削除 / bach:あとで bach でまとめて削除(未実装)
				'root_path' => array(
					'cache_dir' => FBD_UPLOAD_DIRNAME.'/img/',
					'raw_dir' => FBD_UPLOAD_DIRNAME.'/img/raw/',
				),
				'raw_file_path' => APPPATH.'media/img/raw/',// raw ファイルを非公開領域に置く場合
				//'raw_file_path' => DOCROOT.FBD_UPLOAD_DIRNAME.'/img/raw/',
				'tmp' => array(
					'root_path' => array(
						'cache_dir' => FBD_UPLOAD_DIRNAME.'/img_tmp/',
						'raw_dir' => FBD_UPLOAD_DIRNAME.'/img_tmp/raw/',
					),
					//'raw_file_path' => APPPATH.'cache/media/img_tmp/raw/',// raw ファイルを非公開領域に置く場合
					'raw_file_path' => DOCROOT.FBD_UPLOAD_DIRNAME.'/img_tmp/raw/',
					'sizes' => array(
						'thumbnail' => '320x320xc',
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
			'file' => array(
				//'root_path' => array(
				'root_path' => array(
					'cache_dir' => FBD_UPLOAD_DIRNAME.'/file/',
					'raw_dir' => FBD_UPLOAD_DIRNAME.'/file/raw/',
				),
				//'raw_file_path' => APPPATH.'cache/media/file/raw/',// raw ファイルを非公開領域に置く場合
				'raw_file_path' => DOCROOT.FBD_UPLOAD_DIRNAME.'/file/raw/',
				'tmp' => array(
					//'root_path' => array(
					'root_path' => array(
						'cache_dir' => FBD_UPLOAD_DIRNAME.'/file_tmp/',
						'raw_dir' => FBD_UPLOAD_DIRNAME.'/file_tmp/raw/',
					),
					//'raw_file_path' => APPPATH.'cache/media/file_tmp/raw/',// raw ファイルを非公開領域に置く場合
					'raw_file_path' => DOCROOT.FBD_UPLOAD_DIRNAME.'/file_tmp/raw/',
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
	'publicTmpDirRootPath' => FBD_UPLOAD_DIRNAME.'/tmp/',
	'view_params_default' => array(
		'comment' => array(
			'nl2br' => true,
		),
		'ogp' => array(
			'trimWidth' => array(
				'title' => 80,
				'body'  => 200,
			),
		),
		'share' => array(
			'trimWidth' => array(
				'link' => 80,
			),
		),
		'list' => array(
			'limit_pager' => 20,
			'limit' => 10,
			'limit_max' => 12,
			'trim_width' => array(
				'title' => 88,
				'body'  => 500,
			),
			'truncate_lines' => array(
				'body'  => 10,
				'trimmarker'  => '...',
			),
			'trimmarker'  => '...',
			'comment' => array(
				'limit' => 5,
				'limit_max' => 8,
				'trim_width' => 200,
			),
		),
		'detail' => array(
			'comment' => array(
				'limit' => 5,
				'limit_max' => 8,
			),
		),
		'like' => array(
			'members' => array(
				'popover' => array(
					'limit' => 5,
					'limit_max' => 8,
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
		'post' => array(
			'url2link' => array(
				'isEnabled'  => true,
				'truncateWidth'  => '40',
				'trimmarker' => '...',
				'displaySummary' => array(
					'renderAt' => 'client',// 'server' / false / 'client'
					'cache' => array(
						'isEnabled'  => true,
						'expire' => 60 * 60 * 24,
						'prefix'  => 'sitesummary_',
					),
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
	'service' => array(
		'facebook' => array(
			'shareDialog' => array(
				'jsUrl' => '//connect.facebook.net/en_US/all.js',
				'caption' => array(
					'trimWidth' => 100,
				),
				'name' => array(
					'trimWidth' => 150,
				),
				'description' => array(
					'trimWidth' => 200,
				),
			),
		),
	),
	'map' => array(
		'isEnabled' => true,
		'paramsDefault' => array(
			'div' => '#map',
			'lat' => 35.65858,
			'lng' => 139.745433,
			'zoom' => 15,
		),
	),
	'public_flag' => array(
		'enabled' => array(
			FBD_PUBLIC_FLAG_PRIVATE,
			FBD_PUBLIC_FLAG_ALL,
			FBD_PUBLIC_FLAG_MEMBER,
		),
		'default' => FBD_PUBLIC_FLAG_ALL,
		'maxRange' => FBD_PUBLIC_FLAG_ALL,
		'colorTypes' => array(
			FBD_PUBLIC_FLAG_PRIVATE => 'danger',
			FBD_PUBLIC_FLAG_ALL => 'info',
			FBD_PUBLIC_FLAG_MEMBER => 'success',
			//'friend'  => 'warning',
		),
	),
	'like' => array(
		'isEnabled'  => true,
	),
	'member_config_default' => array(
	),
	// site_config default
	'base' => array(
		'isUserInvite' => 1,
		'isDisplayTopPageWithoutAuth' => 1,
		'closedSite' => array(
			'isEnabled' => 0,
			'accessAccepted' => array(
				'actions' => array(
					'content/page/detail',
					'news/news/list',
					'news/news/detail',
				),
				'uris' => array(
					'member/register/signup',
					'auth/login',
					'auth/logout',
					'auth/callback',
					'member/register',
					'member/register/index',
					'member/register/signup',
					'member/register/confirm_signup',
					'member/recover/resend_password',
					'member/recover/send_reset_password_mail',
					'member/recover/reset_password',
				),
			),
		),
	),
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
			'birthdate' => array(
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
$config = Site_Config::merge_module_configs($config, 'site');
try
{
	$config = Site_Config::merge_db_configs($config, 'site_config');
}
catch(Database_Exception $e)
{
	// Task DbSetter 実行時にDBが存在しない場合があるので、スルーする
}

// Change public_flag setting on closed
if (Arr::get($config, 'base.closedSite.isEnabled', 0))
{
	Arr::set($config, 'public_flag.enabled', array(FBD_PUBLIC_FLAG_PRIVATE, FBD_PUBLIC_FLAG_MEMBER));
	Arr::set($config, 'public_flag.default', FBD_PUBLIC_FLAG_MEMBER);
	Arr::set($config, 'public_flag.maxRange', FBD_PUBLIC_FLAG_MEMBER);
	Arr::set($config, 'member_config_default.timeline_public_flag', FBD_PUBLIC_FLAG_MEMBER);
}

return $config;

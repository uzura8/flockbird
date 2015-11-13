<?php

return array(
	'default' => array(
		'token_lifetime' => '5 minute',// user for function.strtodate. if false, not check lifetime.
		//'ajax_timeout' => 10000,
	),
	'batch' => array(
		'default' => array(
			'limit' => array(
				'model' => array(
					'delete' => array(
						'normal' => 5,
						'file' => 3,
					),
				),
			),
		),
	),
	'mail' => array(
		'log' => array(
			'develop' => array(
				'isEnabled' => true,
			),
		),
	),
	'member' => array(
		'register' => array(
			'token_lifetime' => '5 minute',// user for function.strtodate. if false, not check lifetime.
		),
		'recover' => array(
			'password' => array(
				'token_lifetime' => '5 minute',// user for function.strtodate. if false, not check lifetime.
			),
		),
		'setting' => array(
			'email' => array(
				'token_lifetime' => '5 minute',// user for function.strtodate. if false, not check lifetime.
				'codeLifetime' => '5 minute',// user for function.strtodate. if false, not check lifetime.
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
		'tmp_file' => array(
			'lifetime' => 60,
			'delete_record_limit' => 10,
		),
	),
	'view_params_default' => array(
		'list' => array(
			'limit_pager' => 2,
			'limit' => 2,
			'limit_max' => 3,
			'trim_width' => array(
				'title' => 20,
				'body'  => 50,
			),
			'truncate_lines' => array(
				'body'  => 3,
			),
			'comment' => array(
				'limit' => 2,
				'limit_max' => 3,
				'trim_width' => 50,
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
	),
);

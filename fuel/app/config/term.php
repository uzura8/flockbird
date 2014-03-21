<?php

$config = array(
	'form' => array(
		'submit' => '送信',
		'create' => '新規作成',
		'edit' => '編集',
		'do_edit' => '編集する',
		'delete' => '削除',
		'upload' => 'アップロード',
	),
	'member' => array(
		'name' => 'ニックネーム',
		'email' => 'メールアドレス',
		'password' => 'パスワード',
		'sex' => '性別',
		'birthyear' => '生年',
		'birthday' => '誕生日',
		'birthyear_birthday' => '生年月日',
	),
	'toppage' => 'Top',
	'myhome'  => 'Home',
	'profile' => 'Profile',
	'signup'  => 'Sign Up',
	'member_leave' => '退会',
	'guest'   => 'Guest',
	'left_member'  => '退会メンバー',
	'remember_me'  => '次回から自動的にログイン',
	'public_flag' => array(
		'label' => '公開範囲',
		'options' => array(
			PRJ_PUBLIC_FLAG_PRIVATE => '非公開',
			PRJ_PUBLIC_FLAG_ALL => '全公開',
			PRJ_PUBLIC_FLAG_MEMBER => 'SNS内でのみ公開',
			//'friend'  => 'フレンドまで公開',
		),
	),
);

return Site_Util::merge_module_configs($config, 'term');

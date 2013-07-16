<?php

$config = array(
	'toppage' => 'Top',
	'myhome'  => 'Home',
	'profile' => 'Profile',
	'signup'  => 'Sign Up',
	'member_leave' => '退会',
	'guest'   => 'Guest',
	'left_member'  => '退会メンバー',
	'remember_me'  => '次回から自動的にログイン',
);

return Site_Util::merge_module_configs($config, 'term');

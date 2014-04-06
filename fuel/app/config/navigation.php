<?php

$config = array(
	'site' => array(
		'secure_global_head' => array(
			'Home' => 'member/',
			term('timeline') => 'timeline/',
			term('note') => 'note/',
			term('album_image') => 'album/image/',
			term('member.view') => 'member/list/',
			//'Sitemap' => 'sitemap/',
			//'About' => 'about/',
		),
		'insecure_global_head' => array(
			term('member.view') => 'member/list/',
			term('timeline') => 'timeline/',
			term('note') => 'note/',
			term('album_image') => 'album/image/',
			//'Sitemap' => 'sitemap/',
			//'About' => 'about/',
		),
		'secure_user_dropdown' => array(
			term('page.myhome') => 'member/',
			term('profile') => 'member/profile/',
			'Settings' => 'member/setting/',
			'Sign out' => 'auth/logout/',
		),
		'global_side' => array(
			'Top' => '/',
			term('timeline') => 'timeline/',
			term('note') => 'note/',
			term('album_image') => 'album/image/',
			term('member.view') => 'member/list/',
			'Sitemap' => 'sitemap/',
			'About' => 'about/',
			'Contact' => 'contact/',
		),
		'secure_side' => array(
			term('page.myhome') => 'member/',
			term('profile') => 'member/profile/',
			term('timeline') => 'timeline/member/',
			term('note') => 'note/member/',
			term('album') => 'album/member/',
			'Settings' => 'member/setting/',
			'Sign out' => 'auth/logout/',
		),
	),
);

return Site_Util::merge_module_configs($config, 'navigation');

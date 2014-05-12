<?php

$config = array(
	'site' => array(
		'secure_global_head' => array(
			'page.myhome' => 'member/',
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
			'page.myhome' => 'member/',
			'profile' => 'member/profile/',
			'site.setting' => 'member/setting/',
			'site.logout' => 'auth/logout/',
		),
		'global_side' => array(
			'page.top' => '/',
			term('timeline') => 'timeline/',
			term('note') => 'note/',
			term('album_image') => 'album/image/',
			term('member.view') => 'member/list/',
			'Sitemap' => 'sitemap/',
			'About' => 'about/',
			'Contact' => 'contact/',
		),
		'secure_side' => array(
			'page.myhome' => 'member/',
			'profile' => 'member/profile/',
			term('timeline') => 'timeline/member/',
			term('note') => 'note/member/',
			term('album') => 'album/member/',
			'site.setting' => 'member/setting/',
			'site.logout' => 'auth/logout/',
		),
	),
);

return Site_Util::merge_module_configs($config, 'navigation');

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
			'About' => 'site/about',
			//'Sitemap' => 'site/sitemap',
			//'Contact' => 'site/contact',
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
		'global_footer' => array(
			'About' => 'site/about',
			term('site.term') => 'site/term',
			term('site.privacy_policy') => 'site/privacy_policy',
			//'Contact' => 'site/contact',
		),
	),
);

return Site_Util::merge_module_configs($config, 'navigation');

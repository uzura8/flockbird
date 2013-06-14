<?php

return array(
	'site' => array(
		'secure_global_head' => array(
			'Top' => '/',
			'Home' => 'member/',
			Config::get('note.term.note') => 'note/',
			Config::get('album.term.album') => 'album/',
			'Sitemap' => 'sitemap/',
			'About' => 'about/',
			'Contact' => 'contact/',
		),
		'insecure_global_head' => array(
			'Top' => '/',
			Config::get('note.term.note') => 'note/',
			Config::get('album.term.album') => 'album/',
			'Sitemap' => 'sitemap/',
			'About' => 'about/',
			'Contact' => 'contact/',
		),
		'secure_user_dropdown' => array(
			Config::get('site.term.myhome') => 'member/',
			Config::get('site.term.profile') => 'member/profile/',
			'Settings' => 'member/setting/',
			'Sign out' => 'site/logout/',
		),
		'insecure_user_dropdown' => array(
			'Sign in' => 'site/login/',
			'Sign up' => 'member/signup/',
		),
		'global_side' => array(
			'Top' => '/',
			Config::get('note.term.note') => 'note/',
			Config::get('album.term.album') => 'album/',
			'Sitemap' => 'sitemap/',
			'About' => 'about/',
			'Contact' => 'contact/',
		),
		'secure_side' => array(
			Config::get('site.term.myhome') => 'member/',
			Config::get('site.term.profile') => 'member/profile/',
			Config::get('note.term.note') => 'member/note/',
			Config::get('album.term.album') => 'album/member/',
			'Settings' => 'member/setting/',
			'Sign out' => 'site/logout/',
		),
	),
	'admin' => array(
		'secure_global_head' => array(
			'Top' => 'admin/',
			'Member' => array(
				'Member list' => 'admin/member',
			),
			'Admin settings' => array(
				'Account management' => 'admin/setting/account',
				'Change password' => 'admin/setting/change_password',
			),
		),
		'insecure_global_head' => array(
			'Top' => 'admin/',
		),
		'secure_user_dropdown' => array(
			'Sign out' => 'admin/logout',
		),
		'insecure_user_dropdown' => array(
			'Sign in' => 'admin/login',
		),
		'global_side' => array(
			'Top' => 'admin/',
		),
		'secure_side' => array(
			'Member' => 'admin/member',
			'Settings' => 'admin/setting',
		),
	),
);

<?php

return array(
	'global_head' => array(
		'Top' => '/',
		'Home' => 'member/',
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
		Config::get('album.term.album') => 'member/album/',
		'Settings' => 'member/setting/',
		'Sign out' => 'site/logout/',
	),
);

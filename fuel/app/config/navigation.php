<?php

$config = array(
	'site' => array(
		'secure_global_head' => array(
			'page.myhome' => 'member/',
			'timeline' => 'timeline/',
			'thread' => 'thread/',
			'note' => 'note/',
			'album_image' => 'album/image/',
			'member.view' => 'member/list/',
			//'Sitemap' => 'sitemap/',
			//'About' => 'about/',
		),
		'insecure_global_head' => array(
			'timeline' => 'timeline/',
			'thread' => 'thread/',
			'note' => 'note/',
			'album_image' => 'album/image/',
			'member.view' => 'member/list/',
			//'Sitemap' => 'sitemap/',
			//'About' => 'about/',
		),
		'secure_user_dropdown' => array(
			'page.myhome' => 'member/',
			'page.mypage' => 'member/mypage/',
			'form.invite_friend' => 'member/invite/',
			'site.setting' => 'member/setting/',
			'contact.view' => 'contact',
			'site.logout' => 'auth/logout/',
		),
		'global_side' => array(
			'page.top' => '/',
			'timeline' => 'timeline/',
			'thread' => 'thread/',
			'note' => 'note/',
			'album_image' => 'album/image/',
			'member.view' => 'member/list/',
			'news.view' => 'news/list/',
			'About' => 'site/about',
		),
		'secure_side' => array(
			'page.myhome' => 'member/',
			'page.mypage' => 'member/mypage/',
			'timeline' => 'timeline/member/',
			'note' => 'note/member/',
			'album' => 'album/member/',
			'site.setting' => 'member/setting/',
			'contact.view' => 'contact',
			'site.logout' => 'auth/logout/',
		),
		'global_footer' => array(
			term('news.view') => 'news/list/',
			'このサイトについて' => 'site/about',
			term('site.term') => 'site/term',
			term('site.privacy_policy') => 'site/privacy_policy',
		),
	),
);

if (!conf('base.isUserInvite'))
{
	unset($config['secure_user_dropdown']['form.invite_friend']);
}

return Site_Config::merge_module_configs($config, 'navigation');

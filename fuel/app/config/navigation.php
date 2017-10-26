<?php

$config = array(
	'site' => array(
		'secure_global_head' => array(
			'page.myhome' => 'member/',
			//'timeline.plural' => 'timeline/',
			//'thread.view' => 'thread/',
			//'note.plural' => 'note/',
			//'album.image.plural' => 'album/image/',
			'member.view' => 'member/list/',
			//'Sitemap' => 'sitemap/',
			//'site.about' => 'site/about',
		),
		'insecure_global_head' => array(
			//'timeline.plural' => 'timeline/',
			//'thread.view' => 'thread/',
			//'note.plural' => 'note/',
			//'album.image.plural' => 'album/image/',
			'member.view' => 'member/list/',
			//'Sitemap' => 'sitemap/',
			//'site.about' => 'site/about',
		),
		'secure_user_dropdown' => array(
			'page.myhome' => 'member/',
			'page.mypage' => 'member/me/',
			'form.invite_friend' => 'member/invite/',
			'site.setting' => 'member/setting/',
			'contact.view' => 'contact',
			'site.logout' => 'auth/logout/',
		),
		'global_side' => array(
			'page.top' => '/',
			//'timeline.plural' => 'timeline/',
			//'thread.view' => 'thread/',
			//'note.plural' => 'note/',
			//'album.image.plural' => 'album/image/',
			'member.view' => 'member/list/',
			//'news.view' => 'news/list/',
			'site.about' => 'site/about',
		),
		'secure_side' => array(
			'page.myhome' => 'member/',
			'page.mypage' => 'member/me/',
			//'timeline.plural' => 'timeline/member/',
			//'note.plural' => 'note/member/',
			//'album.plural' => 'album/member/',
			'site.setting' => 'member/setting/',
			'contact.view' => 'contact',
			'site.logout' => 'auth/logout/',
		),
		'global_footer' => array(
			//'news.view' => 'news/list/',
			'site.about' => 'site/about',
			'site.term' => 'site/term',
			'site.privacy_policy' => 'site/privacy_policy',
		),
	),
);

if (!conf('base.isUserInvite'))
{
	unset($config['secure_user_dropdown']['form.invite_friend']);
}

return Site_Config::merge_module_configs($config, 'navigation');

<?php
if (is_enabled('news') && is_null(\Config::get('news')))
{
	\Config::load('news::news', 'news');
}

$configs = array(
	'admin' => array(
		'secure_global_head' => array(
			term('site.view', 'site.management') => array(
				term('member.view', 'site.list') => 'admin/member/',
				term('profile', 'site.setting') => 'admin/profile/',
				term('message.view', 'site.list') => 'admin/message/',
			),
			term('site.content', 'site.management') => array(
				term('news.view', 'site.list') => 'admin/news/',
				term('news.view', 'form.create') => conf('image.isInsertBody', 'news') ? array(
					'href' => 'admin/news/create_instantly',
					'method' => 'POST',
					'attr' => array(
						'class' => 'js-simplePost',
						'data-msg' => term('news.view').'を'.term('form.create').'します。よろしいですか？',
					),
				) : 'admin/news/create',
				term('news.category.view') => 'admin/news/category/',
				term('content.page', 'site.management') => 'admin/content/page/',
				term('site.image', 'site.management') => 'admin/content/image/',
				term('site.mail', 'site.template', 'site.management') => 'admin/content/template/mail/',
			),
			term('admin.view', 'page.view', 'site.setting') => array(
				term('admin.account.view', 'site.management') => 'admin/account/',
			),
			term('site.view') => FBD_SITE_URL ?: '/',
		),
		'insecure_global_head' => array(
			term('site.view') => FBD_SITE_URL ?: '/',
		),
		'secure_user_dropdown' => array(
			'site.setting' => 'admin/setting/',
			'site.logout' => 'admin/logout',
		),
		'insecure_user_dropdown' => array(
			'site.login' => 'admin/login',
		),
		'global_side' => array(
		),
		'insecure_side' => array(
			term('admin.view', 'page.view', 'page.top') => 'admin/',
			term('site.view') => FBD_SITE_URL ?: '/',
		),
		'secure_side_admin' => array(
			term('admin.view', 'page.view', 'page.top') => 'admin/',
			term('member.view', 'site.management') => 'admin/member/',
			term('profile', 'site.setting') => 'admin/profile/',
			term('message.view', 'site.list') => 'admin/message/',
			term('site.content', 'site.management') => 'admin/news/',
			term('admin.view', 'page.view', 'site.setting') => 'admin/account/',
			term('site.view') => FBD_SITE_URL ?: '/',
		),
		'secure_side_user' => array(
			'site.setting' => 'admin/setting/',
			'site.logout' => 'admin/logout',
		),
	),
);

return $configs;

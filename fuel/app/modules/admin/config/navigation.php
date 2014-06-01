<?php

return array(
	'admin' => array(
		'secure_global_head' => array(
			term('member.view', 'site.management') => array(
				term('member.view', 'site.list') => 'admin/member/',
			),
			term('news.view', 'site.management') => array(
				term('news.view', 'site.list') => 'admin/news/',
				term('news.category.view') => 'admin/news/category/',
			),
			term('site.view', 'site.management') => array(
				term('profile', 'site.setting') => 'admin/profile/',
			),
			term('admin.view', 'page.view', 'site.setting') => array(
				term('admin.account.view', 'site.management') => 'admin/account/',
			),
			term('site.view') => PRJ_SITE_URL ?: '/',
		),
		'insecure_global_head' => array(
			term('site.view') => PRJ_SITE_URL ?: '/',
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
			term('site.view') => PRJ_SITE_URL ?: '/',
		),
		'secure_side_admin' => array(
			term('admin.view', 'page.view', 'page.top') => 'admin/',
			term('member.view', 'site.management') => 'admin/member/',
			term('news.view', 'site.management') => 'admin/news/',
			term('profile', 'site.setting') => 'admin/profile/',
			term('admin.view', 'page.view', 'site.setting') => 'admin/account/',
			term('site.view') => PRJ_SITE_URL ?: '/',
		),
		'secure_side_user' => array(
			'site.setting' => 'admin/setting/',
			'site.logout' => 'admin/logout',
		),
	),
);

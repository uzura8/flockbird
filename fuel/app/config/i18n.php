<?php

return array(
	'isEnabled' => true,
	'defaultLang' => 'ja',
	'lang' => array(
		'default' => 'ja',
		'options' => array(
			'en' => 'English',
			'ja' => '日本語',
		),
		'countryLang' => array(
			'US' => 'en',
			'GB' => 'en',
			'IN' => 'en',
			'JP' => 'ja',
		),
		'files' => array(
			'site',
			'message',
			'message::message',
		),
	),
	'country' => array(
		'default' => 'JP',
		'options' => array(
			'US' => 'United States',
			'GB' => 'United Kingdom',
			'IN' => 'India',
			'JP' => '日本',
		),
	),
	'locale' => array(
		'options' => array(
			'en_US',
			'en_GB',
			'en_IN',
			'ja_JP',
		),
		'default' => 'ja_JP',
		'defaults' => array(
			'en' => 'en_US',
			'ja' => 'ja_JP',
		),
	),
	'date' => array(
		'defaultFormat' => 'us',
		'localeFormat' => array(
			'en_US' => 'us',
			'en_GB' => 'eu',
			'en_IN' => 'eu',
			'ja_JP' => 'ja',
		),
		'isForceDispGMT' => true,
	),
	'vendor' => array(
		'moment_js' => array(
			'locales' => array(
				'en_US' => 'en',
				'en_GB' => 'en-gb',
				'en_IN' => 'en-gb',
				'ja_JP' => 'ja',
			),
		),
	),
	'adminDefaultLang' => 'ja',
);


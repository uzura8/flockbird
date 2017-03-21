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
			'notice::notice',
			'message::message',
			'contact::contact',
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
		'isForceDispGMT' => false,
		'isDispTimezone' => false,
		'defaultFormat' => 'us',
		'localeFormat' => array(
			'en_US' => 'us',
			'en_GB' => 'eu',
			'en_IN' => 'eu',
			'ja_JP' => 'ja',
		),
	),
	'timezone' => array(
		'isEnabled' => false,
		'default' => 'Asia/Tokyo',
		'options' => array(
			'America/Adak' => '(GMT-10:00) Hawaii',
			'America/Anchorage' => '(GMT-09:00) Alaska',
			'America/Los_Angeles' => '(GMT-08:00) Pacific Time (US & Canada)',
			'America/Denver' => '(GMT-07:00) Mountain Time (US & Canada)',
			'America/Chicago' => '(GMT-06:00) Central Time (US & Canada)',
			'America/New_York' => '(GMT-05:00) Eastern Time (US & Canada)',
			'Europe/London' => '(GMT) London',
			'Asia/Calcutta' => '(GMT+05:30) Kolkata',
			'Asia/Tokyo' => '(GMT+09:00) Tokyo',
		),
		'countryTimezone' => array(
			'US' => array(
				'default' => 'America/New_York',
				'options' => array(
					'America/Adak',
					'America/Anchorage',
					'America/Los_Angeles',
					'America/Denver',
					'America/Chicago',
					'America/New_York',
					'Europe/London',
					'Asia/Tokyo',
				),
			),
			'GB' => array(
				'default' => 'Europe/London',
				'options' => array(
					'Europe/London',
				),
			),
			'IN' => array(
				'default' => 'Asia/Calcutta',
				'options' => array(
					'Europe/London',
				),
			),
			'JP' => array(
				'default' => 'Asia/Tokyo',
				'options' => array(
					'Europe/Tokyo',
				),
			),
		),
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


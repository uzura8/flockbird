<?php
return array(
	'contact' => array(
		'view' => 'お問い合わせ',
		'fields' => array(
			'default' => array(
				'body' => 'お問い合せ本文',
			),
			'pre' => array(
				'category' => array(
					'label' => 'お問い合せ内容',
					'options' => array(
						'0' => '選択してください',
						'1' => '使い方について',
						'2' => 'その他',
					),
				),
			),
		),
	),
	'report' => array(
		'view' => '通報',
		'fields' => array(
			'default' => array(
				'body' => '自由記入',
			),
			'pre' => array(
				'category' => array(
					'label' => '通報内容',
					'options' => array(
						'0' => '選択してください',
						'1' => '不快な情報を投稿している',
						'2' => '個人情報を投稿してる',
						'3' => 'スパムである',
						'4' => 'その他',
					),
				),
			),
		),
	),
	'form' => array(
		'post_report' => '通報する',
	),
);


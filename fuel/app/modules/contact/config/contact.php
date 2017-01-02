<?php
return array(
	'contact' => array(
		'isEnabled' => true,
		'fields' => array(
			'default' => array(
				'body' => array(
					'label' => t('contact.fields.default.body'),
					'attr' => array(
						'type' => 'textarea',
						'rows' => 10,
						'placeholder' => __('form_placeholder_text_enter_characters_within', array('num' => 1000)),
					),
					'rules' => array(
						array('required'),
						array('max_length', 1000),
					),
				),
			),
			'pre' => array(
				'category' => array(
					'label' => t('contact.fields.pre.category.label'),
					'attr' => array(
						'type' => 'select',
						'options' => array(
							'' => t('contact.fields.pre.category.options.0'),
							'使い方について' => t('contact.fields.pre.category.options.1'),
							'その他' => t('contact.fields.pre.category.options.2'),
						),
					),
					'rules' => array(
						array('required'),
					),
				),
			),
			'post' => array(
			),
		),
	),
	'report' => array(
		'isEnabled' => true,
		'types' => array(
			'message' => 'メッセージ',
			'message_member' => 'メンバー間メッセージ',
			'member' => 'メンバーのページ',
			'member_profile' => 'メンバープロフィール',
			'note' => '日記',
			//'note_comment' => '日記コメント',
			'timeline' => 'タイムライン',
			//'timeline_comment' => 'タイムラインコメント',
			'album' => 'アルバム',
			//'album_image' => 'アルバム写真',
			//'album_image_comment' => 'アルバム写真コメント',
			'thread' => 'スレッド',
			//'thread_comment' => 'スレッドコメント',
		),
		'fields' => array(
			'default' => array(
				'body' => array(
					'label' => t('report.fields.default.body'),
					'attr' => array(
						'type' => 'textarea',
						'rows' => 5,
						'placeholder' => __('form_placeholder_text_enter_characters_within', array('num' => 300)),
					),
					'rules' => array(
						array('trim'),
						array('max_length', 300),
					),
				),
			),
			'pre' => array(
				'category' => array(
					'label' => t('report.fields.pre.category.label'),
					'attr' => array(
						'type' => 'select',
						'options' => array(
							'' => t('report.fields.pre.category.options.0'),
							'不快な情報を投稿している' => t('report.fields.pre.category.options.1'),
							'個人情報を投稿してる' => t('report.fields.pre.category.options.2'),
							'スパムである' => t('report.fields.pre.category.options.3'),
							'その他' => t('report.fields.pre.category.options.4'),
						),
					),
					'rules' => array(
						array('required'),
					),
				),
			),
			'post' => array(
			),
		),
	),
);

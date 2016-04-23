<?php
return array(
	'contact' => array(
		'isEnabled' => true,
		'fields' => array(
			'default' => array(
				'body' => array(
					'label' => 'お問い合せ本文',
					'attr' => array(
						'type' => 'textarea',
						'rows' => 10,
						'placeholder' => '1000文字以内で入力してください。',
					),
					'rules' => array(
						array('required'),
						array('max_length', 1000),
					),
				),
			),
			'pre' => array(
				'category' => array(
					'label' => 'お問い合せ内容',
					'attr' => array(
						'type' => 'select',
						'options' => array(
							'' => '選択してください',
							'使い方について' => '使い方について',
							'その他' => 'その他',
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
					'label' => '自由記入',
					'attr' => array(
						'type' => 'textarea',
						'rows' => 5,
						'placeholder' => '300文字以内で入力してください。',
					),
					'rules' => array(
						array('trim'),
						array('max_length', 300),
					),
				),
			),
			'pre' => array(
				'category' => array(
					'label' => '通報分類',
					'attr' => array(
						'type' => 'select',
						'options' => array(
							'' => '選択してください',
							'不快な情報を投稿している' => '不快な情報を投稿している',
							'個人情報を投稿してる' => '個人情報を投稿してる',
							'スパムである' => 'スパムである',
							'その他' => 'その他',
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

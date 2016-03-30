<?php
return array(
	'contact' => array(
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
							// name => value,
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
);

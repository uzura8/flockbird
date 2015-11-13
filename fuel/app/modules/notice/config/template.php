<?php
return array(
	'mail' => array(
		'site' => array(
			'notice' => array(
				'view' =>'新着お知らせメール',
				'format' =>'twig',
				'title' => '【{{ site_name }}】新着お知らせメール',
				'body' => array(
					'default' => array(
						'file' => 'notice::mail/notice',
					),
				),
				'variables' => array(
					'to_name' => 'メンバー名',
					'content' => 'お知らせの内容',
				),
			),
		),
	),
);

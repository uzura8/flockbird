<?php
return array(
	'mail' => array(
		'site' => array(
			'message' => array(
				'view' =>'新着メッセージお知らせメール',
				'format' =>'twig',
				'title' => '【{{ site_name }}】新着メッセージお知らせメール',
				'body' => array(
					'default' => array(
						'file' => 'message::mail/notice',
					),
				),
				'variables' => array(
					'to_name' => 'メンバー名',
					'content' => 'メッセージの内容',
				),
			),
		),
	),
);

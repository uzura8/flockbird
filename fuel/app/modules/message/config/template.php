<?php
return array(
	'mail' => array(
		'site' => array(
			'message' => array(
				'view' =>'新着メッセージお知らせメール',
				'format' =>'twig',
				'title' => '{% if subject %}{{ subject }}{% else %}【{{ site_name }}】新着メッセージお知らせメール{% endif %}',
				'body' => array(
					'default' => array(
						'file' => 'message::mail/notice',
					),
				),
				'variables' => array(
					'to_name' => 'メンバー名',
					'content' => 'メッセージの内容',
					'subject' => 'メッセージの件名',
					'body' => 'メッセージの本文',
				),
			),
		),
	),
);

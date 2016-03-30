<?php
return array(
	'mail' => array(
		'site' => array(
			'contactToMember' => array(
				'view' =>'お問い合わせ受付メール',
				'format' =>'twig',
				'title' => 'お問い合わせを受け付けました',
				'body' => array(
					'default' => array(
						'file' => 'contact::mail/contact_to_member',
					),
				),
				'variables' => array(
					'contact_body' => 'お問い合わせ本文',
					'contact_category' => 'お問い合わせ内容',
				),
			),
		),
	),
);

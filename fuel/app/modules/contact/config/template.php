<?php
return array(
	'mail' => array(
		'site' => array(
			'contactToMember' => array(
				'view' =>'お問い合わせ受付メール',
				'variables' => array(
					'contact_body' => 'お問い合わせ本文',
					'contact_category' => 'お問い合わせ内容',
				),
			),
			'report' => array(
				'view' =>'通報メール',
				'variables' => array(
					'report_category' => '通報分類',
					'report_body' => '通報コメント',
					'content_type' => 'コンテンツ分類',
					'content_url' => '通報されたURL',
					'content_body' => '通報された書き込み内容',
					'member_id_to' => '通報されたメンバーのID',
					'member_name_to' => '通報されたメンバーのニックネーム',
					'member_to_admin_page_url' => '通報されたメンバーの管理画面URL',
					'member_id_from' => '通報したメンバーのID',
					'member_name_from' => '通報したメンバーのニックネーム',
				),
			),
		),
	),
);

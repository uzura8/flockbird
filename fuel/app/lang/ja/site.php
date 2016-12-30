<?phP

return array(
	// Auth
	'site_message_login_complete' => 'ログインしました。',
	'site_message_login_failed' => 'ログインに失敗しました。',
	'site_message_logout_complete' => 'ログアウトしました。',
	'site_message_account_locked' => 'アカウントがロックされています。',
	// member register
	'member_message_signup' => 'ログインに使用するメールアドレスとパスワードを入力してください。',
	'member_message_signup_complete' => '仮登録が完了しました。受信したメール内に記載された URL より本登録を完了してください。',
	'member_registration' => term('member.view', 'site.registration'),
	'member_registration_input_help_name' => '記号・空白は使用できません',
	'agree_and_register' => '同意して登録する',
	'invite_form_email_placeholder' => sprintf('%sの%s', term('form.do_invite', 'common.friend'), term('site.email')),
	'invite_form_message_placeholder' => sprintf('%sへの%s', term('common.friend'), term('form.invite', 'common.message', 'form._not_required')),
	'member_address_setting' => '住所設定',
	'member_address_setting_description' => '住所を設定します',
	'member_form_address01_placeholder' => '市区町村・番地',
	'member_form_address02_placeholder' => '建物名など(オプション)',
	'site_title_access_block_settig' => 'アクセスブロック設定',
	'site_lead_access_block_settig' => 'ブロック中メンバーの確認と登録解除を行います。',
	'site_lead_notice_setting' => 'お知らせ受診項目の設定を行います。',
	'additional_info_count_of' => '(:label: :count 件)',
	'registered_count_of' => ':label :count 件 登録済み',
	// member setting
	'member_message_send_confirmation_code_complete' => '確認用メールを送信しました。受信したメール内に記載された確認用コードを入力してください。',
	'member_message_error_invalid_confirmation_code' => '確認用コードが正しくないか、有効期限が過ぎてます。再度確認用メールを送信してください。',
	'member_message_error_change_email_currently_registered' => 'そのメールアドレスは現在登録済みです。',
	// recover account
	'member_title_resend_password' => 'パスワードの再設定',
	'member_message_resend_password_for_input' => 'アカウントに登録したメールアドレスを入力してください。',
	'member_message_resend_password_complete' => 'パスワードのリセット方法をメールで送信しました。',
	'member_title_reset_password' => 'パスワードの再登録',
	// Leave service
	'member_title_leave_service' => 'このサイトを退会する',
	'member_title_confirmation_of_leave_service' => 'サイト退会の最終確認',
	'member_message_confirmation_of_leave_service' => '本当に退会しますか？(退会した場合、サイトへの投稿は全て削除されます)',
	'member_message_leave_complete' => '退会手続きが完了しました。',
	'member_message_leave_reservation_complete' => '退会手続きが完了しました。(登録データは順次削除されます)',
	'member_message_leave_failed' => '退会手続きが失敗しました。',
);


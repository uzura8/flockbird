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
	'member_registration' => 'メンバー登録',
	'member_registration_input_help_name' => '記号・空白は使用できません',
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
	'member_unregistered_email' => 'メールアドレスが未登録です。登録してください。',
	'member_unregistered_profile' => '未登録のプロフィールがあります。登録してください。',
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
	// member profile image
	'member_error_album_image_disable_to_set_as_profile_image' => 'その写真はプロフィール写真に設定できません。',
	// member relation
	'member_message_follow_from_to' => ':subjectが:objectをフォローしました。',
	'member_message_follow' => 'フォローしました。',
	'member_message_cancel_follow' => 'フォローを解除しました。',
	'member_message_accessBlock' => 'アクセスブロックしました。',
	'member_message_cancel_accessblock' => 'アクセスブロックを解除しました。',
	'member_message_error_access_blocked' => 'このリンクは有効期限切れとなっているか、ページの設定により閲覧できなくなっている可能性があります。',
	// Public flag
	'public_flag_expand_confirm' => '公開範囲が広がります。実行しますか？',
	'public_flag_confirm_change_with_children_of' => ':labelの公開範囲も変更しますか？',
	// Like
	'liked' => 'イイねしました。',
	'canceled_like' => 'イイねを取り消しました。',
	'failed_to_like' => 'イイねに失敗しました。',
	'nobody_liked' => 'イイねしているメンバーはいません。',
	// Location
	'maps_message_leave_failed' => '位置情報の取得に失敗しました。',
	'maps_message_please_set_location' => '位置を指定してください。',
);


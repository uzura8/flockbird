<?php
namespace Notice;

class Form_MemberConfig extends \Form_MemberConfig
{
	public static function get_name($item)
	{
		return sprintf('notice_%s', $item);
	}

	public static function get_validation_notice($member_id)
	{
		$val = \Validation::forge('member_config_notice');

		if (Site_Util::check_enabled_notice_type('comment'))
		{
			$name = self::get_name('comment');
			$value = self::get_value($member_id, $name, parent::get_default_value($name, 1));
			$label = sprintf('自分の%sに%sされた時', term('form.post'), term('form.comment'));
			$options = self::get_options_recieve();
			$val->add($name, $label, array('type' => 'radio', 'options' => $options, 'value' => $value))
					->add_rule('valid_string', 'numeric', 'required')
					->add_rule('required')
					->add_rule('in_array', array_keys($options));
		}

		if (Site_Util::check_enabled_notice_type('like'))
		{
			$name = self::get_name('like');
			$value = self::get_value($member_id, $name, parent::get_default_value($name, 1));
			$label = sprintf('自分の%sに%sされた時', term('form.post'), term('form.like'));
			$options = self::get_options_recieve();
			$val->add($name, $label, array('type' => 'radio', 'options' => $options, 'value' => $value))
					->add_rule('valid_string', 'numeric', 'required')
					->add_rule('required')
					->add_rule('in_array', array_keys($options));
		}

		if (Site_Util::check_enabled_notice_type('follow'))
		{
			$name = self::get_name('follow');
			$value = self::get_value($member_id, $name, parent::get_default_value($name, 1));
			$label = sprintf('自分が%sされた時', term('follow'));
			$options = self::get_options_recieve();
			$val->add($name, $label, array('type' => 'radio', 'options' => $options, 'value' => $value))
					->add_rule('valid_string', 'numeric', 'required')
					->add_rule('required')
					->add_rule('in_array', array_keys($options));
		}

		$name = Site_Util::get_member_config_name_for_watch_content('comment');
		$value = self::get_value($member_id, $name, parent::get_default_value($name, 1));
		$label = sprintf('自分が%sした%s', term('form.comment'), term('form.post'));
		$options = self::get_options_watch();
		$val->add($name, $label, array('type' => 'radio', 'options' => $options, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('required')
				->add_rule('in_array', array_keys($options));

		$name = Site_Util::get_member_config_name_for_watch_content('like');
		$value = self::get_value($member_id, $name, parent::get_default_value($name, 1));
		$label = sprintf('自分が%sした%s', term('form.like'), term('form.post'));
		$options = self::get_options_watch();
		$val->add($name, $label, array('type' => 'radio', 'options' => $options, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('required')
				->add_rule('in_array', array_keys($options));

		$member_auth = null;
		if (conf('noticeMail.isEnabled', 'notice'))
		{
			$name = self::get_name('noticeMailMode');
			if ($value = self::get_value($member_id, $name, parent::get_default_value($name, 1)))
			{
				if (!$member_auth) $member_auth = \Model_MemberAuth::get_one4member_id($member_id);
				if (empty($member_auth->email)) $value = 0;
			}
			$label = term('notice', 'site.mail');
			$options = self::get_options_recieve_mail();
			$val->add($name, $label, array('type' => 'radio', 'options' => $options, 'value' => $value))
					->add_rule('valid_string', 'numeric', 'required')
					->add_rule('required')
					->add_rule('in_array', array_keys($options));
		}

		if (is_enabled('message') && conf('noticeMail.isEnabled', 'message'))
		{
			$name = self::get_name('messageMailMode');
			if ($value = self::get_value($member_id, $name, parent::get_default_value($name, 1)))
			{
				if (!$member_auth) $member_auth = \Model_MemberAuth::get_one4member_id($member_id);
				if (empty($member_auth->email)) $value = 0;
			}
			$label = term('common.newArrival', 'message.view', 'notice', 'site.mail');
			$options = self::get_options_recieve_mail();
			$val->add($name, $label, array('type' => 'radio', 'options' => $options, 'value' => $value))
					->add_rule('valid_string', 'numeric', 'required')
					->add_rule('required')
					->add_rule('in_array', array_keys($options));
		}

		return $val;
	}

	public static function get_options_recieve($value = null, $is_simple = false)
	{
		$options = array(
			'1' => $is_simple ? term('symbol.bool.true') : term('form.recieve'),
			'0' => $is_simple ? term('symbol.bool.false') : term('form.unrecieve'),
		);

		if (!is_null($value) && isset($options[$value])) return $options[$value];

		return $options;
	}

	public static function get_options_watch($value = null, $is_simple = false)
	{
		$options = array(
			'1' => $is_simple ? term('symbol.bool.true') : term('form.do_watch'),
			'0' => $is_simple ? term('symbol.bool.false') : term('form.watch').'しない',
		);

		if (!is_null($value) && isset($options[$value])) return $options[$value];

		return $options;
	}

	public static function get_options_recieve_mail($value = null, $is_simple = false)
	{
		$options = array(
			'1' => $is_simple ? term('symbol.bool.true') : term('form.recieve_mail'),
			'0' => $is_simple ? term('symbol.bool.false') : term('form.unrecieve_mail'),
		);

		if (!is_null($value) && isset($options[$value])) return $options[$value];

		return $options;
	}
}

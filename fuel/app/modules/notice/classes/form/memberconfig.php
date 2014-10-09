<?php
namespace Notice;

class Form_MemberConfig extends \Form_MemberConfig
{
	private static function get_name($item)
	{
		return sprintf('notice_%s', $item);
	}

	public static function get_validation_notice($member_id)
	{
		$val = \Validation::forge('member_config_notice');

		$name = self::get_name('comment');
		$value = self::get_value($member_id, $name, parent::get_default_value($name, 1));
		$label = sprintf('自分の%sに%sされた時', term('form.post'), term('form.comment'));
		$options = self::get_options();
		$val->add($name, $label, array('type' => 'radio', 'options' => $options, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('required')
				->add_rule('in_array', array_keys($options));

		$name = self::get_name('like');
		$value = self::get_value($member_id, $name, parent::get_default_value($name, 1));
		$label = sprintf('自分の%sに%sされた時', term('form.post'), term('form.like'));
		$options = self::get_options();
		$val->add($name, $label, array('type' => 'radio', 'options' => $options, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('required')
				->add_rule('in_array', array_keys($options));

		return $val;
	}

	public static function get_options($value = null, $is_simple = false)
	{
		$options = array(
			'1' => $is_simple ? term('symbol.bool.true') : term('form.recieve'),
			'0' => $is_simple ? term('symbol.bool.false') : term('form.unrecieve'),
		);

		if (!is_null($value) && isset($options[$value])) return $options[$value];

		return $options;
	}
}

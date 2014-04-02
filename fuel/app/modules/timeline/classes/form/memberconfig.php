<?php
namespace Timeline;

class Form_MemberConfig extends \Form_MemberConfig
{

	private static function get_name($item)
	{
		return sprintf('timeline_%s', $item);
	}

	public static function get_validation_viewType($member_id)
	{
		$val = \Validation::forge('member_config_timeline_viewType');

		$name = self::get_name('viewType');
		$value = self::get_value($member_id, $name, parent::get_default_value('timeline_viewType', 0));
		$options = self::get_viewType_options();
		$val->add($name, sprintf('%sの%s', term('myhome'), term(array('timeline', 'site.view'))), array('type' => 'radio', 'options' => $options, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('required')
				->add_rule('in_array', array_keys($options));

		return $val;
	}

	public static function get_viewType_options($value = null)
	{
		$options = array('0' => sprintf('%s全体の%sを表示する', term('site.site'), term('timeline')));
		if (conf('memberRelation.follow.isEnabled')) $options['1'] = sprintf('%sの%sの%sのみ表示する', term('followed'), term('member.view'), term('timeline'));
		if (conf('memberRelation.friend.isEnabled')) $options['2'] = sprintf('%sの%sのみ表示する', term('friend'), term('timeline'));
		if (conf('memberRelation.follow.isEnabled') && conf('memberRelation.friend.isEnabled'))
		{
			$options['3'] = sprintf('%sの%sと%sの%sを表示する', term('followed'), term('member.view'), term('friend'), term('timeline'));
		}

		if (!is_null($value) && isset($options[$value])) return $options[$value];

		return $options;
	}
}

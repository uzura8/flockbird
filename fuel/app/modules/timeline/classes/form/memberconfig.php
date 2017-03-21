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
		$label = t('common.delimitter.of', array('object' => t('page.myhome'), 'subject' => term('timeline.view', 'site.display')));
		$val->add($name, $label, array('type' => 'radio', 'options' => $options, 'value' => $value))
				->add_rule('valid_string', 'numeric', 'required')
				->add_rule('required')
				->add_rule('in_array', array_keys($options));

		return $val;
	}

	public static function get_viewType_options($value = null, $is_simple = false)
	{
		$item = $is_simple ? t('site.entire') : t('form.do_display_for', array('label' => t('common.delmitter.of', array(
			'subject' => t('timeline.plural'),
			'object' => t('site.entire'),
		))));
		$options = array('0' => $item);

		if (conf('memberRelation.follow.isEnabled'))
		{
			$item = t('common.parts.only_of', array('label' => $is_simple ? t('member.following') : t('common.delmitter.of', array(
				'subject' => t('timeline.plural'),
				'object' => t('member.following'),
			))));
			if (! $is_simple) $item = t('form.do_display_for', array('label' => $item));
			$options['1'] = $item;
		}

		if (conf('memberRelation.friend.isEnabled'))
		{
			$item = t('common.parts.only_of', array('label' => $is_simple ? t('member.following') : t('common.delmitter.of', array(
				'subject' => t('timeline.plural'),
				'object' => t('firiend'),
			))));
			if (! $is_simple) $item = t('form.do_display_for', array('label' => $item));
			$options['2'] = $item;
		}

		if (conf('memberRelation.follow.isEnabled') && conf('memberRelation.friend.isEnabled'))
		{
			$item = term('member.following', 'common.delimitter.normal', t('firiend'));
			if (! $is_simple)
			{
				$item = t('form.do_display_for', array('label' => t('common.delmitter.of', array(
					'subject' => t('timeline.plural'),
					'object' => $item,
				))));
			}
			$options['3'] = $item;
		}

		if (!is_null($value) && isset($options[$value])) return $options[$value];

		return $options;
	}
}

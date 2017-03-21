<?php

class Site_Member_Relation
{
	public static function get_relation_types()
	{
		$accept_types = array();
		$configs = conf('memberRelation');
		foreach ($configs as $type => $values)
		{
			if (empty($values['isEnabled'])) continue;
			$accept_types[] = $type;
		}

		return $accept_types;
	}

	public static function check_member_relation($member_id_from, $member_id_to, $accepted_types = array())
	{
		if (!is_array($accepted_types)) $accepted_types = (array)$accepted_types;
		$relation_types = $accepted_types ?: self::get_relation_types();
		foreach ($relation_types as $relation_type)
		{
			if (Model_MemberRelation::check_relation($relation_type, $member_id_from, $member_id_to))
			{
				return $relation_type;
			}
		}

		return false;
	}

	public static function check_enabled_relation_type($relation_type)
	{
		if (!$relation_type) return false;
		$relation_type = Inflector::camelize($relation_type, true);

		$relation_types = self::get_relation_types();
		if ($relation_type == 'follower' && in_array('follow', $relation_types)) $relation_type = 'follow';
		if (!in_array($relation_type, $relation_types)) return false;
		if (!conf(sprintf('memberRelation.%s.isEnabled', Inflector::camelize($relation_type, true)))) return false;

		return true;
	}

	public static function get_updated_status_info($relation_type, $status, $is_api_response_data = false)
	{
		$relation_type_camelized_upper = Inflector::camelize($relation_type);
		$relation_type_camelized_lower = Inflector::camelize($relation_type, true);
		$attr = array();
		switch ($relation_type)
		{
			case 'follow':
				$icon_term = $status ? 'following' : 'do_follow';
				if ($is_api_response_data)
				{
					$attr = $status ? array('class' => array('add' => 'btn-primary')) : array('class' => array('remove' => 'btn-primary'));
				}
				else
				{
					if ($status) $attr['class'] = 'btn-primary';
				}
				break;
			default :
				$icon_term = sprintf('%sdo_%s', $status ? 'un' : '', $relation_type_camelized_lower);
				break;
		}

		if ($is_api_response_data)
		{
			switch ($relation_type_camelized_lower)
			{
				case 'follow':
				case 'accessBlock':
					$message = __(sprintf('member_message_%s%s', $status ? '' : 'cancel_', $relation_type_camelized_lower));
					break;
				default :
					$message = __(sprintf('message_%s_complete', $status ? 'set' : 'cancel'));
					break;
			}

			return array(
				'is'.$relation_type_camelized_upper => (bool)$status,
				'message' => $message,
				'html' => icon_label($icon_term, 'both', false),
				'attr' => $attr,
			);
		}

		return array(
			'label' => icon_label($icon_term, 'both', false),
			'attr' => $attr,
		);
	}
}


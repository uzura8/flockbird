<?php

class Site_Member_Relation
{
	public static function check_relation_type($relation_type)
	{
		if (!$relation_type) return false;
		if (!in_array($relation_type, array('follow', 'access_block'))) return false;
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
				$icon_term = $status ? 'followed' : 'do_follow';
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
			return array(
				'is'.$relation_type_camelized_upper => (bool)$status,
				'message' => sprintf('%s%s', term($relation_type_camelized_lower), $status ? 'しました。' : 'を解除しました。'),
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


<?php
namespace Timeline;

class Site_Util
{
	public static function get_timeline_type($foreign_table = null, $body = null)
	{
		if (!$foreign_table && $body)
		{
			return 1;// 通常 timeline 投稿(つぶやき)
		}

		return 0;
	}

	public static function check_is_editable($type)
	{
		switch ($type)
		{
			case 1:
				return true;
				break;
		}

		return false;
	}

	public static function get_article_view($timeline_obj)
	{
		//$timeline_data = Model_TimelineData::query()->related('member')->where('timeline_id', $timeline_obj->id)->get_one();
		$timeline_data = Model_TimelineData::find('first', array(
			'where' => array('timeline_id' => $timeline_obj->id),
			'related' => array('member')
		));

		return render('_parts/timeline/article', array(
			'timeline' => $timeline_obj,
			'timeline_data' => $timeline_data,
			'truncate_lines' =>\Config::get('timeline.articles.truncate_lines.body'),
		));
	}
}

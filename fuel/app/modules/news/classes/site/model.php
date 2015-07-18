<?php
namespace News;

class Site_Model
{
	public static function get_list($limit, $page = 1, $is_auth = false, $news_category_id = 0, $news_ids = array())
	{
		$where_conds  = array(
			array('is_published', 1),
			array('published_at', '<', \DB::expr('NOW()')),
		);
		if (!$is_auth) $where_conds[] = array('is_secure', 0);
		if ($news_category_id) $where_conds[] = array('news_category_id', $news_category_id);
		if ($news_ids) $where_conds[] = array('id', 'in', $news_ids);
		$data = Model_News::get_pager_list(array(
			'related'  => array('news_category'),
			'where'    => $where_conds,
			'limit'    => $limit,
			'order_by' => array('published_at' => 'desc'),
		), $page);

		return $data;
	}

	public static function convert_raw_bodys($news_list)
	{
		if (!is_array($news_list)) $news = (array)$news_list;
		if (!$news_list) return $news_list;

		$raw_bodys = array();
		foreach ($news_list as $news)
		{
			$body = $news->body;
			if ($news->format == 2) $body = \Markdown::parse($body);
			$raw_bodys[$news->id] = $body;
		}

		return $raw_bodys;
	}
}

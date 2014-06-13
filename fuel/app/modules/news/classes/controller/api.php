<?php
namespace News;

class Controller_Api extends \Controller_Rest
{

	public function before()
	{
		parent::before();
	}

	/**
	 * News list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function get_list($category_name = null)
	{
		$cols = array(
			'id',
			'news_category_id',
			'title',
			'published_at',
			'slug',
		);
		$query = \DB::select_array($cols)->from('news');
		$query->where('is_published', 1);
		$query->and_where('published_at', '<', \DB::expr('NOW()'));
		$query->order_by('published_at', 'desc');
		$response = $query->execute()->current();

		return $this->response($response);
	}
}

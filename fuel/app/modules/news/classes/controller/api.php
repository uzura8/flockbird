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
	public function get_list()
	{
		$query = Model_News::query()
			->related('news_image')
			->related('news_image.file')
			->where('is_published', 1)
			->where('published_at', '<', \DB::expr('NOW()'))
			->order_by('published_at', 'desc');
		$response = $query->get();

		return $this->response($response);
	}
}

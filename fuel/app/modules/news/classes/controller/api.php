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
			'news.id',
			'news.news_category_id',
			'news.title',
			'news.published_at',
			'news.slug',
			array('news_category.name', 'news_category_name'),
			array('news_category.label', 'news_category_label'),
		);
		$query = \DB::select_array($cols)->from('news')
			->join('news_category', 'LEFT')->on('news_category.id', '=', 'news.news_category_id')
			->where('is_published', 1)
			->and_where('published_at', '<', \DB::expr('NOW()'))
			->order_by('published_at', 'desc');
		$response = $query->execute();

		return $this->response($response);
	}

	/**
	 * News detail
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function get_detail($slug = null)
	{
		$cols = array(
			'news.id',
			'news.news_category_id',
			'news.title',
			'news.body',
			'news.published_at',
			'news.slug',
			array('news_category.name', 'news_category_name'),
			array('news_category.label', 'news_category_label'),
		);
		$query = \DB::select_array($cols)->from('news')
			->join('news_category', 'LEFT')->on('news_category.id', '=', 'news.news_category_id')
			->where('slug', $slug)
			->and_where('is_published', 1);
		$response = $query->execute()->current();

		$cols = array(
			'news_image.name',
			array('file.path', 'file_path'),
			array('file.name', 'file_name'),
			array('file.type', 'file_type'),
		);
		$query = \DB::select_array($cols)->from('news_image')
			->join('file', 'LEFT')->on('file.id', '=', 'news_image.file_id')
			->where('news_id', $response['id']);
		$response['images'] = self::add_file_url($query->execute()->as_array(), 'img');

		$cols = array(
			'news_file.name',
			array('file.path', 'file_path'),
			array('file.name', 'file_name'),
		);
		$query = \DB::select_array($cols)->from('news_file')
			->join('file', 'LEFT')->on('file.id', '=', 'news_file.file_id')
			->where('news_id', $response['id']);
		$response['files'] = self::add_file_url($query->execute()->as_array(), 'file');

		return $this->response($response);
	}

	private static function add_file_url($files, $type)
	{
		if (!$files) return array();
		if (!in_array($type, array('img', 'file'))) throw new \InvalidArgumentException('Second parameter is invalid.');

		$confs = conf('upload.types.'.$type);
		foreach ($files as $key => $file)
		{
			$upload_uri = $confs['root_path']['raw_dir'].$file['file_path'].$file['file_name'];
			$files[$key]['file_url_raw'] = \Uri::create($upload_uri);
			if ($type == 'img')
			{
				$upload_uri = $confs['root_path']['cache_dir'].$confs['types']['nw']['sizes']['thumbnail'].'/'.$file['file_path'].$file['file_name'];
				$files[$key]['file_url_thumbnail'] = \Uri::create($upload_uri);
			}
		}

		return $files;
	}
}

<?php
namespace News;

class Controller_Api extends \Controller_Site_Api
{
	protected $check_not_auth_action = array(
		'get_list',
		'get_detail',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Get news list
	 * 
	 * @access  public
	 * @return  Response
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function get_list($category_name_string = null)
	{
		$this->controller_common_api(function() use($category_name_string)
		{
			$limit = (int)\Input::get('limit') ?: \Config::get('news.viewParams.site.list.limit_max', 100);
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
				->order_by('published_at', 'desc')
				->limit($limit);
			if ($category_name_string)
			{
				$category_names = explode('-', $category_name_string);
				$query = $query->and_where('news_category.name', 'in', $category_names);
			}
			$response = $query->execute();

			$this->set_response_body_api($response);
		});
	}

	/**
	 * Get news detail
	 * 
	 * @access  public
	 * @return  Response
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function get_detail($slug = null)
	{
		$this->controller_common_api(function() use($slug)
		{
			$cols = array(
				'news.id',
				'news.news_category_id',
				'news.title',
				'news.body',
				'news.format',
				'news.published_at',
				'news.slug',
				array('news_category.name', 'news_category_name'),
				array('news_category.label', 'news_category_label'),
			);
			$query = \DB::select_array($cols)->from('news')
				->join('news_category', 'LEFT')->on('news_category.id', '=', 'news.news_category_id')
				->where('slug', $slug)
				->and_where('is_published', 1);
			if (!$response = $query->execute()->current()) throw new \HttpNotFoundException;

			$response['body'] = convert_body_by_format($response['body'], $response['format']);
			unset($response['format']);

			$cols = array(
				'news_image.name',
				array('file.name', 'file_name'),
				array('file.type', 'file_type'),
			);
			$query = \DB::select_array($cols)->from('news_image')
				->join('file', 'LEFT')->on('file.name', '=', 'news_image.file_name')
				->where('news_id', $response['id']);
			$response['images'] = self::add_file_options($query->execute()->as_array(), 'img');

			$cols = array(
				'news_file.name',
				array('file.name', 'file_name'),
				array('file.original_filename', 'file_original_filename'),
				array('file.type', 'file_type'),
			);
			$query = \DB::select_array($cols)->from('news_file')
				->join('file', 'LEFT')->on('file.name', '=', 'news_file.file_name')
				->where('news_id', $response['id']);
			$response['files'] = self::add_file_options($query->execute()->as_array(), 'file');

			$cols = array(
				'uri',
				'label',
			);
			$query = \DB::select_array($cols)->from('news_link')
				->where('news_id', $response['id']);
			$response['links'] = $query->execute()->as_array();

			if (\Config::get('news.form.tags.isEnabled'))
			{
				$cols = array('tag.name');
				$query = \DB::select_array($cols)->from('news_tag')
					->join('tag', 'LEFT')->on('tag.id', '=', 'news_tag.tag_id')
					->where('news_id', $response['id']);
				$response['tags'] = $query->execute()->as_array();
			}

			$this->set_response_body_api($response);
		});
	}

	private static function add_file_options($files, $type)
	{
		if (!$files) return array();
		if (!in_array($type, array('img', 'file'))) throw new \InvalidArgumentException('Second parameter is invalid.');

		$confs = conf('upload.types.'.$type);
		foreach ($files as $key => $file)
		{
			$upload_uri = \Site_Upload::get_uploaded_file_path($file['file_name'], 'raw', $type, false, true);
			$files[$key]['file_url_raw'] = \Uri::create($upload_uri);
			if ($type == 'img')
			{
				$upload_uri = \Site_Upload::get_uploaded_file_path($file['file_name'], 'thumbnail', $type, false, true);
				$files[$key]['file_url_thumbnail'] = \Uri::create($upload_uri);
			}

			if ($type == 'file')
			{
				$files[$key]['file_type_view'] = self::get_file_type_view($file['file_type']);
			}
		}

		return $files;
	}

	private static function get_file_type_view($file_type)
	{
		switch ($file_type)
		{
			case 'image/jpeg':
			case 'image/jpeg':
			case 'image/png':
			case 'image/bmp':
				return '画像ファイル';
			case 'text/plain':
				return 'テキストファイル';
			case 'text/csv':
				return 'CSVファイル';
			case 'application/pdf':
				return 'PDFファイル';
			case 'application/msword':
			case 'application/msword':
				return 'ワードファイル';
			case 'application/vnd.ms-excel':
			case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
				return '・エクセルファイル';
			case 'application/vnd.ms-powerpoint':
			case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
				return 'パワーポイントファイル';
			default :
		}

		return '';
	}
}

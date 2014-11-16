<?php
namespace Admin;

class Controller_Content_Image_Api extends Controller_Api
{
	public function before()
	{
		parent::before();
	}

	/**
	 * Api list
	 * 
	 * @access  public
	 * @return  Response (html)
	 */
	public function get_list()
	{
		$response  = '';
		try
		{
			$this->check_response_format(array('html', 'json'));
			list($limit, $page) = $this->common_get_pager_list_params(\Config::get('admin.articles.images.limit'), \Config::get('admin.articles.images.limit_max'));
			$params = array('order_by' => array('id' => 'desc'));
			if ($limit) $params['limit'] = $limit;
			$data = \Model_SiteImage::get_pager_list($params, $page);

			if ($this->format == 'html')
			{
				$response = \View::forge('content/image/_parts/list', $data);
				$status_code = 200;

				return \Response::forge($response, $status_code);
			}

			$list_array = array();
			foreach ($data['list'] as $key => $obj)
			{
				$row = $obj->to_array();
				$list_array[] = $row;
			}
			// json response
			$response = $list_array;
			$status_code = 200;
		}
		catch(\HttpNotFoundException $e)
		{
			$status_code = 404;
		}
		catch(\FuelException $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}

	/**
	 * Album image delete
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_delete($id = null)
	{
		$response = array('status' => 0);
		try
		{
			\Util_security::check_csrf();

			$id = (int)$id;
			if (\Input::post('id')) $id = (int)\Input::post('id');

			\DB::start_transaction();
			$site_image = \Model_SiteImage::check_authority($id);
			$site_image->delete();
			\DB::commit_transaction();

			$response['status'] = 1;
			$status_code = 200;
		}
		catch(\HttpNotFoundException $e)
		{
			$status_code = 404;
		}
		catch(\HttpForbiddenException $e)
		{
			$status_code = 403;
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}

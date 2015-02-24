<?php
namespace Admin;

class Controller_News_Image_Api extends Controller_Api
{
	protected $check_not_auth_action = array();

	public function before()
	{
		parent::before();
	}

	/**
	 * Api get_list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function get_list($parent_id = null)
	{
		$response = '0';
		try
		{
			$this->check_response_format('html');

			$parent_id = (int)$parent_id;
			$news = \News\Model_News::check_authority($parent_id);
			$news_images = \News\Model_NewsImage::get4news_id($news->id);
			$images = \Site_Upload::get_file_objects($news_images, $news->id, true);

			$status_code = 200;
			$data = array(
				'news' => $news,
				'images' => $images,
			);

			return \Response::forge(\View::forge('news/image/form', $data), $status_code);
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
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}

	public function post_upload($parent_id = null)
	{
		$upload_type = 'img';
		$response = '';
		try
		{
			//Util_security::check_csrf();
			$parent_id = (int)$parent_id;
			$news = \News\Model_News::check_authority($parent_id);
			if (!in_array($this->format, array('html', 'json'))) throw new HttpNotFoundException();

			$thumbnail_size = \Input::post('thumbnail_size');
			if (!\Validation::_validation_in_array($thumbnail_size, array('M', 'S'))) throw new \HttpInvalidInputException('Invalid input data');;
			$insert_target = \Input::post('insert_target');

			$is_insert_body_image = conf('image.isInsertBody', 'news');
			$options = \Site_Upload::get_upload_handler_options($this->u->id, true, false, 'nw', $parent_id, true, 'img', $is_insert_body_image);
			$uploadhandler = new \MyUploadHandler($options, false);
			\DB::start_transaction();
			$files = $uploadhandler->post(false);
			$files['files'] = \News\Model_NewsImage::save_images($parent_id, $files['files']);
			\DB::commit_transaction();
			$files['upload_type'] = $upload_type;
			$files['thumbnail_size'] = $thumbnail_size;
			$files['insert_target'] = $insert_target;
			$files['model'] = 'news';

			$status_code = 200;
			if ($this->format == 'html')
			{
				$response = \View::forge('filetmp/_parts/upload_images', $files);
				return \Response::forge($response, $status_code);
			}
			$response = $files;
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
			$status_code = 500;
		}

		return $this->response($response, $status_code);
	}

	/**
	 * News image delete
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
			$news_image = \News\Model_NewsImage::check_authority($id, null, 'news');
			$news_image->delete();
			\DB::commit_transaction();

			$response['status'] = 1;
			$status_code = 200;
		}
		catch(\HttpNotFoundException $e)
		{
			$status_code = 404;
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}

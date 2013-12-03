<?php

class Controller_FileTmp_Api extends Controller_Site_Api
{
	protected $check_not_auth_action = array(
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Api get_upload
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function get_upload()
	{
		$response = '';
		try
		{
			if (!in_array($this->format, array('html', 'json'))) throw new HttpNotFoundException();

			$options = Site_Upload::get_upload_handler_options($this->u->id);
			$uploadhandler = new MyUploadHandler($options, false);
			$file = $uploadhandler->get(false);
			$status_code = 200;

			if ($this->format == 'html')
			{
				$response = View::forge('filetmp/_parts/upload_image', $file);
				return Response::forge($response, $status_code);
			}
			$response = $files;
		}
		catch(FuelException $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}

	/**
	 * Api post_upload
	 * 
	 * @access  public
	 * @return  Response (html or json)
	 */
	public function post_upload()
	{
		$_method = \Input::get_post('_method');
		if (isset($_method) && $_method === 'DELETE')
		{
			return $this->delete_upload();
		}

		$response = '';
		try
		{
			//Util_security::check_csrf();
			if (!in_array($this->format, array('html', 'json'))) throw new HttpNotFoundException();

			$options = Site_Upload::get_upload_handler_options($this->u->id);
			$options['max_file_size'] = PRJ_UPLOAD_MAX_FILESIZE;
			$options['max_number_of_files'] = PRJ_MAX_FILE_UPLOADS;
			$options['is_save_exif'] = PRJ_USE_EXIF_DATA;
			$uploadhandler = new MyUploadHandler($options, false);
			$files = $uploadhandler->post(false);
			$status_code = 200;

			if ($this->format == 'html')
			{
				$response = View::forge('filetmp/_parts/upload_images', $files);
				return Response::forge($response, $status_code);
			}
			$response = $files;
		}
		catch(FuelException $e)
		{
			$status_code = 400;
		}

		return $this->response($response, $status_code);
	}

	/**
	 * Api delete_upload
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function delete_upload()
	{
		$response = '';
		try
		{
			Util_security::check_csrf();

			$id = (int)Input::post('id');
			if (!$id || !$file_tmp = Model_FileTmp::check_authority($id, $this->u->id))
			{
				throw new HttpNotFoundException;
			}

			$options = Site_Upload::get_upload_handler_options($this->u->id);
			$uploadhandler = new MyUploadHandler($options, false);
			$response = $uploadhandler->delete(false, $file_tmp);
			$status_code = 200;
		}
		catch(HttpNotFoundException $e)
		{
			$status_code = 404;
		}
		catch(FuelException $e)
		{
			$status_code = 400;
		}

		return $this->response($response, $status_code);
	}
}

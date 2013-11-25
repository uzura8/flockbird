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
		//if ($this->format != 'html') throw new HttpNotFoundException();

		$response = '';
		try
		{
			$options = $this->get_default_options($this->u->id);
			$uploadhandler = new MyUploadHandler($options, false);
			$response = $uploadhandler->get(false);
			//$response = View::forge('site/_parts/tmp_images', array('file_tmps' => $file_tmps, 'is_tmp' => true));
			$status_code = 200;

			return Response::forge($response, $status_code);
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

			$options = $this->get_default_options($this->u->id);
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

			$options = $this->get_default_options($this->u->id);
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

	private function get_default_options($member_id)
	{
		$filepath = \Site_Upload::get_filepath('m', $member_id);
		$thumbnail_sizes = Site_Upload::conv_size_str_to_array(Config::get('site.upload.types.img.tmp.sizes.thumbnail'));
		$options = array(
			'upload_dir' => Config::get('site.upload.types.img.tmp.raw_file_path').$filepath,
			'upload_url' => Uri::create(Config::get('site.upload.types.img.tmp.root_path.raw_dir')).$filepath,
			'member_id' => $member_id,
			'site_filepath' => $filepath,
			'image_versions' => array(
				'' => array(
					'auto_orient' => true
				),
				'thumbnail' => array(
					'max_width' => $thumbnail_sizes['width'],
					'max_height' => $thumbnail_sizes['height'],
				),
			),
		);

		return $options;
	}
}

<?php

class Controller_Site_Api extends Controller_Base_Site_Api
{
	protected $check_not_auth_action = array(
		'get_login',
	);

	public function before()
	{
		parent::before();

		$this->auth_check_api(true);
		$this->set_current_user();
	}

	/**
	 * Api login
	 * 
	 * @access  public
	 * @return  Response (html)
	 */
	public function get_login()
	{
		if ($this->format != 'html') throw new HttpNotFoundException();

		$response = '';
		try
		{
			$destination = Input::get('destination', '');

			$val = Validation::forge();
			$response = View::forge('site/_parts/login', array('val' => $val, 'destination' => $destination));
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
	 * Api get_tmp_images
	 * 
	 * @access  public
	 * @return  Response (html)
	 */
	public function get_tmp_images($contents = null)
	{
		if ($this->format != 'html') throw new HttpNotFoundException();

		if (!Site_Upload::check_is_temp_accepted_contents($contents)) throw new \HttpNotFoundException;
		if (!$tmp_hash = Input::get('tmp_hash', '')) throw new \HttpNotFoundException;

		$response = '';
		try
		{
			if (Config::get('site.upload.tmp_file.is_delete_olds_when_display'))
			{
				$file_tmps = Model_FileTmp::delete_disables($this->u->id, $contents, $tmp_hash);
			}
			$file_tmps = Model_FileTmp::get_enables($this->u->id, $contents, $tmp_hash);
			$response = View::forge('site/_parts/tmp_images', array('file_tmps' => $file_tmps));
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
	 * Api delete tmp_image
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_delte_tmp_image()
	{
		$response = array('status' => 0);
		try
		{
			\Util_security::check_csrf();

			$id = (int)\Input::post('id');
			if (!$id || !$file_tmp = Model_FileTmp::check_authority($id, $this->u->id))
			{
				throw new \HttpNotFoundException;
			}
			\Model_FileTmp::delete_with_file($id);

			$response['status'] = 1;
			$status_code = 200;
		}
		catch(\FuelException $e)
		{
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}

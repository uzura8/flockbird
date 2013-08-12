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
			}
			$file_tmps = Model_FileTmp::query()
				->where('member_id', $this->u->id)
				->where('contents', $contents)
				->where('hash', $tmp_hash)
				->where('created_at', '>', date('Y-m-d H:i:s', time() - Config::get('site.upload.tmp_file.lifetime')))
				->order_by('id')
				->get();
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
}

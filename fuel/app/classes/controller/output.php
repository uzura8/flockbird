<?php

class Controller_Output extends Controller
{
	//protected $check_not_auth_action = array(
	//	'image',
	//);

	public function before()
	{
		parent::before();
	}

	/**
	 * image
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_image($filename = null, $extention = null, $size = 'raw')
	{
		$filename .= '.'.$extention;
		if (empty($filename) || !Site_Upload::check_filename_format($filename) || !$file = Site_Upload::get_upload_file_path($filename, $size))
		{
			throw new HttpNotFoundException;
		}

		$this->response->set_header('Content-Type', Util_file::get_content_type_string($extention));
		$this->response->body(file_get_contents($file));

		return $this->response;
	}
}

<?php
namespace Admin;

class Controller_News_Image_Api extends Controller_Api
{
	protected $check_not_auth_action = array(
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * News image delete
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function post_delete()
	{
		$response = array('status' => 0);
		try
		{
			\Util_security::check_csrf();

			$id = (int)\Input::post('id');
			if (!$id || !$news_image = \News\Model_NewsImage::check_authority($id))
			{
				throw new \HttpNotFoundException;
			}

			\DB::start_transaction();
			$news_image->delete();
			\DB::commit_transaction();

			$response['status'] = 1;
			$status_code = 200;
		}
		catch(\FuelException $e)
		{
			if (\DB::in_transaction()) \DB::rollback_transaction();
			$status_code = 400;
		}

		$this->response($response, $status_code);
	}
}

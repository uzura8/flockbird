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
	 * Get image list
	 * 
	 * @access  public
	 * @return  Response (html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function get_list($parent_id = null)
	{
		$this->api_accept_formats = 'html';
		$this->controller_common_api(function() use($parent_id)
		{
			$news = \News\Model_News::check_authority($parent_id);
			$news_images = \News\Model_NewsImage::get4news_id($news->id);
			$images = \Site_Upload::get_file_objects($news_images, $news->id, true);

			$data = array(
				'news' => $news,
				'images' => $images,
			);
			$this->set_response_body_api($data);
		});
	}

	/**
	 * Upload images
	 * 
	 * @access  public
	 * @return  Response (json|html)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see  Controller_Base::controller_common_api
	 */
	public function post_upload($parent_id = null)
	{
		$this->api_accept_formats = array('html', 'json');
		$this->api_not_check_csrf = true;
		$this->controller_common_api(function() use($parent_id)
		{
			$upload_type = 'img';
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

			$this->set_response_body_api($files, $this->format == 'html' ? 'filetmp/_parts/upload_images' : null);
		});
	}

	/**
	 * Delete news image
	 * 
	 * @access  public
	 * @param   int  $id  target id
	 * @return  Response(json)
	 * @throws  Exception in Controller_Base::controller_common_api
	 * @see     Controller_Base::api_delete_common
	 */
	public function post_delete($id = null)
	{
		$this->api_delete_common('news_image', $id, null, term('site.image'));
	}
}

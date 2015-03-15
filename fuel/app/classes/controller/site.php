<?php

/**
 * The Site Controller.
 *
 * A basic controller example.  Has examples of how to set the
 * response body and status.
 * 
 * @package  app
 * @extends  Controller
 */
class Controller_Site extends Controller_Base_Site
{
	protected $login_val;

	public function before()
	{
		parent::before();
		if (!Auth::check()) $this->set_login_validation();
	}

	protected function set_login_validation()
	{
		$this->login_val = Validation::forge();
		$options = array('1' => '次回から自動的にログイン');
		$this->login_val->add('rememberme', '', array('type' => 'checkbox', 'options' => $options))->add_rule('checkbox_val', $options);
		$this->login_val->add_model(Model_MemberAuth::forge());
		View::set_global('login_val', $this->login_val);
	}

	protected function display_error($message_display = '', $messsage_log = '', $action = 'error/500', $status = 500)
	{
		if ($messsage_log) \Log::error($messsage_log);
		if ($message_display)
		{
			$this->set_title_and_breadcrumbs($message_display);
		}
		$this->template->content = View::forge($action);
		if ($status) $this->response->status = $status;
	}

	/**
	 * Site index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$data = array();
		if (Config::get('page.site.index.timeline.isEnabled') && is_enabled('timeline'))
		{
			$data['timelines'] = \Timeline\Site_Util::get_list4view(
				\Auth::check() ? $this->u->id : 0,
				0, false, null,
				$this->common_get_list_params(array(
					'desc' => 1,
					'latest' => 1,
					'limit' => Config::get('page.site.index.timeline.list.limit'),
				), Config::get('page.site.index.timeline.list.limit_max'), true)
			);
			$data['timelines']['see_more_link'] = array('uri' => 'timeline');
			//$this->template->post_footer = \View::forge('timeline::_parts/load_timelines');
		}
		if (Config::get('page.site.index.albumImage.isEnabled') && is_enabled('album'))
		{
			list($limit, $page) = $this->common_get_pager_list_params(\Config::get('page.site.index.albumImage.list.limit'), \Config::get('page.site.index.albumImage.list.limit_max'));
			$data['album_images'] =\Album\ Model_AlbumImage::get_pager_list(array(
				'related'  => array('album'),
				'where'    => \Site_Model::get_where_params4list(0, \Auth::check() ? $this->u->id : 0),
				'order_by' => array('id' => 'desc'),
				'limit'    => $limit,
			), $page);
			$data['album_images']['liked_album_image_ids'] = (conf('like.isEnabled') && \Auth::check()) ?
				\Site_Model::get_liked_ids('album_image', $this->u->id, $data['album_images']['list']) : array();
			$data['album_images']['column_count'] = \Config::get('page.site.index.albumImage.list.column_count');
			//$this->template->post_footer = \View::forge('image/_parts/list_footer');
		}
		$this->template->post_footer = \View::forge('site/_parts/index_footer');
		if (Config::get('page.site.index.slide.isEnabled')) $this->template->top_content = View::forge('site/_parts/slide');
		$this->set_title_and_breadcrumbs('', null, null, null, null, true, true);
		$this->template->content = View::forge('site/index', $data);
	}
}

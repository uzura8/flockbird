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
		Fieldset::reset();
		$this->login_val = Validation::forge('site_login');
		$options = array('1' => '次回から自動的にログイン');
		$this->login_val->add('rememberme', '', array('type' => 'checkbox', 'options' => $options))->add_rule('checkbox_val', $options);
		$this->login_val->add_model(Model_MemberAuth::forge());
		$this->login_val->fieldset()->field('email')->delete_rule('unique');
		View::set_global('login_val', $this->login_val);
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

		if (Config::get('page.site.index.news.isEnabled') && is_enabled('news'))
		{
			list($limit, $page) = $this->common_get_pager_list_params(
				\Config::get('page.site.index.news.list.limit'),
				\Config::get('page.site.index.news.list.limit_max')
			);
			$data['news_list'] = \News\Site_Model::get_list($limit, $page, \Auth::check());
			$data['news_list']['see_more_link'] = array('uri' => 'news');
		}

		if (Config::get('page.site.index.albumImage.isEnabled') && is_enabled('album'))
		{
			list($limit, $page) = $this->common_get_pager_list_params(
				\Config::get('page.site.index.albumImage.list.limit'),
				\Config::get('page.site.index.albumImage.list.limit_max')
			);
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

		if (conf('site.index.slide.isEnabled', 'page'))
		{
			if (conf('site.index.slide.recentAlbumImage.isEnabled', 'page'))
			{
				$images = \Album\Site_Util::get_top_slide_image_uris();
			}
			else
			{
				$images = Config::get('page.site.index.slide.images');
			}
			$this->template->top_content = View::forge('site/_parts/slide', array('image_uris' => $images));
		}
		$this->set_title_and_breadcrumbs('', null, null, null, null, true, true);
		$this->template->content = View::forge('site/index', $data);
		if (!empty($data['news_list']['list']))
		{
			$this->template->content->set_safe('html_bodys', \News\Site_Model::convert_raw_bodys($data['news_list']['list']));
		}
	}
}

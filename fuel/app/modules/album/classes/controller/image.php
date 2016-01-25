<?php
namespace Album;

class Controller_Image extends \Controller_Site
{
	protected $check_not_auth_action = array(
		'index',
		'list',
		'detail',
		'member',
	);

	public function before()
	{
		parent::before();
	}

	public function action_index($id = null)
	{
		$this->action_list();
	}

	/**
	 * Album image list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_list()
	{
		list($limit, $page) = $this->common_get_pager_list_params(conf('album.articles.limit', 'album'), conf('album.articles.limit_max', 'album'));
		$data = Site_Model::get_album_images($limit, $page, \Auth::check() ? $this->u->id : 0);

		$this->set_title_and_breadcrumbs(term('album_image', 'site.list'), array('album' => term('album', 'site.list')));
		$this->template->post_footer = \View::forge('image/_parts/list_footer');
		$this->template->content = \View::forge('image/_parts/list', $data);
	}

	/**
	 * Album image member
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_member($member_id = null)
	{
		$member_id = (int)$member_id;
		list($is_mypage, $member) = $this->check_auth_and_is_mypage($member_id);
		list($limit, $page) = $this->common_get_pager_list_params(conf('album.articles.limit', 'album'), conf('album.articles.limit_max', 'album'));

		$data = Site_Model::get_album_images($limit, $page, \Auth::check() ? $this->u->id : 0, $member, $is_mypage, array('related' => array('album')));
		$data['member'] = $member;
		$data['is_member_page'] = true;
		$data['liked_album_image_ids'] = (conf('like.isEnabled') && \Auth::check()) ?
			\Site_Model::get_liked_ids('album_image', $this->u->id, $data['list']) : array();

		$this->set_title_and_breadcrumbs(sprintf('%sの%s', $is_mypage ? '自分' : $member->name.'さん', term('album_image', 'site.list')), null, $member);
		$this->template->subtitle = \View::forge('_parts/member_subtitle', array('member' => $member, 'is_mypage' => $is_mypage));
		$this->template->post_footer = \View::forge('image/_parts/list_footer');

		$this->template->content = \View::forge('image/_parts/list', $data);
	}

	/**
	 * Album image detail
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_detail($id = null)
	{
		$id = (int)$id;
		$album_image = Model_Albumimage::check_authority($id);
		$this->check_browse_authority($album_image->public_flag, $album_image->album->member_id);
		$locations = is_enabled_map('image/detail', 'album') ? Model_AlbumImageLocation::get_locations4album_image_id($id) : null;

		// 既読処理
		if (\Auth::check()) $this->change_notice_status2read($this->u->id, 'album_image', $id);

		// album image_comment
		$default_params = array('latest' => 1);
		list($limit, $is_latest, $is_desc, $since_id, $max_id)
			= $this->common_get_list_params($default_params, conf('view_params_default.detail.comment.limit_max'));
		list($list, $next_id, $all_comment_count)
			= Model_AlbumImageComment::get_list(array('album_image_id' => $id), $limit, $is_latest, $is_desc, $since_id, $max_id, null, false, true);

		// album_image_like
		$is_liked_self = \Auth::check() ? Model_AlbumImageLike::check_liked($id, $this->u->id) : false;

		$data = array(
			'album_image' => $album_image,
			'locations' => $locations,
			'comments' => $list,
			'all_comment_count' => $all_comment_count,
			'comment_next_id' => $next_id,
			'is_liked_self' => $is_liked_self,
			'liked_ids' => (conf('like.isEnabled') && \Auth::check() && $list) ?
				\Site_Model::get_liked_ids('album_image_comment', $this->u->id, $list) : array(),
		);

		// get before and after album_image.id
		if (conf('display_setting.image.detail.displayNextPageButton', 'album'))
		{
			list($data['before_id'], $data['after_id']) = $this->get_before_after_ids($album_image->id);
		}

		$slide_file_names = array();
		if (conf('display_setting.image.detail.displayGallery.isEnabled', 'album'))
		{
			if (conf('display_setting.image.detail.displayNextPageButton', 'album')) Model_AlbumImage::clear_cache();
			$slide_file_names = $this->get_slide_file_names($album_image);
		}

		$title = Site_Util::get_album_image_display_name($album_image, term('album_image', 'site.detail'));
		$this->set_title_and_breadcrumbs($title, array('/album/'.$album_image->album_id => $album_image->album->name), $album_image->album->member, 'album',
			null, false, false, array(
				'title' => $album_image->album->name,
				'description' => $album_image->name ?: FBD_SITE_NAME,
				'image' => \Site_Util::get_image_uri4image_list($album_image, 'ai', 'raw'),
			));
		$this->template->subtitle = \View::forge('image/_parts/detail_subtitle', array('album_image' => $album_image));
		$this->template->post_header = \View::forge('image/_parts/detail_header');
		$this->template->post_footer = \View::forge('image/_parts/detail_footer', array(
			'album_image' => $album_image,
			'locations' => $locations,
			'slide_file_names' => $slide_file_names,
		));
		$this->template->content = \View::forge('image/detail', $data);
	}

	/**
	 * Album_image edit
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_edit($id = null)
	{
		$album_image = Model_Albumimage::check_authority($id, $this->u->id);
		$val = self::get_validation_object($album_image);
		$is_enabled_map = is_enabled_map('image/edit', 'album');
		$location = $is_enabled_map ? Model_AlbumimageLocation::get_locations4album_image_id($id) : null;

		if (\Input::method() == 'POST')
		{
			try
			{
				\Util_security::check_csrf();

				if (!$val->run()) throw new \FuelException($val->show_errors());
				$post = $val->validated();
				if (empty($post['name']) && empty($post['shot_at_time'])
					&& ($is_enabled_map && !strlen($post['latitude']) && !strlen($post['longitude'])))
				{
					throw new \FuelException('入力してください');
				}
				$disabled_to_update_message = Site_Util::check_album_disabled_to_update($album_image->album->foreign_table);
				if ($disabled_to_update_message && isset($post['public_flag']) && $album_image->public_flag != $post['public_flag'])
				{
					throw new \FuelException($disabled_to_update_message);
				}

				\DB::start_transaction();
				$album_image->update_with_relations($post);
				\DB::commit_transaction();

				\Session::set_flash('message', term('album_image').'を編集をしました。');
				\Response::redirect('album/image/'.$album_image->id);
			}
			catch(\FuelException $e)
			{
				if (\DB::in_transaction()) \DB::rollback_transaction();
				\Session::set_flash('error', $e->getMessage());
			}
		}

		$album_image_page_title = Site_Util::get_album_image_display_name($album_image, term('album_image', 'site.detail'));
		$this->set_title_and_breadcrumbs(
			sprintf('%sを%s', term('album_image'), term('form.do_edit')),
			array('/album/'.$album_image->album_id => $album_image->album->name, '/album/image/'.$id => $album_image_page_title),
			$album_image->album->member,
			'album'
		);
		$this->template->post_header = \View::forge('_parts/datetimepicker_header');
		$this->template->post_footer = \View::forge('image/_parts/edit_footer');

		$this->template->content = \View::forge('image/edit', array('val' => $val, 'album_image' => $album_image, 'location' => $location));
	}

	/**
	 * Album_image delete
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_delete($id = null)
	{
		\Util_security::check_method('POST');
		\Util_security::check_csrf();
		$album_image = Model_Albumimage::check_authority($id, $this->u->id);
		$album_id = $album_image->album_id;
		try
		{
			\DB::start_transaction();
			$album_image->delete();
			\DB::commit_transaction();

			\Session::set_flash('message', term('album_image').'を削除しました。');
		}
		catch (Exception $e)
		{
			\Session::set_flash('error', $e->getMessage());
			\DB::rollback_transaction();
		}

		\Response::redirect('album/'.$album_id);
	}

	private static function get_validation_object(Model_AlbumImage $album_image)
	{
		$val = \Validation::forge();
		$val->add_model($album_image);
		$val->fieldset()->field('file_name')->delete_rule('required');

		if (Site_Util::check_album_disabled_to_update($album_image->album->foreign_table, true))
		{
			$val->fieldset()->field('public_flag')->delete_rule('required');
			$val->fieldset()->delete('public_flag');
		}
		else
		{
			$val->add('original_public_flag')
					->add_rule('in_array', \Site_Util::get_public_flags());
		}

		$val->add('shot_at_time', '撮影日時')
				->add_rule('required')
				->add_rule('datetime_except_second')
				->add_rule('datetime_is_past');

		if (is_enabled_map('image/edit', 'album'))
		{
			$val->add('latitude', '緯度')
				->add_rule('numeric_between', -90, 90);

			$val->add('longitude', '経度')
				->add_rule('numeric_between', -180, 180);
		}

		return $val;
	}

	private function get_slide_file_names(Model_AlbumImage $album_image)
	{
		// get for slide images
		$params = $this->get_query_params($album_image);
		$params['limit'] = conf('display_setting.image.detail.displayGallery.limitMax', 'album');
		$loopMax = conf('display_setting.image.detail.displayGallery.checkLoopMax', 'album');

		$i = 0;
		$file_names = array();
		while (empty($file_names[$album_image->id]) && $i < $loopMax)
		{
			if (!empty($last_id))
			{
				if ($i >= 2) array_pop($params['where']);
				$params['where'][] = array('id', '<', $last_id);
			}
			$file_names = Model_AlbumImage::get_assoc('id', 'file_name', $params['where'], $params['order_by'], $params['limit']);
			$last_id = \Util_Array::get_last_key($file_names);
			$i++;
		}
		if (!$file_names) return array();

		return \Util_Array::sort_to_top(array_values($file_names), $album_image->file_name);
	}

	private function get_before_after_ids(Model_AlbumImage $album_image)
	{
		$ids = Model_AlbumImage::get_col_array('id', $this->get_query_params($album_image));

		return \Util_Array::get_neighborings($album_image->id, $ids);
	}

	private function get_query_params(Model_AlbumImage $album_image)
	{
		return array(
			'where' => \Site_Model::get_where_params4list(
				0,
				\Auth::check() ? $this->u->id : 0,
				$this->check_is_mypage($album_image->album->member_id),
				array(array('album_id', $album_image->album->id))
			),
			'order_by' => array('id' => 'desc'),
		);
	}
}

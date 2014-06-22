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
		$this->set_title_and_breadcrumbs(term('album_image', 'site.list'), array('album' => term('album', 'site.list')));
		$this->template->post_footer = \View::forge('_parts/load_masonry');

		$data = \Site_Model::get_simple_pager_list('album_image', 1, array(
			'related'  => array('file', 'album'),
			'where'    => \Site_Model::get_where_params4list(0, \Auth::check() ? $this->u->id : 0),
			'order_by' => array('id' => 'desc'),
			'limit'    => \Config::get('album.articles.limit'),
		), 'Album');
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
		if (!$id || !$album_image = Model_Albumimage::check_authority($id)) throw new \HttpNotFoundException;
		$this->check_public_flag($album_image->public_flag, $album_image->album->member_id);

		$record_limit = conf('view_params_default.detail.comment.limit');
		if (\Input::get('all_comment', 0)) $record_limit = conf('view_params_default.detail.comment.limit_max');
		list($comments, $is_all_records) = Model_AlbumImageComment::get_comments($id, $record_limit);

		$data = array('album_image' => $album_image, 'comments' => $comments, 'is_all_records' => $is_all_records);

		// 前後の id の取得
		$params = array(
			'where' => \Site_Model::get_where_params4list(
				0,
				\Auth::check() ? $this->u->id : 0,
				$this->check_is_mypage($album_image->album->member_id),
				array(array('album_id', $album_image->album->id))
			),
			'order_by' => array('id' => 'desc'),
		);
		$ids = \Site_Model::get_col_array('album_image', 'id', $params, 'Album');
		list($data['before_id'], $data['after_id']) = \Util_Array::get_neighborings($id, $ids);

		$title = Site_Util::get_album_image_page_title($album_image->name, $album_image->file->original_filename);
		$this->set_title_and_breadcrumbs($title, array('/album/'.$album_image->album_id => $album_image->album->name), $album_image->album->member, 'album');
		$this->template->subtitle = \View::forge('image/_parts/detail_subtitle', array('album_image' => $album_image));
		$this->template->content = \View::forge('image/detail', $data);
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

		$this->set_title_and_breadcrumbs(sprintf('%sの%s', $is_mypage ? '自分' : $member->name.'さん', term('album_image', 'site.list')), null, $member);
		$this->template->subtitle = \View::forge('_parts/member_subtitle', array('member' => $member, 'is_mypage' => $is_mypage));
		$this->template->post_footer = \View::forge('_parts/load_masonry');

		$data = \Site_Model::get_simple_pager_list('album_image', 1, array(
			'related' => array('file', 'album'),
			'where' => \Site_Model::get_where_params4list($member->id, \Auth::check() ? $this->u->id : 0, $this->check_is_mypage($member->id), array(), 't2.member_id'),
			'limit' => \Config::get('album.articles.limit'),
			'order_by' => array('id' => 'desc'),
		), 'Album');
		$data['member'] = $member;
		$this->template->content = \View::forge('image/_parts/list', $data);
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
		$with_file = (\Input::method() == 'POST') ? false : true;
		if (!$album_image = Model_AlbumImage::check_authority($id, $this->u->id, $with_file))
		{
			throw new \HttpNotFoundException;
		}
		$disabled_to_update_message = Site_Util::check_album_disabled_to_update($album_image->album->foreign_table);

		$val = self::get_validation_object($album_image, true);

		if (\Input::method() == 'POST')
		{
			\Util_security::check_csrf();

			$post = array();
			$error = '';
			if ($val->run())
			{
				$post = $val->validated();
				if (empty($post['name']) && empty($post['shot_at_time'])) $error = '入力してください';
			}
			else
			{
				$error = $val->show_errors();
			}
			if ($disabled_to_update_message && isset($post['public_flag']) && $album_image->public_flag != $post['public_flag'])
			{
				$error = $disabled_to_update_message;
			}

			if (!$error)
			{
				try
				{
					$post = $val->validated();

					\DB::start_transaction();
					if (isset($post['name']) && $post['name'] !== '' && $post['name'] !== $album_image->name) $album_image->name = $post['name'];
					if ($post['shot_at_time'] && !\Util_Date::check_is_same_minute($post['shot_at_time'], $album_image->shot_at))
					{
						$album_image->shot_at = $post['shot_at_time'].':00';
					}
					$album_image->save();
					if (!$disabled_to_update_message && isset($post['public_flag']))
					{
						$album_image->update_public_flag($post['public_flag'], true);
					}
					\DB::commit_transaction();

					\Session::set_flash('message', term('album_image').'を編集をしました。');
					\Response::redirect('album/image/'.$album_image->id);
				}
				catch(Exception $e)
				{
					\DB::rollback_transaction();
					\Session::set_flash('error', 'Could not save.');
				}
			}
			else
			{
				\Session::set_flash('error', $error);
			}
		}

		$album_image_page_title = Site_Util::get_album_image_page_title($album_image->name, $album_image->file->original_filename);
		$this->set_title_and_breadcrumbs(
			sprintf('%sを%s', term('album_image'), term('form.do_edit')),
			array('/album/'.$album_image->album_id => $album_image->album->name, '/album/image/'.$id => $album_image_page_title),
			$album_image->album->member,
			'album'
		);
		$this->template->post_header = \View::forge('_parts/datetimepicker_header');
		$this->template->post_footer = \View::forge('_parts/datetimepicker_footer', array('attr' => '#shot_at_time'));

		$this->template->content = \View::forge('image/edit', array('val' => $val, 'album_image' => $album_image));
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
		\Util_security::check_csrf();
		if (!$album_image = Model_AlbumImage::check_authority($id, $this->u->id))
		{
			throw new \HttpNotFoundException;
		}

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

		$val->add('shot_at_time', '撮影日時')
				->add_rule('required')
				->add_rule('datetime_except_second')
				->add_rule('datetime_is_past');

		$val->add('original_public_flag')
				->add_rule('in_array', \Site_Util::get_public_flags());

		return $val;
	}
}

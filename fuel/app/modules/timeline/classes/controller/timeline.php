<?php
namespace Timeline;

class Controller_Timeline extends \Controller_Site
{
	protected $check_not_auth_action = array(
		'index',
		'list',
		'member',
		'detail',
	);

	public function before()
	{
		parent::before();
	}

	/**
	 * Timeline index
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
		$this->action_list();
	}

	/**
	 * Timeline list
	 * 
	 * @access  public
	 * @return  Response
	 */
	public function action_list()
	{
		list($list, $is_next) = Site_Model::get_list(\Auth::check() ? $this->u->id : 0);
		$this->set_title_and_breadcrumbs(sprintf('最新の%s一覧', \Config::get('term.timeline')));
		$this->template->post_footer = \View::forge('_parts/timeline/load_timelines');
		$this->template->content = \View::forge('_parts/timeline/list', array('list' => $list, 'is_next' => $is_next));
	}

	/**
	 * Timeline member
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_member($member_id = null)
	{
		$member_id = (int)$member_id;
		list($is_mypage, $member) = $this->check_auth_and_is_mypage($member_id);
		$is_draft = $is_mypage ? \Util_string::cast_bool_int(\Input::get('is_draft', 0)) : 0;
		$is_published = \Util_toolkit::reverse_bool($is_draft, true);

		$this->set_title_and_breadcrumbs(sprintf('%sの%s一覧', $is_mypage ? '自分' : $member->name.'さん', \Config::get('term.note')), null, $member);
		$this->template->subtitle = $is_mypage ? \View::forge('_parts/member_subtitle') : '';
		$data = \Site_Model::get_simple_pager_list('note', 1, array(
			'where'    => \Site_Model::get_where_params4list(
				$member->id,
				\Auth::check() ? $this->u->id : 0,
				$is_mypage,
				null,
				array(array('is_published', $is_published))
			),
			'limit'    => \Config::get('note.articles.limit'),
			'order_by' => array('created_at' => 'desc'),
		), 'Timeline');
		$data['member']       = $member;
		$data['is_mypage']    = $is_mypage;
		$data['is_draft']     = $is_draft;
		$this->template->content = \View::forge('member', $data);
		$this->template->post_footer = \View::forge('_parts/load_item');
	}

	/**
	 * Timeline detail
	 * 
	 * @access  public
	 * @params  integer
	 * @return  Response
	 */
	public function action_detail($id = null)
	{
		if (!$note = Model_Note::check_authority($id)) throw new \HttpNotFoundException;
		$this->check_public_flag($note->public_flag, $note->member_id);

		$images = Model_NoteAlbumImage::get_album_image4note_id($id);

		$record_limit = (\Input::get('all_comment', 0))? 0 : \Config::get('site.record_limit.default.comment.m');
		list($comments, $is_all_records) = Model_NoteComment::get_comments($id, $record_limit);

		$title = array('name' => $note->title);
		$header_info = array();
		if (!$note->is_published)
		{
			$title['label'] = array('name' => \Config::get('term.draft'), 'attr' => 'label-inverse');
			$header_info = array('title' => sprintf('この%sはまだ公開されていません。',  \Config::get('term.note')), 'body' => '');
		}
		$this->set_title_and_breadcrumbs($title, null, $note->member, 'note', $header_info);
		$this->template->subtitle = \View::forge('_parts/detail_subtitle', array('note' => $note));
		$this->template->post_footer = \View::forge('_parts/load_masonry', array('is_not_load_more' => true));
		$this->template->content = \View::forge('detail', array('note' => $note, 'images' => $images, 'comments' => $comments, 'is_all_records' => $is_all_records));
	}
}

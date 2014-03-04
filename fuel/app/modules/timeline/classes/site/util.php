<?php
namespace Timeline;

class Site_Util
{
	public static function get_accept_timeline_foreign_tables()
	{
		return array(
			'member',
			'note',
			'album_image',
			'album',
			'file',
		);
	}

	public static function get_type4key($type_key = null)
	{
		if (!$type_key) $type_key = 'normal';
		if (!$type = \Config::get('timeline.types.'.$type_key))
		{
			throw new \InvalidArgumentException('first parameter is invalid.');
		}

		return $type;
	}

	public static function get_key4type($target_type = null)
	{
		$types = \Config::get('timeeline.types');
		foreach ($types as $key => $type)
		{
			if ($type == $target_type) return $key;
		}

		throw new \InvalidArgumentException('first parameter is invalid.');
	}

	public static function check_type($target_type, $type_key)
	{
		return $target_type == self::get_type4key($type_key);
	}

	public static function get_timeline_save_values($type_key = null)
	{
		$type = self::get_type4key($type_key);
		$foreign_table = null;
		$child_foreign_table = null;
		switch ($type_key)
		{
			case 'member_register':
				$foreign_table = 'member';
				break;
			case 'profile_image':
				$foreign_table = 'file';
				break;
			case 'note':
				$foreign_table = 'note';
				break;
			case 'album':
				$foreign_table = 'album';
				$child_foreign_table = 'album_image';
				break;
			case 'album_image':
				$foreign_table = 'album';
				$child_foreign_table = 'album_image';
				break;
			case 'album_image_profile':
				$foreign_table = 'album_image';
				break;
			case 'album_image_timeline':
				$foreign_table = 'album';
				$child_foreign_table = 'album_image';
				break;
		}

		return array($type, $foreign_table, $child_foreign_table);
	}

	public static function get_timeline_body($type, $body = null, $foreign_table_obj = null, array $optional_info = null)
	{
		$is_safe = false;
		switch ($type)
		{
			case \Config::get('timeline.types.normal'):// 通常 timeline 投稿(つぶやき)
				break;
			case \Config::get('timeline.types.member_register'):// SNS への参加
				$body = PRJ_SITE_NAME.' に参加しました。';
				break;
			case \Config::get('timeline.types.profile_image'):// profile 写真投稿
			case \Config::get('timeline.types.album_image_profile'):// profile 写真投稿(album_image)
				$body = \Config::get('term.profile').'写真を設定しました。';
				break;
			case \Config::get('timeline.types.note'):// note 投稿
				$body = \Config::get('term.note').'を投稿しました。';
				break;
			case \Config::get('timeline.types.album'):// album 作成
				$body = \Config::get('term.album').'を作成しました。';
				break;
			case \Config::get('timeline.types.album_image'):// album_image 投稿
				$is_safe = true;
				$body = $foreign_table_obj ? render('timeline::_parts/body_for_add_album_image', array(
					'album_id' => $foreign_table_obj->id,
					'name' => $foreign_table_obj->name,
					'count' => isset($optional_info['count']) ? $optional_info['count'] : 0,
				)) : null;
				break;
			case \Config::get('timeline.types.album_image_timeline'):
				if (!strlen($body))
				{
					$body = sprintf('%sに写真を投稿しました。', \Config::get('term.timeline'));
					if (!empty($optional_info['count']))
					{
						$body = sprintf('%sに写真を %d 枚投稿しました。', \Config::get('term.timeline'), $optional_info['count']);
					}
				}
				break;
			default :
				break;
		}

		return array($body, $is_safe);
	}

	public static function get_quote_article($type, $foreign_table_obj)
	{
		$accept_types = array(
			\Config::get('timeline.types.note'),
			\Config::get('timeline.types.album'),
			\Config::get('timeline.types.album_image'),
		);
		if (!in_array($type, $accept_types)) return null;

		$title = array(
			'value' => '',
			'truncate_count' => \Config::get('site.view_params_default.list.trim_width.title')
		);
		$body = array(
			'value' => $foreign_table_obj->body,
			'truncate_count' => \Config::get('timeline.articles.truncate_lines.body'),
			'truncate_type' => 'line'
		);
		$read_more_uri  = '';

		switch ($type)
		{
			case \Config::get('timeline.types.note'):
				$title['value'] = $foreign_table_obj->title;
				$read_more_uri = 'note/'.$foreign_table_obj->id;
				break;
			case \Config::get('timeline.types.album'):
				$title['value'] = $foreign_table_obj->name;
				$read_more_uri = 'album/'.$foreign_table_obj->id;
				break;
			case \Config::get('timeline.types.album_image'):
				$read_more_uri = 'timeline/'.$foreign_table_obj->id;
				break;
		}

		return render('_parts/quote_article', array('title' => $title, 'body' => $body, 'read_more_uri' => $read_more_uri));
	}

	public static function get_type_for_save_comment_to_foreign_table()
	{
		return array(
			\Config::get('timeline.types.note'),
			\Config::get('timeline.types.album_image_profile'),
		);
	}

	public static function check_type_for_post_foreign_table_comment($type)
	{
		$types_for_save_comment_to_foreign_table = self::get_type_for_save_comment_to_foreign_table();
		if (in_array($type, $types_for_save_comment_to_foreign_table)) return true;

		return false;
	}

	public static function get_comment_parent_id($type, $timeline_id = 0, $foreign_id = 0)
	{
		return self::check_type_for_post_foreign_table_comment($type) ? $foreign_id : $timeline_id;
	}

	public static function get_comment_api_uri($action, $type, $foreign_table = '', $timeline_id = 0, $foreign_id = 0)
	{
		switch ($action)
		{
			case 'create':
				$common_path = 'comment/api/create.json';
				break;
			case 'delete':
				$common_path = 'comment/api/delete.json';
				break;
			case 'get':
			default :
				$common_path = 'comment/api/list/'.$timeline_id.'.html';
				break;
		}

		switch ($type)
		{
			case \Config::get('timeline.types.note'):// note 投稿
				$pre_path = 'note/';
				if ($action == 'get') $common_path = 'comment/api/list/'.$foreign_id.'.html';
				break;
			case \Config::get('timeline.types.album_image_profile'):// profile 写真投稿(album_image)
				$pre_path = 'album/image/';
				if ($action == 'get') $common_path = 'comment/api/list/'.$foreign_id.'.html';
				break;
			default :
				$pre_path = 'timeline/';
				break;
		}

		return $pre_path.$common_path;
	}

	public static function get_timeline_images($type, $foreign_id, $timeline_id = null, $target_member_id = null, $self_member_id = null)
	{
		// defaults
		$images = array();
		$images['file_cate']    = 'ai';
		$images['size']         = 'N_M';
		$images['column_count'] = 3;

		switch ($type)
		{
			case \Config::get('timeline.types.album_image_profile'):
				$images['list']   = array();
				$images['list'][] = \Album\Model_AlbumImage::check_authority($foreign_id);
				$images['additional_table'] = 'profile';
				$images['size']             = 'P_LL';
				$images['column_count']     = 2;
				break;

			case \Config::get('timeline.types.profile_image'):
				$images['list']   = array();
				$images['list'][] = \Model_File::find($foreign_id);
				$images['file_cate']        = 'm';
				$images['size']             = 'LL';
				$images['column_count']     = 2;
				break;

			case \Config::get('timeline.types.note'):
				list($images['list'], $images['count_all']) = \Note\Model_NoteAlbumImage::get_album_image4note_id(
					$foreign_id,
					\Config::get('timeline.articles.thumbnail.limit.default'),
					array('id' => 'asc'),
					true
				);
				$images['additional_table'] = 'note';
				$images['parent_page_uri']  = 'note/'.$foreign_id;
				break;

			case \Config::get('timeline.types.album'):
			case \Config::get('timeline.types.album_image'):
				list($images['list'], $images['count']) = \Site_Model::get_list_and_count('album_image', array(
					'where'    => \Site_Model::get_where_params4list(
						null,
						$self_member_id,
						($self_member_id && $target_member_id == $self_member_id),
						array(array('id', 'in', Model_TimelineChildData::get_foreign_ids4timeline_id($timeline_id)))
					),
					'limit'    => \Config::get('timeline.articles.thumbnail.limit.default'),
					'order_by' => array('created_at' => 'asc'),
				), 'Album');
				$images['count_all'] = \Site_Model::get_count('album_image', array(
					'where' => \Site_Model::get_where_params4list(
						null,
						$self_member_id,
						($self_member_id && $target_member_id == $self_member_id),
						array(array('album_id', $foreign_id))
					),
				), 'Album');
				$images['parent_page_uri']  = 'album/'.$foreign_id;
				break;

			case \Config::get('timeline.types.album_image_timeline'):
				list($images['list'], $images['count']) = \Site_Model::get_list_and_count('album_image', array(
					'where' => \Site_Model::get_where_params4list(
						null,
						$self_member_id,
						($self_member_id && $target_member_id == $self_member_id),
						array(array('id', 'in', Model_TimelineChildData::get_foreign_ids4timeline_id($timeline_id)))
					),
					'limit'    => \Config::get('timeline.articles.thumbnail.limit.album_image_timeline'),
					'order_by' => array('created_at' => 'asc'),
				), 'Album');
				$images['count_all'] = $images['count'];
				$images['parent_page_uri']  = 'timeline/'.$timeline_id;
				break;

			default :
				break;
		}

		return $images;
	}

	public static function get_timeline_object($type_key, $foreign_id)
	{
		if ($type_key == 'album' || $type_key == 'album_image')
		{
			$since_datetime = date('Y-m-d H:i:s', strtotime('- '.\Config::get('timeline.periode_to_update.album')));
			if ($timeline = Model_Timeline::get4latest_foreign_data('album', $foreign_id, $since_datetime))
			{
				return $timeline;
			}
		}

		return Model_Timeline::forge();
	}

	public static function check_is_editable($type)
	{
		$editable_types = array(
			\Config::get('timeline.types.normal'),
			\Config::get('timeline.types.note'),
			\Config::get('timeline.types.album'),
			\Config::get('timeline.types.album_image_timeline'),
		);

		return in_array($type, $editable_types);
	}

	public static function get_delete_api_info(Model_Timeline $timeline)
	{
		$id  = 0;
		$uri = '';
		switch ($timeline->type)
		{
			case \Config::get('timeline.types.normal'):
			case \Config::get('timeline.types.album_image_timeline'):
				$id  = $timeline->id;
				$uri = 'timeline/api/delete.json';
				break;
			case \Config::get('timeline.types.note'):
				$id  = $timeline->foreign_id;
				$uri = 'note/api/delete.json';
				break;
			case \Config::get('timeline.types.album'):
				$id  = $timeline->foreign_id;
				$uri = 'album/api/delete.json';
				break;
			default :
				break;
		}

		return array($id, $uri);
	}

	public static function get_public_flag_info(Model_Timeline $timeline)
	{
		$info = array(
			'model' => 'timeline',
			'public_flag_target_id' => $timeline->id,
			'have_children_public_flag' => false,
			'child_model' => null,
			'disabled_to_update' => false,
		);
		switch ($timeline->type)
		{
			case \Config::get('timeline.types.normal'):
			case \Config::get('timeline.types.album_image_timeline'):
				break;
			case \Config::get('timeline.types.note'):
				$info['model'] = 'note';
				$info['public_flag_target_id'] = $timeline->foreign_id;
				break;
			case \Config::get('timeline.types.album'):
				$info['model'] = 'album';
				$info['public_flag_target_id'] = $timeline->foreign_id;
				$info['have_children_public_flag'] = true;
				$info['child_model'] = 'album_image';
				break;
			case \Config::get('timeline.types.member_register'):
			case \Config::get('timeline.types.profile_image'):
			case \Config::get('timeline.types.album_image_profile'):
			case \Config::get('timeline.types.album_image'):
				$info['disabled_to_update'] = array('message' => '変更できません。');
				break;
			default :
				break;
		}

		return $info;
	}

	public static function get_article_view($timeline_cache_id, $timeline_id)
	{
		$timeline = Model_Timeline::find($timeline_id, array('related' => array('member')));

		return render('timeline::_parts/article', array(
			'timeline_cache_id' => $timeline_cache_id,
			'timeline' => $timeline,
			'truncate_lines' =>\Config::get('timeline.articles.truncate_lines.body'),
		));
	}
}

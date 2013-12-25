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

	public static function get_timeline_save_values($type_key = null)
	{
		if (!$type_key) $type_key = 'normal';
		if (!$type = \Config::get('timeline.types.'.$type_key))
		{
			throw new \InvalidArgumentException("first parameter is invalid.");
		}

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
				$foreign_table = 'album_image';
				$child_foreign_table = 'album_image';
				break;
		}

		return array($type, $foreign_table, $child_foreign_table);
	}


	public static function get_timeline_body($type, $body = null)
	{
		switch ($type)
		{
			case \Config::get('timeline.types.normal'):// 通常 timeline 投稿(つぶやき)
				return $body;
				break;
			case \Config::get('timeline.types.member_register'):// SNS への参加
				return PRJ_SITE_NAME.' に参加しました。';
				break;
			case \Config::get('timeline.types.profile_image'):// profile 写真投稿
			case \Config::get('timeline.types.album_image_profile'):// profile 写真投稿(album_image)
				return \Config::get('term.profile').'写真を設定しました。';
				break;
			case \Config::get('timeline.types.note'):// note 投稿
				return \Config::get('term.note').'を投稿しました。';
				break;
			case \Config::get('timeline.types.album'):// note 投稿
				return \Config::get('term.album').'を作成しました。';
				break;
			default :
				break;
		}

		return $body;
	}

	public static function get_quote_article($type, $foreign_table, $foreign_id)
	{
		$accept_types = array();
		$accept_types[] = \Config::get('timeline.types.note');
		$accept_types[] = \Config::get('timeline.types.album');
		if (!in_array($type, $accept_types)) return null;

		$title = array('value' => '', 'truncate_count' => 0);
		$body  = array('value' => '', 'truncate_count' => 0, 'truncate_type' => 'line');
		$read_more_uri  = '';
		switch ($type)
		{
			case \Config::get('timeline.types.note'):// note 投稿
				$note = \Note\Model_Note::find($foreign_id);
				$title['value'] = $note->title;
				$title['truncate_count'] = \Config::get('site.view_params_default.list.trim_width.title');
				$body['value'] = $note->body;
				$body['truncate_count'] = \Config::get('timeline.articles.truncate_lines.body');
				$read_more_uri = 'note/'.$note->id;
				break;
			case \Config::get('timeline.types.album'):// note 投稿
				$album = \Album\Model_Album::find($foreign_id);
				$title['value'] = $album->name;
				$title['truncate_count'] = \Config::get('site.view_params_default.list.trim_width.title');
				$body['value'] = $album->body;
				$body['truncate_count'] = \Config::get('timeline.articles.truncate_lines.body');
				$read_more_uri = 'album/'.$album->id;
				break;
			default :
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

	public static function get_comment_api_uri($type, $foreign_table = '', $is_post = false, $timeline_id = 0, $foreign_id = 0)
	{
		$common_path = $is_post ? 'comment/api/create.json' : 'comment/api/list/'.$foreign_id.'.html';

		switch ($type)
		{
			case \Config::get('timeline.types.note'):// note 投稿
				$pre_path = 'note/';
				break;
			case \Config::get('timeline.types.album_image_profile'):// profile 写真投稿(album_image)
				$pre_path = 'album/image/';
				break;
			default :
				$pre_path = 'timeline/';
				if (!$is_post) $common_path = 'comment/api/list/'.$timeline_id.'.html';
				break;
		}

		return $pre_path.$common_path;
	}

	public static function get_timeline_images($type, $foreign_id, $timeline_id = null, $target_member_id = null, $self_member_id = null)
	{
		$accept_types = array(
			\Config::get('timeline.types.album_image_profile'),
			\Config::get('timeline.types.profile_image'),
			\Config::get('timeline.types.note'),
			\Config::get('timeline.types.album'),
		);
		if (!in_array($type, $accept_types)) return null;

		$images = array();
		switch ($type)
		{
			case \Config::get('timeline.types.album_image_profile'):
				$images['list']   = array();
				$images['list'][] = \Album\Model_AlbumImage::find($foreign_id);
				$images['file_cate']        = 'ai';
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
					\Config::get('timeline.articles.thumbnail.limit'),
					array('id' => 'asc'),
					true
				);
				$images['file_cate']        = 'ai';
				$images['additional_table'] = 'note';
				$images['size']             = 'N_M';
				$images['column_count']     = 3;
				$images['parent_page_uri']  = 'note/'.$foreign_id;
				break;

			case \Config::get('timeline.types.album'):
				$images = \Site_Model::get_list_and_count('album_image', array(
					'where'    => \Site_Model::get_where_params4list(
						$target_member_id,
						$self_member_id,
						($self_member_id && $target_member_id == $self_member_id),
						array(array('id', 'in', Model_TimelineChildData::get_foreign_ids4timeline_id($timeline_id)))
					),
					'limit'    => \Config::get('timeline.articles.thumbnail.limit'),
					'order_by' => array('created_at' => 'asc'),
				), 'Album');
				$images['file_cate']        = 'ai';
				$images['size']             = 'N_M';
				$images['column_count']     = 3;
				$images['parent_page_uri']  = 'album/'.$foreign_id;
				break;

			default :
				break;
		}

		return $images;
	}

	public static function get_timeline_object($type_key, $foreign_id)
	{
		if ($type_key == 'album')
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
			\Config::get('timeline.types.normal')
		);

		return in_array($type, $editable_types);
	}

	public static function get_article_view($timeline_cache_id, $timeline_id)
	{
		$timeline = Model_Timeline::find($timeline_id, array('related' => array('member')));

		return render('_parts/timeline/article', array(
			'timeline_cache_id' => $timeline_cache_id,
			'timeline' => $timeline,
			'truncate_lines' =>\Config::get('timeline.articles.truncate_lines.body'),
		));
	}
}

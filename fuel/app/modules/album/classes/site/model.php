<?php
namespace Album;

class Site_Model
{
	public static function get_albums($limit, $page = 1, $self_member_id = 0, $target_member_obj = null, $is_mypage = false, $params = array(), $is_return_array = false)
	{
		if ($target_member_obj && !$target_member_obj instanceof \Model_Member) throw new InvalidArgumentException('parameter target_member_obj is invalid.');
		if (!is_array($params)) $params = (array)$params;
		if (!empty($params['select']) && !\DBUtil::field_exists('album', $params['select'])) throw new \ValidationFailedException();

		$params = array_merge($params, array(
			'where' => \Site_Model::get_where_params4list(
				$target_member_obj ? $target_member_obj->id : 0,
				$self_member_id ?: 0,
				$is_mypage,
				!empty($params['where']) ? $params['where'] : array()
			),
			'limit' => $limit,
			'order_by' => array('id' => 'desc'),
		));
		$data = Model_Album::get_pager_list($params, $page, $is_return_array);
		if (!$is_return_array)
		{
			$data['member'] = $target_member_obj;
			$data['is_member_page'] = $target_member_obj ? true : false;
		}		

		return $data;
	}

	public static function get_album_images($limit, $page = 1, $self_member_id = 0, $target_member_obj = null, $is_mypage = false, $params = array(), $is_return_array = false, $is_asc = false)
	{
		if (!is_array($params)) $params = (array)$params;
		if ($target_member_obj && !$target_member_obj instanceof \Model_Member) throw new InvalidArgumentException('parameter target_member_obj is invalid.');
		if (!empty($params['select']) && !\DBUtil::field_exists('album_image', $params['select'])) throw new \ValidationFailedException();

		$params = array_merge($params, array(
			'where' => \Site_Model::get_where_params4list(
				$target_member_obj ? $target_member_obj->id : 0,
				$self_member_id ?: 0,
				$is_mypage,
				!empty($params['where']) ? $params['where'] : array(),
				$target_member_obj ? 'album.member_id' : 'member_id'
			),
			'limit' => $limit,
			'order_by' => array('id' => $is_asc ? 'asc' : 'desc'),
		));
		if ($target_member_obj) $params['related'] = array('album');
		$data = Model_AlbumImage::get_pager_list($params, $page, $is_return_array);
		$data['liked_album_image_ids'] = (conf('like.isEnabled') && $self_member_id) ?
			\Site_Model::get_liked_ids('album_image', $self_member_id, $data['list']) : array();

		return $data;
	}

	public static function set_optional_data2album_image_list($album_images, $start_album_image_id = 0)
	{
		$list_array = array();
		$list_array_post = array();
		$is_set_main = $start_album_image_id ? false : true;
		foreach ($album_images as $key => $row)
		{
			if (!$is_set_main && $key == $start_album_image_id) $is_set_main = true;
			$row['album']  = Model_Album::get_one_basic4id($row['album_id']);
			$row['member'] = \Model_Member::get_one_basic4id($row['album']['member_id']);
			if ($is_set_main)
			{
				$list_array[] = $row;
			}
			else
			{
				$list_array_post[] = $row;
			}
		}

		return array_merge($list_array, $list_array_post);
	}
}


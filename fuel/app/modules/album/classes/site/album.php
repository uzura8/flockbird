<?php
namespace Album;

class Site_Album
{
	public static function get_album_list($page = 1, $member_id = 0)
	{
		$page = (int)$page;
		if ($page < 1) $page = 1;

		$limit  = \Config::get('album.article_list.limit');
		$offset = $limit * ($page - 1);

		$query = Model_Album::query()
			->related('member')
			->order_by('created_at', 'desc');
		if ($member_id) $query = $query->where('member_id', $member_id);

		$count = $query->count();
		$albums = $query->rows_offset($offset)->rows_limit($limit)->get();

		$is_next = ($count > $offset + $limit) ? true : false;

		return array('member_id' => $member_id, 'albums' => $albums, 'page' => $page, 'is_next' => $is_next);
	}

	public static function get_album_image_list($id, $page)
	{
		$page = (int)$page;
		if ($page < 1) $page = 1;

		$limit  = \Config::get('album.article_list.limit');
		$offset = $limit * ($page - 1);

		$query = Model_AlbumImage::find()
			->where('album_id', $id)
			->related('album')->related('file')
			->order_by('created_at', 'desc');

		$count = $query->count();
		$album_images = $query->rows_offset($offset)->rows_limit($limit)->get();

		$is_next = ($count > $offset + $limit) ? true : false;

		return array('id' => $id, 'album_images' => $album_images, 'page' => $page, 'is_next' => $is_next);
	}
}

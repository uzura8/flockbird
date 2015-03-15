<?php
$configs = array(
	//'^api/auth(.*)$' => 'auth/api$1',
	'^api/members/?$' => 'member/api/list',
	'^api/member/setting/config(.*)$' => 'member/setting/api/config$1',
	'^api/member/relation/(\d+)/(\w+)$' => 'member/relation/api/update/$1/$2',
	'^api/filetmp/upload(.*)$' => 'filetmp/api/upload$1',
);

if ($modules_str = get_enabled_modules_str(array('note', 'timeline', 'thread', 'album')))
{
	$configs += array(
		// note, thread, timeline, album
		'^api/('.$modules_str.')s/?$' => array(
			array('GET', new Route('$1/api/list')),
			//array('POST', new Route('$1/api/create')),
		),
		//'^api/('.$modules_str.')s/(\d+)$' => array(
		//	array('GET', new Route('$1/api/detail/$2')),
		//	array('POST', new Route('$1/api/edit/$2')),
		//	array('DELETE', new Route('$1/api/delete/$2')),
		//),
		'^api/('.$modules_str.')/(\d+)/delete/?$' => '$1/api/delete/$2',
		'^api/('.$modules_str.')/(\d+)/update/public_flag/?$' => '$1/api/update_public_flag/$2',
		'^api/('.$modules_str.')/(\d+)/public_flag/?$' => array(
			array('POST', new Route('$1/api/update_public_flag/$2')),
		),
		'^api/('.$modules_str.')s/(\d+)/menu/?$' => 'note/api/menu/$2',
	);
}

if ($modules_str = get_enabled_modules_str(array('note', 'timeline', 'thread')))
{
	$configs += array(
		'^api/('.$modules_str.')s/(\d+)/like$' => array(
			array('POST',   new Route('$1/like/api/update/$2')),
		),
		'^api/('.$modules_str.')s/(\d+)/like/members/?$' => '$1/like/api/member/$2',
		'^api/('.$modules_str.')s/(\d+)/comments/?$' => array(
			array('GET',    new Route('$1/comment/api/list/$2')),
			array('POST',   new Route('$1/comment/api/create/$2')),
		),
		//'^api/('.$modules_str.')s/comments/(\d+)$' => array(
		//	array('DELETE', new Route('$1/comment/api/delete/$2')),
		//),
		'^api/('.$modules_str.')s/comments/(\d+)/delete$' => '$1/comment/api/delete/$2',
		'^api/('.$modules_str.')s/comments/(\d+)/like$' => array(
			array('POST',   new Route('$1/comment/like/api/update/$2')),
		),
		'^api/('.$modules_str.')s/comments/(\d+)/like/members/?$' => '$1/comment/like/api/member/$2',
		'^api/members/(\d+)/(note|album)s$' => '$2/api/member/$1',
	);
}

if ($modules_str = get_enabled_modules_str(array('note', 'album')))
{
	$configs += array(
		'^api/members/(\d+)/(note|album)s$' => '$2/api/member/$1',
	);
}

if (is_enabled('album'))
{
	$configs += array(
		// album_image
		'^api/album/images/?$' => array(
			array('GET', new Route('album/image/api/list')),
			//array('POST', new Route('album/image/api/create')),
		),
		//'^api/album/images/(\d+)$' => array(
		//	array('GET', new Route('album/image/api/detail/$1')),
		//	array('POST', new Route('album/image/api/edit/$1')),
		//	array('DELETE', new Route('album/image/api/delete/$1')),
		//),
		'^api/album/images/(\d+)/delete/?$' => 'album/image/api/delete/$1',
		'^api/album/images/(\d+)/update/public_flag/?$' => 'album/image/api/update_public_flag/$1',
		'^api/album/images/(\d+)/public_flag/?$' => array(
			array('POST', new Route('album/api/image/update_public_flag/$1')),
		),
		'^api/album/images/(\d+)/menu/?$' => 'album/image/api/menu/$1',
		'^api/members/(\d+)/album/images$' => 'album/image/api/member/$1',
		'^api/album/images/(\d+)/like$' => array(
			array('POST',   new Route('album/image/like/api/update/$1')),
		),
		'^api/album/images/(\d+)/comments/?$' => array(
			array('GET',    new Route('album/image/comment/api/list/$1')),
			array('POST',   new Route('album/image/comment/api/create/$1')),
		),
		//'^api/album/images/comments/(\d+)$' => array(
		//	array('DELETE', new Route('album/image/comment/api/delete/$1')),
		//),
		'^api/album/images/comments/(\d+)/delete$' => 'album/image/comment/api/delete/$1',
		'^api/album/images/comments/(\d+)/like$' => array(
			array('POST',   new Route('album/image/comment/like/api/update/$1')),
		),
		'^api/album/images/comments/(\d+)/like/members/?$' => 'album/image/comment/like/api/member/$1',
	);
}

if (is_enabled('notice'))
{
	$configs += array(
		// notice
		'^api/member/notices/?$' => 'notice/api/list',
		'^api/member/notices/(\w+)/(\d+)/?$' => array(
			array('POST', new Route('notice/api/update_watch_status/$1/$2')),
		),
	);
}

return $configs;

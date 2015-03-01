<?php
namespace Album;

class Model_AlbumImageLocation extends \MyOrm\Model
{
	protected static $_table_name = 'album_image_location';

	protected static $_belongs_to = array(
		'album_image' => array(
			'key_from' => 'album_image_id',
			'model_to' => '\Album\Model_AlbumImage',
			'key_to' => 'id',
			'cascade_save' => false,
			'cascade_delete' => false,
		),
	);

	protected static $_properties = array(
		'id',
		'album_image_id' => array(
			'data_type' => 'integer',
			'validation' => array('required', 'max_length' => array(11)),
			'form' => array('type' => false),
		),
		'latitude' => array(
			'label' => '緯度',
			'data_type' => 'DECIMAL',
			'validation' => array('required', 'numeric_between' => array(-90, 90)),
			'form' => array('type' => 'text'),
		),
		'longitude' => array(
			'label' => '経度',
			'data_type' => 'DECIMAL',
			'validation' => array('required', 'numeric_between' => array(-180, 180)),
			'form' => array('type' => 'text'),
		),
		//'latlng' => array(
		//	'data_type' => 'geometry',
		//	'validation' => array('required'),
		//	'form' => array('type' => false),
		//),
		'created_at',
		'updated_at',
	);

	protected static $_observers = array(
		'Orm\Observer_Validation' => array(
			'events' => array('before_save'),
		),
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => true,
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
			'mysql_timestamp' => true,
		),
		//'MyOrm\Observer_ConvertGeometryData' => array(
		//	'events' => array('before_save', 'after_load'),
		//),
	);

	public static function get_locations4album_image_id($album_image_id)
	{
		$obj = self::query()
			->select('latitude', 'longitude')
			->where('album_image_id', $album_image_id)
			->get_one();

		return $obj ? array($obj->latitude, $obj->longitude) : null;
	}

	public static function get_one4album_image_id($album_image_id)
	{
		return self::query()
			->where('album_image_id', $album_image_id)
			->get_one();
	}

	public static function save4album_image_id($album_image_id, $latitude, $longitude)
	{
		$obj = self::get_one4album_image_id($album_image_id) ?: self::forge();
		$obj->album_image_id = $album_image_id;
		$obj->latitude  = $latitude;
		$obj->longitude = $longitude;

		return $obj->save();
	}
}

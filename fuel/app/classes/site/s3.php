<?php
use Aws\S3\S3Client,
		Aws\S3\Enum\CannedAcl,
		Aws\S3\Exception\S3Exception,
		Guzzle\Http\EntityBody,
		Guzzle\Common\Exception\InvalidArgumentException;

class Site_S3
{
	protected static $s3_instantse;

	public static function _init($options = null)
	{
		if (!PRJ_AWS_ACCESS_KEY || !PRJ_AWS_SECRET_KEY || !PRJ_AWS_S3_BUCKET)
		{
			throw new FuelException('AWS Constant not set.');
		}
	}

	protected static function set_s3_instanse()
	{
		if (self::$s3_instantse) return;

		if (!self::$s3_instantse = S3Client::factory(array(
			'key'  => PRJ_AWS_ACCESS_KEY,
			'secret' => PRJ_AWS_SECRET_KEY,
		))) throw new FuelException('S3Client factory failed.');
	}

	protected static function get_key($file_name)
	{
		if (!PRJ_AWS_S3_PATH) return $file_name;

		return sprintf('%s/%s', trim(PRJ_AWS_S3_PATH, '/'), $file_name);
	}

	public static function get_url($file_name)
	{
		return sprintf('https://%s.s3.amazonaws.com/%s', PRJ_AWS_S3_BUCKET, static::get_key($file_name));
	}

	public static function save($file_path, $file_name = null, $is_private_acl = false)
	{
		if (!file_exists($file_path)) throw new FuelException('File not exists.');
		static::set_s3_instanse();

		return self::$s3_instantse->putObject(array(
			'Bucket' => PRJ_AWS_S3_BUCKET,
			'Key'    => static::get_key($file_name ?: \Site_Upload::get_file_name_from_file_path($file_path)),
			'Body'   => EntityBody::factory(fopen($file_path, 'r')),
			'ACL'    => $is_private_acl ? CannedAcl::PRIVATE_ACCESS : CannedAcl::PUBLIC_READ,
		));
	}

	public static function delete($file_name)
	{
		static::set_s3_instanse();

		return self::$s3_instantse->deleteObject(array(
			'Bucket' => PRJ_AWS_S3_BUCKET,
			'Key'    => static::get_key($file_name),
		));
	}

	public static function copy($file_name_from, $file_name_to, $is_private_acl = false)
	{
		static::set_s3_instanse();

		return self::$s3_instantse->copyObject(array(
			'Bucket' => PRJ_AWS_S3_BUCKET,
			'Key'    => static::get_key($file_name_to),
			'CopySource' => PRJ_AWS_S3_BUCKET.'/'.static::get_key($file_name_from),
			'ACL'    => $is_private_acl ? CannedAcl::PRIVATE_ACCESS : CannedAcl::PUBLIC_READ,
		));
	}

	public static function move($file_name_from, $file_name_to, $is_private_acl = false)
	{
		static::set_s3_instanse();
		if (!$result_copy = static::copy($file_name_from, $file_name_to, $is_private_acl))
		{
			throw new FuelException('Failed to copy S3 object.');
		}
		static::delete($file_name_from);

		return $result_copy;
	}

	public static function change_acl($file_name, $acl)
	{
		static::set_s3_instanse();

		return self::$s3_instantse->putObjectAcl(array(
			'Bucket' => PRJ_AWS_S3_BUCKET,
			'Key'    => static::get_key($file_name),
			'ACL'    => $acl,
		));
	}
}

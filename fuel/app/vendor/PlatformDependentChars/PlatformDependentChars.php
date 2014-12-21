<?php
class PlatformDependentChars {
	static public function check($str, $input_encoding = false, $to_encoding = 'SJIS') {
		if (! $input_encoding) {
			$input_encoding = mb_internal_encoding();
		}
		if (self::isSjis($input_encoding)) {
			$to_encoding   = 'UTF-8';
		}
		$convertedStr = self::reconvert($str, $input_encoding, $to_encoding);
		return ($str == $convertedStr);
	}

	static public function isSjis($encoding = false) {
		$sjisNames = self::getSjisAliases();
		$sjisNames[] = 'SJIS';
		return self::arrayKeyExistsCaseInsensitive($encoding, $sjisNames);
	}

	static public function getSjisAliases() {
		$aliases = mb_encoding_aliases('SJIS');
		if ($mimeName = mb_preferred_mime_name('SJIS')) {
			$aliases[] = $mimeName;
		}
		return $aliases;
	}

	static public function arrayKeyExistsCaseInsensitive($key, $search) {
		return array_key_exists(strtolower($key), array_change_key_case($search));
	}

	static public function reconvert($str, $input_encoding, $to_encoding) {
		$convertedStr = mb_convert_encoding($str,          $to_encoding,   $input_encoding);
		$convertedStr = mb_convert_encoding($convertedStr, $input_encoding, $to_encoding);
		return $convertedStr;
	}
}


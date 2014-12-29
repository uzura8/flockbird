<?php

class Util_StrTruncater
{
	protected $cut_length   = 0;// 実際の切り取り文字数
	protected $offset       = 0;// 確認済み文字数
	protected $check_length = 0;// 残確認文字数(タグは「0」で、特殊文字は「1」で減算)
	protected $tags = array();
	protected $options = array();

	public function __construct($options = array())
	{
		$this->options = array(
			'single_tags' => array('br', 'hr', 'img'),// 閉じタグが不要なタグ
		);
		if ($options) $this->options = $options + $this->options;
	}

	public function execute($string, $limit, $continuation = '...', $is_html = false)
	{
		$this->cut_length = $limit;// 切り取り文字数の初期値をセット
		if ($is_html)
		{
			$this->check_length = $limit;// 確認文字数の初期値をセット

			// Handle all the html tags.
			preg_match_all('/(<[^>]+>)([^<]*)/', $string, $matches, PREG_SET_ORDER);
			// tag がない場合
			if (!$matches)
			{
				// 確認対象文字列に特殊文字を含む場合
				if ($specialchars = static::get_all_specialchars($string))
				{
					list($is_finished, $added_cut_length, $offset) = $this->handle_specialchars($string, $specialchars);
					$this->cut_length += $added_cut_length;// 特殊文字の分の切り詰め文字数を補正
					$this->offset += $offset;
				}
			}
			// tag がある場合
			else
			{
				foreach ($matches as $match)
				{
					// 最初の tag 前の処理
					if (!$this->offset)
					{
						$first_tag_pos = Str::pos($string, $match[0]);
						if ($this->execute_before_first_tag($string, $first_tag_pos)) break;
					}

					$this->cut_length += Str::length($match[1]);// tag の分の切り詰め文字数を補正
					$this->offset += Str::length($match[0]);

					// handle tag.
					$this->handle_tags($match[0]);

					$not_tag_str_length = Str::length($match[2]);// tag 以外の文字数
					$sc_check_target_str = $match[2];// 確認対象文字列(タグ以外)
					// 確認対象文字列に特殊文字を含む場合
					if ($specialchars = static::get_all_specialchars($sc_check_target_str))
					{
						list($is_finished, $added_cut_length, $offset_sc) = $this->handle_specialchars($sc_check_target_str, $specialchars);
						$this->cut_length += $added_cut_length;// 特殊文字の分の切り詰め文字数を補正
						$this->offset += $offset_sc;
						if ($is_finished) break;
					}
					// 確認対象文字列に特殊文字を含まない場合
					else
					{
						if ($this->check_length <= $not_tag_str_length)
						{	
							$this->check_length = 0;
							break;
						}
						$this->check_length -= $not_tag_str_length;
						$this->offset += $not_tag_str_length;
					}
				}
			}
		}
		$new_string  = Str::sub($string, 0, $this->cut_length);// 補正された切り詰め文字数で切り取り
		$new_string .= (count($this->tags = array_reverse($this->tags)) ? '</'.implode('></',$this->tags).'>' : '');// 閉じタグを適切に補完
		$new_string .= (Str::length($string) > $this->cut_length ? $continuation : '');// ... を追加

		return $new_string;
	}

	protected function handle_specialchars($string, $specialchars)
	{
		$is_finished = false;// 確認終了フラグ
		$added_cut_length = 0;// 加算分切り取り文字数
		$offset = 0;// 確認済み文字数
		foreach ($specialchars as $specialchar)
		{
			$pos = Str::pos($string, $specialchar, $offset);// 確認対象特殊文字までの文字数
			$advanced_pos = $pos - $offset;// このターンで進んだ文字数

			// 特殊文字までに切り取りが完了する場合
			if ($this->check_length <= $advanced_pos)
			{
				$is_finished = true;
				$this->check_length = 0;
				$offset += $this->check_length;

				return array($is_finished, $added_cut_length, $offset);
			}
			$offset += $advanced_pos;
			$this->check_length -= $advanced_pos;// 確認済み文字数を減算

			$sc_str_length = Str::length($specialchar);// 特殊文字の文字数
			$added_cut_length += ($sc_str_length - 1);// 特殊文字数分を切り取り文字数に加算
			$offset += $sc_str_length;

			// 特殊文字で切り取りが完了する場合
			if ($this->check_length == 1)
			{
				$is_finished = true;
				$this->check_length = 0;

				return array($is_finished, $added_cut_length, $offset);
			}
			$this->check_length--;// 特殊文字分を減算
		}

		// 特殊文字以降の確認
		$rest_length = Str::length($string) - $offset;
		if ($this->check_length <= $rest_length)
		{
			$is_finished = true;
			$offset += $this->check_length;
			$this->check_length = 0;

			return array($is_finished, $added_cut_length, $offset);
		}

		$offset += $rest_length;
		$this->check_length -= $rest_length;

		return array($is_finished, $added_cut_length, $offset);
	}

	// 最初のタグ前の確認処理
	protected function execute_before_first_tag($string, $first_tag_pos)
	{
		$sc_check_target_str = Str::sub($string, 0, $first_tag_pos);
		// 確認対象文字列に特殊文字を含む場合
		if ($specialchars = static::get_all_specialchars($sc_check_target_str))
		{
			list($is_finished, $added_cut_length, $offset) = $this->handle_specialchars($sc_check_target_str, $specialchars);
			$this->cut_length += $added_cut_length;// 特殊文字の分の切り詰め文字数を補正
			$this->offset += $offset;
			if ($is_finished) return true;
		}
		// 確認対象文字列に特殊文字を含まない場合
		else
		{
			if ($this->check_length <= $first_tag_pos)
			{
				$this->cut_length = $this->check_length;
				$this->check_length = 0;
				return true;
			}
			$this->offset = $first_tag_pos;
			$this->check_length -= $first_tag_pos;
		}

		return false;
	}

	protected function handle_tags($string)
	{
		$tag = Str::sub(strtok($string, " \t\n\r\0\x0B>"), 1);
		if($tag[0] != '/' && !in_array($tag, $this->options['single_tags']))
		{
			$this->tags[] = $tag;
		}
		elseif (end($this->tags) == Str::sub($tag, 1))
		{
			array_pop($this->tags);
		}
	}

	// 特殊文字を全て取得し、配列で返す
	protected static function get_all_specialchars($string)
	{
		if (!preg_match_all('/&[a-z]+;/i', $string, $matches, PREG_SET_ORDER)) return array();

		$specialchars = array();
		foreach ($matches as $match) $specialchars[] = $match[0];

		return $specialchars;
	}
}

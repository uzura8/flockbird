<?php

class Html extends Fuel\Core\Html
{
	/**
	 * Creates an html image tag
	 *
	 * Sets the alt atribute to filename of it is not supplied.
	 * DocumentRoot 以下の絶対パスを返すように改修
	 *
	 * @param	string	the source
	 * @param	array	the attributes array
	 * @return	string	the image tag
	 */
	public static function img($src, $attr = array(), $is_absolute_url = false)
	{
		if ( ! preg_match('#^(\w+://)# i', $src))
		{
			$src = Site_Util::get_media_uri($src, $is_absolute_url);
		}
		$attr['src'] = $src;
		$attr['alt'] = (isset($attr['alt'])) ? $attr['alt'] : pathinfo($src, PATHINFO_FILENAME);
		return html_tag('img', $attr);
	}
}

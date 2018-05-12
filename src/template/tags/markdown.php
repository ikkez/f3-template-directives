<?php
/**
 *	Markdown TagHandler
 *
 *	The contents of this file are subject to the terms of the GNU General
 *	Public License Version 3.0. You may not use this file except in
 *	compliance with the license. Any of the license terms and conditions
 *	can be waived if you get permission from the copyright holder.
 *
 *	Copyright (c) 2018 ~ ikkez
 *	Christian Knuth <ikkez0n3@gmail.com>
 *
 *	@version: 1.0.0
 *	@date: 09.05.2018
 *
 **/

namespace Template\Tags;

class Markdown extends \Template\TagHandler {

	/**
	 * build tag string
	 * @param $attr
	 * @param $content
	 * @return string
	 */
	function build($attr, $content) {
		if (array_key_exists('src', $attr)) {
			$src = $this->tokenExport($attr['src']);
			$md_string = 'file_exists('.$src.') ? \Base::instance()->read('.$src.') : ""';
			$md = '<?php '.
				'$md_content='.$md_string.'; '.
				'eval("?>".\Markdown::instance()->convert('.
				'$this->resolve($md_content,get_defined_vars(),0,FALSE,false)));'.
				' ?>';
			return $md;
		} elseif ($content) {
			return \Markdown::instance()->convert(trim($content));
		} else return '';
	}


}

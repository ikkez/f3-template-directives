<?php
/**
 *	Textarea TagHandler
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

class Textarea extends \Template\TagHandler {

	/**
	 * build tag string
	 * @param $attr
	 * @param $content
	 * @return string
	 */
	function build($attr, $content) {
		$srcKey = Form::instance()->getSrcKey();

		if (isset($attr['name'])) {
			$name = $this->attrExport($attr['name']);

			if ($content=="")
				$content = $this->tmpl->build('{{ isset(@'.$srcKey.$name.')?@'.$srcKey.$name.':\'\'}}');
		}

		// resolve all other / unhandled tag attributes
		if ($attr!=null)
			$attr = $this->resolveParams($attr);

		// create element and return
		return '<textarea'.$attr.'>'.$content.'</textarea>';
	}
}
<?php
/**
 *	Select-Option TagHandler
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

class Option extends \Template\TagHandler {

	/**
	 * build tag string
	 * @param $attr
	 * @param $content
	 * @return string
	 */
	function build($attr, $content) {
		$name = Select::instance()->getActiveName();

		$isSelected='';
		if ($name && array_key_exists("value", $attr)) {
			$name = Form::instance()->getSrcKey().$name;
			$value = $this->tokenExport($attr['value']);

			if (preg_match('/\[\]$/s', $name)) {
				$name=substr($name,0,-2);
				$cond = '(isset(@'.$name.') && is_array(@'.$name.')'.
					' && in_array('.$value.',@'.$name.'))';
			} else {
				$cond = '(isset(@'.$name.') && @'.$name.'=='.$value.')';
			}
			$isSelected = ' '.$this->tmpl->build('{{'.$cond.'?\'selected="selected"\':\'\'}}');
		}

		// resolve all other / unhandled tag attributes
		$attr = $this->resolveParams($attr);
		// create element and return
		return '<option'.$attr.$isSelected.'>'.$content.'</option>';
	}


}

<?php
/**
 *	Select TagHandler
 *
 *	The contents of this file are subject to the terms of the GNU General
 *	Public License Version 3.0. You may not use this file except in
 *	compliance with the license. Any of the license terms and conditions
 *	can be waived if you get permission from the copyright holder.
 *
 *	Copyright (c) 2020 ~ ikkez
 *	Christian Knuth <ikkez0n3@gmail.com>
 *
 *	@version: 1.2.0
 *	@date: 30.04.2020
 *
 **/

namespace Template\Tags;

class Select extends \Template\TagHandler {

	protected $name;

	function getActiveName() {
		return $this->name;
	}

	/**
	 * @param array $node
	 * @param array $attr
	 * @return string
	 */
	protected function resolveContent($node, $attr) {
		// make the current scope available to nested elements
		if (array_key_exists("name", $attr))
			$this->name = $this->attrExport($attr['name']);
		$out = parent::resolveContent($node,$attr);
		$this->name = NULL;
		return $out;
	}

	/**
	 * build tag string
	 * @param $attr
	 * @param $content
	 * @return string
	 */
	function build($attr, $content) {
		$srcKey = Form::instance()->getSrcKey();

		if (array_key_exists("name", $attr))
			$name = $this->attrExport($attr['name']);

		if (array_key_exists('group', $attr)) {
			$attr['group'] = $this->tmpl->token($attr['group']);

			// when value is an array, use different key
			$key = array_key_exists("key", $attr) ? '@val[\''.$attr['key'].'\']' : '@key';
			$label = array_key_exists("label", $attr) ? '@val[\''.$attr['label'].'\']' : '@val';

			if (preg_match('/\[\]$/s', $name)) {
				$name=substr($name,0,-2);
				$cond = '(isset(@'.$srcKey.$name.') && is_array(@'.$srcKey.$name.')'.
					' && in_array('.$key.',@'.$srcKey.$name.'))';
			} else
				$cond = '(isset(@'.$srcKey.$name.') && @'.$srcKey.$name.'=='.$key.')';

			$content .= '<?php foreach('.$attr['group'].' as $key => $val) {?>'.
				$this->tmpl->build('<option value="{{'.$key.'}}"'.
					'{{'.$cond.'?'.
					'\' selected="selected"\':\'\'}}>{{'.$label.'|esc}}</option>').
				'<?php } ?>';
			unset($attr['group']);
		}

		// resolve all other / unhandled tag attributes
		$attr = $this->resolveParams($attr);
		// create element and return		
		return '<select'.$attr.'>'.$content.'</select>';
	}
}

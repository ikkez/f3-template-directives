<?php
/**
 *	Abstract TagHandler for creating own Tag-Element-Renderer
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
 *	@since: 14.07.2015
 *
 **/

namespace Template;

abstract class TagHandler extends \Prefab {

	/** @var \Template */
	protected $tmpl;

	/** @var \Base */
	protected $f3;

	/** @var string */
	protected $tag_name;

	/**
	 * TagHandler constructor.
	 */
	function __construct() {
		$this->tmpl = \Template::instance();
		$this->f3 = \Base::instance();
	}

	/**
	 * set template engine
	 * @param $tmpl
	 */
	function setTemplateEngine($tmpl) {
		$this->tmpl = $tmpl;
	}

	/**
	 * set tag name
	 * @param $tmpl
	 */
	protected function setTagName($tag) {
		$this->tag_name = $tag;
	}

	/**
	 * register tag handler to template engine
	 * @param $name
	 * @param \Template|NULL $tmpl
	 * @param array $opt options array
	 */
	static public function init($name, \Template $tmpl=NULL, $opt=[]) {
		if (!$tmpl)
			$tmpl = \Template::instance();
		$class = get_called_class();
		if (!empty($opt) && is_array($opt))
			$obj = new static($opt);
		else
			$obj = new static();
		$obj->setTemplateEngine($tmpl);
		$obj->setTagName($name);
		\Registry::set('tag_'.$name, $obj);
		$tmpl->extend($name,[$obj,'process']);
	}

	/**
	 * return string representation of current tag instance
	 * @return string
	 */
	function getTagReferenceString() {
		return '\Registry::get(\'tag_'.$this->tag_name.'\')';
	}

	/**
	 * return registered tag handler instance
	 * @param $tag
	 * @return object
	 */
	static function getTagReference($tag) {
		return \Registry::get('tag_'.$tag);
	}

	/**
	 * build tag string
	 * @param array $attr
	 * @param string $content
	 * @return string
	 */
	abstract function build($attr,$content);

	/**
	 * lazy static handler to render a node
	 * @param array $node
	 * @return string
	 */
	static public function render($node) {
		/** @var TagHandler $handler */
		$handler = static::instance();
		return $handler->process($node);
	}

	/**
	 * process node and build the element
	 * @param $node
	 * @return string
	 */
	function process($node) {
		if (isset($node['@attrib'])) {
			$attr = (array) $node['@attrib'];
			unset($node['@attrib']);
		} else
			$attr=[];
		$content = $this->resolveContent($node, $attr);
		return $this->build($attr,$content);
	}

	/**
	 * render the inner content
	 * @param array $node
	 * @param array $attr
	 * @return string
	 */
	protected function resolveContent($node, $attr) {
		return (isset($node[0])) ? $this->tmpl->build($node) : '';
	}

	/**
	 * general bypass for unhandled tag attributes
	 * @param array $params
	 * @return string
	 */
	protected function resolveParams(array $params) {
		$out = '';
		foreach ($params as $key => $value) {
			// build dynamic tokens
			if (preg_match('/{{(.+?)}}/s', $value?:''))
				$value = $this->tmpl->build($value);
			if (preg_match('/{{(.+?)}}/s', $key?:''))
				$key = $this->tmpl->build($key);
			// inline token
			if (is_numeric($key))
				$out .= ' '.$value;
			// value-less parameter
			elseif ($value == NULL)
				$out .= ' '.$key;
			// key-value parameter
			else
				$out .= ' '.$key.'="'.$value.'"';
		}
		return $out;
	}

	/**
	 * export a stringified token variable
	 * to handle mixed attribute values correctly
	 * @param $val
	 * @return string
	 */
	protected function tokenExport($val) {
		if (empty($val) && $val !== "0")
			return '\'\'';
		$split = preg_split('/({{.+?}})/s', $val, -1,
			PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		foreach ($split as &$part) {
			if (preg_match('/({{.+?}})/s', $part))
				$part = $this->tmpl->token($part);
			else
				$part = "'".$part."'";
			unset($part);
		}
		$val = implode('.', $split);
		return $val;
	}

	/**
	 * export resolved attribute values for further processing
	 * samples:
	 * value			=> ['value']
	 * {{@foo}}			=> [$foo]
	 * value-{{@foo}}	=> ['value-'.$foo]
	 * foo[bar][]		=> ['foo']['bar'][]
	 * foo[{{@bar}}][]	=> ['foo'][$bar][]
	 *
	 * @param $attr
	 * @return mixed|string
	 */
	protected function attrExport($attr) {
		$ar_split=preg_split('/\[(.+?)\]/s',$attr,-1,
			PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
		if (count($ar_split)>1) {
			foreach ($ar_split as &$part) {
				if ($part=='[]')
					continue;
				$part='['.$this->tokenExport($part).']';
				unset($part);
			}
			$val = implode($ar_split);
		} else {
			$val = $this->tokenExport($attr);
			$ar_name = preg_replace('/\'*(\w+)(\[.*\])\'*/i','[\'$1\']$2', $val,-1,$i);
			$val = $i ? $ar_name : '['.$val.']';
		}
		return $val;
	}
}

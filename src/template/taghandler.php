<?php
/**
 *	Abstract TagHandler for creating own Tag-Element-Renderer
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
 *	@since: 14.07.2015
 *
 **/

namespace Template;

abstract class TagHandler extends \Prefab {

	/** @var \Template */
	protected $tmpl;

	/** @var \Base */
	protected $f3;

	/**
	 * TagHandler constructor.
	 */
	function __construct() {
		$this->tmpl = \Template::instance();
		$this->f3 = \Base::instance();
	}

	/**
	 * register tag handler to template engine
	 * @param $name
	 * @param \Template|NULL $tmpl
	 * @param array $args
	 */
	static public function init($name,\Template $tmpl=NULL,$args=[]) {
		if (!$tmpl)
			$tmpl = \Template::instance();
		if (!empty($args) && is_array($args))
			$obj = static::instance($args);
		else
			$obj = static::instance();
		$obj->tmpl = $tmpl;
		$tmpl->extend($name,[$obj,'process']);
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
			if (preg_match('/{{(.+?)}}/s', $value))
				$value = $this->tmpl->build($value);
			if (preg_match('/{{(.+?)}}/s', $key))
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
		if(empty($val))
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
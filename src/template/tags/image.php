<?php
/**
 *	Image TagHandler
 *
 *	The contents of this file are subject to the terms of the GNU General
 *	Public License Version 3.0. You may not use this file except in
 *	compliance with the license. Any of the license terms and conditions
 *	can be waived if you get permission from the copyright holder.
 *
 *	Copyright (c) 2020 ~ ikkez
 *	Christian Knuth <ikkez0n3@gmail.com>
 *
 *	@version: 1.1.0
 *	@date: 05.05.2020
 *	@since: 05.11.2015
 *
 **/

namespace Template\Tags;

class Image extends \Template\TagHandler {

	protected $options = [
		'temp_dir' => 'img/',
		'file_type' => 'jpeg', // png, jpeg, gif, wbmp
		'default_quality' => 75,
		'check_UI_path' => false,
		'not_found_fallback' => NULL,
		'not_found_callback' => NULL,
	];

	/**
	 * Image constructor.
	 * @param array $options
	 */
	function __construct($options=[]) {
		$this->setOptions($options);
		parent::__construct();
	}

	/**
	 * set options
	 * @param array $args
	 */
	function setOptions(array $args) {
		$this->options=array_replace_recursive($this->options,$args);
	}

	/**
	 * return defined options
	 * @return array|mixed
	 */
	function getOptions() {
		return $this->options;
	}

	/**
	 * build tag string
	 * @param $attr
	 * @param $content
	 * @return string
	 */
	function build($attr, $content) {

		if (isset($attr['src']) && (isset($attr['width'])||isset($attr['height']))) {
			$opt = [
				'width'=>null,
				'height'=>null,
				'crop'=>false,
				'enlarge'=>false,
				'quality'=>$this->options['default_quality'],
			];
			// merge into defaults
			$opt = array_intersect_key($attr + $opt, $opt);
			// get dynamic path
			$path = preg_match('/{{(.+?)}}/s',$attr['src']) ?
				$this->tmpl->token($attr['src']) : var_export($attr['src'],true);
			// clean up attributes
			$attr=array_diff_key($attr,$opt);
			$opt = var_export($opt,true);
			unset($attr['src']);
			$out='<img src="<?php echo '.$this->getTagReferenceString().'->resize('.
				$path.','.$opt.');?>"'.$this->resolveParams($attr).' />';
		} else
			// just forward / bypass further processing
			$out = '<img'.$this->resolveParams($attr).' />';

		return $out;
	}

	/**
	 * on demand image resize
	 * @param $path
	 * @param $opt
	 * @return string
	 */
	function resize($path,$opt) {
		$hash = $this->f3->hash($path.$this->f3->serialize($opt));
		$ext = $this->options['file_type'];
		if ($ext=='jpeg')
			$ext='jpg';
		elseif($ext=='wbmp')
			$ext='bmp';
		$new_file_name = $hash.'.'.$ext;
		$dst_path = $this->options['temp_dir'];
		if (!file_exists($dst_path.$new_file_name)) {
			$path = explode('/', $path);
			$file = array_pop($path);
			$src_path = implode('/',$path).'/';
			$found=false;
			if ($this->options['check_UI_path']) {
				foreach ($this->f3->split($this->f3->UI,FALSE) as $dir)
					if (is_file($dir.$src_path.$file)) {
						$src_path=$dir.$src_path;
						$found=true;
						break;
					}
			} elseif (file_exists($src_path.$file))
				$found=true;
			if ($found) {
				$imgObj = new \Image($file, false, $src_path);
			} else {
				if ($this->options['not_found_callback'])
					$this->f3->call($this->options['not_found_callback'],array($src_path.$file));
				if ($this->options['not_found_fallback'])
					$imgObj = new \Image($this->options['not_found_fallback'], false);
				else
					return 'http://placehold.it/250x250?text=Not+Found';
			}
			if (!is_dir($dst_path))
				mkdir($dst_path,0775,true);
			$ow = $imgObj->width();
			$oh = $imgObj->height();
			if (!$opt['width'])
				$opt['width'] = round(($opt['height']/$oh)*$ow);
			if (!$opt['height'])
				$opt['height'] = round(($opt['width']/$ow)*$oh);
			if ($ext=='png')
				$opt['quality']=max(0,round(((int)$opt['quality'])/10)-1);
			$imgObj->resize((int)$opt['width'], (int)$opt['height'], $opt['crop'], $opt['enlarge']);
			$file_data = $imgObj->dump($this->options['file_type'], $opt['quality']);
			$this->f3->write($dst_path.$new_file_name, $file_data);
		}
		return $dst_path.$new_file_name;
	}
}
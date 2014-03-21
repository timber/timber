<?php

class TimberImage extends TimberCore {

	var $_can_edit;
	var $_dimensions;
	var $abs_url;
	var $PostClass = 'TimberPost';
	var $object_type = 'image';

	public static $representation = 'image';

	function __construct($iid) {
		$this->init($iid);
	}

	function __toString() {
		if ($this->get_src()){
			return $this->get_src();
		}
		return '';
	}

	function get_pathinfo(){
		return pathinfo($this->file);
	}

	function get_dimensions($dim = null){
		if (isset($this->_dimensions)){
			return $this->get_dimensions_loaded($dim);
		}
		list($width, $height) = getimagesize($this->file_loc);
		$this->_dimensions = array();
		$this->_dimensions[0] = $width;
		$this->_dimensions[1] = $height;
		TimberHelper::error_log($this->_dimensions);
		return $this->get_dimensions_loaded($dim);
	}

	function get_dimensions_loaded($dim){
		if ($dim == null){
			return $this->_dimensions;
		}
		if ($dim == 'w' || $dim == 'width'){
			return $this->_dimensions[0];
		}
		if ($dim == 'h' || $dim == 'height'){
			return $this->_dimensions[1];
		}
	}

	function get_width(){
		return $this->get_dimensions('width');
	}

	function get_height(){
		return $this->get_dimensions('height');
	}

	function get_src( $size = '' ) {
		if (isset($this->abs_url)) {
			return $this->abs_url;
		}

        if ($size && is_string($size) && isset($this->sizes[$size])) {
        	$image = image_downsize($this->ID, $size);
          return reset($image);
        }

        if (!isset($this->file) && isset($this->_wp_attached_file)) {
			$this->file = $this->_wp_attached_file;
		}

		if (!isset($this->file))
            return false;

        $dir = wp_upload_dir();
        $base = ($dir["baseurl"]);
        return trailingslashit($base) . $this->file;
  	}

	function get_path() {
		if (strlen($this->abs_url)) {
			return $this->abs_url;
		}
		return get_permalink($this->ID);
	}

	function get_parent() {
		if (!$this->post_parent) {
			return false;
		}
		return new $this->PostClass($this->post_parent);
	}

	function init($iid) {
		if (!is_numeric($iid) && is_string($iid)) {
			if (strstr($iid, '://')) {
				$this->init_with_url($iid);
				return;
			} else if (strstr(strtolower($iid), '.jpg')) {
				$this->init_with_url($iid);
			}
		}
		$image_info = $iid;
		if (is_numeric($iid)) {
			$image_info = wp_get_attachment_metadata($iid);
			if (!is_array($image_info)) {
				$image_info = array();
			}
			$image_custom = get_post_custom($iid);
			$basic = get_post($iid);
			if ($basic){
				if (isset($basic->post_excerpt)){
					$this->caption = $basic->post_excerpt;
				}
				$image_custom = array_merge($image_custom, get_object_vars($basic));
			}
			$image_info = array_merge($image_info, $image_custom);
		} else if (is_array($image_info) && isset($image_info['image'])) {
			$image_info = $image_info['image'];
		} else if (is_object($image_info)) {
			$image_info = get_object_vars($image_info);
		}
		$this->import($image_info);
		$basedir = wp_upload_dir();
		$basedir = $basedir['basedir'];
		if (isset($this->file)){
			$this->file_loc = $basedir . DIRECTORY_SEPARATOR . $this->file;
		} else if (isset($this->_wp_attached_file)){
			$this->file = reset($this->_wp_attached_file);
			$this->file_loc = $basedir . DIRECTORY_SEPARATOR . $this->file;
		}
		if (isset($image_info['id'])) {
			$this->ID = $image_info['id'];
		} else if (is_numeric($iid)) {
			$this->ID = $iid;
		}
		if (isset($this->ID)) {
			$custom = get_post_custom($this->ID);
			foreach ($custom as $key => $value) {
				$this->$key = $value[0];
			}
		} else {
			if (is_array($iid)){
				TimberHelper::error_log('Not able to init in TimberImage with iid=');
				TimberHelper::error_log($iid);
			} else {
				TimberHelper::error_log('Not able to init in TimberImage with iid=' . $iid);
			}
		}
	}

	function init_with_url($url) {
		$this->abs_url = $url;
		$this->file_loc = $url;
		if (TimberHelper::is_local($url)){
			$this->file = ABSPATH . TimberHelper::get_rel_url($url);
		}
	}

	/* deprecated */
	function get_url() {
		return $this->get_src();
	}

	/* Alias */

	public function aspect(){
		$w = intval($this->width());
		$h = intval($this->height());
		return $w/$h;
	}

	public function height(){
		return $this->get_height();
	}

	public function src($size = '') {
		return $this->get_src($size);
	}

	public function width(){
		return $this->get_width();
	}
}

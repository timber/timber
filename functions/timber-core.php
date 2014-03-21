<?php

class TimberCore {

	function import($info) {
		if (is_object($info)) {
			$info = get_object_vars($info);
		}
		if (is_array($info)) {
			foreach ($info as $key => $value) {
				if(!empty($key)){
					$this->$key = $value;
				}
			}
		}
	}

	function update($key, $value){
		update_metadata($this->object_type, $this->ID, $key, $value);
	}

	function can_edit() {
		if (isset($this->_can_edit)) {
			return $this->_can_edit;
		}
		$this->_can_edit = false;
		if (!function_exists('current_user_can')) {
			return false;
		}
		if (current_user_can('edit_post', $this->ID)) {
			$this->_can_edit = true;
		}
		return $this->_can_edit;
	}

	function get_method_values(){
		$ret = array();
		$ret['can_edit'] = $this->can_edit();
		return $ret;
	}

	//deprecated
	function url_to_path($url = '') {
		if (!strlen($url) && $this->url) {
			$url = $this->url;
		}
		$url_info = parse_url($url);
		return $url_info['path'];
	}
}

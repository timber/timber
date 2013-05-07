<?php
	
	class TimberCore {

		function import($info){
			if (is_object($info)){
				$info = get_object_vars($info);
			}
			if (is_array($info)){
				foreach($info as $key=>$value){
					$this->$key = $value;
				}
			}
		}

		function url_to_path($url = ''){
			if (!strlen($url) && $this->url){
				$url = $this->url;
			}
			$url_info = parse_url($url);
			return $url_info['path'];
		}
	}
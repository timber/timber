<?php
	
	class TimberUser extends TimberCore {

		function __construct($uid){
			$this->init($uid);
		}

		function init($uid){
			if (function_exists('get_userdata')){
				$data = get_userdata($uid); 
				$this->import($data->data);
			}
			$this->ID = $uid;
			$this->import_custom();
		}

		function get_custom(){
			$um = get_user_meta($this->ID);
			$custom = new stdClass();
			foreach($um as $key => $value){
				$v = $value[0];
				$custom->$key = $v;
				if (is_serialized($v)){
					if (gettype(unserialize($v)) == 'array'){
						$custom->$key = unserialize($v);
					}
				}
			}
			return $custom;
		}

		function import_custom(){
			$custom = $this->get_custom();
			$this->import($custom);			
		}

		
		function name(){
			return $this->display_name;
		}

		function path(){
			return '/author/'.$this->slug();
		}

		function slug(){
			return $this->user_nicename;
		}
	}
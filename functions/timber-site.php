<?php

	class TimberSite extends TimberCore {
		function __construct($site_name_or_id = null){
			$this->init($site_name_or_id);
		}

		function init($site_name_or_id){
			if ($site_name_or_id === null){
				//this is necessary for some reason, otherwise returns 1 all the time
				restore_current_blog();
				$site_name_or_id = get_current_blog_id();
			}
			$info = get_blog_details($site_name_or_id);
			$this->import($info);
			$this->ID = $info->blog_id;
			$this->name = $this->blogname;
			$this->title = $this->blogname;
			$theme_slug = get_blog_option($info->blog_id, 'stylesheet');
			//echo 'init '.$theme_slug;
			$this->theme = new TimberTheme($theme_slug);
			$this->description = get_blog_option($info->blog_id, 'blogdescription');
		}

		function __get($field){
			if (!isset($this->$field)){
				$this->$field = get_blog_option($this->ID, $field);
			}
			return $this->$field;
		}

		function get_link(){
			return $this->siteurl;
		}

		function get_url(){
			return $this->get_link();
		}

		function link(){
			return $this->get_link();
		}

		function url(){
			return $this->get_link();
		}
	}
<?php

	class TimberTheme extends TimberCore {

		function __construct($slug = null){
			$this->init($slug);
		}

		function init($slug = null){
			$data = wp_get_theme($slug);
			$this->name = $data->get('Name');
			$ss = $data->get_stylesheet();
			$this->slug = $ss;
			$this->parent_slug = $data->get('Template');
			if ($this->parent_slug && $this->parent_slug != $this->slug){
				$this->parent = new TimberTheme($this->parent_slug);
			}
		}

	}
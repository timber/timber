<?php
	
	class Timber {

		function get_posts($query, $PostClass = 'TimberPost'){
			if (is_array($query) && !PHPHelper::is_array_assoc($query)){
				$results = $query;
			} else {
				$results = get_posts($query);
			} 
			foreach($results as &$result){
				$rid = $result;
				if (isset($result->ID)){
					$rid = $result->ID;
				}
				$result = new $PostClass($rid);
			}
			return $results;
		}

		function loop_to_posts($PostClass = 'TimberPost'){
			if (is_array($PostClass)){
				$map = $PostClass;
			}
			$posts = array();
			$i = 0;
			
			if ( have_posts() ){
				ob_start();
			while ( have_posts() && $i < 99999 ) {
				the_post(); 
				if (isset($map)){
					$pt = get_post_type();
					$PostClass = 'TimberPost';
					if (isset($map[$pt])){
						$PostClass = $map[$pt];
					} 
				}
				$posts[] = new $PostClass(get_the_ID());
				$i++;
			}
			ob_end_clean();
			}
			return $posts;
		}

		function loop_to_ids(){
			$posts = array();
			$i = 0;
			ob_start();
			while ( have_posts() && $i < 99999 ) {
				the_post(); 
				$posts[] = get_the_ID();
				$i++;
			}
			wp_reset_query();
			ob_end_clean();
			return $posts;
		}

		function get_context(){
			$data = array();
			$data['http_host'] = 'http://'.$_SERVER['HTTP_HOST'];
			$data['wp_title'] = get_bloginfo('name');
			$data['wp_head'] = self::get_wp_head();
			$data['wp_footer'] = self::get_wp_footer();
			if (function_exists('wp_nav_menu')){
				$data['wp_nav_menu'] = wp_nav_menu( array( 'container_class' => 'menu-header', 'theme_location' => 'primary' , 'echo' => false) );
			}

			return $data;
		}

		function get_wp_footer(){
			ob_start();
			wp_footer();
			$ret = ob_get_contents();
			ob_end_clean();
			return $ret;
		}

		function get_wp_head(){
			ob_start();
			wp_head();
			$ret = ob_get_contents();
			ob_end_clean();
			return $ret;
		}
	}
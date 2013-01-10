<?php
/**
 * Search results page
 * 
 * Please see /external/starkers-utilities.php for info on Starkers_Utilities::get_template_parts()
 *
 * @package 	WordPress
 * @subpackage 	Starkers
 * @since 		Starkers 4.0
 */
?>


<?php   

		$templates = array('archive.html', 'index.html');

		$data['title'] = 'Search results for '. get_search_query();
		
		$data['wp_nav_menu'] = wp_nav_menu( array( 'container_class' => 'menu-header', 'theme_location' => 'primary' , 'echo' => false) );

		if ( have_posts() ){
			$data['posts'] = PostMaster::loop_to_array();
			render_twig($templates, $data);
		} else {

		}

<?php
/**
 * The main template file
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file 
 *
 * Please see /external/starkers-utilities.php for info on Starkers_Utilities::get_template_parts()
 *
 * @package 	WordPress
 * @subpackage 	Timber
 * @since 		Timber 0.1
 */
?>
<?php get_header();
	$posts = PostMaster::loop_to_array();
	$data['wp_nav_menu'] = wp_nav_menu( array( 'container_class' => 'menu-header', 'theme_location' => 'primary' , 'echo' => false) );
	$data['page_title'] = wp_title('|', false);
	$data['posts'] = $posts;
	render_twig('index.html', $data);



<?php
/**
 * The main template file
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file 
 *
 * Methods for PostMaster and WPHelper can be found in the /functions sub-directory
 *
 * @package 	WordPress
 * @subpackage 	Timber
 * @since 		Timber 0.1
 */

	$posts = PostMaster::loop_to_array();
	
	$data['page_title'] = wp_title('|', false);
	$data['posts'] = $posts;
	$data['wp_title'] = WPHelper::get_wp_title();
	render_twig('index.html', $data);



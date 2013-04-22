<?php
/**
 * Search results page
 * 
 * Methods for PostMaster and WPHelper can be found in the /functions sub-directory
 *
 * @package 	WordPress
 * @subpackage 	Timber
 * @since 		Timber 0.1
 */
  

	$templates = array('archive.twig', 'index.twig');
	$data = get_context();

	$data['title'] = 'Search results for '. get_search_query();

	if ( have_posts() ){
		$data['posts'] = PostMaster::loop_to_array();
	}
	render_twig($templates, $data);

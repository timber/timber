<?php
/**
 * The template for displaying Author Archive pages
 *
 * Methods for PostMaster and WPHelper can be found in the /functions sub-directory
 *
 * @package 	WordPress
 * @subpackage 	Timber
 * @since 		Timber 0.1
 */

	$data['posts'] = PostMaster::loop_to_array();
	$data['title'] = 'Author Archives: '.get_the_author();
	$data['desc'] = get_the_author_meta( 'description' );
	render_twig(array('author.html', 'archive.html'), $data);
?>
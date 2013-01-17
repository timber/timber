<?php
/**
 * The Template for displaying all single posts
 *
 * Methods for PostMaster and WPHelper can be found in the /functions sub-directory
 *
 * @package 	WordPress
 * @subpackage 	Timber
 * @since 		Timber 0.1
 */

	$pi = PostMaster::loop_to_post();
	$data['post'] = $pi;
	$data['wp_nav_menu'] = wp_nav_menu( array( 'container_class' => 'menu-header', 'theme_location' => 'primary' , 'echo' => false) );
	/* comments */
	$comments['responses'] = get_comments(array('post_id' => $pi->ID));
	$comments['respond'] = WPHelper::get_comment_form(null, $pi->ID);

	$data['sidebar'] = WPHelper::get_sidebar();
	/* An alternate way of handling might be: */
	// $data['sidebar'] = render_twig('sidebar.html', $data, false);
	// this way is probably best if your sidebar content is heavily page-dependent.
	// the way shown by default -- with WPHelper::get_sidebar(); -- allows you to include
	// the logic for sidebar data mainly in the sidebar.php instead of in each single/index/archive.php file */


	$data['comments'] = render_twig('comments.html', $comments, false);

	render_twig(array('single-'.$pi->post_type.'.html','single.html'), $data);
?>
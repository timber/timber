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
	$data = get_context();
	$data['post'] = $pi;
	$data['wp_title'] .= ' - '.$pi->post_title;
	/* comments */
	$comments['responses'] = get_comments(array('post_id' => $pi->ID));
	$comments['respond'] = WPHelper::get_comment_form(null, $pi->ID);
	$data['comments'] = render_twig('comments.html', $comments, false);

	render_twig(array('single-'.$pi->post_type.'.html','single.html'), $data);
?>
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

	$context = Timber::get_context();
	$post = new TimberPost();
	$context['post'] = $post;
	$context['wp_title'] .= ' - '.$post->post_title;
	$context['comment_form'] = WPHelper::get_comment_form($pid);

	render_twig(array('single-'.$post->post_type.'.html','single.html'), $context);
?>
<?php
/**
 * The Template for displaying all single posts
 *
 *
 * @package 	WordPress
 * @subpackage 	Timber
 */
?>
<?php 
	$pi = PostMaster::loop_to_post();
	$data['post'] = $pi;
	$data['wp_nav_menu'] = wp_nav_menu( array( 'container_class' => 'menu-header', 'theme_location' => 'primary' , 'echo' => false) );
	/* comments */
	$comments['responses'] = get_comments(array('post_id' => $pi->ID));
	$comments['respond'] = WPHelper::get_comment_form(null, $pi->ID);
	$data['comments'] = render_twig('comments.html', $comments, false);
	
	render_twig(array('single-'.$pi->post_type.'.html','single.html'), $data);
?>
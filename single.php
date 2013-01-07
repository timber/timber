<?php
/**
 * The Template for displaying all single posts
 *
 * Please see /external/starkers-utilities.php for info on Starkers_Utilities::get_template_parts()
 *
 * @package 	WordPress
 * @subpackage 	Timber
 */
?>
<?php get_header();
	$data['post'] = PostMaster::loop_to_post();
	$data['wp_nav_menu'] = wp_nav_menu( array( 'container_class' => 'menu-header', 'theme_location' => 'primary' , 'echo' => false) );
	/* comments */
	$comments['responses'] = get_comments(array('post_id' => $pi->ID));
	$comments['respond'] = WPHelper::get_comment_form(null, $pi->ID);
	$data['comments'] = render_twig('comments.html', $comments, false);
	
	render_twig('single.html', $data);
?>
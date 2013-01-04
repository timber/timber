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
	$data['comments'] = get_comments(array('post_id' => $pi->ID));
	$data['respond'] = WPHelper::get_comment_form(null, $pi->ID);
	render_twig('single.html', $data);
?>
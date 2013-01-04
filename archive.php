<?php
/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * Please see /external/starkers-utilities.php for info on Starkers_Utilities::get_template_parts() 
 *
 * @package 	WordPress
 * @subpackage 	Starkers
 * @since 		Starkers 4.0
 */
?>
<?php 	get_header();
		$data['title'] = 'Archive';
		if (is_day()){
			$data['title'] = 'Archive: '.get_the_date( 'D M Y' );	
		} else if (is_month()){
			$data['title'] = 'Archive: '.get_the_date( 'M Y' );	
		} else if (is_year()){
			$data['title'] = 'Archive: '.get_the_date( 'Y' );	
		} else if (is_tag()){

		} else if (is_category()){
			$data['title'] = single_cat_title('', false);
		}
		if ( have_posts() ){
			$data['posts'] = PostMaster::loop_to_array();
			render_twig('index.html', $data);
		}

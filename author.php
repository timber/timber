<?php
/**
 * The template for displaying Author Archive pages
 *
 * Please see /external/starkers-utilities.php for info on Starkers_Utilities::get_template_parts()
 *
 * @package 	WordPress
 * @subpackage 	Starkers
 * @since 		Starkers 4.0
 */
?>
<?php Starkers_Utilities::get_template_parts( array( 'html-header', 'header' ) ); ?>

<?php
	$data['posts'] = PostMaster::loop_to_array();
	$data['title'] = 'Author Archives: '.get_the_author();
	$data['desc'] = get_the_author_meta( 'description' );
	render_twig('views/author.html', $data);
?>

<?php Starkers_Utilities::get_template_parts( array( 'footer','html-footer' ) ); ?>
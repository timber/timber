<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package WordPress
 * @subpackage Boilerplate
 * @since Boilerplate 1.0
 */

get_header(); ?>
			<article id="post-0" class="post error404 not-found" role="main">
				<h1><?php _e( 'Not Found', 'boilerplate' ); ?></h1>
				<p><?php _e( 'Apologies, but the page you requested could not be found. Perhaps searching will help.', 'boilerplate' ); ?></p>
				<?php
					get_search_form();
					// add focus to search <input>
					echo '<script>document.getElementById(\'s\') && document.getElementById(\'s\').focus();</script>'.PHP_EOL;
				?>
			</article>
<?php get_footer(); ?>

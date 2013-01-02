<?php
/**
 * The template for displaying Comments.
 *
 * The area of the page that contains both current comments
 * and the comment form. The actual display of comments is
 * handled by a callback to starkers_comment() which is
 * located in the functions.php file.
 *
 * @package 	WordPress
 * @subpackage 	Starkers
 * @since 		Starkers 4.0
 */
?>
<div id="comments">
	<?php if ( post_password_required() ) : ?>
	<p>This post is password protected. Enter the password to view any comments</p>
</div>

	<?php
			/* Stop the rest of comments.php from being processed,
			 * but don't kill the script entirely -- we still have
			 * to fully load the template.
			 */
			return;
		endif;
	?>

	<?php // You can start editing here -- including this comment! ?>

	<?php if ( have_comments() ) : ?>

	<h2><?php comments_number(); ?></h2>

	<ol>
		<?php wp_list_comments( array( 'callback' => 'starkers_comment' ) ); ?>
	</ol>

	<?php
		/* If there are no comments and comments are closed, let's leave a little note, shall we?
		 * But we don't want the note on pages or post types that do not support comments.
		 */
		elseif ( ! comments_open() && ! is_page() && post_type_supports( get_post_type(), 'comments' ) ) :
	?>
	
	<p>Comments are closed</p>
	
	<?php endif; ?>

	<?php comment_form(); ?>

</div><!-- #comments -->

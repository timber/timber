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
 * @subpackage 	Comments
 * @since 		Starkers 4.0
 */
?>
<section class="comments">
	<div class="respond">
		<h3 class="h2">Comments</h3>
		{{ respond }}
	</div>
	<div class="responses">
		{% for comment in comments%}
			<div class="blog-comment {{comment.comment_type}}" id="blog-comment-{{comment.comment_ID}}">
				<!-- {{comment|print_r}} -->
				{% if comment.comment_author != "" %}
				<h5 class="comment-author">{{comment.comment_author}} says</h5>
				{% else %}
				<h5 class="comment-author">Anonymous says</h5>
				{% endif %}
				<div class="comment-content">{{comment.comment_content|wpautop}}</div>
			</div>
		{% endfor %}
	</div>
</section>
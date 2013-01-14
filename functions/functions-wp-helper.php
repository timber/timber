<?php

	class WPHelper {

		function __construct(){
			add_filter('cron_schedules', array($this, 'cron_add_quarterly'));
		}

		function get_video_embed($url){
			if (strstr(strtolower($url), 'youtube')){
				return self::get_video_embed_youtube($url);
			}

		}

		function get_wp_title(){
			return wp_title('|', false, 'right'); 
		}

		/* Unlike WP's default get_sidebar(), this function will capture the HTML of the sidebar and return it to the requesting code */
		function get_sidebar($template = ''){
			ob_start();
			get_sidebar($template);
			$return = ob_get_contents();
			ob_end_clean();
			return $return;
		}

		function get_video_embed_youtube($yt_url){
			$yt_url_info = parse_url($yt_url);
			$query = $yt_url_info['query'];
			parse_str($query, $query);
			$vid = $query['v'];
			$ytstr = '<iframe class="video-embed video-embed-youtube" width="560" height="315" src="http://www.youtube.com/embed/'.$vid.'" frameborder="0" allowfullscreen></iframe>';
			return $ytstr;
		}

		function get_comments_template(){

			if (function_exists('dsq_comments_template')){
				ob_start();
				include(dsq_comments_template());
				$data['comments'] = ob_get_contents();
				ob_end_clean();
			} else {
				$comments['responses'] = get_comments(array('post_id' => $pi->ID));
				$comments['respond'] = WPHelper::get_comment_form(null, $pi->ID);
				$data['comments'] = render_twig('comments.html', $comments, false);
			}
		}

		function cron_add_quarterly($schedules) {
			$schedules['quarterly'] = array('interval' => 900, 'display' => 'Every 15 minutes');
			return $schedules;
		}

		function get_comment_form_title( $noreplytext = false, $replytext = false, $linktoparent = true ) {
			global $comment;

			if ( false === $noreplytext ) $noreplytext = __( 'Leave a Reply' );
			if ( false === $replytext ) $replytext = __( 'Leave a Reply to %s' );

			$replytoid = isset($_GET['replytocom']) ? (int) $_GET['replytocom'] : 0;

			if ( 0 == $replytoid ) {
				return $noreplytext;
			} else {
				$comment = get_comment($replytoid);
				$author = ( $linktoparent ) ? '<a href="#comment-' . get_comment_ID() . '">' . get_comment_author() . '</a>' : get_comment_author();
				return sprintf( $replytext, $author );
			}
		}


		function get_comment_form( $args = array(), $post_id = null ) {
			global $id;

			if ( null === $post_id ){
				$post_id = $id;
			} else {
				$id = $post_id;
			}
			$commenter = wp_get_current_commenter();
			
			$user = wp_get_current_user();
			$user_identity = $user->exists() ? $user->display_name : '';

			$req = get_option( 'require_name_email' );
			$aria_req = ( $req ? " aria-required='true'" : '' );
			$fields =  array(
				'author' => '<p class="comment-form-author">' . '<label for="author">' . __( 'Name' ) . '</label> ' . ( $req ? '<span class="required">*</span>' : '' ) .
				            '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>',
				'email'  => '<p class="comment-form-email"><label for="email">' . __( 'Email' ) . '</label> ' . ( $req ? '<span class="required">*</span>' : '' ) .
				            '<input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></p>',
				'url'    => '<p class="comment-form-url"><label for="url">' . __( 'Website' ) . '</label>' .
				            '<input id="url" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></p>',
			);

			$required_text = sprintf( ' ' . __('Required fields are marked %s'), '<span class="required">*</span>' );
			$defaults = array(
				'fields'               => apply_filters( 'comment_form_default_fields', $fields ),
				'comment_field'        => '<p class="comment-form-comment"><label for="comment">' . _x( 'Comment', 'noun' ) . '</label><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>',
				'must_log_in'          => '<p class="must-log-in">' . sprintf( __( 'You must be <a href="%s">logged in</a> to post a comment.' ), wp_login_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
				'logged_in_as'         => '<p class="logged-in-as">' . sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>' ), admin_url( 'profile.php' ), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
				'comment_notes_before' => '<p class="comment-notes">' . __( 'Your email address will not be published.' ) . ( $req ? $required_text : '' ) . '</p>',
				'comment_notes_after'  => '<p class="form-allowed-tags">' . sprintf( __( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes: %s' ), ' <code>' . allowed_tags() . '</code>' ) . '</p>',
				'id_form'              => 'commentform',
				'id_submit'            => 'submit',
				'title_reply'          => __( 'Leave a Reply' ),
				'title_reply_to'       => __( 'Leave a Reply to %s' ),
				'cancel_reply_link'    => __( 'Cancel reply' ),
				'label_submit'         => __( 'Post Comment' ),
			);

			$args = wp_parse_args( $args, apply_filters( 'comment_form_defaults', $defaults ) );

			$return = '';

			if ( comments_open( $post_id ) ){
				do_action( 'comment_form_before' );
				$return .= '<div id="respond">
						<h3 id="reply-title">'.self::get_comment_form_title( $args['title_reply'], $args['title_reply_to'] ).'<small>'.cancel_comment_reply_link( $args['cancel_reply_link'] ).'</small></h3>';
				if ( get_option( 'comment_registration' ) && !is_user_logged_in() ) {
					$return .= $args['must_log_in'];
					do_action( 'comment_form_must_log_in_after' );
				} else {
					$return .= '<form action="'.site_url( '/wp-comments-post.php' ).'" method="post" id="'.esc_attr( $args['id_form']).'">';
					do_action( 'comment_form_top' );
					if ( is_user_logged_in() ){
						$return .= apply_filters( 'comment_form_logged_in', $args['logged_in_as'], $commenter, $user_identity );
						do_action( 'comment_form_logged_in_after', $commenter, $user_identity );
					} else {
						$return .= $args['comment_notes_before'];
						do_action( 'comment_form_before_fields' );
						foreach ( (array) $args['fields'] as $name => $field ) {
							$return .= apply_filters( "comment_form_field_{$name}", $field ) . "\n";
						}
						do_action( 'comment_form_after_fields' );
					}
					$return .= apply_filters( 'comment_form_field_comment', $args['comment_field'] ); 
					$return .= $args['comment_notes_after'];
					$return .= '<p class="form-submit"><input name="submit" type="submit" id="'.esc_attr( $args['id_submit'] ).'" value="'.esc_attr( $args['label_submit'] ).'" />';
					$return .= comment_id_fields( $post_id );
					$return .= '</p>';
					do_action( 'comment_form', $post_id ); 
					$return .= '</form>';
				}
				$return .= '</div><!-- #respond -->';
				do_action( 'comment_form_after' ); 
			} else {
				do_action( 'comment_form_comments_closed' );
			}
			return $return;
		}
	}

	new WPHelper();

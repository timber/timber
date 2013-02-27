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

		function get_comment_form( $args = array(), $post_id = null ) {
			ob_start();
			comment_form( $args, $post_id );
			$ret = ob_get_contents();
			ob_end_clean();
			return $ret;
		}

	}

	new WPHelper();

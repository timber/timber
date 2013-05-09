<?php

	class WPHelper {

		function is_url($url){
			$url = strtolower($url);
			if (strstr('http', $url)){
				return true;
			}
			return false;
		}	

		function get_params($i = -1){
			$args = explode('/', trim(strtolower($_SERVER['REQUEST_URI'])));
			$newargs = array();
			foreach($args as $arg){
				if (strlen($arg)){
					$newargs[] = $arg;
				}
			}
			if ($i > -1){
				return $newargs[$i];
			}
			return $newargs;
		}

		function get_json($url){
			$data = self::get_curl($url);
			return json_decode($data);
		}

		function get_curl($url) {
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); 
			curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
			$content = curl_exec($ch);
			curl_close($ch);
			return $content;
		}

		function get_wp_title(){
			return wp_title('|', false, 'right'); 
		}

		function force_update_option($option, $value){
			global $wpdb;
			$wpdb->query("UPDATE $wpdb->options SET option_value = '$value' WHERE option_name = '$option'");
		}

		function get_current_url(){
			$pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
			if ($_SERVER["SERVER_PORT"] != "80"){
			    $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			} else {
			    $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			}
			return $pageURL;
		}

		function trim_words( $text, $num_words = 55, $more = null, $allowed_tags = 'p a span b i br' ) {
			if ( null === $more )
				$more = __( '&hellip;' );
			$original_text = $text;
			$allowed_tag_string = '';
			foreach(explode(' ', $allowed_tags) as $tag){
				$allowed_tag_string .= '<'.$tag.'>';
			}
			$text = strip_tags($text, $allowed_tag_string);
			/* translators: If your word count is based on single characters (East Asian characters),
			   enter 'characters'. Otherwise, enter 'words'. Do not translate into your own language. */
			if ( 'characters' == _x( 'words', 'word count: words or characters?' ) && preg_match( '/^utf\-?8$/i', get_option( 'blog_charset' ) ) ) {
				$text = trim( preg_replace( "/[\n\r\t ]+/", ' ', $text ), ' ' );
				preg_match_all( '/./u', $text, $words_array );
				$words_array = array_slice( $words_array[0], 0, $num_words + 1 );
				$sep = '';
			} else {
				$words_array = preg_split( "/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY );
				$sep = ' ';
			}
			if ( count( $words_array ) > $num_words ) {
				array_pop( $words_array );
				$text = implode( $sep, $words_array );
				$text = $text . $more;
			} else {
				$text = implode( $sep, $words_array );
			}
			$text = self::close_tags($text);
			return apply_filters( 'wp_trim_words', $text, $num_words, $more, $original_text );
		}

		function trim_text($input, $length, $strip_html = true, $ellipses = '') {
		    //strip tags, if desired
		    if ($strip_html) {
		        $input = strip_tags($input);
		    }
		  
		    //no need to trim, already shorter than trim length
		    if (strlen($input) <= $length) {
		        return $input;
		    }
		  
		    //find last space within length
		    $last_space = strrpos(substr($input, 0, $length), ' ');
		    $trimmed_text = substr($input, 0, $last_space);
		  
		    //add ellipses (...)
		    if ($ellipses) {
		        $trimmed_text .= $ellipses;
		    }
		    return $trimmed_text;
		}

		function close_tags($html) {
  			#put all opened tags into an array
  			preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
  			$openedtags = $result[1];   
  			#put all closed tags into an array
			preg_match_all('#</([a-z]+)>#iU', $html, $result);
			$closedtags = $result[1];
			$len_opened = count($openedtags);
			# all tags are closed
			if (count($closedtags) == $len_opened) {
				return $html;
			}
			$openedtags = array_reverse($openedtags);
			# close tags
			for ($i=0; $i < $len_opened; $i++) {
				if (!in_array($openedtags[$i], $closedtags)){
					$html .= '</'.$openedtags[$i].'>';
				} else {
			  		unset($closedtags[array_search($openedtags[$i], $closedtags)]);   
			  	}
			}  
			return $html;
		} 

		/* this $args thing is a fucking mess, fix at some point: 

		http://codex.wordpress.org/Function_Reference/comment_form */

		function get_comment_form( $post_id = null, $args = array()) {
			ob_start();
			comment_form( $args, $post_id );
			$ret = ob_get_contents();
			ob_end_clean();
			return $ret;
		}

	}
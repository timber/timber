<?php

namespace Timber;

/**
 * An object that lets a user easily modify the post preview to their
 * liking
 * @since 1.0.4
*/
class PostPreview {

	protected $post;
	protected $end = '&hellip;';
	protected $force = false;
	protected $length = 50;
	protected $readmore = 'Read More';
	protected $strip = true;

	public function __construct( $post ) {
		$this->post = $post;
	}

	public function __toString() {
		return $this->run();
	}

	public function length( $len = 50 ) {
		$this->length = $len;
		return $this;
	}

	public function end( $end = '&hellip;' ) {
		$this->end = $end;
		return $this;
	}

	public function force( $force = true ) {
		$this->force = $force;
		return $this;
	}

	public function read_more( $readmore = 'Read More' ) {
		$this->readmore = $readmore;
		return $this;
	}

	public function strip( $strip = true ) {
		$this->strip = $strip;
		return $this;
	}

	protected function run() {
		$end = $this->end;
		$force = $this->force;
		$len = $this->length;
		$readmore = $this->readmore;
		$strip = $this->strip;

		$text = '';
		$trimmed = false;
		if ( isset($this->post->post_excerpt) && strlen($this->post->post_excerpt) ) {
			if ( $this->force ) {
				$text = Helper::trim_words($this->post->post_excerpt, $len, false);
				$trimmed = true;
			} else {
				$text = $this->post->post_excerpt;
			}
		}
		if ( !strlen($text) && preg_match('/<!--\s?more(.*?)?-->/', $this->post->post_content, $readmore_matches) ) {
			$pieces = explode($readmore_matches[0], $this->post->post_content);
			$text = $pieces[0];
			if ( $force ) {
				$text = Helper::trim_words($text, $len, false);
				$trimmed = true;
			}
			$text = do_shortcode($text);
		}
		if ( !strlen($text) ) {
			$text = Helper::trim_words($this->post->content(), $len, false);
			$trimmed = true;
		}
		if ( !strlen(trim($text)) ) {
			return trim($text);
		}
		if ( $strip ) {
			$allowable_tags = (is_string($strip)) ? $strip : null;
			$text = trim(strip_tags($text, $allowable_tags));
		}
		if ( strlen($text) ) {
			$text = trim($text);
			$last = $text[strlen($text) - 1];
			if ( $last != '.' && $trimmed ) {
				$text .= $end;
			}
			if ( !$strip ) {
				$last_p_tag = strrpos($text, '</p>');
				if ( $last_p_tag !== false ) {
					$text = substr($text, 0, $last_p_tag);
				}
				if ( $last != '.' && $trimmed ) {
					$text .= $end.' ';
				}
			}
			$read_more_class = apply_filters('timber/post/get_preview/read_more_class', "read-more");
			if ( $readmore && isset($readmore_matches) && !empty($readmore_matches[1]) ) {
				$text .= ' <a href="'.$this->post->link().'" class="'.$read_more_class.'">'.trim($readmore_matches[1]).'</a>';
			} elseif ( $readmore ) {
				$text .= ' <a href="'.$this->post->link().'" class="'.$read_more_class.'">'.trim($readmore).'</a>';
			}
			if ( !$strip && $last_p_tag && (strpos($text, '<p>') || strpos($text, '<p ')) ) {
				$text .= '</p>';
			}
		}
		return trim($text);
	}

}
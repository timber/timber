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
	protected $char_length = false;
	protected $readmore = 'Read More';
	protected $strip = true;
	protected $destroy_tags = array('script', 'style');

	/**
	 * @param Post $post
	 */
	public function __construct( $post ) {
		$this->post = $post;
	}

	public function __toString() {
		return $this->run();
	}

	/**
	 * @param integer $length (in words) of the target preview
	 */
	public function length( $length = 50 ) {
		$this->length = $length;
		return $this;
	}

	/**
	 * @param integer $char_length (in characters) of the target preview
	 */
	public function chars( $char_length = false ) {
		$this->char_length = $char_length;
		return $this;
	}

	/**
	 * @param string $end how should the text in the preview end
	 */
	public function end( $end = '&hellip;' ) {
		$this->end = $end;
		return $this;
	}

	/**
	 * @param boolean $force If the editor wrote a manual excerpt longer than the set length, should it be "forced" to the size specified?
	 */
	public function force( $force = true ) {
		$this->force = $force;
		return $this;
	}

	/**
	 * @param string $readmore What the text displays as to the reader inside of the <a> tag
	 */
	public function read_more( $readmore = 'Read More' ) {
		$this->readmore = $readmore;
		return $this;
	}

	/**
	 * @param boolean|string $strip strip the tags or what? You can also provide a list of allowed tags
	 */
	public function strip( $strip = true ) {
		$this->strip = $strip;
		return $this;
	}

	/**
	 * @param string $text
	 * @param array|booelan $readmore_matches
	 * @param boolean $trimmed was the text trimmed?
	 */
	protected function assemble( $text, $readmore_matches, $trimmed ) {
		$text = trim($text);
		$last = $text[strlen($text) - 1];
		$last_p_tag = null;
		if ( $last != '.' && $trimmed ) {
			$text .= $this->end;
		}
		if ( !$this->strip ) {
			$last_p_tag = strrpos($text, '</p>');
			if ( $last_p_tag !== false ) {
				$text = substr($text, 0, $last_p_tag);
			}
			if ( $last != '.' && $trimmed ) {
				$text .= $this->end.' ';
			}
		}
		$read_more_class = apply_filters('timber/post/preview/read_more_class', "read-more");
		if ( $this->readmore && !empty($readmore_matches) && !empty($readmore_matches[1]) ) {
			$text .= ' <a href="'.$this->post->link().'" class="'.$read_more_class.'">'.trim($readmore_matches[1]).'</a>';
		} elseif ( $this->readmore ) {
			$text .= ' <a href="'.$this->post->link().'" class="'.$read_more_class.'">'.trim($this->readmore).'</a>';
		}
		if ( !$this->strip && $last_p_tag && (strpos($text, '<p>') > -1 || strpos($text, '<p ')) ) {
			$text .= '</p>';
		}
		return trim($text);
	}

	protected function run() {
		$force = $this->force;
		$len = $this->length;
		$chars = $this->char_length;
		$strip = $this->strip;
		$readmore_matches = array();
		$text = '';
		$trimmed = false;
		if ( isset($this->post->post_excerpt) && strlen($this->post->post_excerpt) ) {
			if ( $this->force ) {
				$text = TextHelper::trim_words($this->post->post_excerpt, $len, false);
				if ( $chars !== false ) {
					$text = TextHelper::trim_characters($this->post->post_excerpt, $chars, false);
				}
				$trimmed = true;
			} else {
				$text = $this->post->post_excerpt;
			}
		}
		if ( !strlen($text) && preg_match('/<!--\s?more(.*?)?-->/', $this->post->post_content, $readmore_matches) ) {
			$pieces = explode($readmore_matches[0], $this->post->post_content);
			$text = $pieces[0];
			if ( $force ) {
				$text = TextHelper::trim_words($text, $len, false);
				if ( $chars !== false ) {
					$text = TextHelper::trim_characters($text, $chars, false);
				}
				$trimmed = true;
			}
			$text = do_shortcode($text);
		}
		if ( !strlen($text) ) {
			$text = $this->post->content();
			$text = TextHelper::remove_tags($text, $this->destroy_tags);
			$text = TextHelper::trim_words($text, $len, false);
			if ( $chars !== false ) {
				$text = TextHelper::trim_characters($text, $chars, false);
			}
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
			return $this->assemble($text, $readmore_matches, $trimmed);
		}

		return trim($text);
	}

}
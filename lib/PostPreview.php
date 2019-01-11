<?php

namespace Timber;

/**
 * The PostPreview class lets a user modify a post preview/excerpt to their liking.
 *
 * It’s designed to be used through the `Timber\Post::preview()` method. The public methods of this
 * class all return the object itself, which means that this is a **chainable object**. You can
 * change the output of the preview by **adding more methods**.
 *
 * By default, the preview will
 *
 * - have a length of 50 words, which will be forced, even if a longer excerpt is set on the post.
 * - be stripped of all HTML tags.
 * - have an ellipsis (…) as the end of the text.
 * - have a "Read More" link appended.
 *
 * @example
 * ```twig
 * {# Use default preview #}
 * <p>{{ post.preview }}</p>
 *
 * {# Change the post preview text #}
 * <p>{{ post.preview.read_more('Continue Reading') }}</p>
 *
 * {# Additionally restrict the length to 50 words #}
 * <p>{{ post.preview.length(50).read_more('Continue Reading') }}</p>
 * ```
 * @since 1.0.4
 * @see \Timber\Post::preview()
 */
class PostPreview {
	/**
	 * Post.
	 *
	 * @var \Timber\Post
	 */
	protected $post;

	/**
	 * Preview end.
	 *
	 * @var string
	 */
	protected $end = '&hellip;';

	/**
	 * Force length.
	 *
	 * @var bool
	 */
	protected $force = false;

	/**
	 * Length in words.
	 *
	 * @var int
	 */
	protected $length = 50;

	/**
	 * Length in characters.
	 *
	 * @var bool
	 */
	protected $char_length = false;

	/**
	 * Read more text.
	 *
	 * @var string
	 */
	protected $readmore = 'Read More';

	/**
	 * HTML tag stripping behavior.
	 *
	 * @var bool
	 */
	protected $strip = true;

	/**
	 * Destroy tags.
	 *
	 * @var array List of tags that should always be destroyed.
	 */
	protected $destroy_tags = array('script', 'style');

	/**
	 * PostPreview constructor.
	 *
	 * @api
	 * @param \Timber\Post $post The post to pull the preview from.
	 */
	public function __construct( $post ) {
		$this->post = $post;
	}

	/**
	 * Returns the resulting preview.
	 *
	 * @api
	 * @return string
	 */
	public function __toString() {
		return $this->run();
	}

	/**
	 * Restricts the length of the preview to a certain amount of words.
	 *
	 * @api
	 * @example
	 * ```twig
	 * <p>{{ post.preview.length(50) }}</p>
	 * ```
	 * @param int $length The maximum amount of words (not letters) for the preview. Default `50`.
	 * @return \Timber\PostPreview
	 */
	public function length( $length = 50 ) {
		$this->length = $length;
		return $this;
	}

	/**
	 * Restricts the length of the preview to a certain amount of characters.
	 *
	 * @api
	 * @example
	 * ```twig
	 * <p>{{ post.preview.chars(180) }}</p>
	 * ```
	 * @param int|bool $char_length The maximum amount of characters for the preview. Default
	 *                              `false`.
	 * @return \Timber\PostPreview
	 */
	public function chars( $char_length = false ) {
		$this->char_length = $char_length;
		return $this;
	}

	/**
	 * Defines the text to end the preview with.
	 *
	 * @api
	 * @example
	 * ```twig
	 * <p>{{ post.preview.end('… and much more!') }}</p>
	 * ```
	 * @param string $end The text for the end of the preview. Default `…`.
	 * @return \Timber\PostPreview
	 */
	public function end( $end = '&hellip;' ) {
		$this->end = $end;
		return $this;
	}

	/**
	 * Forces preview lengths.
	 *
	 * What happens if your custom post excerpt is longer than the length requested? By default, it
	 * will use the full `post_excerpt`. However, you can set this to `true` to *force* your excerpt
	 * to be of the desired length.
	 *
	 * @api
	 * @example
	 * ```twig
	 * <p>{{ post.preview.length(20).force }}</p>
	 * ```
	 * @param bool $force Whether the length of the preview should be forced to the requested
	 *                    length, even if an editor wrote a manual excerpt that is longer than the
	 *                    set length. Default `true`.
	 * @return \Timber\PostPreview
	 */
	public function force( $force = true ) {
		$this->force = $force;
		return $this;
	}

	/**
	 * Defines the text to be used for the "Read More" link.
	 *
	 * Set this to `false` to not add a "Read More" link.
	 *
	 * @api
	 * ```twig
	 * <p>{{ post.preview.read_more('Learn more') }}</p>
	 * ```
	 * @param string $readmore Text for the link. Default 'Read More'.
	 * @return \Timber\PostPreview
	 */
	public function read_more( $readmore = 'Read More' ) {
		$this->readmore = $readmore;
		return $this;
	}

	/**
	 * Defines how HTML tags should be stripped from the preview.
	 *
	 * @api
	 * ```twig
	 * {# Strips all HTML tags, except for bold or emphasized text #}
	 * <p>{{ post.preview.length('50').strip('<strong><em>') }}</p>
	 * ```
	 * @param bool|string $strip Whether or how HTML tags in the preview should be stripped. Use
	 *                           `true` to strip all tags, `false` for no stripping, or a string for
	 *                           a list of allowed tags (e.g. '<p><a>'). Default `true`.
	 * @return \Timber\PostPreview
	 */
	public function strip( $strip = true ) {
		$this->strip = $strip;
		return $this;
	}

	/**
	 * @param string $text
	 * @param array|bool $readmore_matches
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
		$allowable_tags = ( $strip && is_string($strip)) ? $strip : false;
		$readmore_matches = array();
		$text = '';
		$trimmed = false;
		if ( isset($this->post->post_excerpt) && strlen($this->post->post_excerpt) ) {
			$text = $this->post->post_excerpt;
			if ( $this->force ) {
				
				if ( $allowable_tags ) {
					$text = TextHelper::trim_words($text, $len, false, strtr($allowable_tags, '<>', '  '));
				} else {
					$text = TextHelper::trim_words($text, $len, false);
				}
				if ( $chars !== false ) {
					$text = TextHelper::trim_characters($text, $chars, false);
				}
				$trimmed = true;
			} 
		}
		if ( !strlen($text) && preg_match('/<!--\s?more(.*?)?-->/', $this->post->post_content, $readmore_matches) ) {
			$pieces = explode($readmore_matches[0], $this->post->post_content);
			$text = $pieces[0];
			if ( $force ) {
				if ( $allowable_tags ) {
					$text = TextHelper::trim_words($text, $len, false, strtr($allowable_tags, '<>', '  '));
				} else {
					$text = TextHelper::trim_words($text, $len, false);
				}
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
			if ( $allowable_tags ) {
				$text = TextHelper::trim_words($text, $len, false, strtr($allowable_tags, '<>', '  '));
			} else {
				$text = TextHelper::trim_words($text, $len, false);
			}
			if ( $chars !== false ) {
				$text = TextHelper::trim_characters($text, $chars, false);
			}
			$trimmed = true;
		}
		if ( !strlen(trim($text)) ) {
			return trim($text);
		}
		if ( $strip ) {
			$text = trim(strip_tags($text, $allowable_tags));
		}
		if ( strlen($text) ) {
			return $this->assemble($text, $readmore_matches, $trimmed);
		}

		return trim($text);
	}

}

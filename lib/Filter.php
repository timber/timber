<?php

namespace Timber;

/**
 * Class provides different commonly used in WordPress development
 */
class Filter {

    /**
     * Trims text to a certain number of characters.
     * This function can be useful for excerpt of the post
     * As opposed to wp_trim_words trims characters that makes text to
     * take the same amount of space in each post for example
     *
     * @since   1.2.0
     * @author  @CROSP
     * @param   string $text      Text to trim.
     * @param   int    $num_chars Number of characters. Default is 60.
     * @param   string|null $more      Optional. What to append if $text needs to be trimmed. Default '&hellip;'.
     * @return  string trimmed text.
     */
    public static function trim_characters( $text, $num_chars = 60, $more = null ) {
        if ( $more === null ) {
            $more = __('&hellip;');
        }
        $text = wp_strip_all_tags($text);
        $text = mb_strimwidth($text, 0, $num_chars, $more);
        return $text;
    }
}
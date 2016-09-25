<?php

namespace Timber;

/**
 * Class provides different text-related functions 
 * commonly used in WordPress development
 */
class TextHelper {

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

    /**
     *
     *
     * @param string  $text
     * @param int     $num_words
     * @param string|null|false  $more text to appear in "Read more...". Null to use default, false to hide
     * @param string  $allowed_tags
     * @return string
     */
    public static function trim_words( $text, $num_words = 55, $more = null, $allowed_tags = 'p a span b i br blockquote' ) {
        if ( null === $more ) {
            $more = __('&hellip;');
        }
        $original_text = $text;
        $allowed_tag_string = '';
        foreach ( explode(' ', apply_filters('timber/trim_words/allowed_tags', $allowed_tags)) as $tag ) {
            $allowed_tag_string .= '<'.$tag.'>';
        }
        $text = strip_tags($text, $allowed_tag_string);
        /* translators: If your word count is based on single characters (East Asian characters), enter 'characters'. Otherwise, enter 'words'. Do not translate into your own language. */
        if ( 'characters' == _x('words', 'word count: words or characters?') && preg_match('/^utf\-?8$/i', get_option('blog_charset')) ) {
            $text = trim(preg_replace("/[\n\r\t ]+/", ' ', $text), ' ');
            preg_match_all('/./u', $text, $words_array);
            $words_array = array_slice($words_array[0], 0, $num_words + 1);
            $sep = '';
        } else {
            $words_array = preg_split("/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY);
            $sep = ' ';
        }
        if ( count($words_array) > $num_words ) {
            array_pop($words_array);
            $text = implode($sep, $words_array);
            $text = $text.$more;
        } else {
            $text = implode($sep, $words_array);
        }
        $text = self::close_tags($text);
        return apply_filters('wp_trim_words', $text, $num_words, $more, $original_text);
    }

    /**
     *
     *
     * @param string  $html
     * @return string
     */
    public static function close_tags( $html ) {
        //put all opened tags into an array
        preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
        $openedtags = $result[1];
        //put all closed tags into an array
        preg_match_all('#</([a-z]+)>#iU', $html, $result);
        $closedtags = $result[1];
        $len_opened = count($openedtags);
        // all tags are closed
        if ( count($closedtags) == $len_opened ) {
            return $html;
        }
        $openedtags = array_reverse($openedtags);
        // close tags
        for ( $i = 0; $i < $len_opened; $i++ ) {
            if ( !in_array($openedtags[$i], $closedtags) ) {
                $html .= '</'.$openedtags[$i].'>';
            } else {
                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        }
        $html = str_replace(array('</br>', '</hr>', '</wbr>'), '', $html);
        $html = str_replace(array('<br>', '<hr>', '<wbr>'), array('<br />', '<hr />', '<wbr />'), $html);
        return $html;
    }

}
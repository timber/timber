<?php

class TimberTwig 
{

    public static $dir_name;

    function __construct() {
        add_action('twig_apply_filters', array($this, 'add_twig_filters'));
    }

    /**
     * @param Twig_Environment $twig
     * @return Twig_Environment
     */
    function add_twig_filters($twig) {
        /* image filters */
        $twig->addFilter('resize', new Twig_Filter_Function(array('TimberImageHelper', 'resize')));
        $twig->addFilter('letterbox', new Twig_Filter_Function(array('TimberImageHelper', 'letterbox')));
        $twig->addFilter('tojpg', new Twig_Filter_Function(array('TimberImageHelper', 'img_to_jpg')));
        $twig->addFilter('get_src_from_attachment_id', new Twig_Filter_Function('twig_get_src_from_attachment_id'));

        /* debugging filters */
        $twig->addFilter('docs', new Twig_Filter_function('twig_object_docs'));
        $twig->addFilter('get_class', new Twig_Filter_Function('get_class'));
        $twig->addFilter('get_type', new Twig_Filter_Function('get_type'));
        $twig->addFilter('print_r', new Twig_Filter_Function(function($arr){
            return print_r($arr, true);
        }));
        $twig->addFilter('print_a', new Twig_Filter_Function(function($arr){
            return '<pre>' . self::object_docs($arr, true) . '</pre>';
        }));

        /* other filters */
        $twig->addFilter('stripshortcodes', new Twig_Filter_Function('strip_shortcodes'));
        $twig->addFilter('array', new Twig_Filter_Function(array($this, 'to_array')));
        $twig->addFilter('string', new Twig_Filter_Function(array($this, 'to_string')));
        $twig->addFilter('excerpt', new Twig_Filter_Function('wp_trim_words'));
        $twig->addFilter('function', new Twig_Filter_Function(array($this, 'exec_function')));
        $twig->addFilter('path', new Twig_Filter_Function('twig_get_path'));
        $twig->addFilter('pretags', new Twig_Filter_Function(array($this, 'twig_pretags')));
        $twig->addFilter('sanitize', new Twig_Filter_Function('sanitize_title'));
        $twig->addFilter('shortcodes', new Twig_Filter_Function('do_shortcode'));
        $twig->addFilter('time_ago', new Twig_Filter_Function(array($this, 'time_ago')));
        $twig->addFilter('twitterify', new Twig_Filter_Function(array('TimberHelper', 'twitterify')));
        $twig->addFilter('twitterfy', new Twig_Filter_Function(array('TimberHelper', 'twitterify')));
        $twig->addFilter('wp_body_class', new Twig_Filter_Function(array($this, 'body_class')));
        $twig->addFilter('wpautop', new Twig_Filter_Function('wpautop'));
        $twig->addFilter('relative', new Twig_Filter_Function(function ($link) {
            return TimberURLHelper::get_rel_url($link, true);
        }));
        $twig->addFilter('date', new Twig_Filter_Function(array($this, 'intl_date')));

        $twig->addFilter('truncate', new Twig_Filter_Function(function ($text, $len) {
            return TimberHelper::trim_words($text, $len);
        }));

        /* actions and filters */
        $twig->addFunction(new Twig_SimpleFunction('action', function ($context) {
            $args = func_get_args();
            array_shift($args);
            $args[] = $context;
            call_user_func_array('do_action', $args);
        }, array('needs_context' => true)));

        $twig->addFilter(new Twig_SimpleFilter('apply_filters', function () {
            $args = func_get_args();
            $tag = current(array_splice($args, 1, 1));

            return apply_filters_ref_array($tag, $args);
        }));
        $twig->addFunction(new Twig_SimpleFunction('function', array(&$this, 'exec_function')));
        $twig->addFunction(new Twig_SimpleFunction('fn', array(&$this, 'exec_function')));

        /* TimberObjects */
        $twig->addFunction(new Twig_SimpleFunction('TimberPost', function ($pid, $PostClass = 'TimberPost') {
            if (is_array($pid) && !TimberHelper::is_array_assoc($pid)) {
                foreach ($pid as &$p) {
                    $p = new $PostClass($p);
                }
                return $pid;
            }
            return new $PostClass($pid);
        }));
        $twig->addFunction(new Twig_SimpleFunction('TimberImage', function ($pid, $ImageClass = 'TimberImage') {
            if (is_array($pid) && !TimberHelper::is_array_assoc($pid)) {
                foreach ($pid as &$p) {
                    $p = new $ImageClass($p);
                }
                return $pid;
            }
            return new $ImageClass($pid);
        }));
        $twig->addFunction(new Twig_SimpleFunction('TimberTerm', function ($pid, $TermClass = 'TimberTerm') {
            if (is_array($pid) && !TimberHelper::is_array_assoc($pid)) {
                foreach ($pid as &$p) {
                    $p = new $TermClass($p);
                }
                return $pid;
            }
            return new $TermClass($pid);
        }));
        $twig->addFunction(new Twig_SimpleFunction('TimberUser', function ($pid, $UserClass = 'TimberUser') {
            if (is_array($pid) && !TimberHelper::is_array_assoc($pid)) {
                foreach ($pid as &$p) {
                    $p = new $UserClass($p);
                }
                return $pid;
            }
            return new $UserClass($pid);
        }));

        /* bloginfo and translate */
        $twig->addFunction('bloginfo', new Twig_SimpleFunction('bloginfo', function ($show = '', $filter = 'raw') {
            return get_bloginfo($show, $filter);
        }));
        $twig->addFunction('__', new Twig_SimpleFunction('__', function ($text, $domain = 'default') {
            return __($text, $domain);
        }));

        $twig = apply_filters('get_twig', $twig);

        return $twig;
    }

    /**
     * @param mixed $arr
     * @return array
     */
    function to_array($arr) {
        if (is_array($arr)) {
            return $arr;
        }
        $arr = array($arr);
        return $arr;
    }

    /**
     * @param mixed $arr
     * @param string $glue
     * @return string
     */
    function to_string($arr, $glue = ' ') {
        if (is_string($arr)) {
            return $arr;
        }
        if (is_array($arr) && count($arr) == 1) {
            return $arr[0];
        }
        if (is_array($arr)) {
            return implode($glue, $arr);
        }
        return null;
    }

    /**
     * @param string $function_name
     * @return mixed
     */
    function exec_function($function_name) {
        $args = func_get_args();
        array_shift($args);
        return call_user_func_array(trim($function_name), ($args));
    }

    /**
     * @param string $content
     * @return string
     */
    function twig_pretags($content) {
        return preg_replace_callback('|<pre.*>(.*)</pre|isU', array(&$this, 'convert_pre_entities'), $content);
    }

    /**
     * @param array $matches
     * @return string
     */
    function convert_pre_entities($matches) {
        return str_replace($matches[1], htmlentities($matches[1]), $matches[0]);
    }

    /**
     * @param mixed $body_classes
     * @return string
     */
    function body_class($body_classes) {
        ob_start();
        if (is_array($body_classes)) {
            $body_classes = explode(' ', $body_classes);
        }
        body_class($body_classes);
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }

    /**
     * @param string $date
     * @param string $format (optional)
     * @return string
     */
    function intl_date($date, $format = null) {
        if ($format === null) {
            $format = get_option('date_format');
        }
        return date_i18n($format, strtotime($date));
    }

    //debug

    /**
     * @param mixed $obj
     * @param bool $methods
     * @return string
     */
    function object_docs($obj, $methods = true) {
        $class = get_class($obj);
        $properties = (array)$obj;
        if ($methods) {
            /** @var array $methods */
            $methods = $obj->get_method_values();
        }
        $rets = array_merge($properties, $methods);
        ksort($rets);
        $str = print_r($rets, true);
        $str = str_replace('Array', $class . ' Object', $str);
        return $str;
    }

    /**
     * @param int|string $from
     * @param int|string $to
     * @param string $format_past
     * @param string $format_future
     * @return string
     */
    function time_ago($from, $to = null, $format_past = '%s ago', $format_future = '%s from now') {
        $to = $to === null ? time() : $to;
        $to = is_int($to) ? $to : strtotime($to);
        $from = is_int($from) ? $from : strtotime($from);

        if ($from < $to) {
            return sprintf($format_past, human_time_diff($from, $to));
        } else {
            return sprintf($format_future, human_time_diff($to, $from));
        }
    }

}

new TimberTwig();


/* deprecated */

/**
 * @param string $string
 * @param array $data
 * @return string
 */
function render_twig_string($string, $data = array()) {
    return Timber::render_string($string, $data);
}



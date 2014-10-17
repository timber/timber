<?php

class TimberTwig {

    public static $dir_name;

    function __construct() {
        add_action( 'twig_apply_filters', array( $this, 'add_timber_filters_deprecated' ) );
        add_action( 'twig_apply_filters', array( $this, 'add_timber_filters' ) );
    }

    /**
     * These are all deprecated and will be removed in 0.21.0
     *
     * @param Twig_Environment $twig
     * @deprecated since 0.20.7
     * @return Twig_Environment
     */
    function add_timber_filters_deprecated( $twig ) {
        $twig->addFilter( new Twig_SimpleFilter( 'get_src_from_attachment_id', 'twig_get_src_from_attachment_id' ) );
        $twig->addFilter( new Twig_SimpleFilter( 'wp_body_class', array( $this, 'body_class' ) ) );
        $twig->addFilter( new Twig_SimpleFilter( 'twitterify', array( 'TimberHelper', 'twitterify' ) ) );
        $twig->addFilter( new Twig_SimpleFilter( 'twitterfy', array( 'TimberHelper', 'twitterify' ) ) );
        return $twig;
    }

    /**
     *
     *
     * @param Twig_Environment $twig
     * @return Twig_Environment
     */
    function add_timber_filters( $twig ) {
        $filters = array(
            /* image filters */
            array( 'resize', array( 'TimberImageHelper', 'resize' ) ),
            array( 'letterbox', array( 'TimberImageHelper', 'letterbox' ) ),
            array( 'tojpg', array( 'TimberImageHelper', 'img_to_jpg' ) ),

            /* debugging filters */
            array( 'docs', 'twig_object_docs' ),
            array( 'get_class',  'get_class' ),
            array( 'get_type', 'get_type' ),
            array( 'print_r', function( $arr ) {
                return print_r( $arr, true );
            } ),
            array( 'print_a', function( $arr ) {
                return '<pre>' . self::object_docs( $arr, true ) . '</pre>';
            } ),

            /* other filters */
            array( 'stripshortcodes', 'strip_shortcodes' ),
            array( 'array', array( $this, 'to_array' ) ),
            array( 'string', array( $this, 'to_string' ) ),
            array( 'excerpt', 'wp_trim_words' ),
            array( 'function', array( $this, 'exec_function' ) ),
            array( 'pretags', array( $this, 'twig_pretags' ) ),
            array( 'sanitize', 'sanitize_title' ),
            array( 'shortcodes', 'do_shortcode' ),
            array( 'time_ago', array( $this, 'time_ago' ) ),
            array( 'wpautop', 'wpautop' ),

            array( 'relative', function ( $link ) {
                return TimberURLHelper::get_rel_url( $link, true );
            } ),

            array( 'date', array( $this, 'intl_date' ) ),

            array( 'truncate', function ( $text, $len ) {
                return TimberHelper::trim_words( $text, $len );
            } ),

            /* actions and filters */
            array( 'apply_filters', function () {
                $args = func_get_args();
                $tag = current( array_splice( $args, 1, 1 ) );

                return apply_filters_ref_array( $tag, $args );
            } ),
        );

        foreach ($filters as $filter) {
          $twig->addFilter( new Twig_SimpleFilter( $filter[0], $filter[1] ) );
        }

        $functions = array(
            array( 'action', function ( $context ) {
                $args = func_get_args();
                array_shift( $args );
                $args[] = $context;
                call_user_func_array( 'do_action', $args );
            }, array( 'needs_context' => true ) ),

            array( 'function', array( &$this, 'exec_function' ) ),
            array( 'fn', array( &$this, 'exec_function' ) ),

            /* TimberObjects */
            array( 'TimberPost', array( $this, 'timber_post_factory' ) ),
            array( 'TimberImage', array( $this, 'timber_image_factory' ) ),
            array( 'TimberTerm', array( $this, 'timber_term_factory' ) ),
            array( 'TimberUser', array( $this, 'timber_user_factory' ) ),

                    /* bloginfo and translate */
            array( 'bloginfo', function ( $show = '', $filter = 'raw' ) {
                return get_bloginfo( $show, $filter );
            } ),
            array( '__', function ( $text, $domain = 'default' ) {
                return __( $text, $domain );
            } ),
        );

        foreach ($functions as $function) {
            $simpleFunction = new ReflectionClass('Twig_SimpleFunction');

            $twig->addFunction(
                $simpleFunction->newInstanceArgs($function)
            );
        }

        $twig = apply_filters( 'get_twig', $twig );

        return $twig;
    }

    public function timber_post_factory( $pid, $ObjectClass = 'TimberPost' ) {
        return $this->timber_object_factory($pid, $ObjectClass);
    }

    public function timber_image_factory( $pid, $ObjectClass = 'TimberImage' ) {
        return $this->timber_object_factory($pid, $ObjectClass);
    }

    public function timber_term_factory( $pid, $ObjectClass = 'TimberTerm' ) {
        return $this->timber_object_factory($pid, $ObjectClass);
    }

    public function timber_user_factory( $pid, $ObjectClass = 'TimberUser' ) {
        return $this->timber_object_factory($pid, $ObjectClass);
    }

    public function timber_object_factory($pid, $ObjectClass) {
        if ( is_array( $pid ) && !TimberHelper::is_array_assoc( $pid ) ) {
            foreach ( $pid as &$p ) {
                $p = new $ObjectClass( $p );
            }
            return $pid;
        }
        return new $ObjectClass( $pid );
    }

    /**
     *
     *
     * @param mixed   $arr
     * @return array
     */
    function to_array( $arr ) {
        if ( is_array( $arr ) ) {
            return $arr;
        }
        $arr = array( $arr );
        return $arr;
    }

    /**
     *
     *
     * @param mixed   $arr
     * @param string  $glue
     * @return string
     */
    function to_string( $arr, $glue = ' ' ) {
        if ( is_string( $arr ) ) {
            return $arr;
        }
        if ( is_array( $arr ) && count( $arr ) == 1 ) {
            return $arr[0];
        }
        if ( is_array( $arr ) ) {
            return implode( $glue, $arr );
        }
        return null;
    }

    /**
     *
     *
     * @param string  $function_name
     * @return mixed
     */
    function exec_function( $function_name ) {
        $args = func_get_args();
        array_shift( $args );
        return call_user_func_array( trim( $function_name ), ( $args ) );
    }

    /**
     *
     *
     * @param string  $content
     * @return string
     */
    function twig_pretags( $content ) {
        return preg_replace_callback( '|<pre.*>(.*)</pre|isU', array( &$this, 'convert_pre_entities' ), $content );
    }

    /**
     *
     *
     * @param array   $matches
     * @return string
     */
    function convert_pre_entities( $matches ) {
        return str_replace( $matches[1], htmlentities( $matches[1] ), $matches[0] );
    }

    /**
     *
     *
     * @param mixed   $body_classes
     * @return string
     */
    function body_class( $body_classes ) {
        ob_start();
        if ( is_array( $body_classes ) ) {
            $body_classes = explode( ' ', $body_classes );
        }
        body_class( $body_classes );
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }

    /**
     *
     *
     * @param string  $date
     * @param string  $format (optional)
     * @return string
     */
    function intl_date( $date, $format = null ) {
        if ( $format === null ) {
            $format = get_option( 'date_format' );
        }

        if ( $date instanceof DateTime ) {
            $timestamp = $date->getTimestamp();
        } else {
            $timestamp = strtotime( $date );
        }

        return date_i18n( $format, $timestamp );
    }

    //debug

    /**
     *
     *
     * @param mixed   $obj
     * @param bool    $methods
     * @return string
     */
    function object_docs( $obj, $methods = true ) {
        $class = get_class( $obj );
        $properties = (array)$obj;
        if ( $methods ) {
            /** @var array $methods */
            $methods = $obj->get_method_values();
        }
        $rets = array_merge( $properties, $methods );
        ksort( $rets );
        $str = print_r( $rets, true );
        $str = str_replace( 'Array', $class . ' Object', $str );
        return $str;
    }

    /**
     * @param int|string $from
     * @param int|string $to
     * @param string $format_past
     * @param string $format_future
     * @return string
     */
    function time_ago( $from, $to = null, $format_past = '%s ago', $format_future = '%s from now' ) {
        $to = $to === null ? time() : $to;
        $to = is_int( $to ) ? $to : strtotime( $to );
        $from = is_int( $from ) ? $from : strtotime( $from );

        if ( $from < $to ) {
            return sprintf( $format_past, human_time_diff( $from, $to ) );
        } else {
            return sprintf( $format_future, human_time_diff( $to, $from ) );
        }
    }

}

new TimberTwig();

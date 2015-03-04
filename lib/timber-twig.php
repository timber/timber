<?php

class TimberTwig {

    public static $dir_name;

    /**
     * Initialization
     */
    public static function init() {
        new TimberTwig();
    }

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
        /* image filters */
        $twig->addFilter( new Twig_SimpleFilter( 'resize', array( 'TimberImageHelper', 'resize' ) ) );
        $twig->addFilter( new Twig_SimpleFilter( 'retina', array( 'TimberImageHelper', 'retina_resize' ) ) );
        $twig->addFilter( new Twig_SimpleFilter( 'letterbox', array( 'TimberImageHelper', 'letterbox' ) ) );
        $twig->addFilter( new Twig_SimpleFilter( 'tojpg', array( 'TimberImageHelper', 'img_to_jpg' ) ) );

        /* debugging filters */
        $twig->addFilter( new Twig_SimpleFilter( 'docs', 'twig_object_docs' ) );
        $twig->addFilter( new Twig_SimpleFilter( 'get_class',  'get_class' ) );
        $twig->addFilter( new Twig_SimpleFilter( 'get_type', 'get_type' ) );
        $twig->addFilter( new Twig_SimpleFilter( 'print_r', function( $arr ) {
                    return print_r( $arr, true );
                } ) );
        $twig->addFilter( new Twig_SimpleFilter( 'print_a', function( $arr ) {
                    return '<pre>' . self::object_docs( $arr, true ) . '</pre>';
                } ) );

        /* other filters */
        $twig->addFilter( new Twig_SimpleFilter( 'stripshortcodes', 'strip_shortcodes' ) );
        $twig->addFilter( new Twig_SimpleFilter( 'array', array( $this, 'to_array' ) ) );
        $twig->addFilter( new Twig_SimpleFilter( 'string', array( $this, 'to_string' ) ) );
        $twig->addFilter( new Twig_SimpleFilter( 'excerpt', 'wp_trim_words' ) );
        $twig->addFilter( new Twig_SimpleFilter( 'function', array( $this, 'exec_function' ) ) );
        $twig->addFilter( new Twig_SimpleFilter( 'pretags', array( $this, 'twig_pretags' ) ) );
        $twig->addFilter( new Twig_SimpleFilter( 'sanitize', 'sanitize_title' ) );
        $twig->addFilter( new Twig_SimpleFilter( 'shortcodes', 'do_shortcode' ) );
        $twig->addFilter( new Twig_SimpleFilter( 'time_ago', array( $this, 'time_ago' ) ) );
        $twig->addFilter( new Twig_SimpleFilter( 'wpautop', 'wpautop' ) );

        $twig->addFilter( new Twig_SimpleFilter( 'relative', function ( $link ) {
                    return TimberURLHelper::get_rel_url( $link, true );
                } ) );

        $twig->addFilter( new Twig_SimpleFilter( 'date', array( $this, 'intl_date' ) ) );

        $twig->addFilter( new Twig_SimpleFilter( 'truncate', function ( $text, $len ) {
                    return TimberHelper::trim_words( $text, $len );
                } ) );

        /* actions and filters */
        $twig->addFunction( new Twig_SimpleFunction( 'action', function ( $context ) {
                    $args = func_get_args();
                    array_shift( $args );
                    $args[] = $context;
                    call_user_func_array( 'do_action', $args );
                }, array( 'needs_context' => true ) ) );

        $twig->addFilter( new Twig_SimpleFilter( 'apply_filters', function () {
                    $args = func_get_args();
                    $tag = current( array_splice( $args, 1, 1 ) );

                    return apply_filters_ref_array( $tag, $args );
                } ) );
        $twig->addFunction( new Twig_SimpleFunction( 'function', array( &$this, 'exec_function' ) ) );
        $twig->addFunction( new Twig_SimpleFunction( 'fn', array( &$this, 'exec_function' ) ) );

        /* TimberObjects */
        $twig->addFunction( new Twig_SimpleFunction( 'TimberPost', function ( $pid, $PostClass = 'TimberPost' ) {
                    if ( is_array( $pid ) && !TimberHelper::is_array_assoc( $pid ) ) {
                        foreach ( $pid as &$p ) {
                            $p = new $PostClass( $p );
                        }
                        return $pid;
                    }
                    return new $PostClass( $pid );
                } ) );
        $twig->addFunction( new Twig_SimpleFunction( 'TimberImage', function ( $pid, $ImageClass = 'TimberImage' ) {
                    if ( is_array( $pid ) && !TimberHelper::is_array_assoc( $pid ) ) {
                        foreach ( $pid as &$p ) {
                            $p = new $ImageClass( $p );
                        }
                        return $pid;
                    }
                    return new $ImageClass( $pid );
                } ) );
        $twig->addFunction( new Twig_SimpleFunction( 'TimberTerm', function ( $pid, $TermClass = 'TimberTerm' ) {
                    if ( is_array( $pid ) && !TimberHelper::is_array_assoc( $pid ) ) {
                        foreach ( $pid as &$p ) {
                            $p = new $TermClass( $p );
                        }
                        return $pid;
                    }
                    return new $TermClass( $pid );
                } ) );
        $twig->addFunction( new Twig_SimpleFunction( 'TimberUser', function ( $pid, $UserClass = 'TimberUser' ) {
                    if ( is_array( $pid ) && !TimberHelper::is_array_assoc( $pid ) ) {
                        foreach ( $pid as &$p ) {
                            $p = new $UserClass( $p );
                        }
                        return $pid;
                    }
                    return new $UserClass( $pid );
                } ) );

        /* bloginfo and translate */
        $twig->addFunction( 'bloginfo', new Twig_SimpleFunction( 'bloginfo', function ( $show = '', $filter = 'raw' ) {
                    return get_bloginfo( $show, $filter );
                } ) );
        $twig->addFunction( '__', new Twig_SimpleFunction( '__', function ( $text, $domain = 'default' ) {
                    return __( $text, $domain );
                } ) );

        $twig = apply_filters( 'get_twig', $twig );

        return $twig;
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

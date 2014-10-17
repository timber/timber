<?php

class TimberTwig {

    /**
     * TimberTwigHelper
     */
    private $_helper;

    public static $dir_name;

    function __construct(TimberTwigHelper $helper = null) {
        $this->_helper = ($helper) ? $helper : new TimberTwigHelper();

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
        $twig->addFilter( new Twig_SimpleFilter( 'wp_body_class', array( $this->_helper, 'body_class' ) ) );
        $twig->addFilter( new Twig_SimpleFilter( 'twitterify', array( 'TimberHelper', 'twitterify' ) ) );
        $twig->addFilter( new Twig_SimpleFilter( 'twitterfy', array( 'TimberHelper', 'twitterify' ) ) );
        return $twig;
    }

    /**
     * @return array
     */
    private function get_timber_filters() {
        return array(
            /* image filters */
            array( 'resize', array( 'TimberImageHelper', 'resize' ) ),
            array( 'letterbox', array( 'TimberImageHelper', 'letterbox' ) ),
            array( 'tojpg', array( 'TimberImageHelper', 'img_to_jpg' ) ),

            /* debugging filters */
            array( 'docs', 'twig_object_docs' ),
            array( 'get_class',  'get_class' ),
            array( 'get_type', 'get_type' ),
            array(
                'print_r',
                function( $arr ) {
                    return print_r( $arr, true );
                }
            ),
            array(
                'print_a',
                function( $arr ) {
                    return '<pre>' . self::object_docs( $arr, true ) . '</pre>';
                }
            ),

            /* other filters */
            array( 'stripshortcodes', 'strip_shortcodes' ),
            array( 'array', array( $this, 'to_array' ) ),
            array( 'string', array( $this->_helper, 'to_string' ) ),
            array( 'excerpt', 'wp_trim_words' ),
            array( 'function', array( $this, 'exec_function' ) ),
            array( 'pretags', array( $this, 'twig_pretags' ) ),
            array( 'sanitize', 'sanitize_title' ),
            array( 'shortcodes', 'do_shortcode' ),
            array( 'time_ago', array( $this->_helper, 'time_ago' ) ),
            array( 'wpautop', 'wpautop' ),

            array(
                'relative',
                function ( $link ) {
                    return TimberURLHelper::get_rel_url( $link, true );
                }
            ),

            array( 'date', array( $this->_helper, 'intl_date' ) ),

            array(
                'truncate',
                function ( $text, $len ) {
                    return TimberHelper::trim_words( $text, $len );
                }
            ),

            /* actions and filters */
            array(
                'apply_filters',
                function () {
                    $args = func_get_args();
                    $tag = current( array_splice( $args, 1, 1 ) );

                    return apply_filters_ref_array( $tag, $args );
                }
            ),
        );
    }

    /**
     * @return array
     */
    private function get_timber_functions() {
        return array(
            array(
                'action',
                function ( $context ) {
                    $args = func_get_args();
                    array_shift( $args );
                    $args[] = $context;
                    call_user_func_array( 'do_action', $args );
                },
                array( 'needs_context' => true )
            ),

            array( 'function', array( &$this, 'exec_function' ) ),
            array( 'fn', array( &$this, 'exec_function' ) ),

            /* TimberObjects */
            array( 'TimberPost', array( $this, 'timber_post_factory' ) ),
            array( 'TimberImage', array( $this, 'timber_image_factory' ) ),
            array( 'TimberTerm', array( $this, 'timber_term_factory' ) ),
            array( 'TimberUser', array( $this, 'timber_user_factory' ) ),

            /* bloginfo and translate */
            array(
                'bloginfo',
                function ( $show = '', $filter = 'raw' ) {
                    return get_bloginfo( $show, $filter );
                }
            ),
            array(
                '__',
                function ( $text, $domain = 'default' ) {
                    return __( $text, $domain );
                }
            ),
        );
    }

    /**
     *
     *
     * @param Twig_Environment $twig
     * @return Twig_Environment
     */
    function add_timber_filters( $twig ) {
        $filters = $this->get_timber_filters();

        foreach ($filters as $filter) {
          $twig->addFilter( new Twig_SimpleFilter( $filter[0], $filter[1] ) );
        }

        $functions = $this->get_timber_functions();

        foreach ($functions as $function) {
            $simpleFunction = new ReflectionClass('Twig_SimpleFunction');

            $twig->addFunction(
                $simpleFunction->newInstanceArgs($function)
            );
        }

        $twig = apply_filters( 'get_twig', $twig );

        return $twig;
    }

    /**
     * @param mixed $pid
     * @param string $ObjectClass
     */
    public function timber_post_factory( $pid, $ObjectClass = 'TimberPost' ) {
        return $this->timber_object_factory($pid, $ObjectClass);
    }

    /**
     * @param mixed $pid
     * @param string $ObjectClass
     */
    public function timber_image_factory( $pid, $ObjectClass = 'TimberImage' ) {
        return $this->timber_object_factory($pid, $ObjectClass);
    }

    /**
     * @param mixed $pid
     * @param string $ObjectClass
     */
    public function timber_term_factory( $pid, $ObjectClass = 'TimberTerm' ) {
        return $this->timber_object_factory($pid, $ObjectClass);
    }

    /**
     * @param mixed $pid
     * @param string $ObjectClass
     */
    public function timber_user_factory( $pid, $ObjectClass = 'TimberUser' ) {
        return $this->timber_object_factory($pid, $ObjectClass);
    }

    /**
     * @param mixed $pid
     * @param string $ObjectClass
     */
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

}

new TimberTwig();

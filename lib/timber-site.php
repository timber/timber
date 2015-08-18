<?php

class TimberSite extends TimberCore implements TimberCoreInterface {

    public $admin_email;
    public $blogname;
    /**
     * @api
     * @var string
     */
    public $charset;

    /**
     * @var string
     */
    public $description;
    public $id;
    public $language;
    public $language_attributes;
    /**
     * @api
     * @var bool
     */
    public $multisite;
    public $name;
    public $pingback_url;
    public $siteurl;
    /**
     * @api
     * @var [TimberTheme](#TimberTheme)
     */
    public $theme;
    /**
     * @api
     * @var string
     */
    public $title;
    public $url;

    /**
     *
     *
     * @param string|int $site_name_or_id
     */
    function __construct( $site_name_or_id = null ) {
        if ( is_multisite() ) {
            $this->init_with_multisite( $site_name_or_id );
        } else {
            $this->init();
        }
    }

    /**
     * @internal
     * @param string|int $site_name_or_id
     */
    protected function init_with_multisite( $site_name_or_id ) {
        if ( $site_name_or_id === null ) {
            //this is necessary for some reason, otherwise returns 1 all the time
            if ( is_multisite() ) {
                restore_current_blog();
                $site_name_or_id = get_current_blog_id();
            }
        }
        $info = get_blog_details( $site_name_or_id );
        $this->import( $info );
        $this->ID = $info->blog_id;
        $this->id = $this->ID;
        $this->name = $this->blogname;
        $this->title = $this->blogname;
        $this->url = $this->siteurl;
        $theme_slug = get_blog_option( $info->blog_id, 'stylesheet' );
        $this->theme = new TimberTheme( $theme_slug );
        $this->language = get_bloginfo( 'language' );
        $this->charset = get_bloginfo( 'charset' );
        $this->pingback_url = get_bloginfo( 'pingback_url' );
        $this->language_attributes = TimberHelper::function_wrapper( 'language_attributes' );
        $this->description = get_blog_option( $info->blog_id, 'blogdescription' );
        $this->multisite = true;
        $this->admin_email = get_blog_option( $info->blog_id, 'admin_email' );
    }

    /**
     * @internal
     */
    protected function init() {
        $this->admin_email = get_bloginfo( 'admin_email' );
        $this->name = get_bloginfo( 'name' );
        $this->title = $this->name;
        $this->description = get_bloginfo( 'description' );
        $this->url = get_bloginfo( 'url' );
        $this->language = get_bloginfo( 'language' );
        $this->charset = get_bloginfo( 'charset' );
        $this->pingback_url = get_bloginfo( 'pingback_url' );
        $this->theme = new TimberTheme();
        $this->language_attributes = TimberHelper::function_wrapper( 'language_attributes' );
        $this->multisite = false;
    }

    /**
     *
     *
     * @param string  $field
     * @return mixed
     */
    function __get( $field ) {
        if ( !isset( $this->$field ) ) {
            if ( is_multisite() ) {
                $this->$field = get_blog_option( $this->ID, $field );
            } else {
                $this->$field = get_option( $field );
            }
        }
        return $this->$field;
    }

    /**
     *
     * @internal
     * @return string
     */
    function get_link() {
        return $this->url;
    }

    /**
     *
     * @internal
     * @return string
     */
    function get_url() {
        return $this->get_link();
    }

    /**
     *
     * @api
     * @return string
     */
    public function link() {
        return $this->get_link();
    }

    /**
     *
     */
    public function meta( $field ) {
        return $this->__get( $field );
    }

    /**
     *
     *
     * @param string  $key
     * @param mixed   $value
     */
    public function update( $key, $value ) {
        $value = apply_filters( 'timber_site_set_meta', $value, $key, $this->ID, $this );
        if ( is_multisite() ) {
            update_blog_option( $this->ID, $key, $value );
        } else {
            update_option( $key, $value );
        }
        $this->$key = $value;
    }

    /**
     *
     * @api
     * @see TimberSite::link
     * @return string
     */
    function url() {
        return $this->get_link();
    }

}

<?php

class TimberTheme extends TimberCore {

    /**
     * @api
     * @var string the absolute path to the theme (ex: `http://example.org/wp-content/themes/my-timber-theme`)
     */
    public $link;

    /**
     * @api
     * @var string the human-friendly name of the theme (ex: `My Timber Starter Theme`)
     */
    public $name;

    /**
     * @api
     * @var string the relative path to the theme (ex: `/wp-content/themes/my-timber-theme`)
     */
    public $path;
    
    /**
     * @api
     * @var TimberTheme|bool the TimberTheme object for the parent theme (if it exists), false otherwise
     */
    public $parent = false;

    /**
     * @api
     * @var string the slug of the parent theme (ex: `_s`)
     */
    public $parent_slug;

    /**
     * @api
     * @var string the slug of the theme (ex: `my-super-theme`)
     */
    public $slug;
    public $uri;

    /**
     * @param string $slug
     */
    function __construct($slug = null) {
        $this->init($slug);
    }

    /**
     * @internal
     * @param string $slug
     */
    protected function init($slug = null) {
        $data = wp_get_theme($slug);
        $this->name = $data->get('Name');
        $ss = $data->get_stylesheet();
        $this->slug = $ss;
        $this->path = WP_CONTENT_SUBDIR . str_replace(WP_CONTENT_DIR, '', get_stylesheet_directory());
        $this->uri = get_stylesheet_directory_uri();
        $this->link = $this->uri;
        $this->parent_slug = $data->get('Template');
        if (!$this->parent_slug) {
            $this->path = WP_CONTENT_SUBDIR . str_replace(WP_CONTENT_DIR, '', get_template_directory());
            $this->uri = get_template_directory_uri();
        }
        if ($this->parent_slug && $this->parent_slug != $this->slug) {
            $this->parent = new TimberTheme($this->parent_slug);
        }
    }

    /**
     * @param string $name
     * @param bool $default
     * @return string
     */
    public function theme_mod($name, $default = false) {
        return get_theme_mod($name, $default);
    }

    /**
     * @return string
     */
    public function theme_mods() {
        return get_theme_mods();
    }

}

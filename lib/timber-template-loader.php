<?php
// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

class TimberTemplateLoader
{
    const TEMPLATE_IN_OB = 'template_in_ob'; /**< Flag to indicate to-be-rendered template sits in the output buffer **/

    private $_timber_loader;

    public function __construct( $timber_loader = null ){

        if ( !$timber_loader )
            $timber_loader = new TimberLoader;

        $this->_timber_loader = $timber_loader;
    }

    public static function setup() {

        if ( !Timber::$twig_template_hierarchy )
            return;

        $loader = new self();

        add_filter( 'index_template',   array( $loader, 'template_loader' ) );
        add_filter( 'home_template',    array( $loader, 'home_template_loader' ) );
        add_filter( 'template_include', array( $loader, '_template_include' ), 999 );
    }

    public static function load_template( $context = array() ) {
        $self = new self();
        $located = $self->locate_template();

        if ( $located )
            self::_render( $located, $context );
    }

    public function template_loader( $wp_template = false ) {
        $located = $this->locate_template( $wp_template );

        /**
         * Filter the path of the current template before including it.
         *
         * Param is either a twig template or TimberTemplateLoader::TEMPLATE_IN_OB
         *
         * @param string $template The path of the template to include.
         */
        if ( !$located = apply_filters( 'timber/template_include', $located ) )
            return $wp_template;

        /**
         * Returns the .twig template
         *
         * @see TimberTemplateLoader::template_include()
         */
        return $located;

    }

    /**
     * WPs template loader includes index.php for the home template so index.php
     * has first priority after home.php. This is a little hack to undo that hack.
     */
    public function home_template_loader( $wp_template ) {
        // Fastest way to check if string ends with index.php :)
        if ( 0 === stripos( strrev( $wp_template ), 'php.xedni' ) ) {
            return $this->template_loader( $wp_template );
        }

        return $wp_template;
    }

    /**
     * Checks if the to be rendered template is a twig file and renders it.
     *
     * If the template has been loaded into the output buffer (ie. index.php),
     * the output buffer flushed here. If other plugins have overridden the
     * template during the meanwhile, the output buffer will be silently cleaned
     * by the anonymous fn in get_index_template.
     *
     * Should only be used by the template_include hook
     *
     * @param string $template
     * @return string|boolean
     *
     * @access private
     */
    public function _template_include( $template ) {

        if ( self::TEMPLATE_IN_OB === $template ) {

            ob_flush();
            $template = false;

        } elseif ( 0 === stripos( strrev( $template ), 'giwt.' ) ) {

            self::_render( $template );
            $template = false;

        }

        return $template;
    }

    public function locate_template( $wp_template = false ) {
        // Based on WP_INC . template-loader.php
        // The rest is from WP_INC . template.php
        if     ( is_404()            && $template = $this->get_404_template()            ) :
        elseif ( is_search()         && $template = $this->get_search_template()         ) :
        elseif ( is_front_page()     && $template = $this->get_front_page_template()     ) :
        elseif ( is_home() ) :
            $template = $this->get_home_template( $wp_template );
        elseif ( is_post_type_archive() && $template = $this->get_post_type_archive_template() ) :
        elseif ( is_tax()            && $template = $this->get_taxonomy_template()       ) :
        elseif ( is_attachment()     && $template = $this->get_attachment_template()     ) :
            remove_filter('the_content', 'prepend_attachment');
        elseif ( is_single()         && $template = $this->get_single_template()         ) :
        elseif ( is_page()           && $template = $this->get_page_template()           ) :
        elseif ( is_category()       && $template = $this->get_category_template()       ) :
        elseif ( is_tag()            && $template = $this->get_tag_template()            ) :
        elseif ( is_author()         && $template = $this->get_author_template()         ) :
        elseif ( is_date()           && $template = $this->get_date_template()           ) :
        elseif ( is_archive()        && $template = $this->get_archive_template()        ) :
        elseif ( is_paged()          && $template = $this->get_paged_template()          ) :
        else :
            $template = $this->get_index_template( $wp_template );
        endif;

        if ( true !== $template ) {
            /**
             * Filter the path of the current template
             *
             * @param string $template The path of the found template.
             */
            $template = apply_filters( 'timber/locate_template', $template );
        }

        return $template;
    }

    /**
     * Renders the template with default context
     *
     * @uses apply_filters Calls 'timber/context/template_loader' on the context so
     *                      the user can set extra context vars for use only with the
     *                      template loader
     *
     * @param string $template
     */
    protected static function _render( $template, $context = array() ) {
        $context = array_merge( Timber::get_context(), $context );
        $context = apply_filters( 'timber/context/template_loader', $context );

        Timber::render( $template, $context );
    }

    /**
     * Retrieve path to a Timber template
     *
     * Used to quickly retrieve the path of a template without including the file
     * extension. It will check all Timber template locatoins.
     *
     * @param string $type Filename without extension.
     * @param array $templates An optional list of template candidates
     * @return string Full path to file.
     */
    function get_query_template( $type, $templates = array() ) {

        $type = preg_replace( '|[^a-z0-9-]+|', '', $type );

        if ( empty( $templates ) )
            $templates = array("{$type}.twig");

        $template = $this->_timber_loader->choose_template( $templates );

        /**
         * Filter the path of the queried template by type.
         *
         * The dynamic portion of the hook name, $type, refers to the filename
         * -- minus the extension -- of the file to load. This hook also applies
         * to various types of files loaded as part of the Template Hierarchy.
         *
         * @param string $template Path to the template.
         */
        return apply_filters( "timber/template/{$type}", $template );
    }

    /**
     * Retrieve path of index template in all Timber template locations.
     *
     * Checks if index.php generates output. If it does, it falls back to index.php
     * and return true. Otherwise, it returns the path to index.twig.
     *
     * You can override the index.php twig by using the `timber_use_wp_index_template`
     * filter. Hook it to `__return_true` to force the use of index.php and
     * `__return_false` to force the use of index.twig.
     *
     * @param string The full path to the found WordPress template
     * @return bool|string
     */
    function get_index_template( $wp_template = false ) {
        static $use_wp_index_template = null;

        if ( $wp_template ) {

            if ( $use_wp_index_template || apply_filters( 'timber_use_wp_index_template', null ) )
                return false;

            if ( is_null( $use_wp_index_template ) ) {

                // Load index.php into the output buffer
                ob_start();
                include $wp_template;

                if ( ob_get_contents() !== '' ) {

                    // We  have the template in the output buffer
                    // If another plugin provides WP with a template file during the
                    // meanwhiles, the ob should be flushed after Timber Template Loader
                    // has decided on what content to render in ::template_include.
                    add_filter( 'template_include', function( $template ) {

                        ob_end_clean();
                        return $template;

                    }, 1000 );

                    $use_wp_index_template = true;
                    return self::TEMPLATE_IN_OB;

                } else {
                    ob_end_clean();

                }

                $use_wp_index_template = false;
            }
        }

        return $this->get_query_template('index');
    }

    /**
     * Retrieve path of 404 template in all Timber template locations.
     *
     * @return string
     */
    function get_404_template() {
        return $this->get_query_template('404');
    }

    /**
     * Retrieve path of archive template in all Timber template locations.
     *
     * @return string
     */
    function get_archive_template() {
        $post_types = array_filter( (array) get_query_var( 'post_type' ) );

        $templates = array();

        if ( count( $post_types ) == 1 ) {
            $post_type = reset( $post_types );
            $templates[] = "archive-{$post_type}.twig";
        }
        $templates[] = 'archive.twig';

        return $this->get_query_template( 'archive', $templates );
    }

    /**
     * Retrieve path of post type archive template in all Timber template locations.
     *
     * @return string
     */
    function get_post_type_archive_template() {
        $post_type = get_query_var( 'post_type' );
        if ( is_array( $post_type ) )
            $post_type = reset( $post_type );

        $obj = get_post_type_object( $post_type );
        if ( ! $obj->has_archive )
            return '';

        return get_archive_template();
    }

    /**
     * Retrieve path of author template in all Timber template locations.
     *
     * @return string
     */
    function get_author_template() {
        $author = get_queried_object();

        $templates = array();

        if ( is_a( $author, 'WP_User' ) ) {
            $templates[] = "author-{$author->user_nicename}.twig";
            $templates[] = "author-{$author->ID}.twig";
        }
        $templates[] = 'author.twig';

        return $this->get_query_template( 'author', $templates );
    }

    /**
     * Retrieve path of category template in all Timber template locations.
     *
     * Works by first retrieving the current slug, for example 'category-default.twig', and then
     * trying category ID, for example 'category-1.twig', and will finally fall back to category.twig
     * template, if those files don't exist.
     *
     * @return string
     */
    function get_category_template() {
        $category = get_queried_object();

        $templates = array();

        if ( ! empty( $category->slug ) ) {
            $templates[] = "category-{$category->slug}.twig";
            $templates[] = "category-{$category->term_id}.twig";
        }
        $templates[] = 'category.twig';

        return $this->get_query_template( 'category', $templates );
    }

    /**
     * Retrieve path of tag template in all Timber template locations.
     *
     * Works by first retrieving the current tag name, for example 'tag-wordpress.twig', and then
     * trying tag ID, for example 'tag-1.twig', and will finally fall back to tag.twig
     * template, if those files don't exist.
     *
     * @return string
     */
    function get_tag_template() {
        $tag = get_queried_object();

        $templates = array();

        if ( ! empty( $tag->slug ) ) {
            $templates[] = "tag-{$tag->slug}.twig";
            $templates[] = "tag-{$tag->term_id}.twig";
        }
        $templates[] = 'tag.twig';

        return $this->get_query_template( 'tag', $templates );
    }

    /**
     * Retrieve path of taxonomy template in all Timber template locations.
     *
     * Retrieves the taxonomy and term, if term is available. The template is
     * prepended with 'taxonomy-' and followed by both the taxonomy string and
     * the taxonomy string followed by a dash and then followed by the term.
     *
     * The taxonomy and term template is checked and used first, if it exists.
     * Second, just the taxonomy template is checked, and then finally, taxonomy.twig
     * template is used.
     *
     * @return string
     */
    function get_taxonomy_template() {
        $term = get_queried_object();

        $templates = array();

        if ( ! empty( $term->slug ) ) {
            $taxonomy = $term->taxonomy;
            $templates[] = "taxonomy-$taxonomy-{$term->slug}.twig";
            $templates[] = "taxonomy-$taxonomy.twig";
        }
        $templates[] = 'taxonomy.twig';

        return $this->get_query_template( 'taxonomy', $templates );
    }

    /**
     * Retrieve path of date template in all Timber template locations.
     *
     * @return string
     */
    function get_date_template() {
        return $this->get_query_template('date');
    }

    /**
     * Retrieve path of home template in all Timber template locations.
     *
     * This is the template used for the page containing the blog posts.
     *
     * Attempts to locate 'home.twig' first before falling back to first
     * 'index.php' (see ::get_index_template) and then index.twig.
     *
     * @return string
     */
    function get_home_template( $wp_template = false ) {
        $template = $this->get_query_template( 'home' );

        if ( !$template && $wp_template )
            return $this->get_index_template( $wp_template );

        return $template;

    }

    /**
     * Retrieve path of front-page template in all Timber template locations.
     *
     * Looks for 'front-page.twig'.
     *
     * @return string
     */
    function get_front_page_template() {
        $templates = array('front-page.twig');

        return $this->get_query_template( 'front_page', $templates );
    }

    /**
     * Retrieve path of page template in all Timber template locations.
     *
     * Will first look for the specifically assigned page template.
     * Then will search for 'page-{slug}.twig', followed by 'page-{id}.twig',
     * and finally 'page.twig'.
     *
     * @return string
     */
    function get_page_template() {
        $id = get_queried_object_id();
        $pagename = get_query_var('pagename');

        if ( ! $pagename && $id ) {
            // If a static page is set as the front page, $pagename will not be set. Retrieve it from the queried object
            $post = get_queried_object();
            if ( $post )
                $pagename = $post->post_name;
        }

        $templates = array();
        if ( $pagename )
            $templates[] = "page-$pagename.twig";
        if ( $id )
            $templates[] = "page-$id.twig";
        $templates[] = 'page.twig';

        return $this->get_query_template( 'page', $templates );
    }

    /**
     * Retrieve path of paged template in all Timber template locations.
     *
     * @return string
     */
    function get_paged_template() {
        return $this->get_query_template('paged');
    }

    /**
     * Retrieve path of search template in all Timber template locations.
     *
     * @return string
     */
    function get_search_template() {
        return $this->get_query_template('search');
    }

    /**
     * Retrieve path of single template in all Timber template locations.
     *
     * @return string
     */
    function get_single_template() {
        $object = get_queried_object();

        $templates = array();

        if ( ! empty( $object->post_type ) )
            $templates[] = "single-{$object->post_type}.twig";
        $templates[] = "single.twig";

        return $this->get_query_template( 'single', $templates );
    }

    /**
     * Retrieve path of attachment template in all Timber template locations.
     *
     * The attachment path first checks if the first part of the mime type exists.
     * The second check is for the second part of the mime type. The last check is
     * for both types separated by an underscore. If neither are found then the file
     * 'attachment.twig' is checked and returned.
     *
     * Some examples for the 'text/plain' mime type are 'text.twig', 'plain.twig', and
     * finally 'text_plain.twig'.
     *
     * @return string
     */
    function get_attachment_template() {
        global $posts;

        if ( ! empty( $posts ) && isset( $posts[0]->post_mime_type ) ) {
            $type = explode( '/', $posts[0]->post_mime_type );

            if ( ! empty( $type ) ) {
                if ( $template = get_query_template( $type[0] ) )
                    return $template;
                elseif ( ! empty( $type[1] ) ) {
                    if ( $template = get_query_template( $type[1] ) )
                        return $template;
                    elseif ( $template = get_query_template( "$type[0]_$type[1]" ) )
                        return $template;
                }
            }
        }

        return $this->get_query_template( 'attachment' );
    }

}

add_action( 'template_redirect', array( 'TimberTemplateLoader', 'setup' ) );
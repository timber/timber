<?php

class WooCommerceTimber
{
    public function __construct() {
        // WC template loader sits at 10.
        add_action( 'template_include', array( $this, 'template_include' ), 11 );
    }

    /**
     * Checks if the to-be-loaded template is a default WC template (from the plugin,
     * NOT from the theme!) and calls our own template loader if that is the case.
     */
    public function template_include( $template ) {
        // Do we have a WC template?
        if ( strpos( $template, WC()->plugin_path() . '/templates/' ) === 0 ) {
            $template = $this->template_loader( $template );
        }

        return $template;
    }

    /**
     * Looks for a twig template corresponding to the current WooCommerce view
     *
     * Loads WooCommerce templates in the same order as WC itself, and following
     * the same logic as TimberTemplateLoader. If no template is found, it starts
     * looking for woocommerce.twig and page-plugin.twig. If one of these is found,
     * the WooCommerce template will be loaded in the template context as `content`.
     *
     * @see WC_Template_Loader::template_loader()
     */
    public function template_loader( $template ) {
        $find = array( 'woocommerce.twig' );
		$file = '';
        $found = false;

		if ( is_single() && get_post_type() == 'product' ) {

			$file 	= 'single-product.twig';
			$find[] = $file;
			$find[] = WC_TEMPLATE_PATH . $file;

		} elseif ( is_tax( 'product_cat' ) || is_tax( 'product_tag' ) ) {

			$term = get_queried_object();

			$file 		= 'taxonomy-' . $term->taxonomy . '.twig';
			$find[] 	= 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.twig';
			$find[] 	= WC_TEMPLATE_PATH . 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.twig';
			$find[] 	= $file;
			$find[] 	= WC_TEMPLATE_PATH . $file;

		} elseif ( is_post_type_archive( 'product' ) || is_page( wc_get_page_id( 'shop' ) ) ) {

			$file 	= 'archive-product.twig';
			$find[] = $file;
			$find[] = WC_TEMPLATE_PATH . $file;

		}

        // Start looking for the template!
        
        $timber_loader = new TimberLoader();
		if ( $file ) {

            if ( $found = $timber_loader->choose_template( $find ) ) {
                $template = $found;
            }

		}

        // If no alternative template has been found, do desperate measures
        if ( !$found ) {

            $find  = array( 'woocommerce.twig', 'page-plugin.twig' );
            $found = $timber_loader->choose_template( $find );

            if ( $found ) {

                $this->_setup_plugin_compat();
                $template = $found;

            }

        }

		return $template;
    }

        /**
         * Adds the woocommerce_content function to Timber context as content
         */
        private function _setup_plugin_compat() {
            add_filter( 'timber_context', function( $context ){
                $context['content'] = TimberHelper::function_wrapper( 'woocommerce_content' );
                return $context;
            } );
        }
}

add_action( 'woocommerce_init', function(){
    new WooCommerceTimber();
});
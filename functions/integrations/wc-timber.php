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
     * @see WC_Template_Loader::template_loader()
     */
    public function template_loader( $template ) {
        $find = array( 'woocommerce.twig' );
		$file = '';

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

		if ( $file ) {
            
            $timber_loader = new TimberLoader();

            if ( $found = $timber_loader->choose_template( $find ) ) {
                $template = $found;
            }

		}

		return $template;
    }
}

add_action( 'woocommerce_init', function(){
    new WooCommerceTimber();
});
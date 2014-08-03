<?php
$templates = array( 'index.twig' );
$context = array();
if ( is_home() ) {
	echo 'home';
	$home = Timber::get_post( 'fake-home' );
	$context['post'] = $home;
	array_unshift( $templates, 'home.twig' );
} else {
	echo 'or not';
}
Timber::render( $templates, $context );

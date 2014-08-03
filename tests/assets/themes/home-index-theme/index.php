<?php
$templates = array( 'index.twig' );
$context = array();
if ( is_home() ) {
	$home = Timber::get_post( 'fake-home' );
	$context['post'] = $home;
	$context['content'] = 'from index.php';
	array_unshift( $templates, 'home.twig' );
}
Timber::render( $templates, $context );

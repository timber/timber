<?php

define('WP_CONTENT_DIR', '');
define('WP_CONTENT_URL', '');
define('TIMBER_LOC', '');

function wpml_object_id_filter($locations, $nav_menu)
{
    return 'string';
}
function get_coauthors($id)
{
    return [];
}

// Loads twig_array_filter()
new Twig\Extension\CoreExtension();

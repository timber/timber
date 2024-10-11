<?php

namespace Timber\Integration;

use WP_Post;
use WPML_LS_Menu_Item;

class WpmlIntegration implements IntegrationInterface
{
    public function should_init(): bool
    {
        return \function_exists('wpml_object_id_filter');
    }

    public function init(): void
    {
        \add_filter('theme_mod_nav_menu_locations', [$this, 'theme_mod_nav_menu_locations'], 10, 1);
        \add_filter('timber/url_helper/file_system_to_url', [$this, 'file_system_to_url'], 10, 1);
        \add_filter('timber/url_helper/get_content_subdir/home_url', [$this, 'file_system_to_url'], 10, 1);
        \add_filter('timber/url_helper/url_to_file_system/path', [$this, 'file_system_to_url'], 10, 1);
        \add_filter('timber/menu/item_objects', [$this, 'menu_item_objects_filter'], 10, 1);
        \add_filter('timber/image_helper/_get_file_url/home_url', [$this, 'file_system_to_url'], 10, 1);
    }

    public function file_system_to_url($url)
    {
        if (\defined('ICL_LANGUAGE_CODE')) {
            $url = \preg_replace('/(?<!:\/)\/' . ICL_LANGUAGE_CODE . '/', '', (string) $url);
        }
        return $url;
    }

    public function menu_item_objects_filter(array $items)
    {
        return \array_map(
            fn ($item) => ($item instanceof WPML_LS_Menu_Item ? new WP_Post($item) : $item),
            $items
        );
    }

    public function theme_mod_nav_menu_locations($locations)
    {
        if (!\is_array($locations)) {
            return $locations;
        }

        return \array_map(
            fn ($id) => \wpml_object_id_filter($id, 'nav_menu'),
            $locations
        );
    }
}

<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class Timber_UnitTestCase extends TestCase
{
    /**
     * Maintain a list of action/filter hook removals to perform at the end of each test.
     */
    private $temporary_hook_removals = [];

    /**
     * Overload WP_UnitTestcase to ignore deprecated notices
     * thrown by use of wp_title() in Timber
     */
    public function expectedDeprecated()
    {
        if (false !== ($key = array_search('wp_title', $this->caught_deprecated))) {
            unset($this->caught_deprecated[$key]);
        }
        parent::expectedDeprecated();
    }

    public static function enable_error_log($opt = true)
    {
        global $timber_disable_error_log;
        $timber_disable_error_log = !$opt;
    }

    public static function setPermalinkStructure($struc = '/%postname%/')
    {
        global $wp_rewrite;
        $wp_rewrite->set_permalink_structure($struc);
        $wp_rewrite->flush_rules();
        update_option('permalink_structure', $struc);
        flush_rewrite_rules(true);
    }

    public function tear_down()
    {
        self::resetPermalinks();
        parent::tear_down();
        Timber::$context_cache = [];

        // remove any hooks added during this test run
        foreach ($this->temporary_hook_removals as $callback) {
            $callback();
        }
        // reset hooks
        $this->temporary_hook_removals = [];
    }

    public function resetPermalinks()
    {
        delete_option('permalink_structure');
        global $wp_rewrite;
        $wp_rewrite->set_permalink_structure(false);
        $wp_rewrite->init();
        $wp_rewrite->flush_rules();
        flush_rewrite_rules(true);
    }

    public function setupCustomWPDirectoryStructure()
    {
        add_filter('content_url', [$this, 'setContentUrl']);
        add_filter('option_upload_path', [$this, 'setUploadPath']);
        add_filter('option_upload_url_path', [$this, 'setUploadUrlPath']);
        add_filter('option_siteurl', [$this, 'setSiteUrl']);
        add_filter('option_home_url', [$this, 'setHomeUrl']);
    }

    public function tearDownCustomWPDirectoryStructure()
    {
        remove_filter('content_url', [$this, 'setContentUrl']);
        remove_filter('option_upload_path', [$this, 'setUploadPath']);
        remove_filter('option_upload_url_path', [$this, 'setUploadUrlPath']);
        remove_filter('option_siteurl', [$this, 'setSiteUrl']);
        remove_filter('option_home_url', [$this, 'setHomeUrl']);
    }

    public function setContentUrl($url)
    {
        return 'http://' . $_SERVER['HTTP_HOST'] . '/content';
    }

    public function setUploadPath($dir)
    {
        return $_SERVER['DOCUMENT_ROOT'] . 'content/uploads';
    }

    public function setUploadUrlPath($dir)
    {
        return 'http://' . $_SERVER['HTTP_HOST'] . '/content/uploads';
    }

    public function setSiteUrl($url)
    {
        return 'http://' . $_SERVER['HTTP_HOST'] . '/wp';
    }

    public function clearPosts()
    {
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE $wpdb->posts;");
    }

    public function switch_to_locale($locale)
    {
        switch_to_locale($locale);
        // Load the file after switching to override wp tests languages files
        $tests_language_mo = __DIR__ . '/languages/' . $locale . '.mo';
        if (!is_file($tests_language_mo)) {
            wp_die('No language file found for ' . $locale);
        }
        load_textdomain('default', $tests_language_mo);
    }

    public function truncate(string $table)
    {
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$wpdb->$table}");
    }

    /**
     * Exactly the same as add_filter, but automatically calls remove_filter with the same
     * arguments during tear_down().
     */
    protected function add_filter_temporarily(string $filter, callable $callback, int $pri = 10, int $count = 1)
    {
        add_filter($filter, $callback, $pri, $count);
        $this->temporary_hook_removals[] = function () use ($filter, $callback, $pri, $count) {
            remove_filter($filter, $callback, $pri, $count);
        };
    }

    /**
         * Exactly the same as add_action, but automatically calls remove_action with the same
         * arguments during tear_down().
         */
    protected function add_action_temporarily(string $action, callable $callback, int $pri = 10, int $count = 1)
    {
        add_action($action, $callback, $pri, $count);
        $this->temporary_hook_removals[] = function () use ($action, $callback, $pri, $count) {
            remove_action($action, $callback, $pri, $count);
        };
    }

    protected function register_post_classmap_temporarily(array $classmap)
    {
        $this->add_filter_temporarily('timber/post/classmap', function (array $current) use ($classmap) {
            return array_merge($current, $classmap);
        });
    }

    /**
     * Add the given nav_menu_item post IDs to the given menu.
     * @param int $menu_id the term_id of the menu to add to.
     * @param int[] $item_ids the list of nav_menu_item post IDs to add.
     */
    protected function add_menu_items(int $menu_id, array $item_ids)
    {
        global $wpdb;
        foreach ($item_ids as $id) {
            // $query = "INSERT INTO $wpdb->term_relationships (object_id, term_taxonomy_id, term_order) VALUES ($id, $menu_id, 0);";
            $wpdb->query(sprintf(
                'INSERT INTO %s (object_id, term_taxonomy_id, term_order)'
                . ' VALUES (%d, %d, 0);',
                $wpdb->term_relationships,
                $id,
                $menu_id
            ));
        }
        $menu_items_count = count($item_ids);
        $wpdb->query("UPDATE $wpdb->term_taxonomy SET count = $menu_items_count WHERE taxonomy = 'nav_menu'; ");
    }

    /**
     * Test helper for creating posts and corresponding menu/items from raw post data.
     * @param array $posts_data an array of raw post data arrays. Each post array is passed
     * separately to wp_insert_post().
     * @return array an array of the form:
     * [
     *   "term" => (WP_Term menu instance),
     *   "item_ids" => [(...nav_menu_item post IDs...)],
     * ]
     */
    protected function create_menu_from_posts(array $posts_data)
    {
        $item_ids = array_map(function (array $post_data) {
            $post_id = wp_insert_post($post_data);
            $item_id = wp_insert_post([
                'post_title' => '',
                'post_status' => 'publish',
                'post_type' => 'nav_menu_item',
            ]);

            update_post_meta($item_id, '_menu_item_object_id', $post_id);
            update_post_meta($item_id, '_menu_item_type', 'post_type');
            update_post_meta($item_id, '_menu_item_object', 'page');
            update_post_meta($item_id, '_menu_item_menu_item_parent', 0);
            update_post_meta($item_id, '_menu_item_url', '');

            return $item_id;
        }, $posts_data);

        $menu_term = wp_insert_term('Main Menu', 'nav_menu');
        $this->add_menu_items($menu_term['term_id'], $item_ids);

        return [
            'term' => $menu_term,
            'item_ids' => $item_ids,
        ];
    }

    protected function callMethod($obj, $name, array $args = [])
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

	public function isWordPressVersion(string $version, string $operator = '=')
    {
        return version_compare($GLOBALS['wp_version'], $version, $operator);
    }

    public function skipForWordpressVersion(string $version, string $operator = '<')
    {
        if ($this->isWordPressVersion($version, $operator)) {
            $this->markTestSkipped("This test requires WordPress version $version or higher.");
        }
    }
}

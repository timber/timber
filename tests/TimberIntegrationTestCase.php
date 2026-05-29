<?php

namespace Timber\Tests;

use Mantle\Testkit\Integration_Test_Case;
use Timber\Tests\Support\Concerns\InteractsWithState;
use Timber\Timber;

abstract class TimberIntegrationTestCase extends Integration_Test_Case
{
    use InteractsWithState;

    /**
     * Maintain a list of action/filter hook removals to perform at the end of each test.
     */
    private $temporary_hook_removals = [];

    /**
     * Backup for Timber locations.
     */
    protected $backup_timber_locations;

    public function set_up()
    {
        parent::set_up();

        // WP 7.0+ triggers _doing_it_wrong for wp_maybe_inline_styles when
        // stylesheets lack proper paths. This happens during $this->get() calls
        // in tests because the test environment doesn't have real stylesheets.
        // Note: version_compare considers '7.0-alpha' < '7.0', so we check >6.9
        if ($this->isWordPressVersion('6.9', '>')) {
            $this->ignoreIncorrectUsage('wp_maybe_inline_styles');
        }

        // Reset deprecated static properties to prevent test pollution
        Timber::$twig_cache = false;
        Timber::$cache = false;
        Timber::$autoescape = false;

        // Reset the context cache to prevent test pollution
        // This ensures filters added in tests affect the context
        Timber::$context_cache = [];

        // Save original locations and add Fixtures directory for test templates
        $this->backup_timber_locations = Timber::$locations;
        Timber::$locations = $this->getFixturesDir();
    }

    public function tear_down()
    {
        // Restore original Timber locations
        Timber::$locations = $this->backup_timber_locations;

        parent::tear_down();
    }

    public static function enable_error_log($opt = true)
    {
        global $timber_disable_error_log;
        $timber_disable_error_log = !$opt;
    }

    public function setupCustomWPDirectoryStructure()
    {
        // Use array callables (not first-class callable syntax) so that the
        // exact same hook identity is registered here and removed in
        // tearDownCustomWPDirectoryStructure(). Each `$this->method(...)` call
        // mints a distinct Closure with its own spl_object_hash, so
        // remove_filter() would never match and the filters would leak into
        // later tests run in random order.
        \add_filter('content_url', [$this, 'setContentUrl']);
        \add_filter('option_upload_path', [$this, 'setUploadPath']);
        \add_filter('option_upload_url_path', [$this, 'setUploadUrlPath']);
        \add_filter('option_siteurl', [$this, 'setSiteUrl']);

        // wp_upload_dir() memoizes _wp_upload_dir() (which reads the upload_path /
        // upload_url_path options filtered above) in a process-lived static cache.
        // Force a refresh so the custom directory values take effect now, instead
        // of returning a value cached by an earlier test.
        \wp_upload_dir(null, false, true);
    }

    public function tearDownCustomWPDirectoryStructure()
    {
        \remove_filter('content_url', [$this, 'setContentUrl']);
        \remove_filter('option_upload_path', [$this, 'setUploadPath']);
        \remove_filter('option_upload_url_path', [$this, 'setUploadUrlPath']);
        \remove_filter('option_siteurl', [$this, 'setSiteUrl']);

        // Evict the custom-directory value baked into wp_upload_dir()'s static
        // cache so it does not leak into later tests run in random order. With
        // the filters now removed, this recomputes the default uploads location.
        \wp_upload_dir(null, false, true);
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

    /**
     * Exactly the same as add_filter, but automatically calls remove_filter with the same
     * arguments during tear_down().
     */
    protected function add_filter_temporarily(string $filter, callable $callback, int $pri = 10, int $count = 1)
    {
        \add_filter($filter, $callback, $pri, $count);
        $this->temporary_hook_removals[] = function () use ($filter, $callback, $pri, $count) {
            \remove_filter($filter, $callback, $pri, $count);
        };
    }

    /**
     * Same as remove_filter(), but re-adds the filter during set_up().
     */
    protected function remove_filter_temporarily(string $filter, callable $callback, int $pri = 10, int $count = 1)
    {
        \remove_filter($filter, $callback, $pri);
        $this->temporary_hook_removals[] = function () use ($filter, $callback, $pri, $count) {
            \add_filter($filter, $callback, $pri, $count);
        };
    }

    /**
     * Exactly the same as add_action, but automatically calls remove_action with the same
     * arguments during tear_down().
     */
    protected function add_action_temporarily(string $action, callable $callback, int $pri = 10, int $count = 1)
    {
        \add_action($action, $callback, $pri, $count);
        $this->temporary_hook_removals[] = function () use ($action, $callback, $pri, $count) {
            \remove_action($action, $callback, $pri, $count);
        };
    }

    protected function register_post_classmap_temporarily(array $classmap)
    {
        $this->add_filter_temporarily('timber/post/classmap', fn (array $current) => \array_merge($current, $classmap));
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
            $wpdb->query(\sprintf(
                'INSERT INTO %s (object_id, term_taxonomy_id, term_order)'
                . ' VALUES (%d, %d, 0);',
                $wpdb->term_relationships,
                $id,
                $menu_id
            ));
        }
        $menu_items_count = \count($item_ids);
        $wpdb->query("UPDATE $wpdb->term_taxonomy SET count = $menu_items_count WHERE taxonomy = 'nav_menu'; ");
    }

    /**
     * Test helper for creating posts and corresponding menu/items from raw post data.
     * @param array $posts_data an array of raw post data arrays. Each post array is passed
     * separately to wp_insert_post().
     * @return array an array of the form:
     * [
     *   "term" => (\WP_Term menu instance),
     *   "item_ids" => [(...nav_menu_item post IDs...)],
     * ]
     */
    protected function create_menu_from_posts(array $posts_data)
    {
        $item_ids = \array_map(function (array $post_data) {
            $post_id = \wp_insert_post($post_data);
            $item_id = \wp_insert_post([
                'post_title' => '',
                'post_status' => 'publish',
                'post_type' => 'nav_menu_item',
            ]);

            \update_post_meta($item_id, '_menu_item_object_id', $post_id);
            \update_post_meta($item_id, '_menu_item_type', 'post_type');
            \update_post_meta($item_id, '_menu_item_object', 'page');
            \update_post_meta($item_id, '_menu_item_menu_item_parent', 0);
            \update_post_meta($item_id, '_menu_item_url', '');

            return $item_id;
        }, $posts_data);

        $menu_term = \wp_insert_term('Main Menu', 'nav_menu');
        $this->add_menu_items($menu_term['term_id'], $item_ids);

        return [
            'term' => $menu_term,
            'item_ids' => $item_ids,
        ];
    }

    public function isWordPressVersion(string $version, string $operator = '=')
    {
        return \version_compare($GLOBALS['wp_version'], $version, $operator);
    }

    protected function getFixturesDir(): string
    {
        return __DIR__ . '/Fixtures';
    }

    protected function getFixtureAsset(string $fixtureFile): string
    {
        return $this->getFixturesDir() . '/assets/' . $fixtureFile;
    }

    /**
     * Copy a test image to the uploads directory without creating an attachment.
     *
     * Use this for tests that operate on image files directly (not attachments).
     *
     * @param string $file Filename from Fixtures/assets/
     * @param string|null $destName Destination filename (defaults to source name)
     * @return string Full path to copied file
     */
    protected function copyImageToUploads(string $file = 'arch.jpg', ?string $destName = null): string
    {
        $upload_dir = \wp_get_upload_dir();
        $destName ??= $file;
        $destination = $upload_dir['path'] . '/' . $destName;

        // Ensure the upload directory exists
        if (!\is_dir($upload_dir['path'])) {
            \wp_mkdir_p($upload_dir['path']);
        }

        \copy($this->getFixtureAsset($file), $destination);

        return $destination;
    }

    /**
     * Get the URL for a file in the current uploads directory.
     *
     * @param string $file Filename
     * @param bool $relative Return relative URL (without home_url)
     * @return string
     */
    protected function getUploadsUrl(string $file, bool $relative = false): string
    {
        $upload_dir = \wp_get_upload_dir();
        $url = $upload_dir['url'] . '/' . $file;

        if ($relative) {
            $url = \str_replace(\home_url(), '', $url);
        }

        return $url;
    }

    /**
     * Register a file for cleanup after test.
     *
     * Legacy method - Mantle handles most cleanup. This is kept for
     * tracking generated files (like resized images) that may need cleanup.
     *
     * @param string $file Path to file
     */
    protected function addFile(string $file): void
    {
        // No-op: Mantle's test isolation handles cleanup
        // If cleanup issues arise, this can be implemented to track files
    }

    /**
     * Create an attachment with a real image file.
     *
     * Use this when you need actual image manipulation (resize, etc.).
     * For tests that just need an attachment ID, use:
     *   static::factory()->attachment->create()
     *
     * @param int $parent_id Parent post ID (0 for no parent)
     * @param string $file Filename from Fixtures/assets/
     * @return int Attachment ID
     */
    protected function createAttachmentWithImage(int $parent_id = 0, string $file = 'arch.jpg'): int
    {
        return static::factory()->attachment
            ->with_image($this->getFixtureAsset($file))
            ->create([
                'post_parent' => $parent_id,
            ]);
    }
}

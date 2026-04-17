<?php

namespace Timber\Tests;

use Timber\Timber;
use WP_Widget;

class TimberWidgetsTest extends TimberIntegrationTestCase
{
    private function registerSidebars()
    {
        // Add filter to prevent WordPress from auto-populating block widgets
        $this->add_filter_temporarily('pre_option_fresh_site', fn () => '0');

        \register_sidebar([
            'name' => 'Sidebar 1',
            'id' => 'sidebar-1',
            'before_widget' => '<div class="widget">',
            'after_widget' => '</div>',
            'before_title' => '<h3 class="widget-title">',
            'after_title' => '</h3>',
        ]);

        \register_sidebar([
            'name' => 'Sidebar 2',
            'id' => 'sidebar-2',
            'before_widget' => '<div class="widget">',
            'after_widget' => '</div>',
            'before_title' => '<h3 class="widget-title">',
            'after_title' => '</h3>',
        ]);

        // Clear any existing default widgets that WordPress might have added
        // This is necessary in WordPress 6.9+ which auto-populates sidebars with block widgets
        \update_option('sidebars_widgets', [
            'wp_inactive_widgets' => [],
            'sidebar-1' => [],
            'sidebar-2' => [],
        ]);

        // Disable block widget insertion
        \remove_action('after_setup_theme', 'wp_setup_widgets_block_editor');
    }

    private function setupWidget($sidebar_id, $widget_number, $title, $text, $append = false)
    {
        global $wp_registered_widgets;

        $widget = new TimberTestWidget();
        $widget_id = 'timber_test_widget-' . $widget_number;

        // Register the widget in WordPress
        $wp_registered_widgets[$widget_id] = [
            'name' => $title,
            'id' => $widget_id,
            'callback' => function ($args) use ($widget, $title, $text) {
                $instance = [
                    'title' => $title,
                    'text' => $text,
                ];
                $widget->widget($args, $instance);
            },
            'params' => [
                [
                    'before_widget' => '<div class="widget">',
                    'after_widget' => '</div>',
                    'before_title' => '<h3 class="widget-title">',
                    'after_title' => '</h3>',
                ],
            ],
            'classname' => 'timber_test_widget',
        ];

        // Get existing widgets
        $sidebars_widgets = \get_option('sidebars_widgets', []);

        if ($append && isset($sidebars_widgets[$sidebar_id])) {
            // Append to existing widgets
            $sidebars_widgets[$sidebar_id][] = $widget_id;
        } else {
            // Replace all widgets with just this one
            $sidebars_widgets[$sidebar_id] = [$widget_id];
        }

        \update_option('sidebars_widgets', $sidebars_widgets);

        // Force refresh the global sidebars_widgets
        global $wp_registered_sidebars;
        \wp_cache_delete('sidebars_widgets', 'default');
    }

    public function testHTML()
    {
        $this->registerSidebars();
        \register_widget(TimberTestWidget::class);

        $this->setupWidget('sidebar-1', 1, 'Test Widget', 'This is test content');

        // Add a filter to override the sidebars_widgets option at retrieval time
        $this->add_filter_temporarily(
            'sidebars_widgets',
            // Only return our custom widgets, ignoring any defaults
            fn ($sidebars_widgets) => [
                'wp_inactive_widgets' => [],
                'sidebar-1' => ['timber_test_widget-1'],
                'sidebar-2' => [],
            ]
        );

        $content = Timber::get_widgets('sidebar-1');
        $content = \trim($content);

        $this->assertNotEmpty($content);
        $this->assertEquals('<', \substr($content, 0, 1));
        $this->assertStringContainsString('Test Widget', $content);
        $this->assertStringContainsString('This is test content', $content);
    }

    public function testManySidebars()
    {
        $this->registerSidebars();
        \register_widget(TimberTestWidget::class);

        $this->setupWidget('sidebar-1', 1, 'Widget in Sidebar 1', 'Content for sidebar 1');
        $this->setupWidget('sidebar-2', 2, 'Widget in Sidebar 2', 'Content for sidebar 2');

        // Add a filter to override the sidebars_widgets option at retrieval time
        $this->add_filter_temporarily(
            'sidebars_widgets',
            // Only return our custom widgets, ignoring any defaults
            fn ($sidebars_widgets) => [
                'wp_inactive_widgets' => [],
                'sidebar-1' => ['timber_test_widget-1'],
                'sidebar-2' => ['timber_test_widget-2'],
            ]
        );

        $sidebar1 = Timber::get_widgets('sidebar-1');
        $sidebar2 = Timber::get_widgets('sidebar-2');

        $this->assertGreaterThan(0, \strlen($sidebar1));
        $this->assertGreaterThan(0, \strlen($sidebar2));
        $this->assertNotEquals($sidebar1, $sidebar2);
        $this->assertStringContainsString('Widget in Sidebar 1', $sidebar1);
        $this->assertStringContainsString('Widget in Sidebar 2', $sidebar2);
    }

    public function testMultipleWidgetsInSidebar()
    {
        $this->registerSidebars();
        \register_widget(TimberTestWidget::class);

        $this->setupWidget('sidebar-1', 1, 'First Widget', 'First widget content');
        $this->setupWidget('sidebar-1', 2, 'Second Widget', 'Second widget content', true);

        // Add a filter to override the sidebars_widgets option at retrieval time
        $this->add_filter_temporarily(
            'sidebars_widgets',
            // Only return our custom widgets, ignoring any defaults
            fn ($sidebars_widgets) => [
                'wp_inactive_widgets' => [],
                'sidebar-1' => ['timber_test_widget-1', 'timber_test_widget-2'],
                'sidebar-2' => [],
            ]
        );

        $content = Timber::get_widgets('sidebar-1');

        $this->assertStringContainsString('First Widget', $content);
        $this->assertStringContainsString('First widget content', $content);
        $this->assertStringContainsString('Second Widget', $content);
        $this->assertStringContainsString('Second widget content', $content);
    }
}

/**
 * A simple test widget for testing purposes
 */
class TimberTestWidget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'timber_test_widget',
            'Timber Test Widget',
            [
                'description' => 'A simple test widget for Timber tests',
            ]
        );
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . $instance['title'] . $args['after_title'];
        }
        echo '<p>' . ($instance['text'] ?? 'Default text') . '</p>';
        echo $args['after_widget'];
    }
}

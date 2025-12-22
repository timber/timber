<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use Timber\Timber;

#[Group('called-post-constructor')]
class TimberSidebarTest extends TimberIntegrationTestCase
{
    public function testTwigSidebar()
    {
        $context = Timber::context();
        $sidebar_post = static::factory()->post->create([
            'post_title' => 'Sidebar post content',
        ]);
        $sidebar_context = [];
        $sidebar_context['post'] = Timber::get_post($sidebar_post);
        $context['sidebar'] = Timber::get_sidebar($this->getFixtureAsset('sidebar.twig'), $sidebar_context);
        $result = Timber::compile($this->getFixtureAsset('main-w-sidebar.twig'), $context);

        $this->assertEquals('I am the main stuff <h4>Sidebar post content</h4>', \trim($result));
    }

    public function testPHPSidebar()
    {
        $this->add_filter_temporarily('timber/context', function ($context) {
            $context['sidebar'] = Timber::get_sidebar($this->getFixtureAsset('my-sidebar.php'));
            return $context;
        });
        $context = Timber::context();
        $result = Timber::compile($this->getFixtureAsset('main-w-sidebar-php.twig'), $context);

        $this->assertEquals("A Fever You Can't Sweat Out by Panic! at the Disco from 2005", \trim($result));
    }
}

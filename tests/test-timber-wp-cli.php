<?php

use Timber\Integration\CLI\TimberCommand;
use Timber\Loader;

class TestTimberWpCli extends Timber_UnitTestCase
{
    protected function create_timber_database_cache()
    {
        Timber::compile('assets/single-post.twig', [
            'post' => Timber::get_post($this->factory->post->create()),
        ], 600);

        sleep(1);
    }

    protected function create_timber_object_cache()
    {
        Timber::compile('assets/single-post.twig', [
            'post' => Timber::get_post($this->factory->post->create()),
        ], 600, Loader::CACHE_OBJECT);

        sleep(1);
    }

    protected function enable_twig_cache()
    {
        $this->add_filter_temporarily('timber/twig/environment/options', function ($options) {
            $options['cache'] = true;

            return $options;
        });
    }

    protected function create_twig_cache()
    {
        Timber::compile('assets/single-post.twig', [
            'post' => Timber::get_post($this->factory->post->create()),
        ]);

        sleep(1);
    }

    public function test_clear_cache_command_without_cache()
    {
        $command = new TimberCommand();
        $command->clear_cache();

        $this->expectOutputString('Clearing all caches …Success: Cleared all caches.');
    }

    public function test_clear_cache_command_with_caches()
    {
        // Make sure Timber and Twig caches exist.
        $this->create_timber_database_cache();
        $this->create_timber_object_cache();
        $this->enable_twig_cache();
        $this->create_twig_cache();

        $command = new TimberCommand();
        $command->clear_cache();

        $this->expectOutputString('Clearing all caches …Success: Cleared all caches.');
    }

    public function test_clear_cache_timber_command()
    {
        // Make sure a Timber cache exists.
        $this->create_timber_database_cache();
        $this->create_timber_object_cache();

        $command = new TimberCommand();
        $command->clear_cache(['timber']);

        $this->expectOutputString('Clearing Timber caches …Success: Cleared Timber caches.');
    }

    public function test_clear_cache_twig_command()
    {
        // Make sure a Twig cache exists.
        $this->enable_twig_cache();
        $this->create_twig_cache();

        $command = new TimberCommand();
        $command->clear_cache(['twig']);

        $this->expectOutputString('Clearing Twig caches …Success: Cleared Twig caches.');
    }

    public function test_clear_cache_twig_command_without_cache()
    {
        $command = new TimberCommand();
        $command->clear_cache(['twig']);

        $this->expectOutputString('Clearing Twig caches …Success: Cleared Twig caches.');
    }

    public function test_clear_cache_twig_command_without_cache_but_twig_cache_activated()
    {
        $this->enable_twig_cache();

        $command = new TimberCommand();
        $command->clear_cache(['twig']);

        $this->expectOutputString('Clearing Twig caches …Success: Cleared Twig caches.');
    }
}

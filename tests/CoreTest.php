<?php

namespace Timber\Tests;

use stdClass;
use Timber\Post;
use Timber\Timber;

class CoreTester extends Post
{
    public $public = 'public A';

    protected $protected = 'protected A';

    private $private = 'private A';

    public $existing = 'value from A';

    public function foo()
    {
        return 'bar';
    }
}

class ClassB
{
    public $public = 'public B';

    protected $protected = 'protected B';

    private $private = 'private B';

    public $existing = 'value from B';
}

class CoreTest extends TimberIntegrationTestCase
{
    public function testCoreImport()
    {
        $this->register_post_classmap_temporarily([
            'post' => CoreTester::class,
        ]);

        $post_id = static::factory()->post->create();
        $tc = Timber::get_post($post_id);
        $object = new stdClass();
        $object->frank = 'Drebin';
        $object->foo = 'Dark Helmet';
        $tc->import($object);
        $this->assertEquals('Drebin', $tc->frank);
        $this->assertEquals('bar', $tc->foo);
        $tc->import($object, true);
        $this->assertEquals('Dark Helmet', $tc->foo);
        $this->assertEquals('Drebin', $tc->frank);
    }

    public function testCoreImportWithPropertyTypes()
    {
        $this->register_post_classmap_temporarily([
            'post' => CoreTester::class,
        ]);

        $post_id = static::factory()->post->create();
        $tc = Timber::get_post($post_id);
        $object = new ClassB();
        $tc->import((object) (array) $object);
        $this->assertEquals('public B', $tc->public);
        $this->assertEquals('value from B', $tc->existing);
    }
}

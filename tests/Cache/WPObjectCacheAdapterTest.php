<?php

namespace Timber\Tests\Cache;

use PHPUnit\Framework\Attributes\Group;
use Timber\Cache\WPObjectCacheAdapter;
use Timber\Loader;
use Timber\Tests\TimberIntegrationTestCase;

#[Group('cache')]
class WPObjectCacheAdapterTest extends TimberIntegrationTestCase
{
    public function testSaveAndFetch()
    {
        $loader = new Loader();
        $adapter = new WPObjectCacheAdapter($loader);

        // Save returns the value that was saved
        $adapter->save('test_key', 'test_value', 600);

        $result = $adapter->fetch('test_key');

        $this->assertEquals('test_value', $result);
    }

    public function testFetchNonExistent()
    {
        $loader = new Loader();
        $adapter = new WPObjectCacheAdapter($loader);

        $result = $adapter->fetch('non_existent_key');

        $this->assertFalse($result);
    }

    public function testSaveReturnsValue()
    {
        $loader = new Loader();
        $adapter = new WPObjectCacheAdapter($loader);

        $result = $adapter->save('save_test_key', 'saved_value', 600);

        // set_cache returns the value on success
        $this->assertEquals('saved_value', $result);
    }

    public function testSaveAndFetchWithCustomCacheGroup()
    {
        $loader = new Loader();
        $adapter = new WPObjectCacheAdapter($loader, 'custom_group');

        $adapter->save('custom_key', 'custom_value', 600);
        $result = $adapter->fetch('custom_key');

        $this->assertEquals('custom_value', $result);
    }

    public function testSaveWithZeroExpire()
    {
        $loader = new Loader();
        $adapter = new WPObjectCacheAdapter($loader);

        $result = $adapter->save('zero_expire_key', 'zero_value', 0);

        // Returns the value
        $this->assertEquals('zero_value', $result);
    }

    public function testMultipleSavesOverwrite()
    {
        $loader = new Loader();
        $adapter = new WPObjectCacheAdapter($loader);

        $adapter->save('overwrite_key', 'first_value', 600);
        $adapter->save('overwrite_key', 'second_value', 600);

        $result = $adapter->fetch('overwrite_key');

        $this->assertEquals('second_value', $result);
    }

    public function testDifferentCacheGroupsAreIsolated()
    {
        $loader = new Loader();
        $adapter1 = new WPObjectCacheAdapter($loader, 'group1');
        $adapter2 = new WPObjectCacheAdapter($loader, 'group2');

        $adapter1->save('same_key', 'value_from_group1', 600);
        $adapter2->save('same_key', 'value_from_group2', 600);

        $this->assertEquals('value_from_group1', $adapter1->fetch('same_key'));
        $this->assertEquals('value_from_group2', $adapter2->fetch('same_key'));
    }
}

<?php

namespace Timber\Tests\Cache;

use PHPUnit\Framework\Attributes\Group;
use stdClass;
use Timber\Cache\KeyGenerator;
use Timber\Cache\TimberKeyGeneratorInterface;
use Timber\Tests\TimberIntegrationTestCase;
use Timber\Timber;

class TestKeyGeneratorObject implements TimberKeyGeneratorInterface
{
    public function __construct(
        private readonly string $key
    ) {
    }

    public function _get_cache_key()
    {
        return $this->key;
    }
}

#[Group('cache')]
class KeyGeneratorTest extends TimberIntegrationTestCase
{
    public function testGenerateKeyWithString()
    {
        $kg = new KeyGenerator();
        $key = $kg->generateKey('test_string');

        $this->assertIsString($key);
        $this->assertEquals(\md5(\json_encode('test_string')), $key);
    }

    public function testGenerateKeyWithInteger()
    {
        $kg = new KeyGenerator();
        $key = $kg->generateKey(12345);

        $this->assertIsString($key);
        $this->assertEquals(\md5(\json_encode(12345)), $key);
    }

    public function testGenerateKeyWithFloat()
    {
        $kg = new KeyGenerator();
        $key = $kg->generateKey(3.14159);

        $this->assertIsString($key);
        $this->assertEquals(\md5(\json_encode(3.14159)), $key);
    }

    public function testGenerateKeyWithBoolean()
    {
        $kg = new KeyGenerator();
        $key_true = $kg->generateKey(true);
        $key_false = $kg->generateKey(false);

        $this->assertNotEquals($key_true, $key_false);
    }

    public function testGenerateKeyWithNull()
    {
        $kg = new KeyGenerator();
        $key = $kg->generateKey(null);

        $this->assertIsString($key);
        $this->assertEquals(\md5(\json_encode(null)), $key);
    }

    public function testGenerateKeyWithSimpleArray()
    {
        $kg = new KeyGenerator();
        $array = ['foo', 'bar', 'baz'];
        $key = $kg->generateKey($array);

        $this->assertIsString($key);
        $this->assertEquals(\md5(\json_encode($array)), $key);
    }

    public function testGenerateKeyWithNestedArray()
    {
        $kg = new KeyGenerator();
        $array = [
            'level1' => [
                'level2' => [
                    'value' => 'deep',
                ],
            ],
        ];
        $key = $kg->generateKey($array);

        $this->assertIsString($key);
        $this->assertEquals(\md5(\json_encode($array)), $key);
    }

    public function testGenerateKeyWithArrayContainingCacheKey()
    {
        $kg = new KeyGenerator();
        $array = [
            '_cache_key' => 'my_custom_cache_key',
            'other_data' => 'ignored',
        ];
        $key = $kg->generateKey($array);

        $this->assertEquals('my_custom_cache_key', $key);
    }

    public function testGenerateKeyWithTimberKeyGeneratorInterface()
    {
        $kg = new KeyGenerator();
        $obj = new TestKeyGeneratorObject('interface_cache_key');
        $key = $kg->generateKey($obj);

        $this->assertEquals('interface_cache_key', $key);
    }

    public function testGenerateKeyWithTimberPost()
    {
        $post_id = static::factory()->post->create([
            'post_title' => 'Test Post',
        ]);
        $post = Timber::get_post($post_id);

        $kg = new KeyGenerator();
        $key = $kg->generateKey($post);

        $this->assertStringStartsWith('Timber;Post;', $key);
    }

    public function testGenerateKeyWithTimberTerm()
    {
        $term = \wp_insert_term('Test Term', 'category');
        $timber_term = Timber::get_term($term['term_id']);

        $kg = new KeyGenerator();
        $key = $kg->generateKey($timber_term);

        $this->assertStringStartsWith('Timber;Term;', $key);
    }

    public function testGenerateKeyWithTimberUser()
    {
        $user_id = static::factory()->user->create([
            'user_login' => 'testuser',
        ]);
        $user = Timber::get_user($user_id);

        $kg = new KeyGenerator();
        $key = $kg->generateKey($user);

        $this->assertStringStartsWith('Timber;User;', $key);
    }

    public function testGenerateKeyWithStdClass()
    {
        $kg = new KeyGenerator();
        $obj = new stdClass();
        $obj->foo = 'bar';
        $key = $kg->generateKey($obj);

        $this->assertStringStartsWith('stdClass;', $key);
    }

    public function testGenerateKeyReplacesReservedCharacters()
    {
        $kg = new KeyGenerator();

        // Create an object with reserved characters in class name path
        $obj = new stdClass();
        $key = $kg->generateKey($obj);

        // Key should not contain reserved characters: {}()/\@:
        $this->assertDoesNotMatchRegularExpression('/[{}()\/\\\@:]/', $key);
    }

    public function testGenerateKeyIsDeterministic()
    {
        $kg = new KeyGenerator();
        $data = [
            'same' => 'data',
        ];

        $key1 = $kg->generateKey($data);
        $key2 = $kg->generateKey($data);

        $this->assertEquals($key1, $key2);
    }

    public function testGenerateKeyDifferentForDifferentData()
    {
        $kg = new KeyGenerator();

        $key1 = $kg->generateKey([
            'data' => 'one',
        ]);
        $key2 = $kg->generateKey([
            'data' => 'two',
        ]);

        $this->assertNotEquals($key1, $key2);
    }

    public function testGenerateKeyWithPostQuery()
    {
        static::factory()->post->create_many(3);
        $posts = Timber::get_posts([
            'post_type' => 'post',
            'posts_per_page' => 3,
        ]);

        $kg = new KeyGenerator();
        $key = $kg->generateKey($posts);

        $this->assertStringStartsWith('Timber;PostQuery;', $key);
    }

    public function testGenerateKeyWithEmptyArray()
    {
        $kg = new KeyGenerator();
        $key = $kg->generateKey([]);

        $this->assertIsString($key);
        $this->assertEquals(\md5(\json_encode([])), $key);
    }

    public function testGenerateKeyWithEmptyString()
    {
        $kg = new KeyGenerator();
        $key = $kg->generateKey('');

        $this->assertIsString($key);
        $this->assertEquals(\md5(\json_encode('')), $key);
    }
}

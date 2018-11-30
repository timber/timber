<?php

use Timber\Integrations\Command;

class TestTimberIntegrations extends Timber_UnitTestCase {

	function testIntegrationClasses() {
		$integrations = new \Timber\Integrations();
		$integrations->maybe_init_integrations();
		$this->assertEquals('Timber\Integrations', get_class($integrations));
		$this->assertEquals('Timber\Integrations\ACF', get_class($integrations->acf));
		 $this->assertEquals('Timber\Integrations\CoAuthorsPlus', get_class($integrations->coauthors_plus));
	}

	function testWPPostConvert() {
		$pid = $this->factory->post->create();
		$wp_post = get_post( $pid );
		$post = new Timber\Post();
		$timber_post = $post->convert( $wp_post, 'Timber\Post' );
		$this->assertTrue( $timber_post instanceof Timber\Post );
	}

	function testWPCLIClearCacheTimber(){
		$str = Timber::compile('assets/single.twig', array('rand' => 4004), 600);
		$success = Command::clear_cache('timber');
		$this->assertGreaterThan(0, $success);
	}

	function testWPCLIClearCacheTwig(){
		$cache_dir = __DIR__.'/../cache/twig';
    	if (is_dir($cache_dir)){
    		Timber\Loader::rrmdir($cache_dir);
    	}
    	$this->assertFileNotExists($cache_dir);
    	Timber::$cache = true;
    	$pid = $this->factory->post->create();
    	$post = new Timber\Post($pid);
    	Timber::compile('assets/single-post.twig', array('post' => $post));
    	sleep(1);
    	$this->assertFileExists($cache_dir);
    	$success = Command::clear_cache('twig');
		$this->assertTrue($success);
    	Timber::$cache = false;
	}

	function testWPCLIClearCacheAll(){
		$cache_dir = __DIR__.'/../cache/twig';
    	if (is_dir($cache_dir)){
    		Timber\Loader::rrmdir($cache_dir);
    	}
    	$this->assertFileNotExists($cache_dir);
    	Timber::$cache = true;
    	$pid = $this->factory->post->create();
    	$post = new Timber\Post($pid);
    	Timber::compile('assets/single-post.twig', array('post' => $post));
    	sleep(1);
    	$this->assertFileExists($cache_dir);
    	Timber::compile('assets/single.twig', array('data' => 'foobar'), 600);
    	$success = Command::clear_cache('all');
		$this->assertTrue($success);
    	Timber::$cache = false;
	}

	function testWPCLIClearCacheAllArray(){
		$cache_dir = __DIR__.'/../cache/twig';
    	if (is_dir($cache_dir)){
    		Timber\Loader::rrmdir($cache_dir);
    	}
    	$this->assertFileNotExists($cache_dir);
    	Timber::$cache = true;
    	$pid = $this->factory->post->create();
    	$post = new Timber\Post($pid);
    	Timber::compile('assets/single-post.twig', array('post' => $post));
    	sleep(1);
    	$this->assertFileExists($cache_dir);
    	Timber::compile('assets/single.twig', array('data' => 'foobar'), 600);
    	$success = Command::clear_cache(array('all'));
		$this->assertTrue($success);
    	Timber::$cache = false;

    	$success = Command::clear_cache('bunk');
    	$this->assertNull($success);
	}


}

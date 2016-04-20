<?php

use Timber\Integrations\ACF;
use Timber\Integrations\Command;

class TestTimberIntegrations extends Timber_UnitTestCase {

	function testACFGetFieldPost() {
		$pid = $this->factory->post->create();
		update_field( 'subhead', 'foobar', $pid );
		$str = '{{post.get_field("subhead")}}';
		$post = new TimberPost( $pid );
		$str = Timber::compile_string( $str, array( 'post' => $post ) );
		$this->assertEquals( 'foobar', $str );
	}

	function testACFGetFieldTermCategory() {
		update_field( 'color', 'blue', 'category_1' );
		$cat = new TimberTerm( 1 );
		$this->assertEquals( 'blue', $cat->color );
		$str = '{{term.color}}';
		$this->assertEquals( 'blue', Timber::compile_string( $str, array( 'term' => $cat ) ) );
	}

	function testACFGetFieldTermTag() {
		$tid = $this->factory->term->create();
		update_field( 'color', 'green', 'post_tag_'.$tid );
		$term = new TimberTerm( $tid );
		$str = '{{term.color}}';
		$this->assertEquals( 'green', Timber::compile_string( $str, array( 'term' => $term ) ) );
	}

	function testACFInit() {
		$acf = new ACF();
		$this->assertInstanceOf( 'Timber\Integrations\ACF', $acf );
	}

	function testWPCLIClearCacheTimber(){
		$str = Timber::compile('assets/single.twig', array('rand' => 4004), 600);
		$success = Command::clear_cache('timber');
		$this->assertTrue($success);
	}

	function testWPCLIClearCacheTwig(){
		$cache_dir = __DIR__.'/../cache/twig';
    	if (is_dir($cache_dir)){
    		TimberLoader::rrmdir($cache_dir);
    	}
    	$this->assertFileNotExists($cache_dir);
    	Timber::$cache = true;
    	$pid = $this->factory->post->create();
    	$post = new TimberPost($pid);
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
    		TimberLoader::rrmdir($cache_dir);
    	}
    	$this->assertFileNotExists($cache_dir);
    	Timber::$cache = true;
    	$pid = $this->factory->post->create();
    	$post = new TimberPost($pid);
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
    		TimberLoader::rrmdir($cache_dir);
    	}
    	$this->assertFileNotExists($cache_dir);
    	Timber::$cache = true;
    	$pid = $this->factory->post->create();
    	$post = new TimberPost($pid);
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

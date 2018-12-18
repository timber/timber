<?php

use Timber\Integrations\ACF;
use Timber\Integrations\Command;

class TestTimberIntegrations extends Timber_UnitTestCase {

	function testIntegrationClasses() {
		$integrations = new \Timber\Integrations();
		$integrations->maybe_init_integrations();
		$this->assertEquals('Timber\Integrations', get_class($integrations));
		$this->assertEquals('Timber\Integrations\ACF', get_class($integrations->acf));
		 $this->assertEquals('Timber\Integrations\CoAuthorsPlus', get_class($integrations->coauthors_plus));
	}

	function testACFGetFieldPost() {
		$pid = $this->factory->post->create();
		update_field( 'subhead', 'foobar', $pid );
		$str = '{{post.get_field("subhead")}}';
		$post = new TimberPost( $pid );
		$str = Timber::compile_string( $str, array( 'post' => $post ) );
		$this->assertEquals( 'foobar', $str );
	}

	function testWPPostConvert() {
		$pid = $this->factory->post->create();
		$wp_post = get_post( $pid );
		$post = new TimberPost();
		$timber_post = $post->convert( $wp_post );
		$this->assertTrue( $timber_post instanceof \Timber\Post );
	}

	function testACFHasFieldPostFalse() {
		$pid = $this->factory->post->create();
		$str = '{% if post.has_field("heythisdoesntexist") %}FAILED{% else %}WORKS{% endif %}';
		$post = new TimberPost( $pid );
		$str = Timber::compile_string( $str, array( 'post' => $post ) );
		$this->assertEquals('WORKS', $str);
	}

	function testACFHasFieldPostTrue() {
		$pid = $this->factory->post->create();
		update_post_meta($pid, 'best_radiohead_album', 'in_rainbows');
		$str = '{% if post.has_field("best_radiohead_album") %}In Rainbows{% else %}OK Computer{% endif %}';
		$post = new TimberPost( $pid );
		$str = Timber::compile_string( $str, array( 'post' => $post ) );
		$this->assertEquals('In Rainbows', $str);
	}

	function testACFGetFieldTermCategory() {
		update_field( 'color', 'blue', 'category_1' );
		$cat = new TimberTerm( 1 );
		$this->assertEquals( 'blue', $cat->color );
		$str = '{{term.color}}';
		$this->assertEquals( 'blue', Timber::compile_string( $str, array( 'term' => $cat ) ) );
	}

	function testACFCustomFieldTermTag() {
		$tid = $this->factory->term->create();
		update_field( 'color', 'green', 'post_tag_'.$tid );
		$term = new TimberTerm( $tid );
		$str = '{{term.color}}';
		$this->assertEquals( 'green', Timber::compile_string( $str, array( 'term' => $term ) ) );
	}

	function testACFGetFieldTermTag() {
		$tid = $this->factory->term->create();
		update_field( 'color', 'blue', 'post_tag_'.$tid );
		$term = new TimberTerm( $tid );
		$str = '{{term.get_field("color")}}';
		$this->assertEquals( 'blue', Timber::compile_string( $str, array( 'term' => $term ) ) );
	}


	function testACFFieldObject() {
		$key = 'field_5ba2c660ed26d';
		$fp_id = $this->factory->post->create(array('post_content' => 'a:10:{s:4:"type";s:4:"text";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";s:7:"prepend";s:0:"";s:6:"append";s:0:"";s:9:"maxlength";s:0:"";}', 'post_title' => 'Thinger', 'post_name' => $key, 'post_type' => 'acf-field'));
		$pid      = $this->factory->post->create();
		update_field( 'thinger', 'foo', $pid );
		update_field( '_thinger', $key, $pid );
		$post     = new TimberPost($pid);
		$template = '{{ post.meta("thinger") }} / {{ post.field_object("thinger").key }}';
		$str      = Timber::compile_string($template, array( 'post' => $post ));
		$this->assertEquals('foo / '.$key, $str);
	}

	function testACFInit() {
		$acf = new ACF();
		$this->assertInstanceOf( 'Timber\Integrations\ACF', $acf );
	}

	function testWPCLIClearCacheTimber(){
		$str = Timber::compile('assets/single.twig', array('rand' => 4004), 600);
		$success = Command::clear_cache('timber');
		$this->assertGreaterThan(0, $success);
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

<?php

class TimberCoreTester extends TimberPost {
	function foo() {
		return 'bar';
	}
}

class IHavePrivates {

	public $foo = 'foo';
	private $bar = 'bar';
}

class TestTimberCore extends Timber_UnitTestCase {

	function testCoreImport() {
		$post_id = $this->factory->post->create();
		$tc = new TimberCoreTester($post_id);
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

	function testCoreImportWithPrivateProperties() {
		$post_id = $this->factory->post->create();
		$tc = new TimberPost($post_id);
		$object = new IHavePrivates();
		$tc->import($object);
		$this->assertEquals($tc->foo, 'foo');
		$this->assertEquals($tc->bar, false);
	}

}
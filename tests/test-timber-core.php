<?php

class TimberCoreTester extends TimberPost {
	function foo() {
		return 'bar';
	}
}

class TestTimberCore extends WP_UnitTestCase {

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

}

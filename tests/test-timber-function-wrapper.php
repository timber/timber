<?php

class TestTimberFunctionWrapper extends Timber_UnitTestCase {

	function testToStringWithException() {
		ob_start();
		$wrapper = new TimberFunctionWrapper('TestTimberFunctionWrapper::isNum', array('hi'));
		echo $wrapper;
		$content = trim(ob_get_contents());
		ob_end_clean();
		$this->assertEquals('Caught exception: Argument must be of type integer', $content);
	}

	function testToStringWithoutException() {
		ob_start();
		$wrapper = new TimberFunctionWrapper('TestTimberFunctionWrapper::isNum', array(4));
		echo $wrapper;
		$content = trim(ob_get_contents());
		ob_end_clean();
		$this->assertEquals(1, $content);
	}

	function testToStringWithClassObject() {
		ob_start();
		$wrapper = new TimberFunctionWrapper(array($this, 'isNum'), array(4));
		echo $wrapper;
		$content = trim(ob_get_contents());
		ob_end_clean();
		$this->assertEquals(1, $content);
	}

	function testToStringWithClassString() {
		ob_start();
		$wrapper = new TimberFunctionWrapper(array(get_class($this), 'isNum'), array(4));
		echo $wrapper;
		$content = trim(ob_get_contents());
		ob_end_clean();
		$this->assertEquals(1, $content);
	}

	function testWPHead() {
		$context = Timber::context();
		$str = Timber::compile_string('{{ wp_head }}', $context);
		$this->assertRegexp('/<title>Test Blog/', trim($str));
	}

	function testFunctionInTemplate() {
		$context = Timber::context();
		$str = Timber::compile_string("{{ function('my_boo') }}", $context);
		$this->assertEquals('bar!', trim($str));
	}

	function testSoloFunctionUsingWrapper() {
		new TimberFunctionWrapper('my_boo');
		$str = Timber::compile_string("{{ my_boo() }}");
		$this->assertEquals('bar!', trim($str));
	}

	function testNakedSoloFunction() {
		add_filter('timber/twig/functions', function( $twig ) {
			$twig->addFunction(new Timber\Twig_Function('your_boo', array($this, 'your_boo')) );
			return $twig;
		});
		$context = Timber::context();
		$str = Timber::compile_string("{{ your_boo() }}", $context);
		$this->assertEquals('yourboo', trim($str));
	}

	/* Sample function to test exception handling */

	static function isNum($num) {
		if(!is_int($num)) {
			throw new Exception("Argument must be of type integer");
		} else {
			return true;
		}
	}

	function your_boo() {
		return 'yourboo';
	}

}



function my_boo() {
	return 'bar!';
}

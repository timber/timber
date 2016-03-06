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

	/* Sample function to test exception handling */

	static function isNum($num) {
		if(!is_int($num)) {
			throw new Exception("Argument must be of type integer");
		} else {
			return true;
		}
	}

}

<?php

	class TestTimberTextHelper extends Timber_UnitTestCase {

		protected $gettysburg = 'Four score and seven years ago our fathers brought forth on this continent a new nation, conceived in liberty, and dedicated to the proposition that all men are created equal.';

		function testStartsWith() {
			$maybe_starts_with = Timber\TextHelper::starts_with($this->gettysburg, 'Four score');
			$this->assertTrue($maybe_starts_with);

		}

		function testDontStartWith() {
			$maybe_starts_with = Timber\TextHelper::starts_with($this->gettysburg, "Can't get enough of that SugarCrisp");
			$this->assertFalse($maybe_starts_with);
		}

	}

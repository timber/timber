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

		function testTruncateEastAsian() {
			$chars = "寒くなってきましたね。14日には北海道でも記録的に遅い初雪が降ったそ";
			$str = '{{ "'.$chars.'"|truncate( 5, true ) }}';
			$result = Timber::compile_string($str);
			$this->assertEquals(wp_trim_words($chars, 5), $result);
		}

		function testTruncaseEnglish() {
			$chars = $this->gettysburg;
			$str = '{{ "'.$chars.'"|truncate( 5, true ) }}';
			$result = Timber::compile_string($str);
			$this->assertEquals(wp_trim_words($this->gettysburg, 5), $result);
		}

	}

<?php

class WP_Test_Texturize extends WP_UnitTestCase {
	function test_dont_texturize_dashes_in_code() {
		$this->assertEquals( '<code>---</code>', wptexturize( '<code>---</code>' ) );
	}
	
	function test_dont_texturize_dashes_in_pre() {
		$this->assertEquals( '<pre>---</pre>', wptexturize( '<pre>---</pre>' ) );
	}
	
	function test_dont_texturize_code_inside_a_pre() {
		$double_nest = '<pre>"baba"<code>"baba"<pre></pre></code>"baba"</pre>';
		$this->assertEquals( $double_nest, wptexturize( $double_nest ) );
	}
	
	function test_dont_texturize_pre_inside_a_code() {
		$double_nest = '<code>"baba"<pre>"baba"<code></code></pre>"baba"</code>';
		$this->assertEquals( $double_nest, wptexturize( $double_nest ) );
	}
	
}
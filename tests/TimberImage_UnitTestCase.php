<?php

	class TimberImage_UnitTestCase extends Timber_UnitTestCase {

		var $_files;

		protected function addFile( $file ) {
			$this->_files[] = $file;
		}

		function setUp(){
			$this->_files = array();
		}

		function tearDown() {
			parent::tearDown();
			if (isset($this->_files) && is_array($this->_files)) {
				foreach($this->_files as $file) {
					if (file_exists($file)) {
						unlink($file);
					}
				}
				$this->_files = array();
			}
		}

	}

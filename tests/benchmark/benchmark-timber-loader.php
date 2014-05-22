<?php

	class TimberBenchmark {

		static function testLoader(){
			global $TimberLoader;
			$loader = $TimberLoader->get_loader();
		}

		public static function run($function){
			global $TimberLoader;
			$TimberLoader = new TimberLoader();
			$start_time = microtime(true);
			for ($i = 0; $i<5000; $i++){
				self::$function();
			}
			$end_time = microtime(true);
			echo $end_time - $start_time;
		}

	}



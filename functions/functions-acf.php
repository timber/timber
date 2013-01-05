<?php

	if (function_exists('register_field')){
		register_field('Radio_image', dirname(__File__) . '/acf/radio_image.php');
		register_field('Post_Object_Picker', dirname(__File__) . '/acf/post_object_picker.php');
	}
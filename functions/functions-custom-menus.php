<?php
/**
 * Activate Add-ons
 * Here you can enter your activation codes to unlock Add-ons to use in your theme. 
 * Since all activation codes are multi-site licenses, you are allowed to include your key in premium themes. 
 * Use the commented out code to update the database with your activation code. 
 * You may place this code inside an IF statement that only runs on theme activation.
 */ 
 
if(!get_option('acf_repeater_ac')) update_option('acf_repeater_ac', "QJF7-L4IX-UCNP-RF2W");
if(!get_option('acf_options_page_ac')) update_option('acf_options_page_ac', "OPN8-FA4J-Y2LW-81LS");
if(!get_option('acf_flexible_content_ac')) update_option('acf_flexible_content_ac', "FC9O-H6VN-E4CL-LT33");
// if(!get_option('acf_gallery_ac')) update_option('acf_gallery_ac', "xxxx-xxxx-xxxx-xxxx");


/**
 * Register field groups
 * The register_field_group function accepts 1 array which holds the relevant data to register a field group
 * You may edit the array as you see fit. However, this may result in errors if the array is not compatible with ACF
 * This code must run every time the functions.php file is read
 */
if(function_exists("register_field_group"))
{
	register_field_group(array (
		'id' => '50a3cebbb71f8',
		'title' => 'Site Look & Feel',
		'fields' => 
		array (
			0 => 
			array (
				'key' => 'field_50856a5d6bef4',
				'label' => 'Footer Text',
				'name' => 'footer_text',
				'type' => 'textarea',
				'order_no' => '0',
				'instructions' => '',
				'required' => '0',
				'conditional_logic' => 
				array (
					'status' => '0',
					'rules' => 
					array (
						0 => 
						array (
							'field' => '',
							'operator' => '==',
							'value' => '',
						),
					),
					'allorany' => 'all',
				),
				'default_value' => '',
				'formatting' => 'html',
			),
			1 => 
			array (
				'key' => 'field_50a27bb867fa7',
				'label' => 'Homepage Promos',
				'name' => 'homepage_promos',
				'type' => 'relationship',
				'order_no' => '1',
				'instructions' => '',
				'required' => '0',
				'conditional_logic' => 
				array (
					'status' => '0',
					'rules' => 
					array (
						0 => 
						array (
							'field' => 'null',
							'operator' => '==',
						),
					),
					'allorany' => 'all',
				),
				'post_type' => 
				array (
					0 => 'promos',
				),
				'taxonomy' => 
				array (
					0 => 'all',
				),
				'max' => '',
			),
		),
		'location' => 
		array (
			'rules' => 
			array (
				0 => 
				array (
					'param' => 'options_page',
					'operator' => '==',
					'value' => 'Options',
					'order_no' => '0',
				),
			),
			'allorany' => 'all',
		),
		'options' => 
		array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => 
			array (
			),
		),
		'menu_order' => 0,
	));
}

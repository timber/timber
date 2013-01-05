<?php

class radio_image extends acf_Field
{
	
	/*--------------------------------------------------------------------------------------
	*
	*	Constructor
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	*	@updated 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function __construct($parent)
	{
    	parent::__construct($parent);
    	
    	$this->name = 'radio_image';
		$this->title = __('Radio Button with Image','acf');
		
   	}
   	
   		
	/*--------------------------------------------------------------------------------------
	*
	*	create_field
	*
	*	@author Elliot Condon
	*	@since 2.0.5
	*	@updated 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function parse_choice($choice){
		$choices = explode(':', $choice);
		$image = end($choices);
		$return = new stdClass();
		$return->key = $choices[0];
		if (strstr($image, '/')){
			$return->image = $image;
		} else {
			$return->display = $image;
		}
		if (count($choices) == 1){
			$return->display = $choices[0];
		} else {
			$return->display = $choices[1];
		}
		return $return;
	}

	function create_field($field){
		// defaults
		$field['layout'] = isset($field['layout']) ? $field['layout'] : 'vertical';
		$field['choices'] = isset($field['choices']) ? $field['choices'] : array();
		// no choices
		if(empty($field['choices'])){
			echo '<p>' . __("No choices to choose from",'acf') . '</p>';
			return false;
		}
				
		echo '<ul class="radio_list ' . $field['class'] . ' ' . $field['layout'] . '">';
		
		$i = 0;

		if (!is_array($field['choices'])){
			$field['choices'] = explode("\n", $field['choices']);
		}
		foreach($field['choices'] as $choice){
			$selected = '';
			$selected_class = '';
			$choice = self::parse_choice($choice);
			$choice->key = htmlspecialchars($choice->key);
			if (trim($choice->key) == trim(htmlspecialchars($field['value']))){
				$selected = ' checked="checked" data-checked="checked" ';
				$selected_class = ' selected ';
			} 
			echo '<li class="acf-ups-radio-image stuffbox widget'.$selected_class.'"><label>';
			if ($choice->image){
				echo '<img src="'.$choice->image.'" />';
			} else {
				echo '<h3>'.$choice->display.'</h3>';
			}
			echo '<input id="' . $field['id'] . '-' . $choice->key . '" type="radio" name="' . $field['name'] . '" value="' . $choice->key . '" ' . $selected . ' />';
			if ($choice->image){
				echo $choice->display;
			}
			echo '</label></li>';
		}		
		echo '</ul>';

	}


	/*--------------------------------------------------------------------------------------
	*
	*	create_options
	*
	*	@author Elliot Condon
	*	@since 2.0.6
	*	@updated 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function create_options($key, $field){	
		// defaults
		$field['layout'] = isset($field['layout']) ? $field['layout'] : 'vertical';
		$field['default_value'] = isset($field['default_value']) ? $field['default_value'] : '';
		if (!is_array($field['choices'])){
			$field['choices'] = explode("\n", $field['choices']);
		}
		// implode checkboxes so they work in a textarea
		if(isset($field['choices']) && is_array($field['choices'])){		
			foreach($field['choices'] as $choice_key => $choice_val){
				$field['choices'][$choice_key] = $choice_val;
			}
			$field['choices'] = implode("\n", $field['choices']);
		} else {
			$field['choices'] = "";
		}
		
		?>


		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label for=""><?php _e("Choices",'acf'); ?></label>
				<p class="description"><?php _e("Enter your choices one per line",'acf'); ?><br />
				<br />
				<?php _e("Red",'acf'); ?><br />
				<?php _e("Blue",'acf'); ?><br />
				<br />
				<?php _e("red : Red",'acf'); ?><br />
				<?php _e("blue : Blue",'acf'); ?><br />
				</p>
			</td>
			<td>
				<?php 
				$this->parent->create_field(array(
					'type'	=>	'textarea',
					'class' => 	'textarea field_option-choices',
					'name'	=>	'fields['.$key.'][choices]',
					'value'	=>	$field['choices'],
				));
				?>
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Default Value",'acf'); ?></label>
			</td>
			<td>
				<?php 
				$this->parent->create_field(array(
					'type'	=>	'text',
					'name'	=>	'fields['.$key.'][default_value]',
					'value'	=>	$field['default_value'],
				));
				?>
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label for=""><?php _e("Layout",'acf'); ?></label>
			</td>
			<td>
				<?php 
				$this->parent->create_field(array(
					'type'	=>	'radio',
					'name'	=>	'fields['.$key.'][layout]',
					'value'	=>	$field['layout'],
					'layout' => 'horizontal', 
					'choices' => array(
						'vertical' => __("Vertical",'acf'), 
						'horizontal' => __("Horizontal",'acf')
					)
				));
				?>
			</td>
		</tr>
		<?php
	}	
}

?>
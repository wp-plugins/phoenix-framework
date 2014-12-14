<?php
/**
 * class-phenix-framework-checkbox-field-type
 *
 * a
 *
 * @class 		Phoenix_Framework_Text_Field_Type
 * @version		1.0
 * @package		BuddyPress (www\bp\)
 * @category	Class
 * @author 		Vahidd
 */
 
defined('ABSPATH') or die; // Prevents direct access

if( !class_exists( 'Phoenix_Framework_Checkbox_Field_Type' ) ) {
	class Phoenix_Framework_Checkbox_Field_Type extends Phoenix_Framework_Form_Field_Type_Base{

		var $checkbox_label = '';

		function checkbox_label( $label ){
			$this->checkbox_label = $label;
			return $this;
		}


		function display(){
			$output = '';
			$checkbox = Phoenix_Framework_HTML_Helper::htmlCheckbox( $this->settings['inputName'], $this->get_value(), array( 'class' => 'form-control ' . $this->settings['inputClasses'] ) );
			if( !empty( $this->checkbox_label ) )
				$output .= Phoenix_Framework_HTML_Helper::htmlTag( 'label', $checkbox . ' ' . $this->checkbox_label );
			else
				$output .= $checkbox;
			return $output;
		}

	}
}

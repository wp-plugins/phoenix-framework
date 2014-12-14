<?php
/**
 * class-phenix-framework-textarea-field-type
 *
 * a
 *
 * @class 		Phoenix_Framework_Textarea_Field_Type
 * @version		1.0
 * @package		BuddyPress (www\bp\)
 * @category	Class
 * @author 		Vahidd
 */
 
defined('ABSPATH') or die; // Prevents direct access

if( !class_exists( 'Phoenix_Framework_Textarea_Field_Type' ) ) {
	class Phoenix_Framework_Textarea_Field_Type extends Phoenix_Framework_Form_Field_Type_Base{


		function display(){
			Phoenix_Framework::load('html');
			$output = '';
			$output .= htmlTextarea( $this->settings['inputName'], $this->get_value(), array( 'class' => 'form-control ' . $this->settings['inputClasses'] ) );
			return $output;
		}

	}
}

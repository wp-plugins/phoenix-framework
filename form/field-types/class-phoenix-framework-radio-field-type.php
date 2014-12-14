<?php
/**
 * class-phenix-framework-radio-field-type
 *
 * a
 *
 * @class          Phoenix_Framework_Radio_Field_Type
 * @version        1.0
 * @package        BuddyPress (www\bp\)
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

if ( ! class_exists( 'Phoenix_Framework_Radio_Field_Type' ) ) {
	class Phoenix_Framework_Radio_Field_Type extends Phoenix_Framework_Form_Field_Type_Base {

		var $options = array();

		function __construct( $params ){
			parent::__construct( $params );
			$this->options = $params['options'];
		}

		function display() {
			Phoenix_Framework::load('html');
			$output = '';
			$output .= htmlRadioList( $this->settings['inputName'], (string) $this->get_value(), $this->options, array( 'class' => 'form-control ' . $this->settings['inputClasses'] ) );

			return $output;
		}

	}
}

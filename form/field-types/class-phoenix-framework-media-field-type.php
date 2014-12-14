<?php
	/**
	 * @class          Phoenix_Framework_Media_Field_Type
	 * @version        1.0
	 * @package        Phoenix Framework
	 * @category       Class
	 * @author         Vahidd
	 */

	defined( 'ABSPATH' ) or die; // Prevents direct access

	if ( ! class_exists( 'Phoenix_Framework_Media_Field_Type' ) ) {
		class Phoenix_Framework_Media_Field_Type extends Phoenix_Framework_Form_Field_Type_Base {


			function display() {
				$output = '';


				$output .= '<input type="text" name="' . $this->settings[ 'inputName' ] . '" value="' . esc_attr( $this->get_value() ) . '">';
				$output .= '<button class="button" onclick="phoenix.form.openMediaFrame(jQuery(this).prev()[0]);return false;">zx</button>';


				return $output;
			}

		}
	}

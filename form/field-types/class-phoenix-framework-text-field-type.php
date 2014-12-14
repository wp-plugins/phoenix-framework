<?php
	/**
	 * class-phenix-framework-text-field-type
	 *
	 * a
	 *
	 * @class          Phoenix_Framework_Text_Field_Type
	 * @version        1.0
	 * @package        BuddyPress (www\bp\)
	 * @category       Class
	 * @author         Vahidd
	 */

	defined( 'ABSPATH' ) or die; // Prevents direct access

	if ( ! class_exists( 'Phoenix_Framework_Text_Field_Type' ) ) {
		class Phoenix_Framework_Text_Field_Type extends Phoenix_Framework_Form_Field_Type_Base {


			function display() {
				$output = '';
				Phoenix_Framework::load( 'html' );
				$output .= htmlTextInput(
					$this->settings[ 'inputName' ],
					$this->get_value(),
					array(
						'id'    => $this->settings[ 'inputName' ],
						'class' => 'form-control ' . $this->settings[ 'inputClasses' ]
					)
				);

				return $output;
			}

		}
	}

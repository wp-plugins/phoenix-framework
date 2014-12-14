<?php
/**
 * class-phenix-framework-text-field-type
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

if( !class_exists( 'Phoenix_Framework_WP_Editor_Field_Type' ) ) {
	class Phoenix_Framework_WP_Editor_Field_Type extends Phoenix_Framework_Form_Field_Type_Base{

		function display(){
			ob_start();
			wp_editor(
				$this->get_value(),
				$this->settings['id'],
				array(
					'textarea_name' => $this->settings['inputName']
				)
			);
			return ob_get_clean();
		}

	}
}

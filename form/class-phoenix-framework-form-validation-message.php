<?php
/**
 * class-phoenix-framework-form-validation-message
 *
 * a
 *
 * @class          Phoenix_Framework_Form_Validation_Message
 * @version        1.0
 * @package        BuddyPress (www\bp\)
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

if ( ! class_exists( 'Phoenix_Framework_Form_Validation_Message' ) ) {
	/**
	 * Class Phoenix_Framework_Form_Validation_Message
	 * @method has_required_error
	 */
	class Phoenix_Framework_Form_Validation_Message extends Phoenix_Framework_Object {

		/**
		 * @var Phoenix_Framework
		 */
		static $phoenix;
		private $msgs;

		function __construct( Phoenix_Framework_Form_Validation $error_msgs ) {
			$this->msgs = $error_msgs->_error_msgs;
		}
		function __call( $method, $params ){
			if( !preg_match( '/^has_([a-zA-Z0-9_]+)_error$/', $method, $matches ) ) {
				parent::__call( $method, $params );
			}
			$field = !empty( $params[0] ) && is_string( $params[0] ) ? $params[0] : false;
			$msgs =& $this->msgs;
			foreach( $msgs as $msg_field => $msg_field_msgs ) {
				if( array_key_exists( $matches[1], $msg_field_msgs ) && ( $field ? $field == $msg_field : true ) )
					return true;
			}
			return false;
		}

		function has( $field = null ) {
			return $field === null ? ! empty( $this->msgs ) : ! empty( $this->msgs[ $field ] );
		}

		function format( $msg, $format = null ) {
			if ( $format === null ) {
				$format = '<p>:message</p>';
			}

			return str_replace(
				':message',
				$msg,
				$format
			);
		}

		function first( $field = null, $format = null, $default = '' ) {

			if ( ! $this->has() ) {
				return $default;
			}

			if ( $field === null ) {
				return $this->format( $this::$phoenix->array_helper->get_first( $this::$phoenix->array_helper->get_first( $this->msgs ) ), $format );
			}

			if ( $this->has( $field ) ) {
				return $this->format( reset( $this->msgs[ $field ] ) );
			}

			return $default;
		}


		function all( $format = '<p>:message</p>' ){
			if( !$this->has() )
				return '';
			$output = '';
			foreach( $this->msgs as $field ){
				foreach( $field as $msgs )
					$output .= str_replace( ':message', $msgs, $format );
			}
			return $output;
		}

		function all_array(){
			return $this->msgs;
		}

	}
}

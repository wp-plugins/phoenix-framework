<?php
	/**
	 * class-phoenix-framework-field-type-base
	 *
	 * a
	 *
	 * @class          Phoenix_Framework_Field_Type_Base
	 * @version        1.0
	 * @package        BuddyPress (www\bp\)
	 * @category       Class
	 * @author         Vahidd
	 */

	defined( 'ABSPATH' ) or die; // Prevents direct access

	abstract class Phoenix_Framework_Form_Field_Type_Base {

		public
			$settings = array();


		function __construct( $params ) {
			$this->settings = array_merge(
				array(
					'inputClasses' => '',
					'label'        => '',
					'desc'         => '',
					'std_value'    => ''
				),
				$this->settings,
				$params
			);
		}

		function label( $label ) {
			$this->settings[ 'label' ] = $label;

			return $this;
		}

		function desc( $desc ) {
			$this->settings[ 'desc' ] = $desc;

			return $this;
		}

		function value( $val ) {
			$this->settings[ 'value' ] = $val;

			return $this;
		}

		function inputClasses( $classes ) {

			$classes = is_array( $classes ) ? $classes : explode( ' ', $classes );

			$this->settings[ 'inputClasses' ] .= implode( ' ', $classes ) . ' ';

			return $this;
		}

		function get_value() {
			if ( isset( $this->settings[ 'valueHandler' ] ) && is_callable( $this->settings[ 'valueHandler' ] ) ) {
				$call = call_user_func( $this->settings[ 'valueHandler' ], $this->settings[ 'id' ] );

				return $call === null ? $this->settings[ 'std_value' ] : $call;
			}

			return isset( $this->settings[ 'value' ] ) && $this->settings[ 'value' ] !== null ? $this->settings[ 'value' ] : $this->settings[ 'std_value' ];
		}

		function std_val( $val ) {
			$this->settings[ 'std_value' ] = $val;

			return $this;
		}


		function wrap( $field ) {
			$type = get_class( $this );
			$type = ltrim( rtrim( $type, '_Field_Type' ), 'Phoenix_Framework_' );
			$type = strtolower( $type );

			return '<div class="phoenix-form-group ' . $type . '"> ' . $field . '  </div>';
		}

		function render() {
			$field = $this->display();

			if ( $this->settings[ 'callback' ] !== null ) {
				return call_user_func( $this->settings[ 'callback' ], $this );
			} else if ( isset( $this->settings[ 'formBuilderSettings' ][ 'field_callback' ] ) && is_callable( $this->settings[ 'formBuilderSettings' ][ 'field_callback' ] ) ) {
				return call_user_func( $this->settings[ 'formBuilderSettings' ][ 'field_callback' ], $this );
			} else {
				$output = '';
				if ( ! empty( $this->settings[ 'label' ] ) ) {
					$output .= sprintf( '<label>%s</label>', $this->settings[ 'label' ] );
				}
				$output .= $field;

				return $this->wrap( $output );
			}
		}

		function __toString() {
			$return = $this->render();

			return (string) $return;
		}

		/**
		 * @return string
		 */
		abstract function display();

	}

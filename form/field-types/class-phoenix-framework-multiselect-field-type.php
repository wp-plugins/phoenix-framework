<?php
	/**
	 * @class          Phoenix_Framework_Multiselect_Field_Type
	 * @version        1.0
	 * @package        Phoenix Framework
	 * @category       Class
	 * @author         Vahidd
	 */

	defined( 'ABSPATH' ) or die; // Prevents direct access

	class Phoenix_Framework_Multiselect_Field_Type extends Phoenix_Framework_Form_Field_Type_Base {

		var $options = array();

		function __construct( $params ) {
			parent::__construct( $params );
			$this->options                    = $params[ 'options' ];
			$this->settings[ 'multi-select' ] = true;
			$this->settings[ 'sortable' ]     = true;
		}


		protected function generateItem( $option, $value, $selection ) {
			$output    = '';
			$name      = $this->settings[ 'inputName' ] . '[' . $value . ']';


			$bgImage = ! empty( $option[ 'image' ] ) ? $option[ 'image' ] : false;
			$label   = is_string( $option ) ? $option : $option[ 'label' ];
			$checked = isset( $selection[ $value ] ) && $selection[ $value ] == 'checked';
			$styles  = array();
			if ( $bgImage ) {
				$styles[ ] = 'background-image:url(' . $bgImage . ');';
			}
			if ( ! empty( $option[ 'width' ] ) ) {
				$styles[ ] = 'width: ' . ( preg_match( '/(px)|(%)$/', $option[ 'width' ] ) ? $option[ 'width' ] : (int) $option[ 'width' ] . 'px' );
			}
			if ( ! empty( $option[ 'height' ] ) ) {
				$styles[ ] = 'height: ' . ( preg_match( '/(px)|(%)$/', $option[ 'height' ] ) ? $option[ 'height' ] : (int) $option[ 'height' ] . 'px' );
			}
			$output .= '<div class="item ' . ( $checked ? 'checked' : '' ) . '" style="' . implode( ';', $styles ) . '">';
			$output .= ! $bgImage && $label ? '<span class="label">' . $label . '</span>' : '';
			$output .= '<input type="checkbox" name="' . $name . '" value="' . ( $checked ? 'checked' : 'unchecked' ) . '" checked/>';
			$output .= '</div>';

			return $output;
		}

		function display() {
			$output  = '';
			$options = $this->options;
			$containerClasses = array( 'items' );
			if ( $this->settings[ 'sortable' ] ) {
				$containerClasses[ ] = 'sortable';
			}
			if ( $this->settings[ 'multi-select' ] ) {
				$containerClasses[ ] = 'multi-select';
			}
			$output .= '<div class="' . implode( ' ', $containerClasses ) . '">';
			$selection = $this->filterValue( $this->get_value() );
			foreach ( $selection as $itemId => $itemValue ) {
				if ( ! isset( $options[ $itemId ] ) ) {
					continue;
				}
				$output .= $this->generateItem( $options[ $itemId ], $itemId, $selection );
				unset( $options[ $itemId ] );
			}
			foreach ( $options as $id => $option ) {
				if ( ! is_string( $option ) && empty( $option[ 'label' ] ) ) {
					continue;
				}
				$output .= $this->generateItem( $option, $id, $selection );
			}
			$output .= '</div>';

			return $output;
		}


		/**
		 * @return $this
		 */
		public function multiChoice() {
			$this->settings[ 'multi-select' ] = true;

			return $this;
		}

		/**
		 * @return $this
		 */
		public function singleChoice() {
			$this->settings[ 'multi-select' ] = false;

			return $this;
		}

		/**
		 * @param $sortable
		 *
		 * @return $this
		 */
		public function sortable( $sortable ) {
			$this->settings[ 'sortable' ] = (bool) $sortable;

			return $this;
		}

		protected function filterValue( $val ) {

			$output = array();

			if( empty( $val ) )
				return $output;

			if( is_array( $val ) ) {
				Phoenix_Framework::load( 'array' );
				$isAssociative = arrayIsAssociative( $val );
				foreach( $val as $key => $value ) {
					if( $isAssociative ) {
						$output[$key] = $value;
					}
					else {
						$output[$value] = 'checked';
					}
				}
			}
			else {
				if( is_string( $val ) || is_numeric( $val ) )
					$output[$val] = 'checked';
			}

			return $output;
		}
	}

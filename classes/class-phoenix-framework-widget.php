<?php
	/**
	 * Phoenix Framework Widget
	 *
	 * @class          Phoenix_Framework_Widget
	 * @version        1.0
	 * @package        Phoenix Framework
	 * @category       Class
	 * @author         Vahidd
	 */

	defined( 'ABSPATH' ) or die; // Prevents direct access

	class Phoenix_Framework_Widget extends WP_Widget {


		/**
		 * @var Phoenix_Framework_Form_Builder
		 */
		public $formBuilder;

		public
			$settings = array();

		public function __construct() {
			$this->settings = $this->settings();
			parent::__construct(
				$this->settings[ 'id' ],
				$this->settings[ 'name' ],
				array(
					'description' => ! empty( $this->settings[ 'description' ] ) ? $this->settings[ 'description' ] : '',
				)
			);
		}

		public function fieldCallback( Phoenix_Framework_Form_Field_Type_Base $field ) {
			return '<p>
				<label for="' . $field->settings[ 'inputName' ] . '">' . $field->settings[ 'label' ] . '</label>:
				' . $field->display() . '
				<small></small>
			</p>';
		}

		public function generateInputName( $id ) {
			return sprintf( 'phoenix_widget_%s[%s]', $this->settings[ 'id' ], $id );
		}

		public function widget( $args, $instance ) {
			$title = apply_filters( 'widget_title', $instance[ 'title' ] );
			echo $args[ 'before_widget' ];
			if ( ! empty( $title ) ) {
				echo $args[ 'before_title' ] . $title . $args[ 'after_title' ];
			}
			echo $this->output( $args, $instance );
			echo $args[ 'after_widget' ];
		}

		protected function initFormBuilder( $values ) {
			$this->formBuilder = new Phoenix_Framework_Form_Builder( array(
				'field_callback'       => array( $this, 'fieldCallback' ),
				'field_name_generator' => array( $this, 'generateInputName' ),
				'values'               => $values

			) );
		}

		public function form( $instance ) {
			$this->settings[ 'valuesInstance' ] = $instance;
			$this->initFormBuilder( $instance );
			$this->admin( $this->formBuilder );
		}

		public function update( $new_instance, $old_instance ) {
			$instance = array(
				'title' => ''
			);

			if ( empty( $_POST[ 'phoenix_widget_' . $this->settings[ 'id' ] ] ) || ! is_array( $_POST[ 'phoenix_widget_' . $this->settings[ 'id' ] ] ) ) {
				return $instance;
			}

			ob_start();
			$this->initFormBuilder( array() );
			$this->admin( $this->formBuilder );
			ob_end_clean();

			foreach ( $this->formBuilder->getAllFields() as $field ) {
				/**
				 * @var $field Phoenix_Framework_Form_Field_Type_Base
				 */
				if ( isset( $_POST[ 'phoenix_widget_' . $this->settings[ 'id' ] ][ $field->settings[ 'id' ] ] ) ) {
					$instance[ $field->settings[ 'id' ] ] = $_POST[ 'phoenix_widget_' . $this->settings[ 'id' ] ][ $field->settings[ 'id' ] ];
				}
			}


			return $instance;
		}


		public function settings() {
			return array();
		}

		public function output( $args, $instance ) {
			return '';
		}

		public function admin( Phoenix_Framework_Form_Builder $form ) {
			echo $form->add_text( 'title' )->label( __( 'Title', 'phoenix-framework' ) );
		}


	}


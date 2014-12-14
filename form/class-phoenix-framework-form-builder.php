<?php
	/**
	 * class-phoenix-framework-form-builder
	 *
	 * a
	 *
	 * @class          Phoenix_Framework_Form_Builder
	 * @version        1.0
	 * @package        BuddyPress (www\bp\)
	 * @category       Class
	 * @author         Vahidd
	 */

	defined( 'ABSPATH' ) or die; // Prevents direct access

	if ( ! class_exists( 'Phoenix_Framework_Form_Builder' ) ) {

		class Phoenix_Framework_Form_Builder {

			private $_fields;
			private $settings;
			private $_tabs = array();

			private $_fieldTypes = array(
				'text'        => 'Phoenix_Framework_Text_Field_Type',
				'wp_editor'   => 'Phoenix_Framework_WP_Editor_Field_Type',
				'multiselect' => 'Phoenix_Framework_Multiselect_Field_Type',
				'media'       => 'Phoenix_Framework_Media_Field_Type'
			);

			public function __construct( $config = array() ) {

				$this->settings = array_merge(
					array(
						'field_name_generator' => false,
						'values'               => array()
					),
					(array) $config
				);
			}

			public function field_name( $field_id ) {
				if ( is_callable( $this->settings[ 'field_name_generator' ] ) ) {
					return call_user_func( $this->settings[ 'field_name_generator' ], $field_id );
				}

				return $field_id;
			}

			public function set_values( $values ) {
				$this->settings[ 'values' ] = $values;
			}

			public function get_tabs_array() {
				return $this->_tabs;
			}

			public function valueHandler( $field_name ) {
				return isset( $this->settings[ 'values' ][ $field_name ] ) ? $this->settings[ 'values' ][ $field_name ] : null;
			}

			public function getAllFields() {
				return $this->_fields;
			}

			public function get_fields_list() {
				$output = array();
				foreach ( $this->_fields as $field_name => $field_items ) {

					foreach ( $field_items as $field ) {

						$output[ ] = $field->params[ 0 ];

					}

				}

				return $output;
			}

			public function make( $not_echo = false ) {
				$output = '';
				foreach ( $this->_fields as $field_name => $field_items ) {

					/** @var Phoenix_Framework_Form_Field_Type_Base $field */
					foreach ( $field_items as $field ) {

						$output .= $field->render();

					}

				}

				if ( ! $not_echo ) {
					echo $output;
				}

				return $output;
			}

			public function open_tab( $id, $label ) {
				$this->_tabs[ $id ] = $label;

				return '<div class="form-tab" data-id="' . $id . '">';
			}

			public function close_tab() {
				return '</div>';
			}

			/**
			 * @param $type
			 * @param $args
			 *
			 * @return Phoenix_Framework_Form_Field_Type_Base|bool
			 */
			public function addField( $type, $args ) {
				if ( ! array_key_exists( $type, $this->_fieldTypes ) ) {
					return false;
				}
				$class            = $this->_fieldTypes[ $type ];
				$object           = new $class( $args );
				$this->_fields[ ] = $object;

				return $object;
			}

			public function add_text( $id, $callback = null, Array $extra = array() ) {
				if ( ! is_callable( $callback ) ) {
					$callback = null;
				}

				return $this->addField(
					'text',
					array(
						'inputName'           => $this->field_name( $id ),
						'callback'            => $callback,
						'formBuilderSettings' => $this->settings,
						'extra'               => $extra,
						'id'                  => $id,
						'valueHandler'        => array( $this, 'valueHandler' )
					)
				);
			}


			public function add_wp_editor( $id, $callback = null, Array $extra = array() ) {
				if ( ! is_callable( $callback ) ) {
					$callback = null;
				}

				return $this->addField(
					'wp_editor',
					array(
						'inputName'           => $this->field_name( $id ),
						'callback'            => $callback,
						'formBuilderSettings' => $this->settings,
						'extra'               => $extra,
						'id'                  => $id,
						'valueHandler'        => array( $this, 'valueHandler' )
					)
				);
			}

			/**
			 * @param       $id
			 * @param array $options
			 * @param null  $callback
			 * @param array $extra
			 *
			 * @return bool|Phoenix_Framework_Multiselect_Field_Type
			 */
			public function add_multiselect( $id, array $options, $callback = null, Array $extra = array() ) {
				if ( ! is_callable( $callback ) ) {
					$callback = null;
				}

				return $this->addField(
					'multiselect',
					array(
						'inputName'           => $this->field_name( $id ),
						'callback'            => $callback,
						'formBuilderSettings' => $this->settings,
						'options'             => $options,
						'extra'               => $extra,
						'id'                  => $id,
						'valueHandler'        => array( $this, 'valueHandler' )
					)
				);
			}

			public function add_media( $id, $callback = null, Array $extra = array() ) {
				global $wp_version;
				if ( version_compare( '3.5', $wp_version, '>' ) ) {
					throw new Exception( 'Wordpress 3.5+ is required for media field.' );
				}
				if ( ! is_callable( $callback ) ) {
					$callback = null;
				}

				return $this->addField(
					'media',
					array(
						'inputName'           => $this->field_name( $id ),
						'callback'            => $callback,
						'formBuilderSettings' => $this->settings,
						'extra'               => $extra,
						'id'                  => $id,
						'valueHandler'        => array( $this, 'valueHandler' )
					)
				);


			}

			public function add_textarea( $id, $callback = null, Array $extra = array() ) {
				if ( ! is_callable( $callback ) ) {
					$callback = null;
				}

				return new Phoenix_Framework_Textarea_Field_Type(
					array(
						'inputName'           => $this->field_name( $id ),
						'callback'            => $callback,
						'extra'               => $extra,
						'formBuilderSettings' => $this->settings,
						'id'                  => $id,
						'valueHandler'        => array( $this, 'valueHandler' )
					)
				);
			}

			public function add_checkbox( $id, $callback = null, Array $extra = array() ) {
				if ( ! is_callable( $callback ) ) {
					$callback = null;
				}

				return new Phoenix_Framework_Checkbox_Field_Type(
					array(
						'inputName'           => $this->field_name( $id ),
						'callback'            => $callback,
						'extra'               => $extra,
						'formBuilderSettings' => $this->settings,
						'id'                  => $id,
						'valueHandler'        => array( $this, 'valueHandler' )
					)
				);
			}

			public function add_multicheckbox( $id, $options, $callback = null, Array $extra = array() ) {
				if ( ! is_callable( $callback ) ) {
					$callback = null;
				}

				return new Phoenix_Framework_Multicheckbox_Field_Type(
					array(
						'inputName'           => $this->field_name( $id ),
						'callback'            => $callback,
						'extra'               => $extra,
						'formBuilderSettings' => $this->settings,
						'id'                  => $id,
						'options'             => $options,
						'valueHandler'        => array( $this, 'valueHandler' )
					)
				);
			}

			public function add_select( $id, $options, $callback = null, Array $extra = array() ) {
				if ( ! is_callable( $callback ) ) {
					$callback = null;
				}

				return new Phoenix_Framework_Select_Field_Type(
					array(
						'inputName'           => $this->field_name( $id ),
						'callback'            => $callback,
						'extra'               => $extra,
						'formBuilderSettings' => $this->settings,
						'id'                  => $id,
						'options'             => $options,
						'valueHandler'        => array( $this, 'valueHandler' )
					)
				);
			}

			public function add_radio( $id, $options, $callback = null, Array $extra = array() ) {
				if ( ! is_callable( $callback ) ) {
					$callback = null;
				}

				return new Phoenix_Framework_Radio_Field_Type(
					array(
						'inputName'           => $this->field_name( $id ),
						'callback'            => $callback,
						'extra'               => $extra,
						'formBuilderSettings' => $this->settings,
						'id'                  => $id,
						'options'             => $options,
						'valueHandler'        => array( $this, 'valueHandler' )
					)
				);
			}


		}
	}

<?php
	/**
	 * Automatically adds panel fields to theme customize fields
	 *
	 * @class          Phoenix_Framework_Panel_Customize_Support
	 * @version        1.0
	 * @package        Phoenix_Framework
	 * @category       Class
	 * @author         Vahidd
	 */

	defined( 'ABSPATH' ) or die; // Prevents direct access

	class Phoenix_Framework_Panel_Customize_Support {

		/**
		 * @var $panel Phoenix_Framework_Panel
		 */
		protected $panel;

		public static $fieldTypes = array(
			'Phoenix_Framework_Textarea_Field_Type' => 'textarea',
			'Phoenix_Framework_Text_Field_Type'     => 'option',
			'Phoenix_Framework_Media_Field_Type'    => 'option'
		);

		/**
		 * @param Phoenix_Framework_Panel $panel
		 *
		 * @return Phoenix_Framework_Panel_Customize_Support
		 */
		public static function make( Phoenix_Framework_Panel $panel ) {
			return new self( $panel );
		}

		protected function __construct( Phoenix_Framework_Panel $panel ) {
			$this->panel = $panel;
			add_action( 'customize_register', array( $this, 'registerCustomizeFields' ), 1, 100 );
			add_action( 'customize_save_after', array( $this, 'handleSave' ), 1, 100 );
		}

		public static function prepareFieldId( $id ) {
			return sprintf( 'phoenix_customize_field_%s', $id );
		}

		public static function filterPhoenixCustomizeFields( $field ) {
			$output = array();
			foreach ( $field as $key => $value ) {
				if ( strpos( $key, 'phoenix_customize_field_' ) === 0 ) {
					$output[ $key ] = $value;
				}
			}

			return $output;
		}

		public static function filterFieldName( $name ) {
			return preg_replace( '/^phoenix_customize_field_/', '', $name );
		}

		public function registerCustomizeFields( WP_Customize_Manager $customizeManager ) {

			$allFields  = $this->panel->form_builder->getAllFields();
			$fields     = $this->prepareFields( $allFields );
			$fieldTypes = &self::$fieldTypes;


			foreach ( $fields as $tabId => $tabFields ) {
				$tab = $this::getTab( $allFields, $tabId );
				$customizeManager->add_section(
					$tabId,
					array(
						'title' => $tab[ 'name' ]
					)
				);
				foreach ( $tabFields as $field ) {
					/**
					 * @var $field Phoenix_Framework_Form_Field_Type_Base
					 */
					$customizeManager->add_setting(
						$this::prepareFieldId( $field->settings[ 'id' ] ),
						array(
							'default'    => $field->get_value(),
							'type'       => $fieldTypes[ get_class( $field ) ],
							'capability' => 'manage_options',
						)
					);
					$customizeManager->add_control(
						$this::prepareFieldId( $field->settings[ 'id' ] ),
						array(
							'label'   => $field->settings[ 'label' ],
							'section' => $tabId,
						)
					);
				}

			}

		}

		function prepareFields( $fields ) {

			$token      = '0';
			$output     = array();
			$fieldTypes = &self::$fieldTypes;

			foreach ( $fields as $field ) {

				if ( is_object( $field ) ) {
					/**
					 * @var $field Phoenix_Framework_Form_Field_Type_Base
					 */
					if ( ! array_key_exists( get_class( $field ), $fieldTypes ) ) {
						continue;
					}
					$output[ $token ][ ] = $field;
				} else if ( is_array( $field ) ) {
					if ( empty( $field[ 'type' ] ) ) {
						continue;
					}
					if ( $field[ 'type' ] == 'tab' && ! empty( $field[ 'id' ] ) ) {
						$token = $field[ 'id' ];
					}
					if ( $field[ 'type' ] == 'close_tab' ) {
						$token = '0';
					}
				}

			}

			return $output;

		}

		static function getTab( $fields, $id ) {
			foreach ( $fields as $field ) {
				if ( is_array( $field ) && ! empty( $field[ 'type' ] ) && $field[ 'type' ] == 'tab' && ! empty( $field[ 'id' ] ) && $field[ 'id' ] == $id ) {
					return $field;
				}
			}

			return false;
		}

		public function handleSave( WP_Customize_Manager $customizeManager ) {
			$fields     = $this::filterPhoenixCustomizeFields( $customizeManager->settings() );
			$optionName = $this->panel->get_option_name();

			$options = get_option( $optionName, array() );
			foreach ( $fields as $filedId => $field ) {
				/**
				 * @var $field WP_Customize_Setting
				 */
				$fieldid             = $this::filterFieldName( $filedId );
				$options[ $fieldid ] = $field->post_value();
			}

			update_option( $optionName, $options );

		}

	}

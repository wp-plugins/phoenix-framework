<?php
	/**
	 *
	 * Phoenix Panel
	 *
	 * @class          Phoenix_Framework_Panel
	 * @version        1.0
	 * @package        Phoenix Framework
	 * @category       Class
	 * @author         Vahidd
	 */

	defined( 'ABSPATH' ) or die; // Prevents direct access


	if ( ! class_exists( 'Phoenix_Framework_Panel' ) ) {
		class Phoenix_Framework_Panel {


			private
				$_callback,
				$_id,
				$_fields;


			public
				$title = '',
				$hidden_inputs,
				$menu,
				$option_name,
				$form_builder;

			function __construct( $id, $callback, $menu ) {
				$this->_id          = $id;
				$this->form_builder = new Phoenix_Framework_Form_Builder(
					array(
						'field_name_generator' => array( $this, 'field_name_generator' )
					)
				);


				$this->form_builder->set_values( get_option( $this->_id, array() ) );

				$this->_callback = $callback;


				if ( ! is_array( $this->menu ) ) {
					$this->menu = $menu;
				}
				$this->menu[ 'callback' ] = array( $this, 'display' );
				Phoenix_Framework_Admin_Menu::add( $this->menu, array( __CLASS__, 'loadPhoenixAssets' ) );


			}

			function field_name_generator( $field_id ) {
				return sprintf( '_phoenix_panel_%s[%s]', $this->_id, $field_id );
			}

			public static function loadPhoenixAssets() {
				phoenix()->loadAssets( true );
			}

			/**
			 *
			 */
			function display() {


				$this->hidden_inputs = Phoenix_Framework_Action::hidden_inputs( '_phoenix_panel_actions' );
				$this->hidden_inputs .= sprintf( '<input type="hidden" name="%s" value="%s"/>', '_panel_id', $this->_id );

				ob_start();
				call_user_func( $this->_callback, $this );
				$this->_fields = ob_get_clean();


				Phoenix_Framework::loadView(
					'panel/panel-template',
					array(
						'fields'        => $this->_fields,
						'title'         => $this->title,
						'notice'        => Phoenix_Framework_Request::get_post( 'notice' ),
						'hidden_fields' => $this->hidden_inputs,
						'tabs_list'     => $this->form_builder->get_tabs_array(),
						'reset_url'     => add_query_arg( array( '_panel_id' => $this->_id ), Phoenix_Framework_Action::action_url( '_phoenix_panel_reset', true ) )
					)
				);

			}

			function get_option_name() {
				return $this->_id;
			}

		}
	}

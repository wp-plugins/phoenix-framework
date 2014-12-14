<?php
/**
 * Phoenix_Framework_Panel_Factory
 *
 * a
 *
 * @class          Phoenix_Framework_Panel_Factory
 * @version        1.0
 * @package        Phoenix Framework
 * @category       Class
 * @author         Vahidd
 */


defined( 'ABSPATH' ) or die; // Prevents direct access


if ( ! class_exists( 'Phoenix_Framework_Panel_Factory' ) ) {
	class Phoenix_Framework_Panel_Factory {

		static protected $_panels = array();



		static function init(){
			Phoenix_Framework_Action::make(
				'_phoenix_panel_actions',
				array( __CLASS__, 'handle_panel_actions' ),
				array(
					'admin_only' => true
				)
			);
			add_action( 'admin_enqueue_scripts', function(){
				wp_enqueue_media();
			} );
		}


		static function handle_panel_actions() {
			if (
				empty( $_POST[ '_panel_id' ] )
				|| ! is_string( $_POST[ '_panel_id' ] )
				|| ! array_key_exists( $_POST[ '_panel_id' ], self::$_panels ) ) {
				return;
			}

			$id = $_POST['_panel_id'];

			/**
			 * @var Phoenix_Framework_Panel
			 */
			$panel = self::$_panels[$id];


			if( !empty( $_POST['panel-save'] ) ) {


				$option_name = $panel->get_option_name();
				if ( ! empty( $_POST['_phoenix_panel_'.$id] ) ) {
					update_option( $option_name, $_POST['_phoenix_panel_'.$id] );
				}

				wp_redirect( add_query_arg( array( 'notice' => urlencode( 'Settings saved.' ) ) ) );
				die;


			} else if ( !empty( $_POST['panel-reset'] ) ){


				delete_option( $id );

				wp_redirect( add_query_arg( array( 'notice' => urlencode( 'Panel reset.' ) ) ) );
				die;


			}



		}

		static function make( $id, $callback, $menu ) {

			if ( ! is_callable( $callback ) ) {
				throw new Exception( sprintf( 'Uncallable callback for %s', __METHOD__ ) );
			}

			self::$_panels[ $id ] = new Phoenix_Framework_Panel( $id, $callback, $menu );

			return self::$_panels[ $id ];
		}


	}
}

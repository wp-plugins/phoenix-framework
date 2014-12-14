<?php
/**
 * class-phoenix-framework-action
 *
 * a
 *
 * @class          Phoenix_Framework_Action
 * @version        1.0
 * @package        BuddyPress (www\bp\)
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

if ( ! class_exists( 'Phoenix_Framework_Action' ) ) {
	class Phoenix_Framework_Action {

		protected static $_actions = array();


		static function init() {
			add_action( 'init', array( __CLASS__, 'check_action' ), 9999 * 999 );

		}

		static function check_action(){
			if (
				array_key_exists( (string) Phoenix_Framework_Request::post_get( 'phoenix_action' ), self::$_actions )
				&& ! empty( $_REQUEST[ '_wpnonce' ] )
				&& is_string( $_REQUEST[ '_wpnonce' ] )
				&& wp_verify_nonce( $_REQUEST[ '_wpnonce' ], "phoenix_action_{$_REQUEST['phoenix_action']}" )
			) {
				$action = self::$_actions[ $_REQUEST[ 'phoenix_action' ] ];
				if ( $action[ 'post_only' ] && strtolower( $_SERVER[ 'REQUEST_METHOD' ] ) !== 'post' ) {
					return;
				}
				if ( $action[ 'admin_only' ] && ! is_admin() ) {
					return;
				}

				call_user_func( $action[ 'callback' ] );
			}
		}

		/**
		 * @param          $action
		 * @param callback $callback
		 * @param array    $settings
		 *
		 * @return $this
		 */
		static function make( $action, $callback, Array $settings = array() ) {
			self::$_actions[ $action ] = array_merge(
				array(
					'callback'   => $callback,
					'post_only'  => false,
					'admin_only' => false
				),
				$settings
			);
		}

		static function action_exists( $action_id ) {
			return array_key_exists( $action_id, self::$_actions );
		}

		static function action_url( $action_id, $admin = false, $custom_params = array() ) {
			return add_query_arg(
				array_merge(
					$custom_params,
					array(
						'phoenix_action' => $action_id,
						'_wpnonce'       => wp_create_nonce( 'phoenix_action_' . $action_id )
					)
				),
				trailingslashit( $admin ? admin_url() : site_url() )
			);
		}

		public static function hidden_inputs( $action_id ) {

			if ( ! did_action( 'plugins_loaded' ) ) {
				_doing_it_wrong( __FUNCTION__, 'Should be called in plugins_loaded or init action.', '1.0' );

				return '';
			}

			$html = sprintf( '<input type="hidden" name="%s" value="%s"/>', 'phoenix_action', $action_id );
			$html .= sprintf( '<input type="hidden" name="%s" value="%s"/>', '_wpnonce', wp_create_nonce( 'phoenix_action_' . $action_id ) );

			return $html;
		}

	}
}

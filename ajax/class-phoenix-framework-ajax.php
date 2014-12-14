<?php
	/**
	 * @class          Phoenix_Framework_AJAX
	 * @version        1.0
	 * @package        Phoenix Framework
	 * @category       Class
	 * @author         Vahidd
	 */

	defined( 'ABSPATH' ) or die; // Prevents direct access

	class Phoenix_Framework_AJAX extends Phoenix_Framework_AJAX_Helper {

		protected static
			$_priorities = array( 'before', 'on', 'after' ),
			$_instances = array();

		protected
			$_actions = array(),
			$_current_action;

		public static function init( $id ) {
			self::$_instances[ $id ] = new self( $id );

			return self::$_instances[ $id ];
		}

		public function __construct( $action ) {
			add_action( 'wp_ajax_' . $action, array( $this, 'ajax_callback' ) );
			add_action( 'wp_ajax_nopriv_' . $action, array( $this, 'ajax_callback' ) );
		}

		public function attachEvent( $action_name, $callback = null, $options = array(), $priority = 'on' ) {
			if ( ! in_array( $priority, self::$_priorities ) ) {
				return false;
			}
			if ( ! is_callable( $callback ) ) {
				$callback = '___return_null';
			}
			$this->_actions[ str_replace( array( "\n", ' ', '   ' ), '', $action_name ) ][ $priority ] = array(
				'callback' => $callback,
				'options'  => array_merge(
					array(
						'logged_in_only' => false
					),
					$options
				)
			);

			return $this;
		}

		public function on( $action_name, $callback, Array $options = array() ) {
			return $this->attachEvent(
				$action_name,
				$callback,
				$options,
				'on'
			);
		}

		public function before( $action_name, $callback, Array $options = array() ) {
			return $this->attachEvent(
				$action_name,
				$callback,
				$options,
				'before'
			);
		}

		public function after( $action_name, $callback, Array $options = array() ) {
			return $this->attachEvent(
				$action_name,
				$callback,
				$options,
				'after'
			);
		}

		/**
		 * @param $action
		 *
		 * @return null
		 */
		protected function _handle_before( $action ) {
			$is_logged_in  = is_user_logged_in();
			$before_action = $action[ 'before' ];
			if ( $before_action[ 'options' ][ 'logged_in_only' ] && ! $is_logged_in ) {
				return null;
			}

			$call = call_user_func( $action[ 'before' ][ 'callback' ], $this->_current_action );

			if ( $call !== null ) {
				$this::json_die( $call );
			}

		}

		public function slice_array( $array, $offset ) {
			$output = array();
			$i      = 0;
			foreach ( (array) $array as $index => $item ) {
				$output[ $index ] = $item;
				if ( $i == $offset ) {
					break;
				}
				$i ++;
			}

			return $output;
		}

		protected function _handle_action( $action ) {
			$is_logged_on = is_user_logged_in();

			if ( ! empty( $action[ 'on' ] ) && $action[ 'on' ][ 'options' ][ 'logged_in_only' ] && ! $is_logged_on ) {
				return null;
			}

			$call = ! empty( $action[ 'on' ] ) ? call_user_func( $action[ 'on' ][ 'callback' ], $this->_current_action ) : null;


			if ( ! empty( $action[ 'after' ] ) ) {
				$this->_handle_after( $action );
			}

			if ( ! empty( $call ) ) {
				$this::json_die( $call );
			}
		}


		protected function _handle_after( $action ) {
			$is_logged_on = is_user_logged_in();

			if ( empty( $action[ 'after' ] ) ) {
				return null;
			}
			if ( $action[ 'after' ][ 'options' ][ 'logged_in_only' ] && ! $is_logged_on ) {
				return null;
			}

			call_user_func( $action[ 'after' ][ 'callback' ], $this->_current_action );
		}

		protected function _do_action( $action_name ) {
			if ( empty( $this->_actions[ $action_name ] ) ) {
				return false;
			}
			$action = $this->_actions[ $action_name ];
			if ( ! empty( $action[ 'before' ] ) ) {
				$this->_handle_before( $action );
			}
			$this->_handle_action( $action );

			return $this;
		}

		/**
		 *
		 */
		public function ajax_callback() {

			if ( empty( $_REQUEST[ 'action2' ] ) || ! is_string( $_REQUEST[ 'action2' ] ) ) {
				$this::json_die();
			}

			$this->_current_action = $_REQUEST[ 'action2' ];

			$action_parts = explode( '.', $_REQUEST[ 'action2' ] );

			if ( count( $action_parts ) > 1 ) {
				foreach ( $action_parts as $i => $part ) {
					$this->_do_action(
						implode(
							'.',
							$this->slice_array( $action_parts, $i )
						)
					);
				}
			}


			$this->_do_action( $_REQUEST[ 'action2' ] );


			$this::json_die();
		}

	}

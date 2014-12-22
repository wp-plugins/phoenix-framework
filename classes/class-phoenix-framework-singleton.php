<?php
	/**
	 * Singleton Helper Class
	 *
	 * @class          Phoenix_Framework_Singleton
	 * @version        1.0
	 * @package        Phoenix Framework
	 * @category       Class
	 * @author         Vahidd
	 */

	defined( 'ABSPATH' ) or die; // Prevents direct access

	class Phoenix_Framework_Singleton {

		private static $instances = array();

		protected $initFunction = 'init';

		protected function __construct() {
		}

		protected function __clone() {
		}

		public function __wakeup() {
			throw new Exception( "Cannot unserialize singleton" );
		}

		/**
		 * @return $this
		 */
		public static function getInstance() {
			$cls = get_called_class();
			if ( ! isset( self::$instances[ $cls ] ) ) {
				self::$instances[ $cls ] = $object = new static;

				if ( is_callable( array( $object, $object->initFunction ) ) ) {
					$params = func_get_args();
					call_user_func_array( array( $object, $object->initFunction ), $params );
				}
			}

			return self::$instances[ $cls ];
		}

	}

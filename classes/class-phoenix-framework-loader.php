<?php
	/**
	 * Phoenix Loader Class
	 *
	 * @class          Phoenix_Framework_Loader
	 * @version        1.0
	 * @package        Phoenix Framework
	 * @category       Class
	 * @author         Vahidd
	 */

	defined( 'ABSPATH' ) or die; // Prevents direct access

	class Phoenix_Framework_Loader {

		protected static $instance;

		public static function getInstance() {
			if ( self::$instance === null ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		protected function __construct() {
			$this->loadComponents();
		}



		public function loadComponents(){

		}

	}

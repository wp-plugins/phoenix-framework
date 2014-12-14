<?php

	/**
	 * The Phoenix Framework
	 *
	 * Phoenix is a simple wordpress framework that is made by <a href="http://vahidd.com">Vahidd</a>. Phoenix helps
	 * you in creating wordpress stuff.
	 *
	 * @package    Phoenix Framework
	 */

	/**
	 * Plugin Name: Phoenix Framework
	 * Plugin URI:  http://vahidd.com/phoenix
	 * Description: Wordpress Framework
	 * Author:      Vahidd
	 * Author URI:  http://vahidd.com
	 * Version:     1.0
	 * Text Domain: phoenix
	 * Domain Path: /languages/
	 */


	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	require dirname( __FILE__ ) . '/functions.php';

	/**
	 * Class Phoenix_Framework
	 */
	final class Phoenix_Framework {

		public static
			$version = '1.0',
			$phoenixURI = '';

		protected
			$settings;

		static protected
			$instance,
			$_classes = array(),
			$_loadedComs = array(),
			$path;


		public static function getInstance() {
			if ( self::$instance === null ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		public static function autoloadCallback( $class ) {
			if ( array_key_exists( $class, self::$_classes ) ) {
				require dirname( __FILE__ ) . '/' . self::$_classes[ $class ];
			}
		}

		/**
		 * PHP5 Constructor
		 *
		 * @since  1.0
		 * @access protected
		 * @throws Exception
		 */
		protected function __construct() {
			self::$_classes = require dirname( __FILE__ ) . '/classes-list.php';
			spl_autoload_register( array( __CLASS__, 'autoloadCallback' ) );
			self::$path = trailingslashit( dirname( __FILE__ ) );
			Phoenix_Framework_Enqueue::init();
			Phoenix_Framework_Action::init();
			Phoenix_Framework_Panel_Factory::init();
			Phoenix_Framework_Admin_Menu::init();
			load_plugin_textdomain( 'phoenix-framework' );
			$this->settings = array(
				'loadAssets' => false
			);
		}


		function query_builder() {
			return new Phoenix_Framework_DB_Query_Builder();
		}

		public function loadAssets( $load = null ) {
			if ( $load === null ) {
				return $this->settings[ 'loadAssets' ];
			}
			$this->settings[ 'loadAssets' ] = (bool) $load;

			return $this;
		}

		/**
		 * @param       $filename
		 * @param array $vars
		 *
		 * @param bool  $capture_buffer
		 *
		 * @return $this|mixed
		 */
		static function loadView( $filename, $vars = array(), $capture_buffer = false ) {

			$folder = dirname( __FILE__ ) . '/views/';

			$view_name = preg_replace( '/\.php$/', '', $filename ) . '.php';

			if ( file_exists( $folder . $view_name ) ) {
				extract( $vars );
				global $post, $wp_query, $wp;

				if ( $capture_buffer ) {
					ob_start();
					include $folder . $view_name;

					return ob_get_clean();
				}

				return include $folder . $view_name;
			}

			return false;

		}

		public static function load( $com ) {
			static $libs = array(
				'html'  => 'functions/html-functions.php',
				'array' => 'functions/array-functions.php'
			);
			if ( in_array( $com, self::$_loadedComs ) ) {
				return false;
			}
			self::$_loadedComs[ ] = $com;

			return require sprintf( '%s/%s', self::$path, $libs[ $com ] );
		}


	}

	/**
	 * @return bool|Phoenix_Framework
	 */
	function phoenix() {
		if ( ! did_action( 'after_setup_theme' ) ) {
			_doing_it_wrong( __FUNCTION__, __( 'Function need to be called after init hook.' ), 1 );

			return false;
		}

		return Phoenix_Framework::getInstance();
	}

	add_action( 'after_setup_theme', function () {
		Phoenix_Framework::$phoenixURI = plugin_dir_url( __FILE__ );
		Phoenix_Framework::getInstance();
		do_action( 'phoenix_framework_init' );
	} );

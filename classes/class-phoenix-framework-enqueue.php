<?php
	/**
	 * @class          Phoenix_Framework_Enqueue
	 * @version        1.0
	 * @package        Phoenix Framework
	 * @category       Class
	 * @author         Vahidd
	 */

	defined( 'ABSPATH' ) or die; // Prevents direct access

	class Phoenix_Framework_Enqueue {


		static protected $_enqueue_list = array();

		static protected $instance;

		static function init() {
			if ( self::$instance === null ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		protected function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		}


		public function enqueue() {

			$loadAssets = phoenix()->loadAssets();

			if ( ! $loadAssets ) {
				return false;
			}

			foreach ( $this::$_enqueue_list as $asset ) {
				wp_enqueue_script( $asset );
			}

			if ( current_action() == 'admin_enqueue_scripts' ) {
				wp_enqueue_style(
					'phoenix-admin-styles',
					Phoenix_Framework::$phoenixURI . 'assets/css/admin-styles.css'
				);
				if ( function_exists( 'wp_enqueue_media' ) ) {
					wp_enqueue_media();
				}
			}


			wp_enqueue_script(
				'phoenix-scripts',
				Phoenix_Framework::$phoenixURI . 'assets/js/phoenix-all.js',
				array( 'jquery', 'jquery-form' ),
				Phoenix_Framework::$version
			);

			wp_localize_script(
				'phoenix-scripts',
				'phoenix',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' )
				)
			);

		}


	}

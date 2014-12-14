<?php
/**
 * class-phoenix-framework-fake-page
 *
 * a
 *
 * @class          Phoenix_Framework_Fake_Page
 * @version        1.0
 * @package        BuddyPress (www\bp\)
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

if ( ! class_exists( 'Phoenix_Framework_Fake_Page' ) ) {
	class Phoenix_Framework_Fake_Page {

		public $rewrite_default_page = true;

		public function __construct(  ) {

			add_filter( 'the_posts', array( $this, 'init' ) );
		}

		public function init(){
			global $wp, $wp_query;

		}

		public function content() {
			echo '';
		}

	}
}

<?php
/**
 * class-phoenix-framework-url-helper
 *
 * a
 *
 * @class          Phoenix_Framework_URL_Helper
 * @version        1.0
 * @package        BuddyPress (www\bp\)
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

if ( !class_exists( 'Phoenix_Framework_URL_Helper' ) ) {
	class Phoenix_Framework_URL_Helper {


		static function current_page_url(){
			global $wp;
			if( is_object( $wp ) )
				return home_url(add_query_arg(array(),$wp->request));
			else {
				$pageURL = 'http';
				if( is_ssl() ) {
					$pageURL .= "s";
				}
				$pageURL .= "://";
				if ($_SERVER["SERVER_PORT"] != "80") {
					$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
				} else {
					$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
				}
				return $pageURL;
			}
		}



	}
}

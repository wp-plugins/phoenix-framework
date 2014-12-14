<?php
/**
 * class-phoenix-framework-redirect
 *
 * a
 *
 * @class 		Phoenix_Framework_Redirect
 * @version		1.0
 * @package		BuddyPress (www\bp\)
 * @category	Class
 * @author 		Vahidd
 */



defined('ABSPATH') or die; // Prevents direct access


if( !class_exists( 'Phoenix_Framework_Redirect' ) ) {
	class Phoenix_Framework_Redirect{


		static function to( $location, $status = 302, Array $params = array() ){
			$location = !empty( $params ) ? add_query_arg( $params, $location ) : $location;
			if( !did_action( 'plugins_loaded' ) ) {
				header("Location: $location", true, $status);
				die;
			}
			wp_redirect( $location, $status );
			die;
		}

		static function home(){
			static::to( site_url() );
		}



	}
}

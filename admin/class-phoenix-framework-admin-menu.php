<?php
/**
 * @class 		Phoenix_Framework_Admin_Menu
 * @version		1.0
 * @package		Phoenix Framework
 * @category	Class
 * @author 		Vahidd
 */
 
defined('ABSPATH') or die; // Prevents direct access

if( !class_exists( 'Phoenix_Framework_Admin_Menu' ) ) {
	class Phoenix_Framework_Admin_Menu{

		static protected $_menus = array();

		static function init(){
			if( !has_action( 'admin_menu', array( __CLASS__, 'menu' ) ) )
				add_action( 'admin_menu', array( __CLASS__, 'menu' ), 9999 );
		}

		static function menu(){
			$menus = self::$_menus;
			foreach( $menus as $menu ) {
				$args = array(
					$menu['page_title'],
					$menu['menu_title'],
					$menu['capability'],
					$menu['menu_slug']
				);
				if( !empty( $menu['callback'] ) && is_callable( $menu['callback'] ) )
					$args[] = $menu['callback'];
				if( !empty( $menu['icon_url'] ) && is_string( $menu['icon_url'] ) )
					$args[] = $menu['icon_url'];
				$is_submenu = !empty( $menu['parent'] ) && is_string( $menu['parent'] ) ? true : false;
				if( $is_submenu )
					array_unshift( $args, $menu['parent'] );
				$call = call_user_func_array( $is_submenu ? 'add_submenu_page' : 'add_menu_page', $args );
				if( isset( $menu['_load'] ) && is_callable( $menu['_load'] ) )
					add_action( 'load-' . $call, $menu['_load'] );
			}
		}



		static function add( Array $settings, $load = null ){

			$args = array_merge(
				array(

				),
				$settings
			);

			if(
				empty( $args['page_title'] ) || !is_string( $args['page_title'] )
				|| empty( $args['menu_slug'] ) || !is_string( $args['menu_slug'] )
			)
				return false;

			if( empty( $args['menu_title'] ) )
				$args['menu_title'] = $args['page_title'];

			if( empty( $args['capability'] ) )
				$args['capability'] = 'manage_options';

			$args['_load'] = $load;

			self::$_menus[] = $args;


		}

	}
}

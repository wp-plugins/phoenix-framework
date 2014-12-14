<?php
/**
 * class-phoenix-framework-request
 *
 * a
 *
 * @class          Phoenix_Framework_Request
 * @version        1.0
 * @package        BuddyPress (www\bp\)
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

if ( !class_exists( 'Phoenix_Framework_Request' ) ) {

	/**
	 * Class Phoenix_Framework_Request
	 * * @property boolean $isAjax Whether this is an AJAX (XMLHttpRequest) request. This property is read-only.
	 */
	class Phoenix_Framework_Request extends Phoenix_Framework_Object {


		/**
		 * Fetch from array
		 *
		 * Internal method used to retrieve values from global arrays.
		 *
		 * @param    array  &$array $_GET, $_POST, $_COOKIE, $_SERVER, etc.
		 * @param    string $index  Index for item to be fetched from $array
		 *
		 * @internal param bool $xss_clean Whether to apply XSS filtering
		 * @return    mixed
		 */
		protected static function _fetch_from_array( &$array, $index = null ) {
			// If $index is NULL, it means that the whole $array is requested
			if ( $index === null ) {
				$output = array();
				foreach ( array_keys( $array ) as $key ) {
					$output[ $key ] = self::_fetch_from_array( $array, $key );
				}

				return $output;
			}


			if ( isset( $array[ $index ] ) ) {
				$value = $array[ $index ];
			} elseif ( ( $count = preg_match_all( '/(?:^[^\[]+)|\[[^]]*\]/', $index, $matches ) ) > 1 ) // Does the index contain array notation
			{
				$value = $array;
				for ( $i = 0; $i < $count; $i++ ) {
					$key = trim( $matches[ 0 ][ $i ], '[]' );
					if ( $key === '' ) // Empty notation will return the value as array
					{
						break;
					}

					if ( isset( $value[ $key ] ) ) {
						$value = $value[ $key ];
					} else {
						return null;
					}
				}
			} else {
				return null;
			}

			return $value;
		}

		// --------------------------------------------------------------------

		/**
		 * Fetch an item from the GET array
		 *
		 * @param    string $index Index for item to be fetched from $_GET
		 *
		 * @internal param bool $xss_clean Whether to apply XSS filtering
		 *
		 * @return    mixed
		 */
		public static function get( $index = null ) {
			return self::_fetch_from_array( $_GET, $index );
		}

		// --------------------------------------------------------------------

		/**
		 * Fetch an item from the POST array
		 *
		 * @param    string $index Index for item to be fetched from $_POST
		 *
		 * @internal param bool $xss_clean Whether to apply XSS filtering
		 *
		 * @return    mixed
		 */
		public static function post( $index = null ) {
			return self::_fetch_from_array( $_POST, $index );
		}

		// --------------------------------------------------------------------

		/**
		 * Fetch an item from POST data with fallback to GET
		 *
		 * @param    string $index Index for item to be fetched from $_POST or $_GET
		 *
		 * @internal param bool $xss_clean Whether to apply XSS filtering
		 *
		 * @return    mixed
		 */
		public static function post_get( $index ) {
			return isset( $_POST[ $index ] )
				? self::post( $index )
				: self::get( $index );
		}


		public static function get_post( $index ) {
			return isset( $_GET[ $index ] )
				? self::get( $index )
				: self::post( $index );
		}

		public static function getIsAjax(){
			return ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
		}

		public static function get_referer(){
			return $_SERVER['HTTP_REFERER'];
		}

		public static function is_string( $item, $validators = array(), $methods = array() ){
			$index = self::post_get( $item );
			return !empty( $index ) && is_string( $index ) ? $index : false;
		}


		public static function getIp() {
			$ipaddress = '';
			if (getenv('HTTP_CLIENT_IP'))
				$ipaddress = getenv('HTTP_CLIENT_IP');
			else if(getenv('HTTP_X_FORWARDED_FOR'))
				$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
			else if(getenv('HTTP_X_FORWARDED'))
				$ipaddress = getenv('HTTP_X_FORWARDED');
			else if(getenv('HTTP_FORWARDED_FOR'))
				$ipaddress = getenv('HTTP_FORWARDED_FOR');
			else if(getenv('HTTP_FORWARDED'))
				$ipaddress = getenv('HTTP_FORWARDED');
			else if(getenv('REMOTE_ADDR'))
				$ipaddress = getenv('REMOTE_ADDR');
			else
				$ipaddress = false;
			return $ipaddress;
		}

	}
}
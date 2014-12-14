<?php
/**
 * class-phoenix-framework-form-validation
 *
 * a
 *
 * @class          Phoenix_Framework_Form_Validation
 * @version        1.0
 * @package        BuddyPress (www\bp\)
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

if ( ! class_exists( 'Phoenix_Framework_Form_Validation' ) ) {
	class Phoenix_Framework_Form_Validation extends Phoenix_Framework_Object {

		public $validate_rules;
		private $_labels;
		public $validation_data;
		public $_error_msgs = array();
		private $executed = false;

		private $rules = array(
			'required'              => array(),
			'regex_match'           => array(),
			'matches'               => array(),
			'differs'               => array(),
			'min_length'            => array(),
			'max_length'            => array(),
			'exact_length'          => array(),
			'valid_url'             => array(),
			'valid_email'           => array(),
			'valid_emails'          => array(),
			'valid_ip'              => array(),
			'alpha'                 => array(),
			'alpha_numeric'         => array(),
			'alpha_numeric_spaces'  => array(),
			'alpha_dash'            => array(),
			'numeric'               => array(),
			'integer'               => array(),
			'decimal'               => array(),
			'greater_than'          => array(),
			'greater_than_equal_to' => array(),
			'less_than'             => array(),
			'less_than_equal_to'    => array(),
			'is_natural'            => array(),
			'is_natural_no_zero'    => array(),
			'valid_base64'          => array(),
			'email_exists'          => array(),
			'valid_username'        => array(),
			'unique_username'       => array()
		);

		/**
		 * @var Phoenix_Framework
		 */
		static $phoenix;

		function _fetch_array_item( $array, $item ) {
			return isset( $array[ $item ] ) ? $array[ $item ] : null;
		}

		function __construct( Array $validate = null ) {
			$this->validation_data = $validate == null ? $_POST : $validate;
		}

		static function make() {
			return new self();
		}

		public function get_messages() {
			return $this->_error_msgs;
		}

		static function lang( $item, $params = array() ) {
			static $lang;
			$lang[ 'required' ]              = __('The %s field is required.', 'phoenix-framework');
			$lang[ 'valid_username' ]        = __('The %s field must contain a valid username.', 'phoenix-framework');
			$lang[ 'unique_username' ]       = __('Username already exists. Please choose another one.', 'phoenix-framework');
			$lang[ 'isset' ]                 = __('The %s field must have a value.', 'phoenix-framework');
			$lang[ 'valid_email' ]           = __('The %s field must contain a valid email address.', 'phoenix-framework');
			$lang[ 'valid_emails' ]          = __('The %s field must contain all valid email addresses.', 'phoenix-framework');
			$lang[ 'email_exists' ]          = __('Email address already exists. Please choose another one.', 'phoenix-framework');
			$lang[ 'valid_url' ]             = __('The %s field must contain a valid URL.', 'phoenix-framework');
			$lang[ 'valid_ip' ]              = __('The %s field must contain a valid IP.', 'phoenix-framework');
			$lang[ 'min_length' ]            = __('The %s field must be at least %s characters in length.', 'phoenix-framework');
			$lang[ 'max_length' ]            = __('The %s field cannot exceed %s characters in length.', 'phoenix-framework');
			$lang[ 'exact_length' ]          = __('The %s field must be exactly %s characters in length.', 'phoenix-framework');
			$lang[ 'alpha' ]                 = __('The %s field may only contain alphabetical characters.', 'phoenix-framework');
			$lang[ 'alpha_numeric' ]         = __('The %s field may only contain alpha-numeric characters.', 'phoenix-framework');
			$lang[ 'alpha_numeric_spaces' ]  = __('The %s field may only contain alpha-numeric characters and spaces.', 'phoenix-framework');
			$lang[ 'alpha_dash' ]            = __('The %s field may only contain alpha-numeric characters, underscores, and dashes.', 'phoenix-framework');
			$lang[ 'numeric' ]               = __('The %s field must contain only numbers.', 'phoenix-framework');
			$lang[ 'is_numeric' ]            = __('The %s field must contain only numeric characters.', 'phoenix-framework');
			$lang[ 'integer' ]               = __('The %s field must contain an integer.', 'phoenix-framework');
			$lang[ 'regex_match' ]           = __('The %s field is not in the correct format.', 'phoenix-framework');
			$lang[ 'matches' ]               = __('The %s field does not match the %s field.', 'phoenix-framework');
			$lang[ 'differs' ]               = __('The %s field must differ from the %s field.', 'phoenix-framework');
			$lang[ 'is_unique' ]             = __('The %s field must contain a unique value.', 'phoenix-framework');
			$lang[ 'is_natural' ]            = __('The %s field must only contain digits.', 'phoenix-framework');
			$lang[ 'is_natural_no_zero' ]    = __('The %s field must only contain digits and must be greater than zero.', 'phoenix-framework');
			$lang[ 'decimal' ]               = __('The %s field must contain a decimal number.', 'phoenix-framework');
			$lang[ 'less_than' ]             = __('The %s field must contain a number less than %s.', 'phoenix-framework');
			$lang[ 'less_than_equal_to' ]    = __('The %s field must contain a number less than or equal to %s.', 'phoenix-framework');
			$lang[ 'greater_than' ]          = __('The %s field must contain a number greater than %s.', 'phoenix-framework');
			$lang[ 'greater_than_equal_to' ] = __('The %s field must contain a number greater than or equal to %s.', 'phoenix-framework');

			$params = (array) $params;
			array_unshift( $params, $lang[ $item ] );

			return call_user_func_array( 'sprintf', $params );
		}


		function set_rule_matches( $field_name, $label, $rule, $params ) {
			$this->validate_rules[ $field_name ][ ] = array( $rule, $params );
			$this->_labels[ $field_name ]           = $label;

			return $this;
		}

		function _rule_matches( $field_name, $field_val, $field_params ) {

			$matches = $this->matches( $field_val, $field_params[ 0 ] );

			if ( ! $matches ) {

				$field_label  = $this->_labels[ $field_name ];
				$field2_label = $this->_labels[ $field_params[ 0 ] ];

				$this->add_error(
					$this::lang( 'matches', array( $field_label, $field2_label ) ),
					$field_name,
					'matches'
				);
				$this->add_error(
					$this::lang( 'matches', array( $field2_label, $field_label ) ),
					$field_params[ 0 ],
					'matches'
				);

				return false;
			}


			return true;
		}

		function set_rule( $field_name, $label, $rule, $params = array() ) {

			if ( is_string( $rule ) && method_exists( $this, 'set_rule_' . $rule ) ) {
				return $this->{'set_rule_' . $rule}( $field_name, $label, $rule, $params );
			}

			$this->validate_rules[ $field_name ][ ] = array( $rule, $params );
			$this->_labels[ $field_name ]           = $label;

			return $this;
		}

		function add_error( $msg, $field, $error_type ) {
			$this->_error_msgs[ $field ][ $error_type ] = $msg;
		}

		function fails() {
			if ( ! $this->executed ) {
				$this->execute();
			}

			return ! empty( $this->_error_msgs );
		}

		function success() {
			if ( ! $this->executed ) {
				$this->execute();
			}

			return empty( $this->_error_msgs );
		}

		function execute() {

			$validation_array = $this->validation_data;

			foreach ( $this->validate_rules as $field_name => $rules ) {

				$field_value = $this->_fetch_array_item( $validation_array, $field_name );

				foreach ( $rules as $rule_data ) {

					$rule        = $rule_data[ 0 ];
					$rule_params = $rule_data[ 1 ];

					if ( is_object( $rule ) && $rule instanceof Closure ) {

						call_user_func_array(
							$rule,
							array(
								$this,
								$field_value,
								$rule_params
							)
						);

					} else {


						if ( method_exists( $this, '_rule_' . $rule ) ) {

							$this->{'_rule_' . $rule}( $field_name, $field_value, $rule_params );

						} else if ( array_key_exists( $rule, $this->rules ) ) {

							$call_params = array_merge( array( $field_value ), $rule_params );

							$call = call_user_func_array( array( $this, $rule ), $call_params );

							if ( ! $call ) {

								$lang_call_params = array( $this->_labels[ $field_name ] );
								if ( ! empty( $rule_params ) ) {
									$lang_call_params = array_merge( $lang_call_params, $rule_params );
								}
								$this->add_error(
									$this::lang( $rule, $lang_call_params ),
									$field_name,
									$rule
								);
								if ( $rule == 'required' ) {
									continue 2;
								}
							}
						}
						else if ( is_callable( $rule ) ){
							call_user_func_array(
								$rule,
								array(
									$this,
									$field_value,
									$rule_params
								)
							);
						}

					}


				}

//				foreach ( $rules as $rule ) {
//					$params = $rule[ 1 ];
//					array_unshift( $params, $this->_fetch_array_item( $validation_array, $file_name ) );
//					if ( ! isset( $rule[ 0 ] ) ) {
//						continue;
//					}
//					if ( is_callable( $rule[ 0 ] ) ) {
//						call_user_func_array( $rule[ 0 ], array(
//							$this->_fetch_array_item( $validation_array, $file_name ),
//							$this
//						) );
//					} else if ( method_exists( $this, $rule[ 0 ] ) ) {
//						$rule_call = call_user_func_array( array( $this, $rule[ 0 ] ), $params );
//						if ( ! $rule_call ) {
//							$this->add_error(
//								$rule[ 0 ],
//								$file_name
//							);
////							if ( $rule[ 0 ] == 'required' ) {
////								continue 2;
////							}
//						}
//					} else {
//						continue;
//					}
//				}
			}

			return $this;
		}

		/**
		 * @return Phoenix_Framework_Form_Validation_Message
		 */
		function messages() {
			if ( ! $this->executed ) {
				$this->execute();
			}

			return new Phoenix_Framework_Form_Validation_Message($this);
		}

		/**
		 * Required
		 *
		 * @param    string
		 *
		 * @return    bool
		 */
		public function required( $str ) {
			return is_array( $str ) ? (bool) count( $str ) : ( trim( $str ) !== '' );
		}

		/**
		 * Performs a Regular Expression match test.
		 *
		 * @param           string
		 * @param    string $regex
		 *
		 * @return    bool
		 */
		public function regex_match( $str, $regex ) {
			return (bool) preg_match( $regex, $str );
		}

		/**
		 * Match one field to another
		 *
		 * @param    string $str string to compare against
		 * @param    string $field
		 *
		 * @return    bool
		 */
		public function matches( $str, $field ) {
			return isset( $this->validation_data[ $field ] )
				? ( $str === $this->validation_data[ $field ] )
				: false;
		}

		/**
		 * Differs from another field
		 *
		 * @param           string
		 * @param    string $field
		 *
		 * @return    bool
		 */
		public function differs( $str, $field ) {
			return ! ( isset( $this->validation_data[ $field ] ) && $this->validation_data[ $field ][ 'postdata' ] === $str );
		}

		/**
		 * Minimum Length
		 *
		 * @param    string
		 * @param    string
		 *
		 * @return    bool
		 */
		public function min_length( $str, $val ) {
			if ( ! is_numeric( $val ) ) {
				return false;
			}

			return $val <= strlen( $str );
		}

		/**
		 * Max Length
		 *
		 * @param    string
		 * @param    string
		 *
		 * @return    bool
		 */
		public function max_length( $str, $val ) {
			if ( ! is_numeric( $val ) ) {
				return false;
			}

			return $val >= strlen( $str );
		}

		/**
		 * Exact Length
		 *
		 * @param    string
		 * @param    string
		 *
		 * @return    bool
		 */
		public function exact_length( $str, $val ) {
			if ( ! is_numeric( $val ) ) {
				return false;
			}

			return strlen( $str ) === (int) $val;
		}

		/**
		 * Valid URL
		 *
		 * @param    string $str
		 *
		 * @return    bool
		 */
		public function valid_url( $str ) {
			if ( empty( $str ) ) {
				return false;
			} elseif ( preg_match( '/^(?:([^:]*)\:)?\/\/(.+)$/', $str, $matches ) ) {
				if ( empty( $matches[ 2 ] ) ) {
					return false;
				} elseif ( ! in_array( $matches[ 1 ], array( 'http', 'https' ), true ) ) {
					return false;
				}

				$str = $matches[ 2 ];
			}

			$str = 'http://' . $str;

			// There's a bug affecting PHP 5.2.13, 5.3.2 that considers the
			// underscore to be a valid hostname character instead of a dash.
			// Reference: https://bugs.php.net/bug.php?id=51192
			if ( version_compare( PHP_VERSION, '5.2.13', '==' ) OR version_compare( PHP_VERSION, '5.3.2', '==' ) ) {
				sscanf( $str, 'http://%[^/]', $host );
				$str = substr_replace( $str, strtr( $host, array( '_' => '-', '-' => '_' ) ), 7, strlen( $host ) );
			}

			return ( filter_var( $str, FILTER_VALIDATE_URL ) !== false );
		}

		/**
		 * Valid Email
		 *
		 * @param    string
		 *
		 * @return    bool
		 */
		public function valid_email( $str ) {
			if ( function_exists( 'idn_to_ascii' ) && $atpos = strpos( $str, '@' ) ) {
				$str = substr( $str, 0, ++ $atpos ) . idn_to_ascii( substr( $str, $atpos ) );
			}

			return (bool) filter_var( $str, FILTER_VALIDATE_EMAIL );
		}

		/**
		 * Valid Emails
		 *
		 * @param    string
		 *
		 * @return    bool
		 */
		public function valid_emails( $str ) {
			if ( strpos( $str, ',' ) === false ) {
				return $this->valid_email( trim( $str ) );
			}

			foreach ( explode( ',', $str ) as $email ) {
				if ( trim( $email ) !== '' && $this->valid_email( trim( $email ) ) === false ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Validate IP Address
		 *
		 * @param    string
		 *
		 * @return    bool
		 */
		public function valid_ip( $ip ) {
			return filter_var( $ip, FILTER_VALIDATE_IP ) === false;
		}

		/**
		 * Alpha
		 *
		 * @param    string
		 *
		 * @return    bool
		 */
		public function alpha( $str ) {
			return ctype_alpha( $str );
		}

		/**
		 * Alpha-numeric
		 *
		 * @param    string
		 *
		 * @return    bool
		 */
		public function alpha_numeric( $str ) {
			return ctype_alnum( (string) $str );
		}

		/**
		 * Alpha-numeric w/ spaces
		 *
		 * @param    string
		 *
		 * @return    bool
		 */
		public function alpha_numeric_spaces( $str ) {
			return (bool) preg_match( '/^[A-Z0-9 ]+$/i', $str );
		}

		/**
		 * Alpha-numeric with underscores and dashes
		 *
		 * @param    string
		 *
		 * @return    bool
		 */
		public function alpha_dash( $str ) {
			return (bool) preg_match( '/^[a-z0-9_-]+$/i', $str );
		}

		/**
		 * Numeric
		 *
		 * @param    string
		 *
		 * @return    bool
		 */
		public function numeric( $str ) {
			return (bool) preg_match( '/^[\-+]?[0-9]*\.?[0-9]+$/', $str );

		}

		/**
		 * Integer
		 *
		 * @param    string
		 *
		 * @return    bool
		 */
		public function integer( $str ) {
			return (bool) preg_match( '/^[\-+]?[0-9]+$/', $str );
		}

		/**
		 * Decimal number
		 *
		 * @param    string
		 *
		 * @return    bool
		 */
		public function decimal( $str ) {
			return (bool) preg_match( '/^[\-+]?[0-9]+\.[0-9]+$/', $str );
		}

		/**
		 * Greater than
		 *
		 * @param    string
		 * @param    int
		 *
		 * @return    bool
		 */
		public function greater_than( $str, $min ) {
			return is_numeric( $str ) ? ( $str > $min ) : false;
		}

		/**
		 * Equal to or Greater than
		 *
		 * @param    string
		 * @param    int
		 *
		 * @return    bool
		 */
		public function greater_than_equal_to( $str, $min ) {
			return is_numeric( $str ) ? ( $str >= $min ) : false;
		}

		/**
		 * Less than
		 *
		 * @param    string
		 * @param    int
		 *
		 * @return    bool
		 */
		public function less_than( $str, $max ) {
			return is_numeric( $str ) ? ( $str < $max ) : false;
		}

		/**
		 * Equal to or Less than
		 *
		 * @param    string
		 * @param    int
		 *
		 * @return    bool
		 */
		public function less_than_equal_to( $str, $max ) {
			return is_numeric( $str ) ? ( $str <= $max ) : false;
		}

		/**
		 * Is a Natural number  (0,1,2,3, etc.)
		 *
		 * @param    string
		 *
		 * @return    bool
		 */
		public function is_natural( $str ) {
			return ctype_digit( (string) $str );
		}

		/**
		 * Is a Natural number, but not a zero  (1,2,3, etc.)
		 *
		 * @param    string
		 *
		 * @return    bool
		 */
		public function is_natural_no_zero( $str ) {
			return ( $str != 0 && ctype_digit( (string) $str ) );
		}

		/**
		 * Valid Base64
		 *
		 * Tests a string for characters outside of the Base64 alphabet
		 * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
		 *
		 * @param    string
		 *
		 * @return    bool
		 */
		public function valid_base64( $str ) {
			return ( base64_encode( base64_decode( $str ) ) === $str );
		}

		public function email_exists( $email ) {
			return !email_exists( $email );
		}

		public function valid_username( $username ) {
			return validate_username( $username );
		}

		public function unique_username( $username ) {
			return username_exists( $username ) === null;
		}
	}
}

<?php
/**
 * class-phoenix-framework-db-expression
 *
 * a
 *
 * @class          Phoenix_framework_DB_Expression
 * @version        1.0
 * @package        BuddyPress (www\bp\)
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

if ( ! class_exists( 'Phoenix_framework_DB_Expression' ) ) {
	class Phoenix_framework_DB_Expression extends Phoenix_Framework_Object{
		/**
		 * @var string the DB expression
		 */
		public $expression;
		/**
		 * @var array list of parameters that should be bound for this expression.
		 * The keys are placeholders appearing in [[expression]] and the values
		 * are the corresponding parameter values.
		 */
		public $params = array( );

		/**
		 * Constructor.
		 *
		 * @param string $expression the DB expression
		 * @param array  $params     parameters
		 * @param array  $config     name-value pairs that will be used to initialize the object properties
		 */
		public function __construct( $expression, $params = array(), $config = array() ) {
			$this->expression = $expression;
			$this->params     = $params;
			parent::__construct( $config );
		}

		/**
		 * String magic method
		 *
		 * @return string the DB expression
		 */
		public function __toString() {
			return $this->expression;
		}
	}
}

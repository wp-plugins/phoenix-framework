<?php
/**
 * class-phoenix-framework-session
 *
 * a
 *
 * @class          Phoenix_Framework_Session
 * @version        1.0
 * @package        BuddyPress (www\bp\)
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

require_once dirname(__FILE__) . "/../../librearies/wp-session-manager/wp-session-manager.php";

if ( ! class_exists( 'Phoenix_Framework_Session' ) ) {
	class Phoenix_Framework_Session extends WP_Session{
		function __construct(){
			parent::__construct();
		}
	}
}

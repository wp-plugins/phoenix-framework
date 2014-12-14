<?php
/**
 * @class          Phoenix_Framework_Admin_Table
 * @version        1.0
 * @package        Phoenix Framework
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'Phoenix_Framework_Admin_Table' ) ) {
	class Phoenix_Framework_Admin_Table extends WP_List_Table {

		static function make( Array $params ) {

			$settings = array_merge(
				array(
					'ID'           => '',
					'get_data'     => '__return_empty_array',
					'columns'      => array(),
					'singular'     => '',
					'plural'       => '',
					'bulk_actions' => array()
				),
				$params
			);


			return new self( $settings );
		}


		protected $_settings;

		function __construct( $params ) {
			$this->_settings = $params;
			parent::__construct( array(
				'singular' => $this->_settings[ 'singular' ],
				'plural'   => $this->_settings[ 'plural' ],
				'ajax'     => false

			) );
		}


		function no_items() {
			_e( 'No item.' );
		}

		function column_default( $item, $column_name ) {
			if ( array_key_exists( $column_name, $this->_settings[ 'columns' ] ) ) {
				if ( isset( $this->_settings[ 'columns' ][ $column_name ][ 'callback' ] ) && is_callable( $this->_settings[ 'columns' ][ $column_name ][ 'callback' ] ) ) {
					return call_user_func( $this->_settings[ 'columns' ][ $column_name ][ 'callback' ], $item );
				} else {
					return $item[ $column_name ];
				}
			} else {
				return print_r( $item, true );
			}
		}

		function get_sortable_columns() {
			$cols = array();
			foreach ( $this->_settings[ 'columns' ] as $id => $column ) {
				if ( isset( $column[ 'sortable' ] ) && $column[ 'sortable' ] != false ) {
					$cols[ $id ] = array( is_string( $column[ 'sortable' ] ) ? $column[ 'sortable' ] : $id, false );
				}
			}

			return $cols;
		}

		function get_columns() {
			$columns = array(
				'cb' => '<input type="checkbox" />'
			);
			foreach ( $this->_settings[ 'columns' ] as $id => $column ) {
				$columns[ $id ] = $column[ 'label' ];
			}

			return $columns;
		}


		function process_bulk_action() {
			$ca = (string) $this->current_action();
			if ( array_key_exists( $ca, $this->_settings[ 'bulk_actions' ] ) && is_callable( $this->_settings[ 'bulk_actions' ][ $ca ][ 'callback' ] ) ) {
				echo '<div class="wrap">';
				call_user_func( $this->_settings[ 'bulk_actions' ][ $ca ][ 'callback' ] );
				echo '</div>';
			}
		}

		function get_bulk_actions() {
			$actions = array();
			foreach ( $this->_settings[ 'bulk_actions' ] as $action_id => $action ) {
				$actions[ $action_id ] = isset( $action[ 'label' ] ) ? $action[ 'label' ] : $action_id;
			}

			return $actions;
		}

		function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="' . $this->_settings[ 'ID' ] . '[]" value="%s" />', $item[ 'ID' ]
			);
		}

		function display() {
			echo '<form method="get">';
			echo '<input type="hidden" name="page" value="' . $_REQUEST[ 'page' ] . '"/>';
			$this->search_box( __( 'search' ), '_search' );
			parent::display();
			echo '</form>';
		}

		function prepare_items() {
			$columns               = $this->get_columns();
			$hidden                = array();
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );

			$this->process_bulk_action();

			$orders = array( 'asc', 'desc' );
			$data   = call_user_func(
				$this->_settings[ 'get_data' ],
				array(
					'page_num' => $this->get_pagenum(),
					'order'    => isset( $_GET[ 'order' ] ) && in_array( strtolower( $_GET[ 'order' ] ), $orders ) ? strtolower( $_GET[ 'order' ] ) : 'asc',
					'orderby'  => isset( $_GET[ 'orderby' ] ) && is_string( $_GET[ 'orderby' ] ) ? $_GET[ 'orderby' ] : false,
					's'        => ! empty( $_REQUEST[ 's' ] ) ? $_REQUEST[ 's' ] : false
				)
			);

			$per_page    = isset( $this->_settings[ 'per_page' ] ) ? $this->_settings[ 'per_page' ] : 10;
			$total_items = isset( $this->_settings[ 'total_items' ] ) ? $this->_settings[ 'total_items' ] : count( $data );


			$this->set_pagination_args( array(
				'total_items' => $total_items,                  //WE have to calculate the total number of items
				'per_page'    => $per_page                     //WE have to determine how many items to show on a page
			) );

			$this->items = $data;
		}


	}
}

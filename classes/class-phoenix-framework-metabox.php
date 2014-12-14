<?php
	/**
	 * class-phoenix-framework-metabox
	 *
	 * a
	 *
	 * @class          Phoenix_Framework_Metabox
	 * @version        1.0
	 * @package        BuddyPress (www\bp\)
	 * @category       Class
	 * @author         Vahidd
	 */

	defined( 'ABSPATH' ) or die; // Prevents direct access

	if ( ! class_exists( 'Phoenix_Framework_Metabox' ) ) {
		class Phoenix_Framework_Metabox {

			/**
			 * @var Phoenix_Framework_Form_Builder
			 */
			var $form_builder;

			/**
			 * @var Phoenix_Framework
			 */
			static $phoenix;

			private $_id, $_metabox_content, $_callback;

			public
				$title = '',
				$prefix = '',
				$post_types = array( 'post', 'page' ),
				$save_in_single = true;

			static public function make( $id, $callback ) {

				global $pagenow;

				if ( ! is_callable( $callback ) ) {
					throw new Exception( sprintf( 'Uncallable callback for %s', __METHOD__ ) );
				}

				if ( in_array( $pagenow, array( 'post-new.php', 'post.php' ) ) ) {
					phoenix()->loadAssets( true );
				}

				return new self( $id, $callback );
			}


			protected function __construct( $id, $callback ) {
				$this->_id          = $id;
				$this->form_builder = new Phoenix_Framework_Form_Builder(
					array(
						'field_name_generator' => array( $this, 'field_name_generator' )
					)
				);
				$this->_callback    = $callback;
				$this->title        = $id;
				add_action( 'add_meta_boxes', array( $this, 'add' ) );
				add_action( 'save_post', array( $this, 'save' ) );
			}

			function field_name_generator( $field_id ) {
				return sprintf( '_phoenix_metabox_%s[%s]', $this->_id, $field_id );
			}

			function add() {
				$vals = $this->get_values();
				$this->form_builder->set_values( $vals );
				ob_start();
				call_user_func( $this->_callback, $this );
				$this->_metabox_content = ob_get_clean();
				foreach ( (array) $this->post_types as $post_type ) {
					add_meta_box(
						$this->_id,
						$this->title,
						array( $this, 'callback' ),
						$post_type
					);
				}
			}

			function callback() {
				printf(
					'<div class="%s">%s</div>',
					'phoenix-metabox-inner',
					$this->_metabox_content
				);
			}

			function get_values() {
				global $post;
				if ( $this->save_in_single ) {
					$items = get_post_meta( $post->ID, $this->prefix . $this->_id, true );

					return $items;
				} else {
					$fields = $this->form_builder->get_fields_list();
					$output = array();
					foreach ( $fields as $field ) {
						$output[ $field ] = get_post_meta( $post->ID, $this->prefix . $field, true );
					}

					return $output;
				}
			}

			function save( $post_id ) {
				if ( ! empty( $_POST[ '_phoenix_metabox_' . $this->_id ] ) && is_array( $_POST[ '_phoenix_metabox_' . $this->_id ] ) ) {

					$data = $_POST[ '_phoenix_metabox_' . $this->_id ];

					if ( $this->save_in_single ) {

						update_post_meta( $post_id, $this->prefix . $this->_id, $data );

					} else {

						foreach ( $data as $to_save_key => $val ) {

							update_post_meta( $post_id, $this->prefix . $to_save_key, $val );

						}

					}


				}
			}

		}
	}

<?php
	/**
	 * Taxonomy Meta
	 *
	 * @class          Phoenix_Framework_Taxonomy_Meta
	 * @version        1.0
	 * @package        Phoenix Framework
	 * @category       Class
	 * @author         Vahidd
	 */

	defined( 'ABSPATH' ) or die; // Prevents direct access

	class Phoenix_Framework_Taxonomy_Meta {

		static public function make( $id, $taxonomies, $callback ) {

			if ( ! is_callable( $callback ) ) {
				throw new Phoenix_Framework_Exception(
					__( 'Callback for %s is not callable.' ),
					__METHOD__
				);
			}

			if ( ! is_string( $id ) && ! is_numeric( $id ) ) {
				throw new Phoenix_Framework_Exception(
					__( 'The ID parameter for %s should be a string or an integer.' ),
					__METHOD__
				);
			}

			global $pagenow;
			settype( $taxonomies, 'array' );
			if ( $pagenow === 'edit-tags.php' && isset( $_REQUEST[ 'taxonomy' ] ) && in_array( $_REQUEST[ 'taxonomy' ], $taxonomies ) ) {
				phoenix()->loadAssets( true );
			}

			return new self( $id, $taxonomies, $callback );
		}

		/**
		 * @var Phoenix_Framework_Form_Builder
		 */
		public
			$formBuilder;

		protected
			$id,
			$taxonomies,
			$callback;

		protected function __construct( $id, array $taxonomies, $callback ) {
			$this->id       = $id;
			$this->callback = $callback;

			foreach ( $taxonomies as $tax ) {
				add_action( $tax . '_add_form_fields', array( $this, 'displayAddForm' ) );
				add_action( $tax . '_edit_form_fields', array( $this, 'displayEditForm' ) );
				add_action( "edited_$tax", array( $this, 'handleTermEdit' ) );
				add_action( "created_$tax", array( $this, 'handleTermInsert' ) );
				add_action( "delete_$tax", array( $this, 'handleTermDelete' ) );
			}


		}

		static public function generateOptionName( $id ) {
			return apply_filters( 'phoenix_framework_taxonomy_meta_option_name', sprintf( '_phoenix_term_%s_meta', $id ), $id );
		}

		static public function addFormFieldsWrapper( Phoenix_Framework_Form_Field_Type_Base $field ) {
			$type = get_class( $field );
			$type = ltrim( rtrim( $type, '_Field_Type' ), 'Phoenix_Framework_' );
			$type = strtolower( $type );

			return '<div class="form-field phoenix-form-group ' . $type . '">
	<label for="' . $field->settings[ 'inputName' ] . '">' . $field->settings[ 'label' ] . '</label>
	' . $field->display() . '
	'.( !empty( $field->settings['desc'] ) ? '<p>'.$field->settings['desc'].'</p>' : '' ).'
</div>';
		}

		static public function editFormFieldsWrapper( Phoenix_Framework_Form_Field_Type_Base $field ) {
			$type = get_class( $field );
			$type = ltrim( rtrim( $type, '_Field_Type' ), 'Phoenix_Framework_' );
			$type = strtolower( $type );

			return '<tr class="form-field phoenix-form-group ' . $type . '">
			<th scope="row"><label for="' . $field->settings[ 'inputName' ] . '">' . $field->settings[ 'label' ] . '</label></th>
						<td>' . $field->display() . '
						'.( !empty( $field->settings['desc'] ) ? '<p class="description">'.$field->settings['desc'].'</p>' : '' ).'
						</td>
		</tr>';
		}

		public function inputNameGenerator( $id ) {
			return apply_filters( 'phoenix_framework_taxonomy_meta_input_name', '_phoenix_tax_meta[' . $id . ']', $id );
		}

		/**
		 * @param $taxonomyName
		 */
		public function displayAddForm( $taxonomyName ) {

			$this->formBuilder = new Phoenix_Framework_Form_Builder(
				array(
					'field_callback'       => array( __CLASS__, 'addFormFieldsWrapper' ),
					'field_name_generator' => array( $this, 'inputNameGenerator' )
				)
			);

			do_action( 'phoenix_taxonomy_meta_before_add_form', $taxonomyName );

			call_user_func( $this->callback, $this );

			do_action( 'phoenix_taxonomy_meta_after_add_form', $taxonomyName );

		}

		public function displayEditForm( $taxonomyObject ) {
			$this->formBuilder = new Phoenix_Framework_Form_Builder(
				array(
					'field_callback'       => array( __CLASS__, 'editFormFieldsWrapper' ),
					'field_name_generator' => array( $this, 'inputNameGenerator' ),
					'values'               => get_option( $this::generateOptionName( $taxonomyObject->term_id ), array() )
				)
			);

			do_action( 'phoenix_taxonomy_meta_before_edit_form', $taxonomyObject );


			call_user_func( $this->callback, $this );

			do_action( 'phoenix_taxonomy_meta_after_edit_form', $taxonomyObject );

		}

		public function handleTermEdit( $termId ) {
			if ( ! empty( $_POST[ '_phoenix_tax_meta' ] ) && is_array( $_POST[ '_phoenix_tax_meta' ] ) ) {
				update_option( $this::generateOptionName( $termId ), $_POST[ '_phoenix_tax_meta' ] );
				do_action( 'phoenix_taxonomy_meta_term_update', $termId );
			}
		}

		public function handleTermInsert( $termId ) {
			if ( ! empty( $_POST[ '_phoenix_tax_meta' ] ) && is_array( $_POST[ '_phoenix_tax_meta' ] ) ) {
				add_option( $this::generateOptionName( $termId ), $_POST[ '_phoenix_tax_meta' ] );
				do_action( 'phoenix_taxonomy_meta_term_insert', $termId );
			}
		}

		public function handleTermDelete( $termId ) {
			delete_option( $this::generateOptionName( $termId ) );
			do_action( 'phoenix_taxonomy_meta_term_delete', $termId );
		}

	}

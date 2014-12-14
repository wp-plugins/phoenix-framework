<?php
	/**
	 * @class          Phoenix_Framework_File_Uploader
	 * @version        1.0
	 * @package        Phoenix Framework
	 * @category       Class
	 * @author         Vahidd
	 */

	defined( 'ABSPATH' ) or die; // Prevents direct access

	if ( ! class_exists( 'Phoenix_Framework_File_Uploader' ) ) {
		class Phoenix_Framework_File_Uploader {

			/**
			 * @var array settings
			 */
			protected $_settings;

			public
				$tmp_name;

			public function __construct( $input ) {
				$this->_settings[ 'input' ] = $input;
			}

			public function move( $target ) {
				if ( ! is_writable( $target ) ) {
					throw new Exception(
						'The directory specified, %s, is not writable',
						$target
					);
				}
			}

			/**
			 * Sets the maximum size the uploaded file may be
			 *
			 * This method should be used with the
			 * [http://php.net/file-upload.post-method `MAX_FILE_SIZE`] hidden form
			 * input since the hidden form input will reject a file that is too large
			 * before the file completely uploads, while this method will wait until the
			 * whole file has been uploaded. This method should always be used since it
			 * is very easy for the `MAX_FILE_SIZE` post field to be manipulated on the
			 * client side.
			 *
			 * This method can only further restrict the
			 * [http://php.net/upload_max_filesize `upload_max_filesize` ini setting],
			 * it can not increase that setting. `upload_max_filesize` must be set
			 * in the php.ini (or an Apache configuration) since file uploads are
			 * handled before the request is handed off to PHP.
			 *
			 * @param  string $size The maximum file size (e.g. `1MB`, `200K`, `10.5M`) - `0` for no limit
			 *
			 * @throws Exception
			 * @return $this
			 */
			public function setMaxSize( $size ) {
				$ini_max_size = ini_get( 'upload_max_filesize' );
				$ini_max_size = ( ! is_numeric( $ini_max_size ) ) ? self::convertToBytes( $ini_max_size ) : $ini_max_size;
				$size         = self::convertToBytes( $size );
				if ( $size && $size > $ini_max_size ) {
					throw new Exception(
						'The requested max file upload size, %1$s, is larger than the %2$s ini setting, which is currently set at %3$s. The ini setting must be increased to allow files of this size.',
						$size,
						'upload_max_filesize',
						$ini_max_size
					);
				}
				$this->_settings[ 'max_size' ] = $size;

				return $this;
			}

			/**
			 * Get file size
			 *
			 * @return int
			 */
			protected function getFileSize() {
				return filesize( $this->tmp_name );
			}

			/**
			 * Convert bytes to mb.
			 *
			 * @param int $bytes
			 *
			 * @return int
			 */
			static function bytesToMb( $bytes ) {
				return round( ( $bytes / 1048576 ), 2 );
			}

			/**
			 * Checks whether Files post array is valid
			 *
			 * @param $file
			 *
			 * @return bool
			 */
			static function check_file_array( $file ) {
				return isset( $file[ 'error' ] )
				       && ! empty( $file[ 'name' ] )
				       && ! empty( $file[ 'type' ] )
				       && ! empty( $file[ 'tmp_name' ] )
				       && ! empty( $file[ 'size' ] );
			}


			/**
			 * Takes a file size including a unit of measure (i.e. kb, GB, M) and converts it to bytes
			 *
			 * Sizes are interpreted using base 2, not base 10. Sizes above 2GB may not
			 * be accurately represented on 32 bit operating systems.
			 *
			 * @param  string $size The size to convert to bytes
			 *
			 * @throws Exception
			 * @return int The number of bytes represented by the size
			 */
			static function convertToBytes( $size ) {
				if ( ! preg_match( '#^(\d+(?:\.\d+)?)\s*(k|m|g|t)?(ilo|ega|era|iga)?( )?b?(yte(s)?)?$#D', strtolower( trim( $size ) ), $matches ) ) {
					throw new Exception(
						'The size specified, %s, does not appears to be a valid size',
						$size
					);
				}
				if ( empty( $matches[ 2 ] ) ) {
					$matches[ 2 ] = 'b';
				}
				$size_map = array(
					'b' => 1,
					'k' => 1024,
					'm' => 1048576,
					'g' => 1073741824,
					't' => 1099511627776
				);

				return round( $matches[ 1 ] * $size_map[ $matches[ 2 ] ] );
			}

			static function check( $field, $throw_exception = true ) {
				if ( isset( $_GET[ $field ] ) && $_SERVER[ 'REQUEST_METHOD' ] != 'POST' ) {
					if ( $throw_exception ) {
						throw new Exception(
							'Missing method="post" attribute in form tag'
						);
					}

					return false;
				}
				if ( isset( $_POST[ $field ] ) && ( ! isset( $_SERVER[ 'CONTENT_TYPE' ] ) || stripos( $_SERVER[ 'CONTENT_TYPE' ], 'multipart/form-data' ) === false ) ) {
					if ( $throw_exception ) {
						throw new Exception(
							'Missing enctype="multipart/form-data" attribute in form tag'
						);
					}

					return false;
				}

				return isset( $_FILES ) && isset( $_FILES[ $field ] ) && is_array( $_FILES[ $field ] );
			}


		}
	}

<?php
	/**
	 * Theme Helper Class
	 *
	 * @class          Phoenix_Framework_Theme_Helper
	 * @version        1.0
	 * @package        Phoenix Framework
	 * @category       Class
	 * @author         Vahidd
	 */

	defined( 'ABSPATH' ) or die; // Prevents direct access

	if ( ! class_exists( 'Phoenix_Framework_Theme_Helper' ) ) {
		class Phoenix_Framework_Theme_Helper {

			/**
			 * Retrieve image URL of given post or current post
			 *
			 * @param string $thumbSize
			 * @param null   $post
			 *
			 * @return bool|string
			 */
			public static function getPostThumbnailSrc( $thumbSize = 'full', $post = null ) {
				if ( ! $thumbSize ) {
					$thumbSize = 'full';
				}
				$post  = get_post( $post );
				$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $thumbSize );

				return $thumb ? $thumb[ 0 ] : false;
			}

			/**
			 * @param string $pageLabel
			 */
			public static function title( $pageLabel = 'Page %s' ) {
				global $page, $paged;
				wp_title( '|', true, 'right' );
				bloginfo( 'name' );
				$site_description = get_bloginfo( 'description', 'display' );
				if ( $site_description && ( is_home() || is_front_page() ) ) {
					echo " | $site_description";
				}
				if ( $paged >= 2 || $page >= 2 ) {
					echo ' | ' . sprintf( $pageLabel, max( $paged, $page ) );
				}
			}


			/**
			 * @param array $args
			 *
			 * @return WP_Query
			 */
			public static function relatedPosts( $args = array() ) {

				$args = array_merge(
					array(
						'type'           => 'tag',
						'limit'          => 5,
						'thumbnail_size' => 'thumbnail'
					),
					$args
				);

				$queryArgs = array(
					'post__not_in'   => array( get_the_ID() ),
					'posts_per_page' => $args[ 'limit' ],
					'no_found_rows'  => true
				);
				if ( $args[ 'type' ] == 'category' ) {
					$cats     = get_the_category();
					$postCats = array();
					foreach ( $cats as $cat ) {
						$postCats[ ] = $cat->term_id;
					}
					$queryArgs[ 'category__in' ] = $postCats;
				} else {
					$tags     = get_the_tags();
					$postTags = array();
					foreach ( $tags as $tag ) {
						$postTags[ ] = $tag->term_id;
					}
					$queryArgs[ 'tag__in' ] = $postTags;

				}


				return new WP_Query(
					$queryArgs
				);

			}

		}
	}

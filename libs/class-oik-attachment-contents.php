<?php
if ( !defined( 'CLASS_OIK_ATTACHMENT_CONTENTS_INCLUDED' ) ) {
	define( 'CLASS_OIK_ATTACHMENT_CONTENTS_INCLUDED', '0.0.3' );

	/**
	 * Class Oik_attachment_contents
	 * @copyright (C) Copyright Bobbing Wide 2021, 2023
	 * @package oik-libs
	 */
	class Oik_attachment_contents {

		private $file = null;
		private $content = null;
		private $contents_array = null;
		private $key = null;

		function __construct() {
			$this->file = null;
			$this->content = null;
			$this->contents_array = null;
			$this->key = 'src';
		}

		function set_key( $key ) {
			$this->key = $key;
		}

		/**
		 * Returns the
		 * @param $atts
		 * @param $content
		 * @param $tag
		 */
		function get_contents_array( $atts, $content ) {
			$this->get_content( $atts, $content );
			$this->get_contents();
			return $this->contents_array;
		}

		/**
		 * Populates $this->content.
		 *
		 * @param $atts
		 * @param $content
		 */
		function get_content( $atts, $content ) {
			$this->content = null;
			bw_trace2();
			if ( !empty( $content ) ) {
				$this->content=$content;
			} else {
				$src = bw_array_get_from( $atts, $this->key, null );
				if ( $src ) {
					$this->load_content( $src );
				}
			}
			return $this->content;
		}

		/**
		 * Populates $this->contents_array from $this->content
		 */
		function get_contents() {
			$content=$this->content;
			if ( null === $content ) {
				$content_array=[''];
			} else {
				$content=str_replace( '\n', "\n", $content );
				$content=str_replace( "<br />\n", "\n", $content );
				$content=rtrim( $content );
				bw_trace2( $content, "content", false );
				$content_array=explode( "\n", $content );
				bw_trace2( $content_array, "content_array", false );
			}
			$this->contents_array = $content_array;
		}

		/**
		 * Loads content given the $src
		 *
		 * In bw_csv the logic attempted to use file() to load the file given the attachment URL.
		 * This may not work in a development environment where SSL isn't quite right.
		 * So, if we need to get a file name given a local URL, we need
		 * to replace the base_URL with the base_dir
		 *
		 * @param $src integer | string
		 */
		function load_content( $src ) {
			if ( is_numeric( $src ) ) {
				$this->file = get_attached_file( $src );
			} else {
				$this->file = $this->get_file_name( $src );
			}
			if ( $this->file ) {
				if ( file_exists( $this->file ) ) {
					//e( "Loading CSS from: " . $this->file );
					//e( ABSPATH );
					$this->content=file_get_contents( $this->file );
					//e( strlen( $this->content ) );
					//e( esc_html( $this->content ));
				} else {
					$this->content = $this->file;
					e( "File does not exist: " . $this->file );
				}
			} else {
				//e( "Invalid request");
				//$this->content = "Invalid request";
			}
		}

		/**
		 * Returns the filename if it's local.
		 *
		 * src | processing
		 * ---- | ----------
		 * URL | attempt to convert to local file name
		 * fully qualified file | use given file name
		 * starts '/wp-' | prepend ABSPATH without the trailing slash
		 *
		 * Note: The file name is validated to prevent directory traversal.
		 * and accidental display of wp-config.php
		 * Any other files are considered fair game.
		 * We'll try to rely on the user's common sense.
		 *
		 * @param $src string
		 * @return array|mixed|string|string[]
		 */
		function get_file_name( $src ) {
			$file_name = $src;
			$uploads = wp_get_upload_dir();
			if ( false === $uploads['error'] ) {
				$file_name = str_replace( $uploads['baseurl'], $uploads['basedir'], $src );
			}
			if ( 0 === strpos( $file_name, '/wp-' ) ) {
				$validated = validate_file( $file_name );
				if ( 0 === $validated && $file_name !== '/wp-config.php' ) {
					$file_name=untrailingslashit( ABSPATH ) . $file_name;
				} else {
					e( "File name not allowed: " . $file_name );
					return null;
				}
			}
			bw_trace2( $file_name, "file_name", false );
			return $file_name;
		}
	}
}
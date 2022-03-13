<?php
namespace oik\oik_blocks;

if ( ! defined( 'OIK_BLOCKS_INCLUDED' ) ) {
	define( 'OIK_BLOCKS_INCLUDED', '1.2.0' );
	define( 'OIK_BLOCKS_FILE', __FILE__ );

	/**
	 * The oik-blocks shared library for Gutenberg blocks.
	 *
	 * @copyright (C) Bobbing Wide 2019, 2020, 2022
	 * Library: oik-blocks
	 * Depends: oik_boot, bobbfunc
	 * Provides: oik-blocks
	 */

	/**
	 * Registers the frontend block styles for a plugin
	 *
	 * We expect the CSS for all blocks to be combined into one file in the $stylepath file.
	 *
	 * @param  string $plugin plugin slug.
	 */
	function oik_blocks_register_block_styles( $plugin ) {
		$style_path = 'blocks/build/css/blocks.style.css';
		wp_enqueue_style(
			$plugin . '-blocks-css',
			oik_url( $style_path, $plugin ),
			[],
			filemtime( oik_path( $style_path, $plugin ) )
		);
	}

	/**
	 * Checks if the server function is available
	 *
	 * Returns null if everything is OK, HTML if there's a problem.
	 *
	 * @TODO Check if the implementing plugin is actually activated!
	 *
	 * @param string $filename - relative path for the file to load.
	 * @param string $plugin - plugin slug.
	 * @param string $funcname - required function name.
	 *
	 * @return string| null
	 */
	function oik_blocks_check_server_func( $filename, $plugin, $funcname ) {
		$html = null;
		if ( is_callable( $funcname ) ) {
			return $html;
		}
		if ( $filename && $plugin ) {
			$path = oik_path( $filename, $plugin );
			if ( file_exists( $path ) ) {
				require_once $path;
			}
		}
		if ( ! is_callable( $funcname ) ) {
			$html = "Server function $funcname not available. <br />Check $plugin is installed and activated.";
		}
		return $html;
	}

	/**
	 * Registers the scripts we'll need for the editor
	 *
	 * Not sure why we'll need Gutenberg scripts for the front-end.
	 * But we might need Javascript stuff for some things, so these can be registered here.
	 *
	 * Dependencies were initially
	 * `[ 'wp-i18n', 'wp-element', 'wp-blocks', 'wp-components', 'wp-api' ]`
	 *
	 * why do we need the dependencies?
	 *
	 * @param string $plugin plugin slug.
	 * @param string $domain plugin's domain path.
	 */
	function oik_blocks_register_editor_scripts( $plugin, $domain ) {
		$scripts = array( $plugin . '-blocks-js' => 'blocks/build/js/editor.blocks.js' );
		foreach ( $scripts as $name => $block_path ) {
			wp_register_script(
				$name,
				oik_url( $block_path, $plugin ),
				[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wp-i18n', 'wp-data' ],
				filemtime( oik_path( $block_path, $plugin ) )
			);
			wp_set_script_translations( $name, $domain );
		}
	}

	/**
	 * Generates a block editor block.
	 *
	 * @param string     $block_type_name e.g. core/paragraph.
	 * @param array|null $atts array of block attributes.
	 * @param string|null $content block content
	 *
	 * @return string
	 */
	function oik_blocks_generate_block( $block_type_name, $atts = null, $content = null ) {
		$block = oik_blocks_generate_block_start( $block_type_name, $atts, $content );
		$block .= oik_blocks_generate_block_end( $block_type_name );
		return $block;
	}

	/**
	 * Generates the start of a block.
	 *
	 * @param $block_type_name
	 * @param null $atts
	 * @param null $content
	 *
	 * @return string
	 */
	function oik_blocks_generate_block_start( $block_type_name, $atts = null, $content = null ) {
		$block = "<!-- wp:$block_type_name ";
		if ( $atts ) {
			$block.=$atts;
			$block.=' ';
		}
		$block.='-->';
		$block.="\n";
		if ( $content ) {
			$block.=$content;
			$block.="\n";
		}
		return $block;
	}

	/**
	 * Generates the end of a block.
	 *
	 * @param $block_type_name
	 *
	 * @return string
	 */
	function oik_blocks_generate_block_end( $block_type_name ) {
		$block ="<!-- /wp:$block_type_name -->";
		$block.="\n\n";
		return $block;
	}

	/**
	 * Encodes attributes for a block.
	 *
	 * @param array $atts Attributes to be JSON encoded.
 	 * @return string|null JSON encoded attributes
	 */
	function oik_blocks_atts_encode( $atts ) {
		if ( null !== $atts ) {
			$block_atts = json_encode( $atts, JSON_UNESCAPED_SLASHES );
		} else {
			$block_atts = null;
		}
		return $block_atts;
	}

	/**
	 * Unsets or trims an attribute to make it shortcode compatible.
	 *
	 * How do we cater for parameters that allow the value to be ' ' ?
	 * Perhaps we simply don't call this routine.
	 *
	 * @param array $attributes Array of attributes
	 * @param string $key key of the attribute to check
	 *
	 * @return mixed
	 */
	function oik_blocks_attribute_unset_or_trim( $attributes, $key ) {
		$value = bw_array_get( $attributes, $key, null );
		$value = trim( $value );
		if ( '' === $value ) {
			unset( $attributes[ $key ] );
		} else {
			$attributes[ $key ] = $value;
		}
		return $attributes;
	}
}
<?php
if ( !defined( 'CLASS_OIK_SVG_ICONS_INCLUDED' ) ) {
    define( 'CLASS_OIK_SVG_ICONS_INCLUDED', '0.1.0');

    /**
     * @copyright (C) Copyright Bobbing Wide 2016-2022
     * @package oik, oik-libs
     *
     * Implement SVG icons to replace dashicons and genericons
     *
     * Most of the SVG icons are sourced from Gutenberg's icons library,
     * which is JavaScript code.
	 *
	 * Usage:
	 * ```
	 * oik_require_lib( 'class-oik-svg-icons' );
	 * $svgicons = new OIK_svg_icons();
	 * $dash = $svgicons->get_icon( "menu", "" );
     * ```
     */
    class OIK_SVG_icons {
	    private static $svgicons;
	    /**
	     * @var OIK_SVG_icons - the true instance
	     */
	    private static $instance;

	    /**
	     * Return a single instance of this class
	     *
	     * @return object
	     */
	    public static function instance() {
		    if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
			    self::$instance=new self;
		    }

		    return self::$instance;
	    }

	    function __construct() {
		    $this->list_svg_icons();
	    }

	    function get_icons() {
		    return self::$svgicons;
	    }

	    /**
	     * Returns the SVG for the chosen icon.
	     *
	     * If the icon's not found it should return the default... menu?
	     *
	     * @param $icon
	     * @param $class
	     *
	     * @return string
	     */
	    function get_icon( $icon, $class, $size=24 ) {
		    //oik_require( "shortcodes/oik-dash.php", "oik-bob-bing-wide" );
		    //oik_require( "shortcodes/oik-dash-svg-list.php", "oik-bob-bing-wide" );
		    //$svgicons = bw_dash_list_svg_icons();
		    //echo count( self::$svgicons );
		    //gob();
		    $dpath=bw_array_get( self::$svgicons, $icon, null );
		    if ( ! $dpath ) {
			    //$dpath = bw_array_get( self::$svgicons, 'menu', null );
		    }
		    $dash=null;
		    if ( $dpath ) {
			    bw_push();
			    $this->svg_icon( $icon, "svg", $class, $dpath, $size );
			    $dash=bw_ret();
			    bw_pop();
		    }

		    return $dash;

	    }

	    /**
	     * Displays an SVG icon
	     * Duplicates what's done in the new editor
	     *
	     * `
	     * <svg aria-hidden
	     * role="img"
	     * focusable="false"
	     * className={ className }
	     * xmlns="http://www.w3.org/2000/svg"
	     * width={ size }
	     * height={ size }
	     * viewBox="0 0 20 20"
	     * >
	     * <path d={ path } />
	     * </svg>
	     * `
	     *
	     * @param string $icon Icon name - e.g. button
	     * @param string $font_class SVG
	     * @param string $class Additional CSS class - can be used to override width, height and viewBox
	     * @param string $dpath - All the SVG stuff
	     */
	    function svg_icon( $icon, $font_class, $class, $dpath, $size=24 ) {
		    if ( '<' === $dpath[0] ) {
			    $classes="svg_$icon $font_class $class";
			    $this->svg_icon_raw( $dpath, $classes, $size );
		    } else {

			    $svg=null;
			    $svg.=kv( "role", 'img' );
			    $svg.=kv( "focusable", "false" );
			    //$svg .= kv( "className", $font_class ); // needed?
			    $svg.=kv( "xmlns", "http://www.w3.org/2000/svg" );
			    $svg.=kv( "width", $size );
			    $svg.=kv( "height", $size );
			    $svg.=kv( "viewBox", "0 0 24 24" );
			    // Prefix the icon name with svg_ to avoid unwanted CSS styling on icons such as button.
			    stag( "svg aria-hidden", "svg_$icon $font_class $class", null, $svg );
			    $this->svg_icon_dpath( $dpath );
			    etag( "svg" );
		    }

	    }

	    /**
	     * Creates a `<path>` tag for the SVG icon
	     */
	    function svg_icon_dpath( $dpath ) {
		    $kv=kv( "d", $dpath );
		    bw_echo( "<path" . $kv . " />" );
	    }

	    function svg_icon_raw( $dpath, $classes, $size=24 ) {
		    bw_trace2();
		    $svg  =null;
		    $svg  .=kv( "width", $size );
		    $svg  .=kv( "height", $size );
		    $dpath=str_ireplace( '<svg', "<svg class=\"$classes\" $svg", $dpath );
		    bw_trace2( $dpath, "dpath", null );
		    //gob();
		    bw_echo( $dpath );
	    }

	    /**
	     * List SVG icons
	     *
	     * Code originally copied and cobbled from gutenberg/components/dashicon/index.js
	     * and restructured to return the list of all SVG icons.
	     *
	     * Sources for "icons" are
	     * - gutenberg/packages/block-library/src/social-link/icons - social link icons
	     * - gutenberg/packages/icons/src/library - icons in the WordPress icons library
	     * - https://github.com/WordPress/dashicons/tree/master/sources/svg - orginal dashicons
	     * -
	     * Some Blocks ( e.g. archives ) have their own SVG which isn't just the contents of the path d parameter.
	     * Originally I thought we needed to copy all the HTML within the SVG from the icon: attribute.
	     * But now we have a Block icon, we do not need to do this.
	     * The dashicon for archive has a different appearance from the block icon.
	     *
	     */
	    function list_svg_icons() {
		    if ( ! function_exists( 'bw_dash_list_svg_icons' ) ) {
			    oik_require_lib( 'oik-dash-svg-list' );
		    }
		    self::$svgicons=bw_dash_list_svg_icons();
		    //print_r( self::$svgicons );
		    // This doesn't return anything.
	    }

    }
}

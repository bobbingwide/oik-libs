<?php // (C) Copyright Bobbing Wide 2015-2019
if ( !defined( "OIK_AUTOLOAD_INCLUDED" ) ) {
define( "OIK_AUTOLOAD_INCLUDED", "0.1.0" );

/**
 * Autoload library functions
 *
 * Library: oik-autoload
 * Provides: oik-autoload
 * Type: Shared 
 *
 * Implements logic to enable PHP classes to be autoloaded
 * taking into account the libraries that are being used.
 * 
 */
 
/**
 * Force "autoload" of a class
 *
 * @param string $class - the class to be loaded
 * @param array $args
 */
function oik_require_class( $class, $args=null ) {
	bw_trace2( null, null, true, BW_TRACE_DEBUG );
	$oik_autoload = oik_autoload();
	bw_trace2( $oik_autoload, "oik_autoload", false, BW_TRACE_VERBOSE );
	$oik_autoload->autoload( $class );
	//bw_trace2( "done?" );
}

/**
 * Load the OIK_Autoload logic
 * 
 * You might think that the fact that you invoke oik_require_lib( "oik_autoload" ); 
 * would be enough to tell the autoload library that you'll be using autoloading for your classes.
 * But I think it's better to implicitely invoke either oik_require_class() or oik_autoload() to instantiate the
 * autoloading logic when you know that OO code will be used.
 * 
 * Notice we use oik_require_file() to load a class file manually
 */
function oik_autoload() {
	if ( !class_exists( "OIK_Autoload" ) ) {
		oik_require_file( "class-oik-autoload.php", "oik-autoload" );
	}
	if ( class_exists( "OIK_Autoload" ) ) {
		$oik_autoload = OIK_Autoload::instance();
	} else {
		bw_trace2( "Class OIK_Autoload does not exist", null, false, BW_TRACE_ERROR );
		die();
	}
	return( $oik_autoload );
}

} /* end !defined */

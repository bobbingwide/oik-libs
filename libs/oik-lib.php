<?php // (C) Copyright Bobbing Wide 2015
if ( !defined( 'OIK_LIB_INCLUDED' ) ) {
define( 'OIK_LIB_INCLUDED', "0.1" );

/**
 * oik library management functions
 * 
 * Library: oik-lib
 * Provides: oik-lib
 * Depends: oik_boot, bwtrace
 *
 * These are the functions that implement shared library logic that enable
 * plugins and themes to define their dependencies on other plugins and/or library functions.
 * These functions are loaded as WordPress starts up.
 * 
 * Plugins should expect the base APIs to be available from "muplugins_loaded" - when this is also implemented in a Must Use plugin
 * OR "plugins_loaded" otherwise.
 * 
 * They don't have to wait for "init" BUT they should actually wait for the appropriate "lib_loaded" action message 
 * If they need a library then they should invoke oik_require_lib( $lib, $version ) and test the result.
 * If they need a library function then they should invoke oik_require_func( $func, $lib, $version ) and test the result
 * These functions may also support parameters passed as $args arrays
 
 * 
 * This logic is intended to be run as part of a must-use plugin ( __oik-lib-mu.php ) 
 * but may also be invoked as a normal plugin
 * 
 * Libraries implemented:
 *
 * Library    | Functionality
 * ---------- | -------------
 * oik_boot 	|	Bootstrap functions equivalent to oik's oik_boot.php
 * bwtrace    | Trace functions equivalent to oik's bwtrace.php
 * 
 * 
 * Plugins that use/share these functions or provide other libraries are:
 * 
 * Plugin | Functionality
 * ------ | --------------- 
 * oik    | oik_boot
 * oik    | bobbforms
 * oik    | bobbfunc
 * oik    | oik-admin
 * oik    | lib-fields
 * oik    | lib-shortcodes
 * oik    | lib-depends
 * oik    | lib-update
 * etcetera...
 * 
 * Note: These "libraries" are more modular than the PHP libraries listed in 
 * {@link https://github.com/ziadoz/awesome-php#dependency-management-extras}
 * You may want to consider them as modules, similar to Drupal modules or those implemented by Jetpack.
 * The difference between a library and a plugin is that the library provides some functionality.
 * 
 * The difference between a library and an include file is that the library is dynamically fetched when required;
 * it's not just loaded at startup in the vain assumption that it's going to be needed.
 *  
 * The difference between a library and a package has yet to be defined.
 * 
 * 
 */
 
/**
 * Return the singular instance of the OIK_libs class
 *
 * @return object the OIK_libs class
 */
function oik_libs() {
	if ( !class_exists( "OIK_libs" ) ) {
		oik_require( "includes/class-oik-libs.php", "oik-lib" );
		oik_require( "includes/class-oik-lib.php", "oik-lib" );
	}
	if ( class_exists( "OIK_libs" ) ) {
		$oik_libs = OIK_libs::instance();
	} else {
		die();
	}
	return( $oik_libs );
}
 
/**
 * Register a library
 * 
 * Registers a library so that it can be loaded. 
 * Similar to wp_enqueue_script() in its implementation
 * 
 * A plugin can use oik_register_lib() to define the libraries that it Provides
 * even when the library has not yet been initialised
 * Alternatively a plugin can return this information in response to the "oik_lib_query_lib" filter
 * 
 * The OIK_libs class 
 *
 */
function oik_register_lib( $library, $src=null, $deps=array(), $version=null, $args=null ) {
	$oik_libs = oik_libs();
	$result = $oik_libs->register_lib( $library, $src, $deps, $version, $args );
	bw_trace2( $result, "result" );
	return( $result );
}
 
/**
 * Require a library 
 *
 * Locates and loads (once) a library in order to make functions available to the invoking routine
 *
 * @param string $library the name of the (registered) library
 * @param string $version the required library version. null means don't care
 * @return string the full path to the library file to load
 */
if ( !function_exists( "oik_require_lib" ) ) { 
function oik_require_lib( $library, $version=null, $args=null ) {
	$oik_libs = oik_libs();
	$library_file = $oik_libs->require_lib( $library, $version, $args );
	bw_trace2( $library_file, "library_file" );
	return( $library_file );	
}
}

/**
 * Determine the library file to load
 * 
 * @param string $library the name of the (registered) library
 * @param string $version the required library version. null means don't care
 * @return string the full path to the library file to load
 */ 
function oik_lib_determine_lib( $library, $version=null, $args=null ) {
	$oik_libs = oik_libs();
	$library_file = $oik_libs->determine_lib( $library, $version );
	return( $library_file );
} 

/**
 * Require a library function
 *
 */
function oik_require_func( $library, $func, $version=null, $args=null ) {
	$oik_libs = oik_libs();
	$required_func = $oik_libs->require_func( $library, $func, $version, $args );
	return( $required_func );
}

/**
 * Register the default libraries 
 *
 * Note: It doesn't really matter which version of "oik_boot" or "bwtrace" has been loaded
 * so long as we can determine the version for any dependency checking being performed by other plugins.
 *
 * The convention for these "Must Use" libraries is that we set some constants representing the source file and version. 
 * We expect the *_INCLUDED constants to be defined but not necessarily the *_FILE ones, since this is dependent on
 * the correct version of the oik and oik-bwtrace plugins being installed. 
 * There will be warning messages if the *_FILE defines are not set.
 * 
 */
function oik_lib_register_default_libs() {
	oik_register_lib( "oik_boot", OIK_BOOT_FILE , null, OIK_BOOT_INCLUDED );
	oik_register_lib( "bwtrace", BWTRACE_FILE , null, BWTRACE_INCLUDED );
	oik_register_lib( "oik-lib", __FILE__, "oik_boot,bwtrace", OIK_LIB_INCLUDED );
}

/**
 * Check the existence of the available libraries for the given plugin
 *
 * A plugin may say that it offers a set of libraries but that doesn't guarantee
 * that the library file exists. 
 * This routine checks for the existence of the source file
 * before it's added to the array of libraries.
 * It doesn't check for the dependencies.
 *
 * @param array $libraries array of registered libraries
 * @param array $libs array of libraries to add in form "library" => "dependencies"
 * @param string $plugin plugin slug
 * @return array the updated libraries array
 */
function oik_lib_check_libs( $libraries, $libs, $plugin ) {
	$lib_args = array();
	foreach ( $libs as $library => $depends ) {
		$src = oik_path( "libs/$library.php", $plugin ); 
		if ( file_exists( $src ) ) {
			$lib_args['library'] = $library;
      $lib_args['src'] = $src;
			$lib_args['deps'] = $depends;
			$lib = new OIK_lib( $lib_args );
			$libraries[] = $lib;
		}
	}
	return( $libraries );
}

/**
 * Satisfy pre-requisites for oik-lib shared library processing
 *
 * The oik-lib library is dependent upon "oik_boot" and "bwtrace"
 * These are shared libraries from "oik" and "oik-bwtrace" plugins
 * If they are not registered by these plugins then we need to load them ourselves
 *
 *
 * @return bool true if "oik_boot" and "bwtrace" 
 */
function oik_lib_boot() {
	$loaded = true;
	if ( !function_exists( "oik_path" ) ) {
		$oik_boot_file = __DIR__ . "/oik_boot.php";
		$loaded = include_once( $oik_boot_file );
	}
	if ( $loaded && !function_exists( "bw_trace2" ) && function_exists( "oik_require" ) ) {
		$trace_file = oik_path( "libs/bwtrace.php", "oik-lib" );
		$loaded = include_once( $trace_file );
	}
	
	if ( $loaded ) { 
		oik_lib_register_default_libs();
	}
	return( $loaded );
}

} /* end !defined */


 
 
 
 

<?php
if ( !defined( "CLASS_OIK_AUTOLOAD_INCLUDED" ) ) {
define( "CLASS_OIK_AUTOLOAD_INCLUDED", "1.1.0" );

/**
 * @copyright (C) Copyright Bobbing Wide 2016-2021
 * @package oik, oik-libs
 *
 * Implement autoloading for shared libraries
 *
 * The autoload function is not supposed to load the classes willy nilly.
 * It needs to check compatibility with the libraries that are already loaded
 * @TODO Add this logic.
 * 
 * {@link http://woocommerce.wp-a2z.org/oik_api/wc_autoloaderautoload}
 * 
 *
 */
class OIK_Autoload {

	/**
	 * Array of information about classes to load
	 * Format of each item in the array
	 * "classname" => array( "class"=> "plugin"=> "file"=> ) 
	 * 
	 * @TODO Should also support theme? 
	 */
	public static $loads;
	
	/**
	 * Array of available classes
	 */
	public $classes;
	
	/**
	 * @var OIK_autoload - the true instance
	 */
	private static $instance;

	/**
	 * @var bool True to autoload shared library classes. False initially.
	 */
	private $autoload_shared_library;
	/**
	 * Return a single instance of this class
	 *
	 * @return object 
	 */
	public static function instance() {
		if ( !isset( self::$instance ) && !( self::$instance instanceof self ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	/**
	 * Constructor for the OIK_autoload class
	 */
	function __construct() {
		$this->autoload_shared_library = false;
		spl_autoload_register( array( $this, 'autoload' ) );
	}

	/**
	 * Runs / reruns the oik_query_autoload_classes filter.
	 */
	function query_autoload_classes() {
		self::$loads = array();
		$loads_more = apply_filters( "oik_query_autoload_classes", self::$loads );
		self::$loads = $loads_more;
		$this->classes = null;
	}
	
	/**
	 * Autoloads a class if we know how to.
	 * 
	 * The fact that we have gotten here means that the class is not already loaded so we need to load it.
	 * @TODO We should also know which pre-requisite classes to load. Does spl_autoload_register() handle this?
	 * 
	 * What if we can't?
	 */
	function autoload( $class ) {
		if ( $this->autoload_shared_library ) {
			$library_file = $this->load_shared_library_class_file( $class );
			if ( $library_file && !is_wp_error( $library_file)) {
				return;
			}
		}

		$class_file = $this->locate_class( $class );
		if ( $class_file ) {
			$file = $this->file( $class_file );
			oik_require( $file, $class_file->plugin );
		} else {
			// Perhaps it's a shared library file
			// or perhaps it's in classes
			$this->load_shared_library_class_file( $class );
		}
	}
	
	/**
	 * Determine the file name from the class and path
	 * 
	 * If no file is specified we try to make it up.
	 * If no path is specified we assume it's been passed in the file name
	 * 
	 * 
	 * @param object $class_file
	 * @return string fully qualified file name
	 */
	function file( $class_file ) {
		bw_trace2( null, null, true, BW_TRACE_DEBUG );
		bw_backtrace( BW_TRACE_VERBOSE );
		$file = $class_file->file;
		if ( !$file ) {
			$file = str_replace( "_", "-", $class_file->class );
			$file = strtolower( $file );
			$file = "class-$file.php";
			if ( $class_file->path ) {
				$file = $class_file->path . '/' . $file;
			}
		}
		return( $file );
	}
	
	/**
	 * Locate the required class
	 * 
	 * self::$loads contains the raw information about classes we may want to load
	 * self::$classes is the post processed version
	 */
	function locate_class( $class ) {
		if ( !isset( $this->classes ) ) {
			$this->set_classes();
		}
		$class_file = bw_array_get( $this->classes, $class, null );
		if ( $class_file ) {
			$class_file = (object) $class_file;
		}
		bw_trace2( $class_file, "class_file", true, BW_TRACE_DEBUG );
		return( $class_file );
	}
	
	/**
	 * Register a set of classes that can be autoloaded
	 * 
	 * Here we receive an array of classes that may or may not be complete.
	 * We should allow multiple definitions and extract the class name from the definition
	 * if it's not given in the key. 
	 * @TODO Can this be deferred until the actual autoload() is requested? 
	 * 
	 * 
	 * 
	 *
	 * @TODO Each class should specify its version and dependencies
	 * 
		$class_file = bw_array_get( self::$loads, $class, null );
	 */
	function set_classes() {
		bw_trace2( null, null, false, BW_TRACE_VERBOSE );
		foreach ( self::$loads as $class => $load ) {
			self::set_class( $class, $load );
		}
	}
	
	/**
	 * Register a class that can be autoloaded
	 *
	 * If the $class is numeric we need to extract the name from the array
	 */
	function set_class( $class, $load ) {
		bw_trace2( $load, $class, false, BW_TRACE_VERBOSE );
		if ( is_numeric( $class ) ) {
			$class = $load[ "class" ];
		}
		$this->classes[ $class ] = $load;
	}

	/**
	 * Apply "oik_autoload" filter/action
	 * 
	 * Not a good idea since we don't know which hooks to filter on
	 * so perhaps it should just be an action hook
	 */
	function nortoload( $loads_more ) {
		self::loads( $loads_more );
		return( $loads_more );
	}

	/**
	 * Enables / disables  the autoload shared library logic.
	 *
	 * @param bool $autoload_shared_library
	 */
	function set_autoload_shared_library( $autoload_shared_library ) {
		if ( $autoload_shared_library ) {
			$this->autoload_shared_library=$autoload_shared_library;
		}
	}

	/**
	 * Try loading a shared library class.
	 *
	 * @param $class
	 * @return object/bool the library loaded or a simple bool if oik_libs is not loaded, so we used the fallback
	 */
	function load_shared_library_class_file( $class ) {
		$lib = 'class-';
		$file = str_replace( "_", "-", $class );
		$file = strtolower( $file );
		$lib .= $file;
		$library_file = oik_require_lib( $lib );
		return $library_file;
	}


}

} /* end if !defined */

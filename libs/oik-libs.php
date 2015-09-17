<?php // (C) Copyright Bobbing Wide 2015
if ( !defined( 'OIK_LIBS_INCLUDED' ) ) {
define( 'OIK_LIBS_INCLUDED', "0.0.1" );

/**
 * oik library management functions
 * 
 * Library: oik-libs
 * Provides: oik-libs
 * Depends: oik_boot, bwtrace
 * Shareable: No - since it expects to be part of the oik-libs plugin. See oik_require() below
 * Conflicts: oik:<=3.0.0
 */
 
			 
/**
 * Compare the library files in two directories
 *
 * This solution is a temporary solution until a better one is found using 
 * - Git submodules 
 * - or Composer packages.
 * 
 * The oik-libs /libs directory is supposed to contain the master
 * The plugin's /libs directory should either be updated from the master
 * OR, if it's newer than the master then we update the master
 *
 * When packaging a plugin we need to ensure that we've got the latest version of the shared library files
 * that we use.
 * 
 * For each file in the plugin's directory we compare it with the master
 *
 * plugin time vs master time | processing
 * ---------------------- | -----------
 * earlier | Copy to plugins dir from master
 * same | OK
 * later | Copy to master dir from plugins dir
 *  		
 * 
 * 
 * Note: We may need to loop through all the plugins a couple of times
 * 
 
 * master
 *
 * @param string $master_dir - the master directory
 * @param string $plugins_dir - the plugins directory
 *  
 */			 
function oik_libs_compare_libs( $master_dir, $plugins_dir ) {
	oik_require( "admin/oik-apis.php", "oik-shortcodes" );
  $files = _oiksc_get_php_files( $plugins_dir, null );
	bw_trace2( $files, "files", true );
	foreach ( $files as $key => $file ) {
		if ( substr( $file, -1 ) !== "." ) {
			//echo "$key $file" . PHP_EOL;
			$master_file = oik_libs_get_file( $file, $master_dir );
			$plugin_file = oik_libs_get_file( $file, $plugins_dir );
			oik_libs_compare_file( $plugin_file, $master_file );
		}
	}



}

/**
 * Return the file name to use
 * 
 * _oiksc_get_php_files fiddles about with the file path
 * so we need to reconstruct it
 */
function oik_libs_get_file( $file, $master_dir ) {
	$base_file = basename( $file );
	$master_file = $master_dir . '/' . $base_file;
	return( $master_file );
}

/**
 * Return the file last modified time
 */
function oik_libs_get_filemtime( $file ) {
	$time = filemtime( $file );
	return( $time );
}	


function oik_libs_compare_file( $file, $master_file ) {
	$plugin_time = oik_libs_get_filemtime( $file );
	$master_time = oik_libs_get_filemtime( $master_file );
	
	echo "$plugin_time $file" . PHP_EOL;
	echo "$master_time $master_file" . PHP_EOL;
	if ( $plugin_time < $master_time ) {
		echo "Plugin file needs updating";
	} elseif ( $plugin_time == $master_time ) {
		echo "Match";
	} else {
		echo "Master file needs updating";
	}
	echo PHP_EOL;
	echo PHP_EOL;
   
		
}

/**
 * Compare the library files between the pugin and the master
 * 
 * We consider this to be the master.
 */
function oik_libs_compare( $plugin ) {
	$master_dir = __DIR__;
	//$master_dir = s
	$plugin_dir = dirname( dirname( $master_dir ) ) . '/'. $plugin . "/libs"; 
	oik_libs_compare_libs( $master_dir, $plugin_dir );
}

if ( false ) {
	oik_libs_compare_libs( "C:/apache/htdocs/wordpress/wp-content/plugins/oik-libs/libs" 
											 , "C:/apache/htdocs/wordpress/wp-content/plugins/oik-bwtrace/libs"
											 );
} else {
	oik_libs_compare( "oik-bwtrace" );
	oik_libs_compare( "oik-lib" );
	oik_libs_compare( "oik" );
}										
	

										 
										 
										 
										 
} /* end if !defined */										  

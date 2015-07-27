<?php // (C) Copyright Bobbing Wide 2015

/**
 * oik-lib MU enablement
 * 
 * Library: oik-lib-mu
 * Provides: oik-lib-mu
 *
 * Provides functionality to MU enable certain library functions
 * including the ability to self promote plugin files to MU status
 *
 * Note: This functionality is not tested in a multi-server environment
 * NOR one where self modifying code is detected and prevented.
 */


/**
 * Activate / deactivate MU library processing
 *
 * Either copies an MU plugin from the source to the target or removes the MU plugin file from the target
 *
 *
 * Note: WPMU_PLUGIN_DIR may not have been defined if we're invoked from wp-config.php, but ABSPATH should have been.
 *
 * MU plugin files are loaded in ascending alphabetical order.
 * For MU plugin for oik-bwtrace is prefixed with an underscore so that it's loaded very early.
 * The MU plugin for oik-lib is prefixed with two underscores so that it's loaded before oik-bwtrace
 * @TODO Check if this is a good idea or not.
 *
 * @param bool $activate true to activate, false to deactivate
 * @param string $file
 * @param string $plugin
 * @param string $target
 */
function oik_lib_activate_mu( $activate=true, $file="includes/__oik-lib-mu.php", $plugin="oik-lib", $target=null ) {
	//bw_trace2();
	$source = oik_path( $file, $plugin );
	if ( defined( 'WPMU_PLUGIN_DIR' ) ) {
		$mu_target = WPMU_PLUGIN_DIR;
	} else {
		$mu_target = ABSPATH . '/wp-content/mu-plugins';
	}
	//bw_trace2( $mu_target, "target dir" );
	if ( is_dir( $mu_target ) ) {
		$mu_target .= '/';
		if ( $target ) {
			$mu_target .= $target;
		} else {
			$mu_target .= basename( $file );
		}
		//bw_trace2( $mu_target, "target file:$activate" );
		if ( $activate ) {
		
			if ( !file_exists( $mu_target ) ) {
				copy( $source, $mu_target );
			}
		} else {
			if ( file_exists( $mu_target ) ) {
				unlink( $mu_target );
			} 
		}
	} else {
		// Do we need to make this ourselves?
		bw_trace2( "Not a dir?" );
    //gobang();
	}
}

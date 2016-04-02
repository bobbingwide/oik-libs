<?php // (C) Copyright Bobbing Wide 2015

/**
 * Command Line Interface functions
 *
 * This file should eventually be a shared library file
 * containing some of the common routines used in the oik-zip, oik-tip and other routines
 * including those that deal with directory changes in symlinked environments
 * and other that return responses to the calling routines and make decisions based on them
 */
 
 

/**
 * Prompt to check if the process should be continued
 *
 * This routine does not make any decisions.
 * If you want to stop you just press Ctrl-Break.
 *
 */
if ( !function_exists( 'docontinue' ) ) { 
function docontinue( $plugin="Press Ctrl-Break to halt" ) {
	echo PHP_EOL;
	echo "Continue? $plugin ";
	$stdin = fopen( "php://stdin", "r" );
	$response = fgets( $stdin );
	$response = trim( $response );
	fclose( $stdin );
	return( $response );
}
}

if ( !function_exists( "oik_batch_run_me" ) ) {
function oik_batch_run_me( $me ) {
	$run_me = false;
	echo PHP_SAPI;
	echo PHP_EOL;
	$included_files = get_included_files();
	// print_r( $included_files[0] );
	if ( $included_files[0] == __FILE__) {
		$run_me = true;
	} else {
		//  has been loaded by another PHP routine so that routine is in charge. e.g. boot-fs.php for WP-CLI
		$basename = basename( $included_files[0] );
		if ( $basename == "oik-wp.php" ) {
			print_r( $_SERVER );
			$fetched = bw_array_get( $_SERVER['argv'], 1, null );
			if ( $fetched ) {
				$fetched_basename = basename( $fetched );
				$me_basename = basename( $me );
				$run_me = ( $fetched_basename == $me_basename );
			}	
		}
		if ( $basename == "oik-batch.php" ) {
		
			print_r( $_SERVER );
			$fetched = $_SERVER['argv'][0];
			$fetched_basename = basename( $fetched );
			$me_basename = basename( $me );
			$run_me = ( $fetched_basename == $me_basename );
		}
	}
	return( $run_me );
}
}


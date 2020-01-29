<?php // (C) Copyright Bobbing Wide 2017

/**
 * @package libs-oik-depends
 * 
 * Tests for logic libs/oik-depends.php
 */
class Tests_libs_oik_depends extends BW_UnitTestCase {

	function setup(): void {
		oik_require_lib( "oik-depends" ); 
	}

	/**
	 * Test for bobbingwide/oik/issues/67
	 */
	function test_bw_get_all_plugin_names_false_parameter() {
		$expected_output = array();
		$plugins = bw_get_all_plugin_names( false );
		$this->assertEquals( $plugins, $expected_output );
	}

	/**
	 * Test the documented array of active plugins
	 *
	 * $active_plugins                                     | $names (keys only - 
	 * --------------------------------------------------- | -------------------------------
	 *  [0] => abt-featured-heroes/abt-featured-heroes.php |   ['abt-featured-heroes'] 
	 *  [1] => effort/tasks.php                            |   ['tasks'] 
	 *  [2] => fancybox-for-wordpress/fancybox.php         |   ['fancybox']
	 *  [3] => oik/oik-bob-bing-wide.php                   |   ['oik-bob-bing-wide']
	 *  [4] => oik/oik-bwtrace.php                         |   ['oik-bwtrace'] 
	 */
	function test_bw_get_all_plugin_names_documented_example() {
		$active_plugins = array( 'abt-featured-heroes/abt-featured-heroes.php'
													 , 'effort/tasks.php'
													 , 'fancybox-for-wordpress/fancybox.php'
													 , 'oik/oik-bob-bing-wide.php'
													 , 'oik/oik-bwtrace.php'
													 );
		$plugins = bw_get_all_plugin_names( $active_plugins );
		$expected_output = array( 'abt-featured-heroes' => 'abt-featured-heroes/abt-featured-heroes.php'
													 , 'tasks' => 'effort/tasks.php'
													 , 'fancybox' => 'fancybox-for-wordpress/fancybox.php'
													 , 'oik-bob-bing-wide' => 'oik/oik-bob-bing-wide.php'
													 , 'oik-bwtrace' => 'oik/oik-bwtrace.php'
													 );
		$this->assertEquals( $expected_output, $plugins );
	}
	
	/**
	 * Testing two plugins which have the plugin files called y.php and z.php
	 * 
	 * We want to find the plugin which matches the folder name ( y => y/y.php  and z => z/z/php ) 
	 * and ignore the plugins which appear to be in the wrong folder.
	 * 
	 */
	function test_bw_get_all_plugin_names_two_plugins_with_same_files() {
		$active_plugins = array( "y/y.php", "y/z.php", "z/y.php", "z/z.php" );
		$plugins = bw_get_all_plugin_names( $active_plugins );
		$expected_output = array( "y" => "y/y.php", "z" => "z/z.php" );
		$this->assertEquals( $expected_output,  $plugins );
	}
	
	/**
	 * Testing we find the correct versions of oik, oik-batch and oik-wp
	 */
	function test_bw_get_all_plugin_names_renamed_oik_and_oik_batch() {
		$active_plugins = array( "oik-clone-20171114/oik.php", "oik/oik.php", "oik-batch/oik-batch.php", "oik-batch-renamed/oik-batch.php", "oik-batch/oik-wp.php" );
		$plugins = bw_get_all_plugin_names( $active_plugins );
		$expected_output = array( "oik" => "oik/oik.php", "oik-batch" => "oik-batch/oik-batch.php", "oik-wp" => "oik-batch/oik-wp.php" );
		$this->assertEquals( $expected_output, $plugins );
	}

}

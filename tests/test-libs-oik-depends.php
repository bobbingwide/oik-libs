<?php // (C) Copyright Bobbing Wide 2017

/**
 * @package libs-oik-depends
 * 
 * Tests for logic libs/oik-depends.php
 */
class Tests_libs_oik_depends extends BW_UnitTestCase {

	function setUp() {
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
		$this->assertEquals( $plugins, $expected_output );
	}
	
	/**
	 * Test the TODO
   * 
	 * Testing two plugins which have the main plugin file called z.php
	 * The result is that index 'z' gets overridden by the last plugin.
	 * This is not really an issue for us, since we don't have any plugins that still deliver
	 * multiple plugins with the same name.
	 */
	function test_bw_get_all_plugin_names_two_plugins_with_same_main_file() {
		$active_plugins = array( "y/z.php", "z/z.php" );
		$plugins = bw_get_all_plugin_names( $active_plugins );
		$expected_output = array( "z" => "z/z.php" );
		$this->assertEquals( $plugins, $expected_output );
		
	} 
}

<?php // (C) Copyright Bobbing Wide 2017

/** 
 * Unit tests for the libs\oik-sc-help.php file
 * 
 * Note: Some of the logic in libs\oik-sc-help.php is tested implicitely by tests in tests\test-libs-bobbfunc.php
 *
 */
class Tests_libs_oik_sc_help extends BW_UnitTestCase {

	/** 
	 * set up logic
	 * 
	 * - ensure any database updates are rolled back
	 * - we need oik-googlemap to load the functions we're testing
	 */
	function setUp() {
		parent::setUp();
		oik_require_lib( "oik-sc-help" );
	}
	
	/**
	 * Test default help for a shortcode which doesn't exist
	 */ 
	function test__sc__help() {
		$this->switch_to_locale( "en_GB" );
		$html = _sc__help( null );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	/**
	 * Test default help for a shortcode which doesn't exist bb_BB
	 *
	 * Note: bb_BB for `?` is `?` so this test is not that effective.
	 * 
	 */
	function test__sc__help_bb_BB() {
		$this->switch_to_locale( "bb_BB" );
		$html = _sc__help( null );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	/**
	 * Tests _sc__syntax for an unknown shortcode
	 * 
	 * We test it for known shorcodes in the specific plugins
	 */
	function test__sc__syntax() {
		$syntax = _sc__syntax( null );
		$this->assertNull( $syntax );
	}
	
	/**
	 * Note: wpautop() may wrap the expanded null shortcode in a paragraph tag and appends a trailing newline char
	 */
	function test__sc__example() {
		$this->switch_to_locale( "en_GB" );
		_sc__example( null );
		$html = bw_ret();
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	function test__sc__example_bb_BB() {
		$this->switch_to_locale( "bb_BB" );
		_sc__example( null );
		$html = bw_ret();
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	// test_sc_classes
	// test_sc_posts
	// test_sc_thumbnail

}

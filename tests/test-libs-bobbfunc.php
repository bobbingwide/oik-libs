<?php // (C) Copyright Bobbing Wide 2017

/** 
 * Unit tests for the libs\bobbfunc.php file
 */

class Tests_libs_oik_bobbfunc extends BW_UnitTestCase {

	/** 
	 * set up logic
	 * 
	 * - ensure any database updates are rolled back
	 * - we need oik-googlemap to load the functions we're testing
	 */
	function setUp() {
		parent::setUp();
		oik_require_lib( "bobbfunc" );
	}
	
	/**
	 * Do we need to test the help for a shortcode which exists?
	 */ 
	function test_bw_sc_help() {
	
		do_action( "oik_add_shortcodes" );
		bw_sc_help( "oik" );
		$html = bw_ret();
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	function test_bw_sc_help_bb_BB() {
		$this->switch_to_locale( "bb_BB" );
		bw_sc_help( "bw" );
		$html = bw_ret();
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	/**
	 * Tests bw_sc_syntax 
	 * 
	 * bw_sc_syntax is called by [bw_codes]
	 *
	 * - Currently we need shortcodes/oik-codes.php to be loaded for bw_sc_link
	 * - Links will need to be generalized - changing https://qw/oikcom to 
	 * - And in order to test bw_address we also need for oik's shortcodes to have been defined
	 *
	 * @TODO - use a test shortcode or a WordPress standard shortcode.
	 */
	function test_bw_sc_syntax() {
		do_action( "oik_add_shortcodes" );
		oik_require( "shortcodes/oik-codes.php" );
		bw_sc_syntax( "bw_address" );
		$html = bw_ret();
		$html = str_replace( oik_get_plugins_server(), "https://qw/src", $html );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		
	}
 
	/**
	 * Tests bw_sc_example
	 * 
	 * - Here we're testing the example for the oik shortcode as well as bw_address
	 * - wpautop() can create invalid HTML for the bw_address example. 
	 * - We need to disable wpautop() processing for "the_content" filtering.
	 * - See also notes for test_bw_sc_syntax
	 */
	function test_bw_sc_example() {
	
    remove_filter( 'the_content', 'bw_wpautop', 99 );
		bw_sc_example();
		bw_sc_example( "bw_address" );
		$html = bw_ret();
		$html = str_replace( oik_get_plugins_server(), "https://qw/src", $html );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	/**
	 * Tests bw_sc_snippet
	 */
	function test_bw_sc_snippet() {
		bw_sc_snippet();
		bw_sc_snippet( "bw_address" );
		$html = bw_ret();
		$html = str_replace( oik_get_plugins_server(), "https://qw/src", $html );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
		
	

}
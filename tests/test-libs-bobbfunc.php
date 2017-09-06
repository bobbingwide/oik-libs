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
		bw_sc_help( "oik" );
		$html = bw_ret();
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	function test_bw_sc_help_bb_BB() {
		$this->switch_to_locale( "bb_BB" );
		bw_sc_help( "bw" );
		$html = bw_ret();
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
																			 
	

}

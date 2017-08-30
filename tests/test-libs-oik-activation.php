
<?php // (C) Copyright Bobbing Wide 2017

/**
 * @package libs-oik-activation
 * 
 * Tests for logic libs/oik-activation.php
 */
class Tests_libs_oik_activation extends BW_UnitTestCase {

	function setUp() {
		oik_require_lib( "oik-depends" ); 
		oik_require_lib( "oik-activation" );
	}
	
	function test_translate_install() {
		$text = __('Install' );
		$this->assertEquals( "Install", $text );
		
		$text . __('Install' ) . "where does this go?";
		
	}
	
	
	/**
	 */
	function test_oik_plugin_install_plugin() {
		$this->switch_to_locale( "en_GB" );
		$html = oik_plugin_install_plugin( "us-tides" );
		$html = $this->replace_admin_url( $html );
		$html = $this->replace_created_nonce( $html, "install-plugin_us-tides" );
		
		$html_array = $this->tag_break( $html );
		
		$this->assertNotNull( $html_array );
		//$this->generate_expected_file( $html_array );
		$this->assertArrayEqualsFile( $html_array );
	}
	
	/**
	 */
	function test_oik_plugin_install_plugin_bb_BB() {
		$this->switch_to_locale( "bb_BB" );
		$html = oik_plugin_install_plugin( "us-tides" );
		$html = $this->replace_admin_url( $html );
		$html = $this->replace_created_nonce( $html, "install-plugin_us-tides" );
		
		$html_array = $this->tag_break( $html );
		
		$this->assertNotNull( $html_array );
		//$this->generate_expected_file( $html_array );
		$this->assertArrayEqualsFile( $html_array );
		$this->switch_to_locale( "en_GB" );
	}
	
	/**
	 */
	function test_oik_plugin_activate_plugin() {
		$this->switch_to_locale( "en_GB" );
		$html = oik_plugin_activate_plugin( "us-tides/us-tides.php", "us-tides" );
		$html = $this->replace_admin_url( $html );
		$html = $this->replace_created_nonce( $html, "activate-plugin_us-tides/us-tides.php" );
		
		$html_array = $this->tag_break( $html );
		
		$this->assertNotNull( $html_array );
		//$this->generate_expected_file( $html_array );
		$this->assertArrayEqualsFile( $html_array );
	}
	/**
	 */
	function test_oik_plugin_activate_plugin_bb_BB() {
		$this->switch_to_locale( "bb_BB" );
		$html = oik_plugin_activate_plugin( "us-tides/us-tides.php", "us-tides" );
		$html = $this->replace_admin_url( $html );
		$html = $this->replace_created_nonce( $html, "activate-plugin_us-tides/us-tides.php" );
		
		$html_array = $this->tag_break( $html );
		
		$this->assertNotNull( $html_array );
		//$this->generate_expected_file( $html_array );
		$this->assertArrayEqualsFile( $html_array );
		$this->switch_to_locale( "en_GB" );
	}
	
}

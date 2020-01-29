<?php // (C) Copyright Bobbing Wide 2017

/**
 * @package libs-oik_themes
 * 
 * Tests some parts of the oik_themes library
 * For other tests see the oik base plugin.
 *
 * For oik_themes_check() we can get three different results. How to achieve this?
 * 
 * Result | check_theme / check_version
 * ------ | ---------------------------
 * Error | Invalid plugin on the server or invalid server
 * new version | ensure it's lower than the current version
 * up to date | ensure it's the same version
 * 
 */
class Tests_libs_oik_themes extends BW_UnitTestCase {

	/**
	 * oik_plugins_check() assumes that oik_require_lib( "class-oik-remote" ) will work
	 * It also assumes that class-oik-update has been loaded.
	 * There's a cyclical dependency that needs to be satisfied.
	 * oik solves this... but how?
	 */
	function setup(): void {
		parent::setUp();
		oik_require_lib( "oik_themes" );
		oik_require_lib( "class-oik-update" );
		//oik_require( "libs/oik-l10n.php", "oik-libs" );
		bw_load_plugin_textdomain( "oik" );
		$this->force_rebuild_bw_slugs();
		
		
	}
	
	
	/**
	 * Test oik_themes_check for getting an error, bb_BB locale
	 *
	 * We want to get an error from the server that is NOT 
	 *	cURL error 60: SSL certificate problem: unable to get local issuer certificate
	 * We can achieve this by communicating with a theme server that supports the request
	 * but where the theme we're asking for is not registered. 
	 * At the moment http://qw/oikcouk satisfies that.
	 * As for testing oik_plugins, qw is not available from s.b
	 *
	 * 
	 */
	function test_oik_themes_check_bb_BB_error() {
	
		$this->update_theme_options();
		$server = "http://qw/oikcouk";
		$server = "https://herbmiller.me";
		$server = 'https://oik-plugins.com';
	  oik_update::oik_register_theme_server( WP_CONTENT_DIR . "/themes/genesis-image/functions.php", $server );
		$this->switch_to_locale( "bb_BB" );
		$_REQUEST['check_theme'] =  "genesis-image";
		$_REQUEST['check_version'] = "99.0.0";
		$html = bw_ret( oik_themes_check() );
		
		$this->assertNotNull( $html );
		$html = $this->replace_admin_url( $html );
		$html_array = $this->tag_break( $html );
		$this->assertNotNull( $html_array );
		
		$this->assertArrayEqualsFile( $html_array );
		
		$this->switch_to_locale( "en_GB" );
	}
	
	/**
	 * Test for a new version of genesis-oik
	 * 
	 * This test relies on the theme server not already being registered 
	 * since the value from the bw_themes option field is used in preference.
	 */
	function test_oik_themes_check_bb_BB_new_version() {
		$this->update_theme_options();
		$server = "http://qw/oikcom";
		$server = "https://oik-plugins.com";
	  oik_update::oik_register_theme_server( WP_CONTENT_DIR . "/themes/genesis-oik/functions.php", $server );
		$this->switch_to_locale( "bb_BB" );
		$_REQUEST['check_theme'] =  "genesis-oik";
		$_REQUEST['check_version'] = "0.0.0";
		$html = bw_ret( oik_themes_check() );
		
		$this->assertNotNull( $html );
		
		$html = $this->replace_admin_url( $html );
		$html = $this->replace_created_nonce( $html, "upgrade-theme_genesis-oik" );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	/**
	 * Replaces the created nonce with nonsense
	 *
	 * @param string $html the HTML string
	 * @param string $action the action that was passed to wp_create_nonce()
	 * @param string $id the ID that was passed to wp_create_nonce()
	 * @return string updated HTML
	 */ 
	function replace_created_nonce( $html, $action, $id='_wpnonce' ) {
		$created_nonce = $id . '=' . wp_create_nonce( $action );
		$pos = strpos( $html, $created_nonce );
		$this->assertNotFalse( $pos );
		$html = str_replace( $created_nonce, $id . "=nonsense", $html );
		return $html;
	}
	
	/**
	 *
	 * In order to get an 'up to date' response the version we pass must be greater than or equal to the version on the server.
	 * and the package file for that version must be loaded.
	 * Otherwise we get <p>Package not found</p>
	 */
	function test_oik_themes_check_bb_BB_uptodate() {
	  //oik_update::oik_register_theme_server( WP_CONTENT_DIR . "/themes/genesis-oik/functions.php", "http://qw/oikcom" );
		$this->switch_to_locale( "bb_BB" );
		$_REQUEST['check_theme'] =  "genesis-oik";
		$_REQUEST['check_version'] = "99.0.0";
		$html = bw_ret( oik_themes_check() );
		$this->assertNotNull( $html );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		
		$this->switch_to_locale( "en_GB" );
	}
	
	
	function force_rebuild_bw_slugs() {
		global $bw_registered_themes, $bw_theme_slugs;
		$bw_registered_themes = null;
		$bw_theme_slugs = null;
		
	}
	
	/**
	 * Tests the message from oik_themes_validate_theme
	 */
	function test_oik_themes_validate_theme() {
		$this->switch_to_locale( "en_GB" );
		$valid = oik_themes_validate_theme( null );
		$html = bw_ret();
		$this->assertFalse( $valid );
		$this->assertNotNull( $html );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		//$this->switch_to_locale( "en_GB" );
	}
	
	/**
	 * Tests the message from oik_themes_validate_theme
	 */
	function test_oik_themes_validate_theme_bb_BB() {
		$this->switch_to_locale( "bb_BB" );
		$valid = oik_themes_validate_theme( null );
		$html = bw_ret();
		$this->assertFalse( $valid );
		$this->assertNotNull( $html );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	
	/**
	 * Empty bw_themes so that the required theme server is used.
	 *
	 */
	function update_theme_options() {
		$bw_themes = array();
		update_option( "bw_themes", $bw_themes );
	}
	
	
		
	
	
}
	
		

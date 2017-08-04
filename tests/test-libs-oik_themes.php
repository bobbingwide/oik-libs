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
	function setUp() {
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
	 * 
	 */
	function test_oik_themes_check_bb_BB_error() {
	  oik_update::oik_register_theme_server( WP_CONTENT_DIR . "/themes/genesis-image/functions.php", "http://qw/oikcouk" );
		$this->switch_to_locale( "bb_BB" );
		$_REQUEST['check_theme'] =  "genesis-image";
		$_REQUEST['check_version'] = "99.0.0";
		$html = bw_ret( oik_themes_check() );
		
		$this->assertNotNull( $html );
		$html = $this->replace_admin_url( $html );
		$html_array = $this->tag_break( $html );
		$this->assertNotNull( $html_array );
		
		$this->assertArrayEqualsFile( $html_array );
	}
	
	/**
	 * 
	 */
	function test_oik_themes_check_bb_BB_new_version() {
	  oik_update::oik_register_theme_server( WP_CONTENT_DIR . "/themes/genesis-oik/functions.php", "http://qw/oikcom" );
		$this->switch_to_locale( "bb_BB" );
		$_REQUEST['check_theme'] =  "genesis-oik";
		$_REQUEST['check_version'] = "0.0.0";
		$html = bw_ret( oik_themes_check() );
		
		$this->assertNotNull( $html );
		$html = $this->replace_created_nonce( $html, "upgrade-theme_genesis-oik" );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
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
		switch_to_locale( "bb_BB" );
		$_REQUEST['check_theme'] =  "genesis-oik";
		$_REQUEST['check_version'] = "99.0.0";
		$html = bw_ret( oik_themes_check() );
		$this->assertNotNull( $html );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	
	/**
	 * Switch to the required target language
	 * 
	 * - WordPress core's switch_to_locale() function leaves much to be desired when the default language is en_US
	 * - and/or when the translations are loaded from the plugin's language folders rather than WP_LANG_DIR
	 * - We have to (re)load the language files ourselves.
	 * 
	 * @TODO We also need to remember to pass the slug/domain to translate() :-)
	 *
	 * Note: For switch_to_locale() see https://core.trac.wordpress.org/ticket/26511 and https://core.trac.wordpress.org/ticket/39210 
	 */
	function switch_to_locale( $locale ) {
		$tdl = is_textdomain_loaded( "oik" );
		$this->assertTrue( $tdl );
		$switched = switch_to_locale( 'bb_BB' );
		if ( $switched ) {
			$this->assertTrue( $switched );
		}
			$locale = $this->query_la_CY();
			$this->assertEquals( "bb_BB", $locale );
			$this->reload_domains();
			$tdl = is_textdomain_loaded( "oik" );
			$this->assertTrue( $tdl );
			//$this->test_domains_loaded();
			$bw = translate( "bobbingwide", "oik" );
			$this->assertEquals( "bboibgniwde", $bw );
			
	}
	
	
	/**
	 * Reloads the text domains
	 * 
	 * - Loading oik-libs from oik-libs invalidates tests where the plugin is delivered from WordPress.org so oik-libs won't exist.
	 * - but we do need to reload oik's text domain 
	 * - and cause the null domain to be rebuilt.
	 */
	function reload_domains() {
		$domains = array( "oik" );
		foreach ( $domains as $domain ) {
			$loaded = bw_load_plugin_textdomain( $domain );
			$this->assertTrue( $loaded, "$domain not loaded" );
		}
		oik_require_lib( "oik-l10n" );
		oik_l10n_enable_jti();
	}
	
	function force_rebuild_bw_slugs() {
		global $bw_registered_themes, $bw_theme_slugs;
		$bw_registered_themes = null;
		$bw_theme_slugs = null;
		
	}
	
	
		
	
	
}
	
		

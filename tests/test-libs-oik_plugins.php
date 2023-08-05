<?php // (C) Copyright Bobbing Wide 2017-2019

/**
 * @package libs-oik_plugins
 * 
 * Tests some parts of the oik_plugins library
 * For other tests see the oik base plugin.
 *
 * For oik_plugins_check() we can get three different results. How to achieve this?
 * 
 * Result | check_plugin / check_version
 * ------ | ---------------------------
 * Error | Invalid plugin on the server or invalid server
 * new version | ensure it's lower than the current version
 * up to date | ensure it's the same version
 * 
 */
class Tests_libs_oik_plugins extends BW_UnitTestCase {

	/**
	 * oik_plugins_check() assumes that oik_require_lib( "class-oik-remote" ) will work
	 * It also assumes that class-oik-update has been loaded.
	 * There's a cyclical dependency that needs to be satisfied.
	 * oik solves this... but how?
	 */
	function setup(): void {
		parent::setUp();
		oik_require_lib( "oik_plugins" );
		oik_require_lib( "class-oik-update" );
		//oik_require( "libs/oik-l10n.php", "oik-libs" );
		bw_load_plugin_textdomain( "oik" );
		$this->force_rebuild_bw_slugs();
		$this->update_plugin_options();
	}
	
	/**
	 * Test oik_plugins_check for getting an error, bb_BB locale
	 *
	 * We want to get an error from the server that is NOT
	 * `cURL error 60: SSL certificate problem: unable to get local issuer certificate`
	 *
	 * We can achieve this by communicating with a plugin server that supports the request,
	 * but where the plugin we're asking for is not registered. 
	 * At the moment http://qw/oikcouk satisfies that; oik-fum is in Draft form.
	 * http://qw/oikcouk is not available to s.b,
	 * Let's try https://herbmiller.me or https://oik-plugins.uk
	 * 
	 */
	function test_oik_plugins_check_bb_BB_error() {
		$server = "http://qw/oikcouk";
		$server = "https://herbmiller.me";
		$server = 'https://oik-plugins.uk';
	  oik_update::oik_register_plugin_server( oik_path( "oik-fum.php", "oik-fum" ), $server );
	
		$this->switch_to_locale( "bb_BB" );
		$_REQUEST['check_plugin'] =  "oik-fum";
		$_REQUEST['check_version'] = "99.0.0";
		$html = bw_ret( oik_plugins_check() );
		
		$this->assertNotNull( $html );
		$html = $this->replace_admin_url( $html );
		//$html = str_replace( oik_get_plugins_server(), "http://qw/oikcom", $html );
		$html_array = $this->tag_break( $html );
		$this->assertNotNull( $html_array );
		//$this->generate_expected_file( $html_array );
		
		$this->assertArrayEqualsFile( $html_array );
		
		$this->switch_to_locale( "en_GB" );
	}
	
	
	/**
	 * Test for a new version of the plugin.
	 * 
	 * Note: The plugin must be installed, and recognized by WordPress, but does not have to be active.
	 * If the plugin is not installed and recognised by WordPress then the nonce will not be created properly.
	 * @TODO Find the URL name automatically, instead of https://s.b/oikcom
	 */
	function test_oik_plugins_check_bb_BB_new_version() {
	  oik_update::oik_register_plugin_server( oik_path( "oik-fum.php", "oik-fum" ), "https://s.b/oikcom" );
		$this->switch_to_locale( "bb_BB" );
		$_REQUEST['check_plugin'] =  "oik-fum";
		$_REQUEST['check_version'] = "0.0.0";
		$html = bw_ret( oik_plugins_check() );
		$this->assertNotNull( $html );
		//$this->generate_expected_file( $html );
		$html = $this->replace_admin_url( $html );
		$html = $this->replace_created_nonce( $html, "upgrade-plugin_oik-fum/oik-fum.php" );
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
		if ( false === $pos ) {
			echo $html . PHP_EOL;
			echo $created_nonce . PHP_EOL;
		}
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
	function test_oik_plugins_check_bb_BB_uptodate() {	
		// Default server now uses the https protocol. Uncomment to test with http.
	  //oik_update::oik_register_plugin_server( oik_path( "oik-fum.php", "oik-fum" ), "http://qw/oikcom" );
		
		$this->switch_to_locale( "bb_BB" );
		$_REQUEST['check_plugin'] =  "oik-fum";
		$_REQUEST['check_version'] = "1.2.1";
		$html = bw_ret( oik_plugins_check() );
		$this->assertNotNull( $html );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		
		$this->switch_to_locale( "en_GB" );
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
	function switch_to_locale( $locale='bb_BB' ) {
		//$tdl = is_textdomain_loaded( "oik" );
		//$this->assertTrue( $tdl );
		$switched = switch_to_locale( $locale );
		if ( $switched ) {
			$this->assertTrue( $switched );
		}
		$new_locale = $this->query_la_CY();
		$this->assertEquals( $locale, $new_locale );
		$this->reload_domains();
		$tdl = is_textdomain_loaded( "oik" );
		$this->assertTrue( $tdl );
		//$this->test_domains_loaded();
		if ( $locale === 'bb_BB' ) {
			$bw = translate( "bobbingwide", "oik" );
			$this->assertEquals( "bboibgniwde", $bw );
		}	
			
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
		global $bw_registered_plugins, $bw_slugs;
		$bw_registered_plugins = null;
		$bw_slugs = null;
	}
	
	/**
	 * Tests the message from oik_plugins_validate_plugin
	 */
	function test_oik_plugins_validate_plugin() {
		//$this->setExpectedDeprecated( "bw_translate" );
		$this->switch_to_locale( "en_GB" );
		$valid = oik_plugins_validate_plugin( null );
		$html = bw_ret();
		$this->assertFalse( $valid );
		$this->assertNotNull( $html );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		//$this->switch_to_locale( "en_GB" );
	}
	
	/**
	 * Tests the message from oik_plugins_validate_plugin
	 */
	function test_oik_plugins_validate_plugin_bb_BB() {
		//$this->setExpectedDeprecated( "bw_translate" );
		$this->switch_to_locale( "bb_BB" );
		$valid = oik_plugins_validate_plugin( null );
		$html = bw_ret();
		$this->assertFalse( $valid );
		$this->assertNotNull( $html );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	/**
	 * Empty bw_plugins so that the required plugin server is used.
	 *
	 */
	function update_plugin_options() {
		$bw_plugins = array();
		update_option( "bw_plugins", $bw_plugins );
	}


	
}
	
		

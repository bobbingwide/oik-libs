<?php // (C) Copyright Bobbing Wide 2017

/**
 * @package libs-l10n
 * 
 * Tests translations of strings from the oik-libs domain
 * 
 * The theory is something like this:
 * 
 * - makepot's job is to find strings that can be translated
 * - WordPress.org expects each plugin and theme to use a unique domain
 * - though it doesn't enforce it
 * - So makepot outputs any translatable string regardless of the value of $domain
 * - Prove this. 
 * 
 * - which means translators end up translating the same string over and over
 * - especially if we use shared libraries ( a la oik ) or Composer dependency
 * 
 * - Users don't really care who did the translation
 * - They just want it in their language
 * 
 * - If we use oik_require_lib() 
 * - AND the library file knows that translation will be required
 * - then we can hook into `gettext` to provide Just In Time - Just Translate It logic
 * 
 * @TODO Support `gettext_with_context` in the future, if required.
 * 
 */
class Tests_libs_l10n extends BW_UnitTestCase {

	function setUp() {
		oik_require_lib( "oik-l10n" );
		//oik_require( "libs/oik-l10n.php", "oik-libs" );
	}
	
	/**
	 * Tests Just Translate It logic
	 * 
	 * Note: Since other tests may use JTI logic we need to unload the domains manually
	 * and disable its processing.
	 */
	function test_oik_l10n_enable_jti() {
		$domain = null;
		unload_textdomain( "oik-libs" );
		unload_textdomain( $domain );
		oik_l10n_enable_jti( false );
		
		
		$loaded = oik_l10n_domain_loaded( "oik-libs" );
		$this->assertFalse( $loaded );
		$loaded = oik_l10n_domain_loaded( $domain );
		$this->assertFalse( $loaded );
		
		oik_l10n_trace();
		$translated = __( "None", null );
		$this->assertEquals( "None", $translated );
	
    oik_l10n_enable_jti();
    unload_textdomain( $domain );
		$loaded = oik_l10n_domain_loaded( $domain );
		$this->assertFalse( $loaded );

		switch_to_locale( "bb_BB" );
		$loaded = load_textdomain( "oik-libs", dirname( __DIR__) . "/languages/oik-libs-bb_BB.mo" );
    $this->assertTrue( $loaded );
		
		oik_l10n_trace( false );
		
		oik_l10n_load_domain( $domain );
		$loaded = oik_l10n_domain_loaded( $domain );
		$this->assertTrue( $loaded );
		
		oik_l10n_trace( false );
		oik_l10n_trace( true );
		
		$translated = __( "None", null );
		$this->assertEquals( "Nnoe", $translated );
		
		oik_l10n_trace( $domain );
		
		// We can disable the processing using the API but need to unload the domains manually
		
		oik_l10n_enable_jti( false );
		unload_textdomain( "oik-libs" );
		unload_textdomain( $domain );
		
	}
	
		
	
	
}
	
		

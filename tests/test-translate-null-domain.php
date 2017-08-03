<?php // (C) Copyright Bobbing Wide 2017

/**
 * @package libs-translate
 * 
 * Tests translations of strings from the oik-libs domain
 */
class Tests_translate_null_domain extends BW_UnitTestCase {

	function setUp() {
		unload_textdomain( "oik" );
		unload_textdomain( "oik-libs" );
		unload_textdomain( null );
	}
	
	/**
	 * Demonstrates that we can do translation the normal way.
	 * 
	 * Note: Switch to locale is pretty useless with regards to loading the language files for the new domain ( see TRAC 39210 ).
	 * We have to load the bb_BB file ourselves.
	 * 
	 */
	function test_translate_null_domain() {
		$translated = __( "None", null );
		$this->assertEquals( "None", $translated );
	
		switch_to_locale( "bb_BB" );
		$loaded = load_textdomain( "oik-libs", dirname( __DIR__) . "/languages/oik-libs-bb_BB.mo" );
		$this->assertTrue( $loaded );
		$translated = __( "None", "oik-libs" );
		$this->assertEquals( "Nnoe", $translated );
		
		unload_textdomain( "oik-libs" );
		unload_textdomain( null );
		
	}
	
	/**
	 * Tests load and unload with null domain
	 * 
	 * This test demonstrates that null is a valid value for $domain
	 * How can we use this to determine how to proceed?
	 * 
	 */
	function test_load_null_domain() {
		$loaded = load_textdomain( null, dirname( __DIR__) . "/languages/oik-libs-bb_BB.mo" );
		$this->assertTrue( $loaded );
		$loaded = $this->null_domain_loaded();
		$this->assertTrue( $loaded );
		
		$translated = __( "None", null );
		$this->assertEquals( "Nnoe", $translated );
		
		unload_textdomain( "oik-libs" );
		unload_textdomain( null );
		$loaded = $this->null_domain_loaded();
		$this->assertFalse( $loaded );
		
		global $l10n;
		bw_trace2( array_keys( $l10n) , "array keys" );
		//bw_trace2( $l10n, "l10n after null load" );
	}
	
	/**
	 * This test allows us to intercept translations for the null text domain
	 * 
	 * So how do we populate the null text domain?
	 */
	function test_hook_gettext_null_domain() {
	
		$loaded = load_textdomain( null, dirname( __DIR__) . "/languages/oik-libs-bb_BB.mo" );
		add_filter( "gettext", array( $this, "hook_gettext" ), 10, 3 );
		
		$translated = __( "None", null );
		$this->assertEquals( "Nnoe", $translated );
		
		remove_filter( "gettext", array( $this, "hook_gettext" ), 10 );
		
		$this->trace_l10n();
		
		unload_textdomain( "oik-libs" );
		unload_textdomain( null );
	}
	
	/**
	 * Implements deferred translation for the null domain
	 * 
	 * @TODO Should we use oik-libs instead of the null domain?
	 */
	function hook_gettext( $translation, $text, $domain ) {
		if ( null === $domain ) {
			$try_again = !$this->null_domain_loaded();
			bw_trace2();
			$this->load_null_domain();
			if ( $try_again && $this->null_domain_loaded() ) {
				$translation = translate( $text, $domain );
			}
		}
		return $translation;
	}
	
	/**
	 * Checks if the domain is loaded
	 */
	function null_domain_loaded() {
		global $l10n;
		return isset( $l10n[ null ] );
	}
	
	/** 
	 * Loads the anonymous ( null ) domain
	 * 
	 * Just In Time ( JIT ) loading of the Just Translate It ( JTI ) domain
	 * 
	 * Merges the translation strings from each domain.
	 */
	function load_null_domain() {
		global $l10n;
		$null_domain = new MO();
		foreach (  $l10n as $domain => $MO ) {
			$null_domain->merge_with( $MO );
		}
		$l10n[ null ] = &$null_domain;
		$this->trace_l10n( true );
	}
	
	/**
	 * Traces $l10n
	 */
	function trace_l10n( $details=false ) {
		global $l10n;
		bw_trace2( array_keys( $l10n) , "array keys" );
		if ( $details ) {
			bw_trace2( $l10n, "l10n after null load" );
		}
	}
		
	
	
}
	
		

<?php // (C) Copyright Bobbing Wide 2017

/** 
 * Unit tests for the libs\oik-sc-help.php file
 * 
 * Note: Some of the logic in libs\oik-sc-help.php is tested implicitely by tests in tests\test-libs-bobbfunc.php
 * Other functions are tested in the oik base plugin.
 *
 */
class Tests_libs_oik_sc_help extends BW_UnitTestCase {

	/** 
	 * set up logic
	 * 
	 * - ensure any database updates are rolled back
	 * - we need oik-googlemap to load the functions we're testing
	 */
	function setup(): void {
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
	
	function test_sc_classes() {
		$this->switch_to_locale( "en_GB" );
		$array = _sc_classes();
		$html = $this->arraytohtml( $array, true );
    //$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	
	function test_sc_classes_bb_BB() {
		$this->switch_to_locale( "bb_BB" );
		$array = _sc_classes();
		$html = $this->arraytohtml( $array ); 
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	/**
	 * Reduce a print_r'ed string
	 *
	 * print_r's an array then removes unwanted white space
	 */
	function arraytohtml( $array ) {
		$string = print_r( $array, true );
		$again = explode( "\n", $string );
		$again = array_map( "trim", $again );
		$string = implode( "\n", $again );
		return $string;
	}
	
	/**
	 * 
	 */
	function test_sc_posts() {
		update_option( "posts_per_page", "10" );
		$this->switch_to_locale( "en_GB" );
		$array = _sc_posts();
		$html = $this->arraytohtml( $array, true );
    //$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	
	/**
	 * 
	 */
	function test_sc_posts_bb_BB() {
		update_option( "posts_per_page", "10" );
		$this->switch_to_locale( "bb_BB" );
		$array = _sc_posts();
		$html = $this->arraytohtml( $array, true );
    //$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	/**
	 * 
	 */
	function test_sc_thumbnail() {
		$this->switch_to_locale( "en_GB" );
		$array = _sc_thumbnail();
		$html = $this->arraytohtml( $array, true );
    //$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	/**
	 * Note: nnn and wxh do not translate in bb_BB 
	 * 
	 */
	function test_sc_thumbnail_bb_BB() {
		$this->switch_to_locale( "bb_BB" );
		$array = _sc_thumbnail();
		$html = $this->arraytohtml( $array, true );
    //$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
		
	}
	
	/**
	 */ 
	function test_caption__help() {
		$this->switch_to_locale( "en_GB" );
		$html = caption__help( null );
    //$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	/**
	 */ 
	function test_caption__help_bb_BB() {
		$this->switch_to_locale( "bb_BB" );
		$html = caption__help( null );
    //$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	function test_caption__example() {
		$this->switch_to_locale( "en_GB" );
		$html = bw_ret( caption__example( null ) );
    //$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	function test_caption__example_bb_BB() {
		$this->switch_to_locale( "bb_BB" );
		$html = bw_ret( caption__example( null ) );
    //$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	function test_caption__syntax() {
		$this->switch_to_locale( "en_GB" );
		$array = caption__syntax();
		$html = $this->arraytohtml( $array, true );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	function test_caption__syntax_bb_BB() {
		$this->switch_to_locale( "bb_BB" );
		$array = caption__syntax();
		$html = $this->arraytohtml( $array, true );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	/**
	 */ 
	function test_gallery__help() {
		$this->switch_to_locale( "en_GB" );
		$html = gallery__help( null );
    //$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	/**
	 */ 
	function test_gallery__help_bb_BB() {
		$this->switch_to_locale( "bb_BB" );
		$html = gallery__help( null );
    //$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	function test_gallery__syntax() {
		$this->switch_to_locale( "en_GB" );
		$array = gallery__syntax();
		$html = $this->arraytohtml( $array, true );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	function test_gallery__syntax_bb_BB() {
		$this->switch_to_locale( "bb_BB" );
		$array = gallery__syntax();
		$html = $this->arraytohtml( $array, true );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	function test_embed__help() {
		$this->switch_to_locale( "en_GB" );
		$html = embed__help( null );
    //$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	function test_embed__help_bb_BB() {
		$this->switch_to_locale( "bb_BB" );
		$html = embed__help( null );
    //$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	function test_embed__example() {
		$this->switch_to_locale( "en_GB" );
		$this->delete_oembed_cache();
		$this->disable_jetpack_youtube_embed();
		$html = bw_ret( embed__example() );
    //$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	function test_embed__example_bb_BB() {
		$this->switch_to_locale( "bb_BB" );
		$this->delete_oembed_cache();
		$this->disable_jetpack_youtube_embed();
		$html = bw_ret( embed__example() );
    //$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	function test_embed__syntax() {
		$this->switch_to_locale( "en_GB" );
		$array = embed__syntax();
		$html = $this->arraytohtml( $array, true );
		$this->assertArrayEqualsFile( $html );
	}
	
	function test_embed__syntax_bb_BB() {
		$this->switch_to_locale( "bb_BB" );
		$array = embed__syntax();
		$html = $this->arraytohtml( $array, true );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	function test_audio__syntax() {
		$this->switch_to_locale( "en_GB" );
		$array = audio__syntax();
		$html = $this->arraytohtml( $array, true );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	function test_audio__syntax_bb_BB() {
		$this->switch_to_locale( "bb_BB" );
		$array = audio__syntax();
		$html = $this->arraytohtml( $array, true );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	function test_video__syntax() {
		$this->switch_to_locale( "en_GB" );
		$array = video__syntax();
		$html = $this->arraytohtml( $array, true );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	function test_video__syntax_bb_BB() {
		$this->switch_to_locale( "bb_BB" );
		$array = video__syntax();
		$html = $this->arraytohtml( $array, true );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	
	function test_playlist__syntax() {
		//$this->setExpectedDeprecated( "bw_translate" );
		$this->switch_to_locale( "en_GB" );
		$array = playlist__syntax();
		$html = $this->arraytohtml( $array, true );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	function test_playlist__syntax_bb_BB() {
		//$this->setExpectedDeprecated( "bw_translate" );
		$this->switch_to_locale( "bb_BB" );
		$array = playlist__syntax();
		$html = $this->arraytohtml( $array, true );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	/**
	 * Disables forced usage of oembed_cache posts
	 * 
	 * This solution doesn't work since the caching logic may still use a recently cached post, 
	 * which may contain different content from what's expected.
	 *
	 * Of course, the test may still fail in the future for a similar reason.
	 * 
	 * @TODO Consider forcing the use of cached content, where we control the content of the oembed_cache post.
	 */
	function disable_wp_embed_caching() {
		$wp_embed = $GLOBALS['wp_embed'];
		$this->assertTrue( $wp_embed->usecache );
		$wp_embed->usecache = false;
	}
	
	/**
	 * We can't unregister the oembed_cache post type since it's builtin
	 */
	function deregister_oembed_cache() {
		$result = unregister_post_type( "oembed_cache" );
		print_r( $result );
		$this->assertTrue( $result );
	}
	
	/**
	 * Update any oembed_cache posts to "draft" status
	 * 
	 */
	function delete_oembed_cache() {
		$posts = get_posts( array( "post_type" => "oembed_cache", "numberposts" => -1 ) );
		if ( count( $posts ) ) {
			foreach ( $posts as $post ) {
				$post->post_status = "draft";
				wp_update_post( $post );
			}
		}
	}
	
	/**
	 * Disable Jetpack shortcode embed for YouTube
	 * 
	 * During init processing Jetpack registers some handlers that alter the HTML generated by YoutTube embeds.	 
	 * We need to disable this for environment independence; the tests were originally written for an environment without Jetpack.
	 */
	function disable_jetpack_youtube_embed() {
		wp_embed_unregister_handler( 'wpcom_youtube_embed_crazy_url' );
	}

}

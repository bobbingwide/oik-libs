<?php

/**
 * @package oik-libs
 * @copyright (C) Copyright Bobbing Wide 2021
 *
 * Tests for logic libs/class-oik-attachment-contents.php
 */
class Tests_libs_class_oik_attachment_contents extends BW_UnitTestCase {

	/**
	 * set up logic
	 *
	 * - ensure any database updates are rolled back
	 * - load the shared library file containing the Oik_attachment_updates class
	 */
	function setup(): void {
		parent::setUp();
	}

	/**
	 * Ensures the shared library can be loaded and that it's the latest version
	 * as defined by the constant.
	 */

	function load_lib() {
		$lib = oik_require_lib( "class-oik-attachment-contents" );
		$this->assertStringEndsWith('libs/class-oik-attachment-contents.php', $lib );
		$this->assertEquals( '0.0.2', CLASS_OIK_ATTACHMENT_CONTENTS_INCLUDED );
	}

	function test_load_lib() {
		$this->load_lib();
		$this->assertTrue( class_exists( 'Oik_attachment_contents'));
	}

	/**
	 * Once the library has been loaded can we assume that we can call the constructor?
	 * How do we know that test_load_lib() has been called?
	 */
	function test__construct() {
		$oik_attachment_contents = new Oik_attachment_contents();
		$this->assertInstanceOf( 'Oik_attachment_contents', $oik_attachment_contents );
	}

	/**
	 * Tests when $atts is null
	 */
	function test_get_content() {
		$oik_attachment_contents = new Oik_attachment_contents();
		$content = $oik_attachment_contents->get_content( null, null );
		$this->AssertNull( $content );
		$content = $oik_attachment_contents->get_content( null, "" );
		$this->AssertNull( $content );
		$content = $oik_attachment_contents->get_content( null, 'A,B,C\n1,2,3' );
		$this->AssertEquals( 'A,B,C\n1,2,3', $content );
	}


	/**
	 * Tests when $atts is null.
	 * We can't test get_contents() separately since it doesn't return the contents_array.
	 */
	function test_get_contents_array() {
		$oik_attachment_contents = new Oik_attachment_contents();
		//$content = $oik_attachment_contents->get_content( null, null );
		$contents_array = $oik_attachment_contents->get_contents_array( null, null );
		$this->assertEquals([ '' ], $contents_array );
		//$content = $oik_attachment_contents->get_content( null, "" );
		$contents_array = $oik_attachment_contents->get_contents_array( null, "");
		$this->assertEquals([ '' ], $contents_array );
		//$content = $oik_attachment_contents->get_content( null, 'A,B,C\n1,2,3' );
		$contents_array = $oik_attachment_contents->get_contents_array( null, 'A,B,C\n1,2,3');
		$this->AssertEquals( [ 'A,B,C', '1,2,3'], $contents_array );
		$contents_array = $oik_attachment_contents->get_contents_array( null, "A,B,C\n1,2,3");
		$this->AssertEquals( [ 'A,B,C', '1,2,3'], $contents_array );
		// <br /> has to be immediately before the new line.
		// The code allows for \n appearing in a string enclosed in single quotes. - which isn't actually a new line character
		$contents_array = $oik_attachment_contents->get_contents_array( null, 'A,B,C<br />\n1,2,3');
		$this->AssertEquals( [ 'A,B,C', '1,2,3'], $contents_array );
		$contents_array = $oik_attachment_contents->get_contents_array( null, "A,B,C<br />\n1,2,3");
		$this->AssertEquals( [ 'A,B,C', '1,2,3'], $contents_array );

	}

	/**
	 * Note: If the file_name returned is null then we have to call bw_ret() to clear any bw_echo'ed output.
	 * If we don't do this subsequent tests may fail.
	 */
	function test_get_file_name() {
		$oik_attachment_contents=new Oik_attachment_contents();
		$file_name  =$oik_attachment_contents->get_file_name( '/wp-config.php' );
		$this->assertNull( $file_name );
		$html = bw_ret();
		$this->assertEquals( 'File name not allowed: /wp-config.php', $html );

		$file_name  =$oik_attachment_contents->get_file_name( '/wp-config-sample.php' );
		$this->assertEquals( ABSPATH . 'wp-config-sample.php', $file_name );

		$file_name  =$oik_attachment_contents->get_file_name( 'https://example.com' );
		$this->assertEquals( 'https://example.com', $file_name );

		$file_name  =$oik_attachment_contents->get_file_name( 'examples/you-wont-find-me' );
		$this->assertEquals( 'examples/you-wont-find-me', $file_name );
	}

	function test_load_content() {
		$oik_attachment_contents=new Oik_attachment_contents();
		/** We don't expect to find post ID 0 so there should be no attached file and no message */
		$oik_attachment_contents->load_content( 0 );
		$html = bw_ret();
		$this->assertNull( $html );

		/** We don't expect to find an attached file for post ID 1 since it's a post not an attachment */
		$oik_attachment_contents->load_content( 1 );
		$html = bw_ret();
		$this->assertNull( $html );

		$oik_attachment_contents->load_content( 'examples/you-wont-find-me' );
		$html = bw_ret();
		$this->assertEquals( "File does not exist: examples/you-wont-find-me", $html );
	}

	/**
	 * Here I'm assuming that test__sc__help.html file contains "?" followed by CRLF
	 */
	function test_get_contents_array_from_src() {
		$oik_attachment_contents=new Oik_attachment_contents();
		$contents_array = $oik_attachment_contents->get_contents_array( ['src' => '/wp-content/plugins/oik-libs/tests/data/en_GB/test__sc__help.html'], null );
		$this->assertEquals( [ '?' ], $contents_array );
		$contents_array = $oik_attachment_contents->get_contents_array( ['src' => '/wp-content/plugins/oik-libs/tests/data/en_GB/test__sc__help.html'], '' );
		$this->assertEquals( [ '?' ], $contents_array );
		$contents_array = $oik_attachment_contents->get_contents_array( ['src' => '/wp-content/plugins/oik-libs/tests/data/en_GB/test__sc__help.html'], 'overrides src' );
		$this->assertEquals( [ 'overrides src' ], $contents_array );
	}


}

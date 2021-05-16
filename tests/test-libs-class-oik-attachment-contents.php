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
		$this->assertEquals( '0.0.1', CLASS_OIK_ATTACHMENT_CONTENTS_INCLUDED );
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

	function test_get_content() {
		$oik_attachment_contents = new Oik_attachment_contents();
		$content = $oik_attachment_contents->get_content( null, null );
		$this->AssertNull( $content );
		$content = $oik_attachment_contents->get_content( null, "" );
		$this->AssertNull( $content );
		$content = $oik_attachment_contents->get_content( null, 'A,B,C\n1,2,3' );
		$this->AssertEquals( 'A,B,C\n1,2,3', $content );
	}

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



}
